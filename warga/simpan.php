<?php
session_start();
include "../config/database.php";
$user_id = $_SESSION['user']['id'];

$nik = $_POST['nik'];
$nama = $_POST['nama'];
$jk = $_POST['jenis_kelamin'];
$pekerjaan = $_POST['pekerjaan'];
$penghasilan = $_POST['penghasilan'];
$tanggungan = $_POST['jumlah_tanggungan'];
$rt = isset($_POST['rt']) ? trim($_POST['rt']) : '';
$rw = isset($_POST['rw']) ? trim($_POST['rw']) : '';

if($penghasilan < 1000000){
    $status = "Miskin";
} elseif($penghasilan < 3000000){
    $status = "Rentan";
} else {
    $status = "Sejahtera";
}


mysqli_query($conn, "INSERT INTO data_warga
    (nik, nama, jenis_kelamin, pekerjaan, penghasilan, jumlah_tanggungan, rt, rw, status_kemiskinan, user_id)
    VALUES
    ('$nik','$nama','$jk','$pekerjaan','$penghasilan','$tanggungan','$rt','$rw','$status','$user_id')");
header("Location: ../dashboard/index.php");
exit;
?>