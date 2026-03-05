<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Ambil ID dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['error'] = "ID tidak valid.";
    header("Location: list.php");
    exit;
}

// Hapus data dengan prepared statement
$stmt = $conn->prepare("DELETE FROM data_warga WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        $_SESSION['success'] = "Data warga berhasil dihapus.";
    } else {
        $_SESSION['error'] = "Data tidak ditemukan atau sudah dihapus.";
    }
} else {
    $_SESSION['error'] = "Terjadi kesalahan saat menghapus data.";
}

$stmt->close();
$conn->close();

header("Location: list.php");
exit;
?>