<?php
error_reporting(E_ALL & ~E_DEPRECATED);
session_start();
include "../config/database.php";

// Cek login
if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_ids']) && is_array($_POST['selected_ids'])) {
    $ids = array_map('intval', $_POST['selected_ids']); // sanitasi integer
    if (empty($ids)) {
        $_SESSION['error'] = "Tidak ada data warga yang dipilih.";
        header("Location: list.php");
        exit;
    }

    // Buat placeholder untuk prepared statement
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
    $stmt = $conn->prepare("DELETE FROM data_warga WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$ids);

    if ($stmt->execute()) {
        $deleted = $stmt->affected_rows;
        $_SESSION['success'] = "$deleted data warga berhasil dihapus.";
    } else {
        $_SESSION['error'] = "Gagal menghapus data: " . $conn->error;
    }
    $stmt->close();
} else {
    $_SESSION['error'] = "Permintaan tidak valid.";
}

header("Location: list.php");
exit;