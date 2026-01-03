<?php
include_once __DIR__ . '/../../../config/database.php';

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$address = trim($_POST['address'] ?? '');
$contact = trim($_POST['contact'] ?? '');
$uname   = trim($_POST['username'] ?? '');
$pwd     = trim($_POST['password'] ?? '');

if ($name === '' || $email === '' || $address === '' || $contact === '' || $uname === '' || $pwd === '') {
    echo '<script>alert("All fields are required.");</script>';
    exit;
}

// Insert supplier
$supplierStmt = $conn->prepare("INSERT INTO supplier (name, email, contact, address) VALUES (?, ?, ?, ?)");
$supplierStmt->bind_param('ssss', $name, $email, $contact, $address);

if ($supplierStmt->execute()) {
    $supplierId = $conn->insert_id;

    // Insert login row mapped to supplier id
    $loginStmt = $conn->prepare("INSERT INTO supplier_login_details (sid, uname, password) VALUES (?, ?, ?)");
    $loginStmt->bind_param('iss', $supplierId, $uname, $pwd);

    if ($loginStmt->execute()) {
        echo '<script>alert("Registration successful.");</script>';
    } else {
        echo '<script>alert("Error creating login: ' . $conn->error . '");</script>';
    }

    $loginStmt->close();
} else {
    echo '<script>alert("Error creating supplier: ' . $conn->error . '");</script>';
}

$supplierStmt->close();
$conn->close();
?>
