<?php
/**
 * Database Configuration Example
 * 
 * Copy this file to database.php and update with your credentials
 * DO NOT commit database.php to version control
 */

$servername = "localhost";
$username = "root";
$password = ""; // Update with your MySQL password
$db = "fashion"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");

// Optional: Set timezone
date_default_timezone_set('UTC');

?>
