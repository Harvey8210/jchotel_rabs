<?php
session_start();
require_once '../config/db_connection.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to continue']);
    exit;
}

// Get POST data
$raw_data = file_get_contents('php://input');
$data = json_decode($raw_data, true);

// Debug log
error_log("Received data: " . print_r($data, true));

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data received: ' . $raw_data]);
    exit;
}

try {
    $conn->begin_transaction();

    // Insert into reservations table for each booking
    $reservation_ids = [];
    foreach ($data['bookings'] as $booking) {
        // Debug log
        error_log("Processing booking: " . print_r($booking, true));

        // First, get the room_id from the rooms table using room_number
        $stmt = $conn->prepare("SELECT room_id FROM rooms WHERE room_number = ?");
        $stmt->bind_param("s", $booking['roomNumber']);
        $stmt->execute();
        $result = $stmt->get_result();
        $room = $result->fetch_assoc();
        
        if (!$room) {
            throw new Exception("Room not found: " . $booking['roomNumber']);
        }

        $room_id = $room['room_id'];

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

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $notes = "Room Type: " . $booking['roomType'] . ", Price: ₱" . $booking['roomPrice'];
        
        $stmt->bind_param(
            "iisss", 
            $data['customer_id'],
            $room_id,
            $booking['checkIn'],
            $booking['checkOut'],
            $notes
        );

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $reservation_ids[] = $conn->insert_id;
    }

    // Calculate total amount and points value
    $total_amount = 0;
    foreach ($data['bookings'] as $booking) {
        $total_amount += floatval($booking['roomPrice']);
    }

    // Insert into billing table
    $stmt = $conn->prepare("
        INSERT INTO billing (
            reservation_id,
            total_amount,
            discount,
            loyalty_points_used,
            status
        ) VALUES (?, ?, ?, ?, 'unpaid')
    ");

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    // Use the first reservation_id for billing
    $points_redeemed = isset($data['points_redeemed']) ? intval($data['points_redeemed']) : 0;
    $discount = $points_redeemed; // 1 point = 1 peso discount
    
    $stmt->bind_param(
        "iddi",
        $reservation_ids[0],
        $total_amount,
        $discount,
        $points_redeemed
    );

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $billing_id = $conn->insert_id;

    // Update loyalty points if points were used
    if ($points_redeemed > 0) {
        $stmt = $conn->prepare("
            INSERT INTO loyalty_transactions (
                customer_id,
                points,
                status,
                description
            ) VALUES (?, ?, 'redeemed', ?)
        ");

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $description = "Points redeemed for booking #" . $billing_id;
        $stmt->bind_param(
            "iis",
            $data['customer_id'],
            $points_redeemed,
            $description
        );

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Booking processed successfully',
        'reference' => $billing_id
    ]);

} catch (Exception $e) {
    $conn->rollback();
    error_log("Error in process_booking.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error processing booking: ' . $e->getMessage()
    ]);
}
?> 