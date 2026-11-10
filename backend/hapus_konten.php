<?php 
session_start();
include "/connection.php";
/** @var mysqli $conn */

$id = $_POST['id'];

$sql ="UPDATE konten_dashboard SET status = 0 WHERE id_konten = '$id'";
mysqli_query($conn, $sql);

header("Location: /../dashboard/admin/admin_form.php");
exit();
?>