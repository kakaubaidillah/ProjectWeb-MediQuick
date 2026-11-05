<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi</title>
</head>
<body>
    <h2>Form Registrasi</h2>
    <form action="regis_akses.php" method="POST">
        <label>Nama:</label>
        <input type="text" name = "name" required>
        <br><br>
        <label>Email:</label>
        <input type="email" name="email" required>
        <br><br>
        <label>Password:</label>
        <input type="password" name="pass" placeholder="password akun apotek" required>
        <br><br>
        <label>Tanggal Lahir:</label>
        <input type="date" name="date" required>
        <br><br>
        <label>Jenis Kelamin:</label>
        <br>
        <label>
            <input type="radio" name="gender" value="Laki-laki">
            <span>Laki-laki</span>
        </label>
        <label>
            <input type="radio" name="gender" value="Perempuan">
            <span>Perempuan</span>
        </label>



    </form>
</body>
</html>