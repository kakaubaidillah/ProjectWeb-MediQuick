<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../asset/css/formStyle.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <title>LOGIN</title>
</head>

<body>
    <?php
    if (isset($_GET['pesan']) && $_GET['pesan'] == "failed") {
    ?>
        <script>
            alert("Login Gagal")
        </script>
    <?php } ?>
    <div class="form-container" id="loginform">
        <h2>LOGIN</h2>
        <form action="access_login.php" method="POST">
            <div class="form-ketik">
                <label><i class="bi bi-envelope-at-fill"></i> Email </label><br>
                <input type="text" name="email" placeholder="Masukkan email anda"><br>
                <label><i class="bi bi-lock-fill"></i> Password </label><br>
                <input type="password" name="pass" placeholder="password"><br>
            </div>
            <button type="submit" class="form-btn" id="login-btn" value="login">LOGIN</button>
        </form>
        <p>Belum punya akun? <a href="registrasi.php" id="toregistpage-btn" style="color: blue; text-decoration: none">Daftar</a></p>
    </div>
    <script>
        const loginbtn = document.getElementById('login-btn');
        const todaftarbtn = document.getElementById('toregistpage-btn')

        loginbtn.onmouseover =()=> {
            loginbtn.style.backgroundColor = "#0D75DE";
        }
        loginbtn.onmouseout =()=> {
            loginbtn.style.backgroundColor = "#2791F5";
        }

        todaftarbtn.onmouseover =()=> {
            todaftarbtn.style.textDecoration = "underline";
        }
        todaftarbtn.onmouseout =()=> {
            todaftarbtn.style.textDecoration = "none";
        }
    </script>
</body>
</html>