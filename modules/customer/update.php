<?php
include_once __DIR__ . '/../../config/database.php';
?>
<html>
    <head></head>

    <body>


        <?php
            $name = $_POST['name'];
            $email = $_POST['email'];
            $location = $_POST['address'];
            $contact  = $_POST['contact'];

            $sql = "UPDATE customer SET name = ?, email = ?, address = ? WHERE contact = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssi", $name, $email, $location, $contact);
            mysqli_stmt_execute($stmt);
           
            
            if (mysqli_stmt_execute($stmt)) {
                echo '<script language="javascript">';
                echo 'alert("message successfully sent");';
                echo '</script>';
            } else {
                $errorMessage = "Error updating record: " . mysqli_error($conn);
            }

            mysqli_stmt_close($stmt);
        ?>

    </body>


</html>
<?php 
mysqli_close($conn);