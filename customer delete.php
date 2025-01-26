<?php
include_once 'server.php';

if (isset($_GET['id'])) {
    $customerId = $_GET['id'];

    $deleteQuery = "DELETE FROM customer WHERE cid = $customerId";
    $deleteResult = mysqli_query($conn, $deleteQuery);

    if ($deleteResult) {
        header("Location: customer details.php");
    } else {
        echo "Error deleting customer record: " . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>
