<?php
session_start();
require_once '../../config/db_connection.php';

if (!isset($_SESSION['customer_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['billing_id']) || empty($_GET['billing_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Billing ID is required']);
    exit();
}

$billing_id = $_GET['billing_id'];
$customer_id = $_SESSION['customer_id'];

$query = "SELECT b.*, r.check_in, r.check_out, r.customer_id,
          rm.room_number, rm.type as room_type, rm.price as room_rate,
          DATEDIFF(r.check_out, r.check_in) as nights
          FROM billing b
          JOIN reservations r ON b.reservation_id = r.reservation_id
          JOIN rooms rm ON r.room_id = rm.room_id
          WHERE b.billing_id = ? AND r.customer_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $billing_id, $customer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Billing details not found']);
    exit();
}

$billing_details = $result->fetch_assoc();
echo json_encode($billing_details);