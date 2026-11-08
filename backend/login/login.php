<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGIN</title>
    <style>

    </style>
</head>
<body>
    <?php
    if(isset($_GET['pesan']) && $_GET['pesan'] == "failed"){
       ?>
        <script>alert("Login Gagal")</script>
    <?php } ?>
    <div class="form-container">
        <form action = "access_login.php" method="POST">
        Email:
        <input type="email" name = "email" required>
        <br>
        <br>
        Password: 
        <input type = "password" name="pass" required>
        <br>
        <br>
        <input type="submit" value="Login">
        <br>
        <br>
        <a href="registrasi.php" style="color: gray; text-decoration: none;">Daftar baru</a>
    </form>
    </div>
</body>
</html>