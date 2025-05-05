<?php
require_once '../config/db_connection.php';
header('Content-Type: application/json');

if (!isset($_POST['checkIn']) || !isset($_POST['checkOut'])) {
    die(json_encode(['error' => 'Missing required dates']));
}

$checkIn = $_POST['checkIn'];
$checkOut = $_POST['checkOut'];

// Query to fetch available rooms
$query = "SELECT r.*
          FROM rooms r
          WHERE NOT EXISTS (
              SELECT 1 FROM reservations res
              WHERE res.room_id = r.room_id
              AND res.status IN (pending', 'checked-out')
              AND (
                  (res.check_in <= ? AND res.check_out >= ?)
                  OR (res.check_in <= ? AND res.check_out >= ?)
                  OR (res.check_in >= ? AND res.check_out <= ?)
              )
          )";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die(json_encode(['error' => 'Database error: ' . $conn->error]));
}

$stmt->bind_param("ssssss", $checkIn, $checkIn, $checkOut, $checkOut, $checkIn, $checkOut);
$stmt->execute();
$result = $stmt->get_result();

$availableRooms = [];
while ($room = $result->fetch_assoc()) {
    $availableRooms[] = $room;
}

$response = [
    'available' => count($availableRooms) > 0,
    'availableRooms' => $availableRooms,
    'message' => count($availableRooms) > 0 
        ? "We have " . count($availableRooms) . " rooms available for your selected dates!" 
        : "Sorry, all rooms are booked for these dates. Please try different dates."
];

echo json_encode($response);
