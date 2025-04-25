<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database configuration
require_once "includes/config.php";

echo "<h1>Database Check</h1>";

// Check if users table exists
$table_check = $conn->query("SHOW TABLES LIKE 'users'");
if ($table_check->num_rows == 0) {
    echo "<p style='color:red;'>Users table does not exist. Creating it now...</p>";
    
    // Create users table
    $create_table = "CREATE TABLE users (
        user_id INT(11) NOT NULL AUTO_INCREMENT,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'staff', 'frontdesk') NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (user_id)
    )";
    
    if ($conn->query($create_table) === TRUE) {
        echo "<p style='color:green;'>Users table created successfully!</p>";
    } else {
        echo "<p style='color:red;'>Error creating users table: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color:green;'>Users table exists.</p>";
    
    // Show table structure
    echo "<h2>Users Table Structure:</h2>";
    $result = $conn->query("DESCRIBE users");
    if ($result) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}

// Check if admin user exists
$admin_check = $conn->query("SELECT * FROM users WHERE role = 'admin'");
if ($admin_check->num_rows == 0) {
    echo "<p style='color:red;'>No admin users found. Creating default admin user...</p>";
    
    // Create default admin user
    $username = "admin";
    $password = password_hash("admin123", PASSWORD_DEFAULT);
    $role = "admin";
    $full_name = "Administrator";
    
    $insert_admin = "INSERT INTO users (username, password, role, full_name) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_admin);
    $stmt->bind_param("ssss", $username, $password, $role, $full_name);
    
    if ($stmt->execute()) {
        echo "<p style='color:green;'>Default admin user created successfully!</p>";
        echo "<p>Username: admin<br>Password: admin123</p>";
    } else {
        echo "<p style='color:red;'>Error creating admin user: " . $stmt->error . "</p>";
    }
    $stmt->close();
} else {
    echo "<p style='color:green;'>Admin users exist.</p>";
    
    // Show admin users
    echo "<h2>Admin Users:</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Username</th><th>Full Name</th><th>Created At</th></tr>";
    while ($row = $admin_check->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['user_id'] . "</td>";
        echo "<td>" . $row['username'] . "</td>";
        echo "<td>" . $row['full_name'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Close connection
$conn->close();
?> 