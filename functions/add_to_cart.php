<?php
session_start();
require_once '../config/db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['customer_id'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Please login to add rooms to cart'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = $_POST['room_id'] ?? null;
    $check_in = $_POST['check_in'] ?? null;
    $check_out = $_POST['check_out'] ?? null;
    $customer_id = $_SESSION['customer_id'];

    if (!$room_id || !$check_in || !$check_out) {
        echo json_encode([
            'success' => false, 
            'message' => 'Missing required information'
        ]);
        exit;
    }

    // Check if room is still available
    $availability_query = "SELECT * FROM rooms 
        WHERE room_id = ? AND room_id NOT IN (
            SELECT room_id FROM reservations 
            WHERE (check_in <= ? AND check_out >= ?)
            AND status != 'cancelled'
        )";
    
    $stmt = $conn->prepare($availability_query);
    $stmt->bind_param("iss", $room_id, $check_out, $check_in);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'This room is no longer available for the selected dates'
        ]);
        exit;
    }

    // Insert into reservations
    $query = "INSERT INTO reservations (customer_id, room_id, check_in, check_out, status) 
              VALUES (?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiss", $customer_id, $room_id, $check_in, $check_out);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Room has been added to your cart successfully!'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to add room to cart: ' . $conn->error
        ]);
    }
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid request method'
    ]);
}
