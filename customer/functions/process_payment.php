<?php
session_start();
require_once '../../config/db_connection.php';

header('Content-Type: application/json');

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/payment_error.log');

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to continue']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['reservations']) || empty($data['reservations'])) {
            throw new Exception('No reservations selected');
        }

        $conn->begin_transaction();
        
        foreach ($data['reservations'] as $reservation_id) {
            // Verify reservation belongs to customer and is in pending status
            $stmt = $conn->prepare("
                SELECT r.*, rm.price * DATEDIFF(r.check_out, r.check_in) as total_price 
                FROM reservations r 
                JOIN rooms rm ON r.room_id = rm.room_id 
                WHERE r.reservation_id = ? AND r.customer_id = ? AND r.status = 'pending'
            ");
            $stmt->bind_param("ii", $reservation_id, $_SESSION['customer_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception('Invalid reservation: ' . $reservation_id);
            }

            $reservation = $result->fetch_assoc();
            
            // Create billing record
            $stmt = $conn->prepare("
                INSERT INTO billing (
                    reservation_id,
                    total_amount,
                    status,
                    created_at
                ) VALUES (?, ?, 'checking', NOW())
            ");
            $stmt->bind_param("id", $reservation_id, $reservation['total_price']);
            $stmt->execute();
            $billing_id = $conn->insert_id;

            // Update reservation status to confirmed
            $stmt = $conn->prepare("UPDATE reservations SET status = 'checking' WHERE reservation_id = ?");
            $stmt->bind_param("i", $reservation_id);
            if (!$stmt->execute()) {
                throw new Exception('Failed to update reservation status');
            }
        }

        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Payment processed successfully',
            'reservation_ids' => $data['reservations']
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error in process_payment.php: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error processing payment: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}