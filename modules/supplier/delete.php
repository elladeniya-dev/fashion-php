<?php
include_once __DIR__ . '/../../config/database.php';

if (isset($_GET['id'])) {
    $customerId = $_GET['id'];

    $deleteQuery = "DELETE FROM supplier WHERE sid = ' $customerId'";
    $deleteResult = mysqli_query($conn, $deleteQuery);


    if ($deleteResult) {
        header("Location:details.php");
    } else {
        echo "Error deleting employee record: " . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>