<?php
require_once __DIR__ . '/../../config/db_connection.php';

/**
 * Get customer profile information
 * @param mysqli $conn Database connection
 * @param int $customer_id Customer ID
 * @return array|false Customer profile data or false if not found
 */
function getCustomerProfile($conn, $customer_id) {
    $stmt = $conn->prepare("
        SELECT c.*
        FROM customers c 
        WHERE c.customer_id = ?
    ");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Get customer's active reservations
 * @param mysqli $conn Database connection
 * @param int $customer_id Customer ID
 * @return mysqli_result Result set of active reservations
 */
function getCustomerActiveReservations($conn, $customer_id) {
    $stmt = $conn->prepare("
        SELECT r.*, rm.room_number, rm.type, rm.price
        FROM reservations r
        JOIN rooms rm ON r.room_id = rm.room_id
        WHERE r.customer_id = ? AND r.status IN ('confirmed', 'checked-in')
        ORDER BY r.check_in DESC
    ");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * Get customer's past reservations
 * @param mysqli $conn Database connection
 * @param int $customer_id Customer ID
 * @return mysqli_result Result set of past reservations
 */
function getCustomerPastReservations($conn, $customer_id) {
    $stmt = $conn->prepare("
        SELECT r.*, rm.room_number, rm.type, rm.price
        FROM reservations r
        JOIN rooms rm ON r.room_id = rm.room_id
        WHERE r.customer_id = ? AND r.status IN ('completed', 'cancelled')
        ORDER BY r.check_out DESC
    ");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * Get customer's loyalty points
 * @param mysqli $conn Database connection
 * @param int $customer_id Customer ID
 * @return int Number of loyalty points
 */
function getCustomerLoyaltyPoints($conn, $customer_id) {
    // For now, return 0 since loyalty points system is not yet implemented
    return 0;
    
    /* Commented out until loyalty_points table is created
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(points), 0) as total_points
        FROM loyalty_points
        WHERE customer_id = ?
    ");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['total_points'];
    */
}

/**
 * Get customer's billing history
 * @param mysqli $conn Database connection
 * @param int $customer_id Customer ID
 * @return mysqli_result Result set of billing history
 */
function getCustomerBillingHistory($conn, $customer_id) {
    $stmt = $conn->prepare("
        SELECT b.*, r.check_in, r.check_out, rm.room_number, rm.type
        FROM billing b
        JOIN reservations r ON b.reservation_id = r.reservation_id
        JOIN rooms rm ON r.room_id = rm.room_id
        WHERE r.customer_id = ?
        ORDER BY b.created_at DESC
    ");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    return $stmt->get_result();
} 