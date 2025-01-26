<?php
    include_once 'server.php';


    
if (isset($_POST['login'])) {
    $username = $_POST['email'];
    $password = $_POST['password'];

   
    $query = "SELECT * FROM admin WHERE email='$username' AND password='$password'";
    $result = mysqli_query($conn, $query);

   
    if ($result) {
        
        if (mysqli_num_rows($result) == 1) {
         
            header("Location: admin profile.html");
           
        } else {
          
            echo '<script language="javascript">';
            echo 'alert("User name or password incorrect");';
            echo '</script>';
        }
    } else {
     
        echo "Error: " . mysqli_error($conn);
    }


    mysqli_free_result($result);
}


mysqli_close($conn);
?>
