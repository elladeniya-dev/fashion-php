<?php

include_once __DIR__ . '/../../../config/database.php';


if (isset($_POST['login'])) {
    $email = $_POST['username'];
    $password = $_POST['password'];

   
    $query = "SELECT * FROM admin WHERE username = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
       
        $storedPassword = $row['password'];
        $isValid = password_verify($password, $storedPassword) || $password === $storedPassword; // fallback for legacy plaintext

        if ($isValid) {
            session_start();
            $_SESSION['Admin_id'] = $row['Admin_id'];
            header("Location: dashboard.php");
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
