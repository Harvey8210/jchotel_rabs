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


/**
 * Get available rooms by category
 * @param mysqli $conn Database connection
 * @param string $category Room category
 * @return mysqli_result Result set of available rooms
 */


/**
 * Get room statistics
 * @param mysqli $conn Database connection
 * @return array Room statistics
 */


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