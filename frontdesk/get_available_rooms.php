<?php
session_start();
require_once '../config/db_connection.php';
require_once 'functions.php';

// Check if frontdesk is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'frontdesk') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

// Validate category parameter
if (!isset($_GET['category']) || empty($_GET['category'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Category parameter is required']);
    exit();
}

$category = $_GET['category'];

// Validate category value
$valid_categories = ['standard', 'deluxe', 'superior', 'suite'];
if (!in_array(strtolower($category), $valid_categories)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid category']);
    exit();
}

// Get available rooms for the selected category
$rooms = getAvailableRoomsByCategory($conn, $category);

// Convert result to array
$rooms_array = [];
while ($room = $rooms->fetch_assoc()) {
    $rooms_array[] = $room;
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($rooms_array); 