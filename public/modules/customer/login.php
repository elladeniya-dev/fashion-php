<?php

include_once __DIR__ . '/../../../config/database.php';


if (isset($_POST['login'])) {
    $username = $_POST['uname']; 
    $password = $_POST['password'];

    
    $query = "SELECT * FROM customer_login_details WHERE uname = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        
        $storedPassword = $row['password'];
        $isValid = password_verify($password, $storedPassword) || $password === $storedPassword; // legacy fallback

        if ($isValid) {
            session_start();
            $_SESSION['sid'] = $row['sid']; 
            header("Location: profile.php");
            exit();
        } else {
            header("Location: login.html?error=invalid_password");
            exit();
        }
    } else {
     
        header("Location: login.html?error=user_not_found"); 
        exit();
    }

    mysqli_stmt_close($stmt);
}


mysqli_close($conn);
?>
