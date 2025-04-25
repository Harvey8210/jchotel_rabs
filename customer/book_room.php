<?php
require_once '../config/db_connection.php';
session_start();

header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['customer_id']) || $_SESSION['role'] !== 'customer') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

// Validate input
if (!isset($_POST['roomId'], $_POST['checkIn'], $_POST['checkOut'], $_POST['roomType'], $_POST['roomPrice'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit();
}

$roomId = intval($_POST['roomId']);
$checkIn = $_POST['checkIn'];
$checkOut = $_POST['checkOut'];
$customerId = $_SESSION['customer_id'];
$status = 'pending';

// Insert the reservation into the database
$query = "INSERT INTO reservations (room_id, customer_id, check_in, check_out, status) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Failed to prepare the statement.']);
    exit();
}

$stmt->bind_param('iisss', $roomId, $customerId, $checkIn, $checkOut, $status);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Room booked successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to book the room.']);
}

$stmt->close();
$conn->close();
