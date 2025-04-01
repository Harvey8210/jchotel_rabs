<?php
require_once 'config/database.php';

try {
    $stmt = $conn->query("DESCRIBE customers");
    echo "Structure of the 'customers' table:<br>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Field: " . $row['Field'] . " - Type: " . $row['Type'] . "<br>";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 