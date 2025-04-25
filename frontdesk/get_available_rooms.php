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

// Get parameters
$room_type = isset($_GET['type']) ? $_GET['type'] : '';
$check_in = isset($_GET['check_in']) ? $_GET['check_in'] : '';
$check_out = isset($_GET['check_out']) ? $_GET['check_out'] : '';

// Validate parameters
if (empty($room_type) || empty($check_in) || empty($check_out)) {
    echo json_encode(['error' => 'Missing required parameters']);
    exit;
}

try {
    // Query to get available rooms
    $query = "
        SELECT r.room_number, r.price, r.type
        FROM rooms r
        WHERE r.type = ?
        AND r.room_number NOT IN (
            SELECT rm.room_number
            FROM reservations res
            JOIN rooms rm ON res.room_id = rm.room_id
            WHERE (
                (res.check_in <= ? AND res.check_out >= ?) OR
                (res.check_in <= ? AND res.check_out >= ?) OR
                (res.check_in >= ? AND res.check_out <= ?)
            )
            AND res.status != 'cancelled'
        )
        ORDER BY CAST(r.room_number AS UNSIGNED)
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssss", 
        $room_type,
        $check_out, $check_in,  // First overlap check
        $check_out, $check_in,  // Second overlap check
        $check_in, $check_out   // Third overlap check
    );
    $stmt->execute();
    $result = $stmt->get_result();

    $rooms = [];
    while ($row = $result->fetch_assoc()) {
        $rooms[] = [
            'room_number' => $row['room_number'],
            'price' => $row['price'],
            'type' => $row['type']
        ];
    }

    echo json_encode($rooms);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?> 