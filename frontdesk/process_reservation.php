<?php
session_start();
require_once '../config/db_connection.php';

// Check if frontdesk is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'frontdesk') {
    header('Location: ../admin/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->begin_transaction();

        // Insert customer information
        $stmt = $conn->prepare("
            INSERT INTO customers (
                full_name,
                phone,
                created_at
            ) VALUES (?, ?, NOW())
        ");

        $stmt->bind_param("ss", $_POST['customer_name'], $_POST['contact_number']);
        $stmt->execute();
        $customer_id = $conn->insert_id;

        // Insert reservation
        $stmt = $conn->prepare("
            INSERT INTO reservations (
                customer_id,
                room_id,
                check_in,
                check_out,
                status,
                notes,
                created_at
            ) VALUES (?, ?, ?, ?, 'pending', ?, NOW())
        ");

        $stmt->bind_param(
            "iisss",
            $customer_id,
            $_POST['room_id'],
            $_POST['check_in'],
            $_POST['check_out'],
            $_POST['notes']
        );

        $stmt->execute();
        $reservation_id = $conn->insert_id;

        // Insert billing record
        $stmt = $conn->prepare("
            INSERT INTO billing (
                reservation_id,
                total_amount,
                status,
                created_at
            ) VALUES (?, ?, ?, NOW())
        ");

        $stmt->bind_param(
            "ids",
            $reservation_id,
            $_POST['total_amount'],
            $_POST['payment_status']
        );

        $stmt->execute();

        // Record in audit trail
        $action = "Reservation #" . $reservation_id . " created by frontdesk";
        $stmt = $conn->prepare("INSERT INTO audit_trails (user_id, action_taken) VALUES (?, ?)");
        $stmt->bind_param("is", $_SESSION['user_id'], $action);
        $stmt->execute();

        $conn->commit();

        $_SESSION['success_message'] = "Reservation created successfully!";
        header('Location: reservations.php');
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "Error creating reservation: " . $e->getMessage();
        header('Location: reservations.php');
        exit();
    }
} else {
    header('Location: reservations.php');
    exit();
}
?> 