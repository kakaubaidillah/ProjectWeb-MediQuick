<?php 
session_start();
include __DIR__. "/../../../config/connection.php";

$id = $_POST['id'];

$sql ="UPDATE konten_dashboard SET status = 0 WHERE id_konten = '$id'";
mysqli_query($conn, $sql);

header("Location: ../admin_form.php");
exit();
?>