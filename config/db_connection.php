<?php
$host = "localhost";
$user = "root"; // Change if necessary
$pass = ""; // Change if necessary
$db = "jchotel_rabs";

$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>