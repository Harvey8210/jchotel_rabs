<?php
require_once __DIR__ . '/../../config/db_connection.php';

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