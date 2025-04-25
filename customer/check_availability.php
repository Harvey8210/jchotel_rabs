<?php
require_once '../config/db_connection.php';
header('Content-Type: application/json');

if (!isset($_POST['checkIn']) || !isset($_POST['checkOut'])) {
    die(json_encode(['error' => 'Missing required dates']));
}

$checkIn = $_POST['checkIn'];
$checkOut = $_POST['checkOut'];

// Query to check conflicting reservations
$query = "SELECT COUNT(r.reservation_id) as conflict_count
          FROM reservations r 
          WHERE r.status NOT IN ('confirmed', 'checked-out')
          AND (
              (r.check_in <= ? AND r.check_out >= ?)
              OR (r.check_in <= ? AND r.check_out >= ?)
              OR (r.check_in >= ? AND r.check_out <= ?)
          )";

$stmt = $conn->prepare($query);
$stmt->bind_param("ssssss", $checkIn, $checkIn, $checkOut, $checkOut, $checkIn, $checkOut);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Get total rooms count
$totalRooms = $conn->query("SELECT COUNT(*) as total FROM rooms")->fetch_assoc()['total'];
$availableRooms = $totalRooms - $row['conflict_count'];

$response = [
    'available' => $availableRooms > 0,
    'availableRooms' => $availableRooms,
    'totalRooms' => $totalRooms,
    'message' => $availableRooms > 0 
        ? "We have {$availableRooms} rooms available for your selected dates!" 
        : "Sorry, all rooms are booked for these dates. Please try different dates."
];

echo json_encode($response);
