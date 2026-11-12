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
    <link rel="stylesheet" href="../../asset/css/formStyle.css">
    <title>Daftar Akun</title>
</head>

<body>

    <div class="form-container" id="registform">
        <h2>REGISTRASI</h2>
        <?php if(!empty($registerror)) { ?>
            <p class="existed-email">
                <?= $registerror ?>
            </p>
        <?php } ?>
        <form action="access_regist.php" method="POST">
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
    <!-- <h2>Registrasi</h2>
    <form action="access_regist.php" method="POST">
        <label>Nama:</label>
        <input type="text" name="name" required>
        <br><br>
        <label>Email:</label>
        <input type="email" name="email" required>
        <br><br>
        <label>Password:</label>
        <input type="password" name="pass" placeholder="password akun apotek" required>
        <br><br>
        <label>No. Telephone:</label>
        <input type="tel" name="tlp" placeholder="08..." required>
        <br><br>
        <label>Tanggal Lahir:</label>
        <input type="date" name="date" required>
        <br><br>
        <label>Jenis Kelamin:</label>
        <br>
            <label>
                <input type="radio" name="gender" value="Laki-laki">
                Laki-laki
            </label>
        <label>
            <input type="radio" name="gender" value="Perempuan">
            Perempuan
        </label>
        <br><br>
        <input type="submit" value="Buat Akun">
    </form> -->
</body>

</html>