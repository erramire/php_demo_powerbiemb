<?php
 

  

    $servername = "localhost:3306";
    $dbname = "phppwbiemded";
    $username = "root";
    $password = "";
  
    $conn = new mysqli($servername,
        $username, $password,$dbname
    );
    
    if (!$conn) {

        echo "Connection failed!";
    
    }
?>