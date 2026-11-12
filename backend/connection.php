<<<<<<< HEAD
<?php
    $hostname = "localhost";
    $username = "root";
    $password = "";
    $database = "mediquick";

    $conn = mysqli_connect($hostname, $username, $password, $database);

    if(!$conn) {
        die(mysqli_connect_error());
    }
=======
<?php 
$conn = mysqli_connect("localhost", "root", "", "mediquick");
>>>>>>> origin/kaUhuyy
?>