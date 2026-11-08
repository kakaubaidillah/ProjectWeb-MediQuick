<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Akun</title>
</head>

<body>

    <h2>Registrasi</h2>
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
    </form>
</body>

</html>