<?php
require_once '../config/db_connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['bookings']) || !is_array($input['bookings'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
        exit;
    }

    $bookings = $input['bookings'];

    $conn->begin_transaction();

    try {
        foreach ($bookings as $booking) {
            $stmt = $conn->prepare("INSERT INTO billing (reservation_id, room_number, room_type, check_in, check_out, nights, total_price) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param(
                "issssis",
                $booking['reservation_id'],
                $booking['room_number'],
                $booking['room_type'],
                $booking['check_in'],
                $booking['check_out'],
                $booking['nights'],
                $booking['total_price']
            );

            if (!$stmt->execute()) {
                throw new Exception("Failed to insert billing data for reservation ID: " . $booking['reservation_id']);
            }
        }

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Billing data successfully inserted.']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}