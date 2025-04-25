<?php
session_start();

// Sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if user is logged in
function require_login() {
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        header("location: login.php");
        exit;
    }
}

// Get all users
function get_all_users($conn) {
    $sql = "SELECT * FROM users ORDER BY created_at DESC";
    $result = mysqli_query($conn, $sql);
    return $result;
}

// Get all rooms
function get_all_rooms($conn) {
    $sql = "SELECT * FROM rooms ORDER BY room_number ASC";
    $result = mysqli_query($conn, $sql);
    return $result;
}

// Add new user
function add_user($conn, $username, $password, $full_name, $role) {
    // First check if username already exists
    $check_sql = "SELECT user_id FROM users WHERE username = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "s", $username);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);
    
    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        return false; // Username already exists
    }
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssss", $username, $hashed_password, $full_name, $role);
    return mysqli_stmt_execute($stmt);
}

// Add new user by staff (with role restrictions)
function staff_add_user($conn, $username, $password, $full_name, $role) {
    // Check if the current user is staff
    if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "staff") {
        return array("success" => false, "message" => "Unauthorized access");
    }
    
    // Validate role - staff can only create staff or frontdesk users
    if ($role !== "staff" && $role !== "frontdesk") {
        return array("success" => false, "message" => "You can only create staff or frontdesk users");
    }
    
    // First check if username already exists
    $check_sql = "SELECT user_id FROM users WHERE username = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "s", $username);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);
    
    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        return array("success" => false, "message" => "Username already exists");
    }
    
    // Validate password
    if (strlen($password) < 6) {
        return array("success" => false, "message" => "Password must be at least 6 characters long");
    }
    
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert the new user
    $sql = "INSERT INTO users (username, password, full_name, role, created_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssss", $username, $hashed_password, $full_name, $role);
    
    if (mysqli_stmt_execute($stmt)) {
        return array("success" => true, "message" => "User added successfully");
    } else {
        return array("success" => false, "message" => "Error adding user: " . mysqli_error($conn));
    }
}

// Add new room
function add_room($conn, $room_number, $type, $price, $description, $image_name = null) {
    // First check if room number already exists
    $check_sql = "SELECT room_id FROM rooms WHERE room_number = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "s", $room_number);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);
    
    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        return false; // Room number already exists
    }
    
    $sql = "INSERT INTO rooms (room_number, type, price, description, image) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssdss", $room_number, $type, $price, $description, $image_name);
    return mysqli_stmt_execute($stmt);
}

// Update room status
function update_room_status($conn, $room_id, $status) {
    $sql = "UPDATE rooms SET status = ? WHERE room_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $status, $room_id);
    return mysqli_stmt_execute($stmt);
}

// Delete user
function delete_user($conn, $user_id) {
    $sql = "DELETE FROM users WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    return mysqli_stmt_execute($stmt);
}

// Delete room
function delete_room($conn, $room_id) {
    $sql = "DELETE FROM rooms WHERE room_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $room_id);
    return mysqli_stmt_execute($stmt);
}
?> 