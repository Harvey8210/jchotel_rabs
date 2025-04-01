<?php
require_once __DIR__ . '/../../config/db_connection.php';

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