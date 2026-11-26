<?php
session_start();

if(isset($_SESSION['id'])) {
    if($_SESSION['role'] == "apoteker") {
        header("location: ../dashboard/apoteker/dashboard_apoteker.php");
        exit();
    } else {
        header("location: ../dashboard/user/index.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../asset/css/formStyle.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-yEPZ7jP9S0QKiUQ0m3jjsO4ProIW7wvR9gXkI7JOGa2XVsw53Yke2bsdkBbjmmiX" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <title>LOGIN</title>
</head>

<body>
    <div class="form-container" id="loginform">
        <h2>LOGIN</h2>
        <?php if (isset($_GET['pesan']) && $_GET['pesan'] == "failed") { ?>
            <div class="alert alert-danger fw-bold text-center" role="alert">LOGIN GAGAL</div>
        <?php } ?>
        <form action="proses/access_login.php" method="POST">
            <div class="form-ketik">
                <label><i class="bi bi-envelope-at-fill"></i> Email </label><br>
                <input type="text" name="email" placeholder="Masukkan email anda"><br>
                <label><i class="bi bi-lock-fill"></i> Password </label><br>
                <input type="password" name="pass" placeholder="password"><br>
            </div>
            <button type="submit" class="form-btn" id="login-btn" name="login">LOGIN</button>
        </form>
        <p>Belum punya akun? <a href="registrasi.php" id="toregistpage-btn" style="color: blue; text-decoration: none">Daftar</a></p>
    </div>
    <script>
        const loginbtn = document.getElementById('login-btn');
        const todaftarbtn = document.getElementById('toregistpage-btn')

        loginbtn.addEventListener("mouseover", function() {
            loginbtn.style.backgroundColor = "#2C5EAD";
        })
        loginbtn.addEventListener("mouseout", function() {
            loginbtn.style.backgroundColor = "#1591DC";
        })
        
        
        todaftarbtn.addEventListener("mouseover", function() {
            todaftarbtn.style.textDecoration = "underline";
        })
        todaftarbtn.addEventListener("mouseout", function() {
            todaftarbtn.style.textDecoration = "none";
        })

    </script>
</body>

</html>