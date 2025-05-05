<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['room_id'], $_POST['check_in'], $_POST['check_out'])) {
    $roomId = $_POST['room_id'];
    $checkIn = $_POST['check_in'];
    $checkOut = $_POST['check_out'];
    $customerId = $_SESSION['customer_id']; // Assuming customer is logged in

    $query = "INSERT INTO reservations (room_id, customer_id, check_in, check_out, status) VALUES (?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("iiss", $roomId, $customerId, $checkIn, $checkOut);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Reservation successfully created.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create reservation.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
    exit;
}
?>