<?php
session_start();
include '../config/db_connection.php';

// Process login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validate inputs
    if (empty($email) || empty($password)) {
        die("Email and Password are required.");
    }

    // Check if the customer exists
    $stmt = $conn->prepare("SELECT customer_id, full_name, email, password FROM customers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($customer_id, $full_name, $email, $hashed_password);
        $stmt->fetch();

        // Verify password
        if (password_verify($password, $hashed_password)) {
            $_SESSION['customer_id'] = $customer_id;
            $_SESSION['email'] = $email;
            $_SESSION['full_name'] = $full_name;
            $_SESSION['role'] = 'customer';

            // Redirect to customer dashboard
            header("Location: ../customer/customer_dashboard.php");
            exit();
        } else {
            die("Invalid email or password.");
        }
    } else {
        die("Invalid email or password.");
    }

    $stmt->close();
}

$conn->close();
?>
