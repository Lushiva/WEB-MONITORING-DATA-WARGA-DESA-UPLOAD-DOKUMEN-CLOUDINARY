<?php
session_start();
include "../config/database.php";
require_once '../vendor/autoload.php';

use Cloudinary\Cloudinary;
use Cloudinary\Api\Upload\UploadApi;

// Cek login
if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user = $_SESSION['user'];

// Ambil parameter
$id_warga = isset($_GET['id_warga']) ? (int)$_GET['id_warga'] : 0;
$jenis = isset($_GET['jenis']) ? $_GET['jenis'] : ''; // ktp, kk, dokumen

if ($id_warga <= 0 || !in_array($jenis, ['ktp', 'kk', 'dokumen'])) {
    $_SESSION['error'] = "Parameter tidak valid.";
    header("Location: list.php");
    exit;
}

// Ambil data warga untuk mendapatkan public_id
$stmt = $conn->prepare("SELECT * FROM data_warga WHERE id = ?");
$stmt->bind_param("i", $id_warga);
$stmt->execute();
$result = $stmt->get_result();
$warga = $result->fetch_assoc();
$stmt->close();

if (!$warga) {
    $_SESSION['error'] = "Data warga tidak ditemukan.";
    header("Location: list.php");
    exit;
}

// Tentukan kolom public_id berdasarkan jenis
$kolom = $jenis . '_public_id';
$public_id = $warga[$kolom];

if (empty($public_id)) {
    $_SESSION['error'] = "Tidak ada file untuk dihapus.";
    header("Location: detail.php?id=" . $id_warga);
    exit;
}

// Konfigurasi Cloudinary
$cloudinaryConfig = include '../config/cloudinary.php';
$uploadApi = new UploadApi($cloudinaryConfig);

// Hapus dari Cloudinary
try {
    $result = $uploadApi->destroy($public_id);
    if ($result['result'] == 'ok') {
        // Update database: set kolom menjadi NULL
        $updateStmt = $conn->prepare("UPDATE data_warga SET $kolom = NULL WHERE id = ?");
        $updateStmt->bind_param("i", $id_warga);
        $updateStmt->execute();
        $updateStmt->close();

        $_SESSION['success'] = "File berhasil dihapus.";
    } else {
        $_SESSION['error'] = "Gagal menghapus file dari Cloudinary.";
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
}

header("Location: detail.php?id=" . $id_warga);
exit;