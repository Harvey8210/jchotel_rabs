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
    $group_number = time(); // Using timestamp as group number

    $conn->begin_transaction();

    try {
        foreach ($bookings as $booking) {
            $stmt = $conn->prepare("INSERT INTO billing (group_number, reservation_id, total_amount, status) VALUES (?, ?, ?, 'unpaid')");
            $stmt->bind_param(
                "iid",
                $group_number,
                $booking['reservation_id'],
                $booking['total_price']
            );

            if (!$stmt->execute()) {
                throw new Exception("Failed to insert billing data for reservation ID: " . $booking['reservation_id']);
            }

            // Update reservation status to confirmed
            $stmt = $conn->prepare("UPDATE reservations SET status = 'confirmed' WHERE reservation_id = ?");
            $stmt->bind_param("i", $booking['reservation_id']);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update reservation status for ID: " . $booking['reservation_id']);
            }
        }

        $conn->commit();
        echo json_encode([
            'success' => true, 
            'message' => 'Billing data successfully inserted and reservations confirmed.',
            'group_number' => $group_number
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}