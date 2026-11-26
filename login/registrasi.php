<?php
session_start();

if(isset($_SESSION['register-error'])) {
    $registerror = $_SESSION['register-error'];
    unset($_SESSION['register-error']);
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-yEPZ7jP9S0QKiUQ0m3jjsO4ProIW7wvR9gXkI7JOGa2XVsw53Yke2bsdkBbjmmiX" crossorigin="anonymous">
    <link rel="stylesheet" href="../asset/css/formStyle.css">
    <title>Daftar Akun</title>
</head>

<body>

    <div class="form-container" id="registform">
        <h2>REGISTRASI</h2>
        <?php if(!empty($registerror)) { ?>
            <div class="alert alert-danger fw-bold text-center" role="alert"><?= $registerror ?></div>
        <?php } ?>
        <form action="proses/access_regist.php" method="POST">
            <div class="form-ketik">
                <!-- Nama lengkap-->
                <label>Nama <span class="text-danger">*</span></label><br>
                <input type="text" name="name" placeholder="Nama Lengkap" required><br>
                <!-- Email -->
                <label>Email <span class="text-danger">*</span></label><br>
                <input type="email" name="email" placeholder="Email" required><br>
                <!-- Password -->
                <label>Password <span class="text-danger">*</span></label><br>
                <input type="password" name="pass" placeholder="password" required><br>
                <!-- Nomor Telepon -->
                <label>Nomor Telepon <span class="text-danger">*</span></label><br>
                <input type="tel" name="tlp" placeholder="Nomor Telepon" required><br>
                <!-- Tanggal Lahir -->
                <label>Tanggal Lahir <span class="text-danger">*</span></label><br>
                <input type="date" name="date" required><br>
            </div>
            <div class="form-gender">
                <!-- Gender -->
                <label class="gender-title">Gender <span class="text-danger">*</span></label>
                <label class="gender-cat" for="male">
                    <input type="radio" name="gender" id="male" value="L">
                    Pria
                </label>
                <label class="gender-cat" for="female">
                    <input type="radio" name="gender" id="female" value="P">
                    Wanita
                </label><br>
            </div>
            <button type="submit" class="form-btn" id="regist-btn" name="registrasi">Daftar</button>
        </form>
        <p>Sudah punya akun? <a href="login.php" id="tologin-btn" style="color: blue; text-decoration: none;">Login</a></p>
    </div>
    <script>
        const registbtn = document.getElementById('regist-btn');
        const tologinbtn = document.getElementById('tologin-btn');

        registbtn.addEventListener("mouseover", function() {
            registbtn.style.backgroundColor = "#2C5EAD";
        })
        registbtn.addEventListener("mouseout", function() {
            registbtn.style.backgroundColor = "#1591DC";
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