<?php
require_once __DIR__ . '/../../config/db_connection.php';

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