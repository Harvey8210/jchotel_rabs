<?php
session_start();
require_once '../config/db_connection.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in as frontdesk staff
if (!isset($_SESSION['staff_id']) || $_SESSION['role'] !== 'frontdesk') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get POST data
$raw_data = file_get_contents('php://input');
$data = json_decode($raw_data, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data received']);
    exit;
}

try {
    $conn->begin_transaction();

    // First, insert customer information
    $stmt = $conn->prepare("
        INSERT INTO customers (
            name,
            contact_number,
            created_at
        ) VALUES (?, ?, NOW())
    ");

    $stmt->bind_param("ss", $data['customer_name'], $data['contact_number']);
    $stmt->execute();
    $customer_id = $conn->insert_id;

    // Insert into reservations table
    $stmt = $conn->prepare("
        INSERT INTO reservations (
            customer_id,
            room_id,
            check_in,
            check_out,
            status,
            notes
        ) VALUES (?, ?, ?, ?, 'pending', ?)
    ");

    $stmt->bind_param(
        "iisss",
        $customer_id,
        $data['room_id'],
        $data['check_in'],
        $data['check_out'],
        $data['notes']
    );

    $stmt->execute();
    $reservation_id = $conn->insert_id;

    // Insert into billing table
    $stmt = $conn->prepare("
        INSERT INTO billing (
            reservation_id,
            total_amount,
            discount,
            loyalty_points_used,
            status
        ) VALUES (?, ?, 0, 0, ?)
    ");

    $stmt->bind_param(
        "ids",
        $reservation_id,
        $data['total_amount'],
        $data['payment_status']
    );

    $stmt->execute();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Walk-in reservation saved successfully',
        'reservation_id' => $reservation_id
    ]);

} catch (Exception $e) {
    $conn->rollback();
    error_log("Error in process_walk_in.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error processing reservation: ' . $e->getMessage()
    ]);
}
?> 