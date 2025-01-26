<html>
<head>
      
        <link rel="stylesheet" type="text/css" href="customer login.css">
        <link rel="stylesheet" type="text/css" href="button.css">
        
           
    </head>

    <body>


    <h1>Supplier Profile</h1>
<?php
    include_once 'server.php';


    
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    
   $query = "SELECT * FROM supplier_login_details WHERE uname='$username' AND password='$password'";

   $result = mysqli_query($conn, $query);

    
    $sql = "SELECT * FROM supplier c
        INNER JOIN supplier_login_details cl
        ON c.sid = cl.sid
        WHERE cl.uname = '$username'";

    $result1 = mysqli_query($conn, $sql); 


    if ($result) {
        
        if (mysqli_num_rows($result) == 1) {
          
           

           
           if ($result1) {
            if ($result1->num_rows > 0) {
                
                echo "<div class = 'table'>";
                echo "<table border='0'>";
                while($row = $result1->fetch_assoc()) {
                    echo "<tr><td class'th'>CID:</td><td>" . $row['sid'] . "</td></tr>";
                    echo "<tr><td class'th'>Name:</td><td>" . $row['name'] . "</td></tr>";
                    echo "<tr><td class'th'>Email:</td><td>" . $row['email'] . "</td></tr>";
                    echo "<tr><td class'th'>Location:</td><td>" . $row['address'] . "</td></tr>";
                }
                echo "</table>";
                echo "</div>";
            } else {
                echo "No profile found for the logged-in user.";
            }
        } else {
          
            echo "Error: " . mysqli_error($conn);
        }
        
 
    mysqli_free_result($result);
}


mysqli_close($conn);
    }
}
?>

<a href="supplier update.html"><button>Update Details</button>

</body>

</html>