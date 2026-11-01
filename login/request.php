<?php
    session_start();

    $user = $_POST['usn'];
    $pass = $_POST['pass'];

    if(($user == 'admin') && ($pass == '123')){
        $_SESSION['nama'] = $user;
        $_SESSION['status'] = "LOGIN";
        header("Location: index.php");
    }else{
        header("Location: login.php?pesan=failed");
    }

?>