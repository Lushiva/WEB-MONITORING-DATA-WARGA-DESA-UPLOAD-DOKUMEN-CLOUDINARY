<?php
error_reporting(E_ALL & ~E_DEPRECATED);
session_start();
include "../config/database.php";

// Cek login dan role admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['error'] = "ID tidak valid.";
    header("Location: users.php");
    exit;
}

// Prevent admin menghapus dirinya sendiri (opsional)
if ($id == $_SESSION['user']['id']) {
    $_SESSION['error'] = "Anda tidak dapat menghapus akun Anda sendiri.";
    header("Location: users.php");
    exit;
}

$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    $_SESSION['success'] = "User berhasil dihapus.";
} else {
    $_SESSION['error'] = "User tidak ditemukan atau gagal dihapus.";
}
$stmt->close();

header("Location: users.php");
exit;