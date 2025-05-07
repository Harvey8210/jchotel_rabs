<?php
session_start();
require_once '../config/db_connection.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log the request method and raw input
file_put_contents('debug_log.txt', "Request Method: " . $_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND);
file_put_contents('debug_log.txt', "Raw Input: " . file_get_contents('php://input') . "\n", FILE_APPEND);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Log decoded data
    file_put_contents('debug_log.txt', "Decoded Data: " . print_r($data, true) . "\n", FILE_APPEND);

    // Validate session
    if (!isset($_SESSION['customer_id'])) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit;
    }

    // Validate required fields
    if (!isset($data['room_id']) || !isset($data['check_in']) || !isset($data['check_out'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    // Ensure the room_id is correctly retrieved from the JSON payload
    if (!isset($data['room_id']) || empty($data['room_id'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid room ID']);
        exit;
    }

    // Sanitize and validate the room_id
    $roomId = intval($data['room_id']);
    if ($roomId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid room ID']);
        exit;
    }

    // Sanitize inputs
    $customerId = intval($_SESSION['customer_id']);
    $checkIn = $conn->real_escape_string($data['check_in']);
    $checkOut = $conn->real_escape_string($data['check_out']);

    // Add notes column to the reservations table if it doesn't exist
    if (!isset($data['notes'])) {
        $data['notes'] = '';
    }

    $notes = $conn->real_escape_string($data['notes']);

    // Log sanitized data
    file_put_contents('debug_log.txt', "Sanitized Data: room_id=$roomId, customer_id=$customerId, check_in=$checkIn, check_out=$checkOut, notes=$notes\n", FILE_APPEND);

    // Insert the reservation
    $insertQuery = "INSERT INTO reservations (room_id, customer_id, check_in, check_out, notes, status)
                   VALUES (?, ?, ?, ?, ?, 'pending')";
    
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("iisss", $roomId, $customerId, $checkIn, $checkOut, $notes);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Reservation created successfully',
            'reservation_id' => $conn->insert_id
        ]);
        
        // Log success
        file_put_contents('debug_log.txt', "Reservation created successfully. ID: " . $conn->insert_id . "\n", FILE_APPEND);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create reservation: ' . $conn->error]);
        
        // Log error
        file_put_contents('debug_log.txt', "Database Error: " . $conn->error . "\n", FILE_APPEND);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>