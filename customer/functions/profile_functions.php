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