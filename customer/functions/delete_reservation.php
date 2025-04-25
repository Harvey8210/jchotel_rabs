<?php
session_start();
require_once '../../config/db_connection.php';

header('Content-Type: application/json');

function deleteReservation($conn, $reservation_id, $customer_id) {
    $query = "DELETE FROM reservations WHERE reservation_id = ? AND customer_id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("ii", $reservation_id, $customer_id);
        return $stmt->execute();
    } else {
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $reservation_id = $data['reservation_id'] ?? null;

    if ($reservation_id && isset($_SESSION['customer_id'])) {
        if (deleteReservation($conn, $reservation_id, $_SESSION['customer_id'])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to delete reservation.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid request.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}
?>