<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Profile</title>
</head>
<body>
    <div class="profile">
        <?php
        
        include_once __DIR__ . '/../../config/database.php';


        $customer_id = 1;

        $query = "SELECT * FROM customer WHERE cid = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $customer_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            
            echo '<h1>Welcome, ' . $row['name'] . '</h1>';
            echo '<p>Email: ' . $row['email'] . '</p>';
            echo '<p>Contact: ' . $row['contact'] . '</p>';
            echo '<p>Address: ' . $row['address'] . '</p>';

        } else {
            echo '<p>Customer not found.</p>';
        }

        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        ?>
    </div>
</body>
</html>
