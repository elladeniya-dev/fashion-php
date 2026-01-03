<?php

include_once __DIR__ . '/../../../config/database.php';

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$address = trim($_POST['address'] ?? '');
$contact = trim($_POST['contact'] ?? '');
$uname   = trim($_POST['username'] ?? '');
$pwd     = trim($_POST['password'] ?? '');

// Basic required checks
if ($name === '' || $email === '' || $address === '' || $contact === '' || $uname === '' || $pwd === '') {
    echo '<script>alert("All fields are required.");</script>';
    exit;
}

// Insert customer
$customerStmt = $conn->prepare("INSERT INTO customer (name, email, contact, address) VALUES (?, ?, ?, ?)");
$customerStmt->bind_param('ssss', $name, $email, $contact, $address);

if ($customerStmt->execute()) {
    $customerId = $conn->insert_id;

    // Insert login record (plaintext to match existing login logic)
    $loginStmt = $conn->prepare("INSERT INTO customer_login_details (cid, uname, password) VALUES (?, ?, ?)");
    $loginStmt->bind_param('iss', $customerId, $uname, $pwd);

    if ($loginStmt->execute()) {
        echo '<script>alert("Registration successful.");</script>';
    } else {
        echo '<script>alert("Error creating login: ' . $conn->error . '");</script>';
    }

    $loginStmt->close();
} else {
    echo '<script>alert("Error creating customer: ' . $conn->error . '");</script>';
}

$customerStmt->close();
$conn->close();
?>
