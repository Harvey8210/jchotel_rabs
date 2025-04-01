<?php
require_once '../config/db_connection.php';

header('Content-Type: application/json');

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate inputs
    if (empty($full_name) || empty($email) || empty($phone) || empty($address) || empty($password) || empty($confirm_password)) {
        echo json_encode(['error' => 'All fields are required.']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['error' => 'Invalid email format.']);
        exit;
    }

    if ($password !== $confirm_password) {
        echo json_encode(['error' => 'Passwords do not match.']);
        exit;
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT email FROM customers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        echo json_encode(['error' => 'Email already exists.']);
        exit;
    }
    $stmt->close();

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into the database
    $stmt = $conn->prepare("INSERT INTO customers (full_name, email, phone, address, password, loyalty_points) VALUES (?, ?, ?, ?, ?, 0)");
    $stmt->bind_param("sssss", $full_name, $email, $phone, $address, $hashed_password);

    if ($stmt->execute()) {
        echo json_encode(['success' => 'User registered successfully.']);
    } else {
        echo json_encode(['error' => 'Registration failed. Please try again.']);
    }

    $stmt->close();
    $conn->close();
}
