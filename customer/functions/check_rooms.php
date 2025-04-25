<?php
session_start();
require_once '../../config/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $room_type = $_POST['room_type'];
    $guests = $_POST['guests'];
    
    // Get available rooms
    $rooms_sql = "SELECT r.*, 
                  CASE 
                    WHEN EXISTS (
                        SELECT 1 FROM reservations res 
                        WHERE res.room_id = r.room_id 
                        AND (
                            (res.check_in BETWEEN ? AND ?) 
                            OR (res.check_out BETWEEN ? AND ?)
                            OR (res.check_in <= ? AND res.check_out >= ?)
                        )
                    ) THEN 'booked'
                    ELSE 'available'
                  END as availability,
                  r.image as room_image
                  FROM rooms r 
                  WHERE r.type = ?
                  ORDER BY r.price ASC";
    
    $stmt = $conn->prepare($rooms_sql);
    $stmt->bind_param("sssssss", $check_in, $check_out, $check_in, $check_out, $check_in, $check_out, $room_type);
    $stmt->execute();
    $rooms_result = $stmt->get_result();
    
    $available_rooms = [];
    $booked_rooms = [];
    
    while ($room = $rooms_result->fetch_assoc()) {
        if ($room['availability'] == 'available') {
            $available_rooms[] = $room;
        } else {
            $booked_rooms[] = $room;
        }
    }
    
    // Calculate number of nights
    $check_in_date = new DateTime($check_in);
    $check_out_date = new DateTime($check_out);
    $nights = $check_in_date->diff($check_out_date)->days;
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'available_rooms' => $available_rooms,
        'booked_rooms' => $booked_rooms,
        'nights' => $nights
    ]);
    exit;
}
?> 