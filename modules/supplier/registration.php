<?php
include_once __DIR__ . '/../../config/database.php';

$name = $_POST['name'];
$email = $_POST['email'];
$address = $_POST['address'];
$contact = $_POST['contact'];
$uname = $_POST['username'];
$pwd = $_POST['password'];

$sql = "INSERT INTO supplier (name, email, contact, address) VALUES ('$name', '$email', '$contact', '$address')";

if (mysqli_query($conn, $sql)) {
    echo '<script language="javascript">';
    echo 'alert("Supplier data successfully inserted");';
    echo '</script>';
    
    $supplierId = mysqli_insert_id($conn); // Get the last inserted ID
    $sql1 = "INSERT INTO supplier_login_details (sid, uname, password) VALUES ('$supplierId', '$uname', '$pwd')";

    if (mysqli_query($conn, $sql1)) {
        echo '<script language="javascript">';
        echo 'alert("Login data successfully inserted");';
        echo '</script>';
    } else {
        echo '<script language="javascript">';
        echo 'alert("Error inserting login data: ' . mysqli_error($conn) . '");';
        echo '</script>';
    }
} else {
    echo '<script language="javascript">';
    echo 'alert("Error inserting supplier data: ' . mysqli_error($conn) . '");';
    echo '</script>';
}

mysqli_close($conn);
?>
