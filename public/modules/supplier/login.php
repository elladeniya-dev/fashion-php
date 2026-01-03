<?php
// Include database connection
include_once __DIR__ . '/../../../config/database.php';

// Check if the login form is submitted
if (isset($_POST['login'])) {
    $username = $_POST['uname']; // Changed from 'uname' to 'username' to match the form input name
    $password = $_POST['password'];

    // Query to retrieve user information based on the provided username
    $query = "SELECT * FROM supplier_login_details WHERE uname = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        // Check if the provided password matches the password in the database
        $storedPassword = $row['password'];
        $isValid = password_verify($password, $storedPassword) || $password === $storedPassword; // legacy fallback

        if ($isValid) {
            session_start();
            $_SESSION['sid'] = $row['sid']; // You can store other user data in the session as needed
            header("Location: products.php");
            exit();
        } else {
            header("Location: login.html?error=invalid_password"); // Changed to supplier login page
            exit();
        }
    } else {
        // User not found
        header("Location: login.html?error=user_not_found"); // Changed to supplier login page
        exit();
    }

    mysqli_stmt_close($stmt);
}

// Close the database connection
mysqli_close($conn);
?>
