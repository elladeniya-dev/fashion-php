<?php

include_once 'server.php';

$name = $_POST['name'];
$email = $_POST['email'];
$address = $_POST['address'];
$contact = $_POST['contact'];
$uname = $_POST['username'];
$pwd = $_POST['password'];

$sql = "INSERT INTO customer(cid,name,email,contact,address)
        VALUES('','$name','$email','$contact', '$address')";


if (mysqli_query($conn, $sql)) {
    echo '<script language="javascript">';
    echo 'alert("message successfully sent");';
    echo '</script>';
} else {
    echo '<script language="javascript">';
        echo 'alert("Error: ' . mysqli_error($conn) . '");';
        echo '</script>';
}

$sql1 = "INSERT INTO customer_login_details(cid,uname,password)
         VALUES('','$uname','$pwd')";

if (mysqli_query($conn, $sql1)) {
    echo '<script language="javascript">';
    echo 'alert("message successfully sent");';
    echo '</script>';
} else {
    echo '<script language="javascript">';
        echo 'alert("Error: ' . mysqli_error($conn) . '");';
        echo '</script>';
}


mysqli_close($conn);
?>
