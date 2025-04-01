<?php
require_once "includes/config.php";

// Admin user details
$username = "admin";
$password = "admin123"; // This will be hashed
$full_name = "System Administrator";
$role = "admin";

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Prepare the SQL statement
$sql = "INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ssss", $username, $hashed_password, $full_name, $role);

// Execute the statement
if (mysqli_stmt_execute($stmt)) {
    echo "Admin user created successfully!<br>";
    echo "Username: " . $username . "<br>";
    echo "Password: " . $password . "<br>";
    echo "Full Name: " . $full_name . "<br>";
    echo "Role: " . $role . "<br>";
} else {
    echo "Error creating admin user: " . mysqli_error($conn);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?> 