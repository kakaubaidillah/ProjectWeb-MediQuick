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
    <link rel="stylesheet" href="../asset/css/formStyle.css">
    <link rel="icon" type="image/-ico" href="../asset/img/icon.ico">
    <title>Daftar Akun</title>

</head>

<body>

    <div class="form-container" id="registform">
        <h2>REGISTRASI</h2>
<<<<<<< HEAD:auth/registrasi.php
        <?php if(!empty($registerror)) { ?>
            <p class="existed-email">
                <?= $registerror ?>
            </p>
        <?php } ?>
        <form action="../backend/access_regist.php" method="POST">
=======
        <form action="proses/access_regist.php" method="POST">
>>>>>>> kaUhuyy:login/registrasi.php
            <div class="form-ketik">
                <!-- Nama -->
                <label>Nama</label><br>
                <input type="text" name="name" placeholder="Nama Lengkap" required><br>
                <!-- Email -->
                <label>Email</label><br>
                <input type="email" name="email" placeholder="Email" required><br>
                <!-- Password -->
                <label>Password</label><br>
                <input type="password" name="pass" placeholder="password" required><br>
                <!-- Nomor Telepon -->
                <label>Nomor Telepon</label><br>
                <input type="tel" name="tlp" placeholder="08xxxxxxxxx" required><br>
                <!-- Tanggal Lahir -->
                <label>Tanggal Lahir</label><br>
                <input type="date" name="date" required><br>
            </div>
            <div class="form-gender">
                <!-- Gender -->
                <label class="gender-title">Gender</label>
                <label class="gender-cat" for="male">
                    <input type="radio" name="gender" id="male" value="Laki-laki">
                    Pria
                </label>
                <label class="gender-cat" for="female">
                    <input type="radio" name="gender" id="female" value="Perempuan">
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

        registbtn.onmouseover =()=> {
            registbtn.style.backgroundColor = "#0D75DE";
        }
        registbtn.onmouseout =()=> {
            registbtn.style.backgroundColor = "#2791F5";
        }

        tologinbtn.onmouseover =()=> {
            tologinbtn.style.textDecoration = "underline";
        }
        tologinbtn.onmouseout =()=> {
            tologinbtn.style.textDecoration = "none"
        }
    </script>
</body>

</html>