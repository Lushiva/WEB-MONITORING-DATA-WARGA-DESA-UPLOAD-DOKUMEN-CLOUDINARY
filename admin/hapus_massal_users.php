<?php
error_reporting(E_ALL & ~E_DEPRECATED);
session_start();
include "../config/database.php";

// Cek login dan role admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_ids']) && is_array($_POST['selected_ids'])) {
    $ids = array_map('intval', $_POST['selected_ids']); // sanitasi integer
    if (empty($ids)) {
        $_SESSION['error'] = "Tidak ada user yang dipilih.";
        header("Location: users.php");
        exit;
    }

    // Buat placeholder untuk prepared statement
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
    $stmt = $conn->prepare("DELETE FROM users WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$ids);

    if ($stmt->execute()) {
        $deleted = $stmt->affected_rows;
        $_SESSION['success'] = "$deleted user berhasil dihapus.";
    } else {
        $_SESSION['error'] = "Gagal menghapus user: " . $conn->error;
    }
    $stmt->close();
} else {
    $_SESSION['error'] = "Permintaan tidak valid.";
}

header("Location: users.php");
exit;