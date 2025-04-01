<?php
require_once '../config/db_connection.php';

/**
 * Create a walk-in reservation
 * @param mysqli $conn Database connection
 * @param string $customer_name Customer's full name
 * @param string $phone Customer's phone number
 * @param int $room_id Selected room ID
 * @param string $check_in Check-in date and time
 * @param string $check_out Check-out date and time
 * @param string $notes Additional notes
 * @return int|false Reservation ID on success, false on failure
 */
function createWalkInReservation($conn, $customer_name, $phone, $room_id, $check_in, $check_out, $notes = '') {
    try {
        $conn->begin_transaction();
        
        // First create customer record
        $stmt = $conn->prepare("INSERT INTO customers (full_name, phone, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ss", $customer_name, $phone);
        $stmt->execute();
        $customer_id = $conn->insert_id;
        
        // Create reservation
        $stmt = $conn->prepare("INSERT INTO reservations (customer_id, room_id, check_in, check_out, status, notes, created_at) VALUES (?, ?, ?, ?, 'confirmed', ?, NOW())");
        $stmt->bind_param("iisss", $customer_id, $room_id, $check_in, $check_out, $notes);
        $stmt->execute();
        $reservation_id = $conn->insert_id;
        
        // Update room status
        $stmt = $conn->prepare("UPDATE rooms SET status = 'occupied' WHERE room_id = ?");
        $stmt->bind_param("i", $room_id);
        $stmt->execute();
        
        // Record in audit trail
        $action = "Walk-in reservation #" . $reservation_id . " created by frontdesk";
        $stmt = $conn->prepare("INSERT INTO audit_trails (user_id, action_taken) VALUES (?, ?)");
        $stmt->bind_param("is", $_SESSION['user_id'], $action);
        $stmt->execute();
        
        $conn->commit();
        return $reservation_id;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

/**
 * Process walk-in payment
 * @param mysqli $conn Database connection
 * @param int $reservation_id Reservation ID
 * @param float $amount_paid Amount paid
 * @param string $payment_method Payment method used
 * @return bool True on success, false on failure
 */
function processWalkInPayment($conn, $reservation_id, $amount_paid, $payment_method) {
    try {
        $conn->begin_transaction();
        
        // Get room price
        $stmt = $conn->prepare("
            SELECT r.price, rm.type 
            FROM reservations r 
            JOIN rooms rm ON r.room_id = rm.room_id 
            WHERE r.reservation_id = ?
        ");
        $stmt->bind_param("i", $reservation_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $room = $result->fetch_assoc();
        
        // Create billing record
        $stmt = $conn->prepare("INSERT INTO billing (reservation_id, total_amount, status, created_at) VALUES (?, ?, 'paid', NOW())");
        $stmt->bind_param("id", $reservation_id, $room['price']);
        $stmt->execute();
        $billing_id = $conn->insert_id;
        
        // Record payment
        $stmt = $conn->prepare("INSERT INTO payments (billing_id, amount_paid, payment_method, payment_date) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("ids", $billing_id, $amount_paid, $payment_method);
        $stmt->execute();
        
        // Record in audit trail
        $action = "Walk-in payment processed for reservation #" . $reservation_id;
        $stmt = $conn->prepare("INSERT INTO audit_trails (user_id, action_taken) VALUES (?, ?)");
        $stmt->bind_param("is", $_SESSION['user_id'], $action);
        $stmt->execute();
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

/**
 * Get available rooms by category
 * @param mysqli $conn Database connection
 * @param string $category Room category
 * @return mysqli_result Result set of available rooms
 */
function getAvailableRoomsByCategory($conn, $category) {
    $stmt = $conn->prepare("
        SELECT room_id, room_number, type, price, description
        FROM rooms 
        WHERE status = 'available' AND type = ?
        ORDER BY room_number
    ");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * Get room statistics
 * @param mysqli $conn Database connection
 * @return array Room statistics
 */
function getRoomStatistics($conn) {
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_rooms,
            SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available_rooms,
            SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) as occupied_rooms,
            SUM(CASE WHEN status = 'reserved' THEN 1 ELSE 0 END) as reserved_rooms,
            SUM(CASE WHEN status = 'maintenance' THEN 1 ELSE 0 END) as maintenance_rooms
        FROM rooms
    ");
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Get room categories with counts and prices
 * @param mysqli $conn Database connection
 * @return mysqli_result Result set of room categories
 */
function getRoomCategories($conn) {
    $stmt = $conn->prepare("
        SELECT type, COUNT(*) as count, MIN(price) as min_price, MAX(price) as max_price
        FROM rooms 
        GROUP BY type
        ORDER BY FIELD(type, 'standard', 'deluxe', 'superior', 'suite')
    ");
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * Get today's check-ins
 * @param mysqli $conn Database connection
 * @return mysqli_result Result set of today's check-ins
 */
function getTodaysCheckIns($conn) {
    $stmt = $conn->prepare("
        SELECT r.*, c.full_name, c.phone, rm.room_number, rm.type, rm.price
        FROM reservations r
        JOIN customers c ON r.customer_id = c.customer_id
        JOIN rooms rm ON r.room_id = rm.room_id
        WHERE DATE(r.check_in) = CURDATE() AND r.status = 'confirmed'
        ORDER BY r.check_in
    ");
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * Get today's check-outs
 * @param mysqli $conn Database connection
 * @return mysqli_result Result set of today's check-outs
 */
function getTodaysCheckOuts($conn) {
    $stmt = $conn->prepare("
        SELECT r.*, c.full_name, c.phone, rm.room_number, rm.type, rm.price
        FROM reservations r
        JOIN customers c ON r.customer_id = c.customer_id
        JOIN rooms rm ON r.room_id = rm.room_id
        WHERE DATE(r.check_out) = CURDATE() AND r.status = 'checked-in'
        ORDER BY r.check_out
    ");
    $stmt->execute();
    return $stmt->get_result();
} 