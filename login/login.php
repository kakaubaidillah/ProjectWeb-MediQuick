<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SESI LOGIN</title>
</head>
<body>
    <?php
    if(isset($_GET['pesan'])){
        $pesan = $_GET['pesan'];
        if($pesan == "failed"){
           ?>
            <script>alert("Logout berhasil")</script>
            <?php 
        }
    }  
    ?>
    <form action = "request.php" method="POST">
        Username:
        <input type="text" name = "usn">
        <br>
        <br>
        Password: 
        <input type = "password" name="pass">
        <br>
        <br>
        <input type="submit">
    </form>
</body>
</html>