<?php
    session_start();
    include "backend/connection.php";

    $user = $_POST['email'];
    $pass = $_POST['pass'];

    $sytx = "SELECT * FROM admin WHERE email = '$user";
    
?>