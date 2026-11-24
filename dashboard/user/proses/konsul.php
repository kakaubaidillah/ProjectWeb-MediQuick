<?php
session_start();

include __DIR__. "/../../../config/connection.php";

if(isset($_POST['kirim'])){
    $id = $_POST['id'];
    $gejala = $_POST['gejala'];
    $alergi = $_POST['alergi'];
    $penyakit = $_POST['penyakit'];
    $mulai_gejala = $_POST['mulai_gejala'];

    if(!empty($_FILES['images']['name'])){
        $gambar = $_FILES['images']['name'];
        $namaBaru = time(). "_". basename($gambar);
        $tmp = $_FILES['images']['tmp_name'];

        $location = __DIR__. "/../../../asset/uploads/". $namaBaru;
        if(!move_uploaded_file($tmp, $location)){
            echo "Gagal Menyimpan Gambar";
            exit();
        }
        $sql_insert = "INSERT INTO konsultasi
                        (id_pasien, gejala, riwayat_alergi, riwayat_penyakit, sejak_kapan, gambar)
                        VALUES ('$id', '$gejala', '$alergi', '$penyakit', '$mulai_gejala', '$namaBaru')";
    }else{
        $sql_insert = "INSERT INTO konsultasi
                    (id_pasien, gejala, riwayat_alergi, riwayat_penyakit, sejak_kapan)
                    VALUES ('$id', '$gejala', '$alergi', '$penyakit', '$mulai_gejala')";
    }

    $data = mysqli_query($conn, $sql_insert);

    if(!$data){
        echo "Gagal Kirim konsultasi!: ". mysqli_error($conn);
    }else{
        header("Location: user.php?id=$id");
        exit();
    }
}

?>