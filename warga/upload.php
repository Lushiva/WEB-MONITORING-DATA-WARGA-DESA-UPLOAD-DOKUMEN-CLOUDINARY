<?php
session_start();
include "../config/database.php";
require_once '../vendor/autoload.php'; // Pastikan path sesuai

use Cloudinary\Cloudinary;
use Cloudinary\Api\Upload\UploadApi;

// Cek login
if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user = $_SESSION['user'];

// Ambil ID dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['error'] = "ID tidak valid.";
    header("Location: list.php");
    exit;
}

// Ambil data warga
$stmt = $conn->prepare("SELECT * FROM data_warga WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$warga = $result->fetch_assoc();
$stmt->close();

if (!$warga) {
    $_SESSION['error'] = "Data warga tidak ditemukan.";
    header("Location: list.php");
    exit;
}

// Konfigurasi Cloudinary
$cloudinaryConfig = include '../config/cloudinary.php';
$cloudinary = new Cloudinary($cloudinaryConfig);
$uploadApi = new UploadApi($cloudinaryConfig);

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {
    $uploaded = false;

    // Fungsi untuk upload file dengan kompresi
    function uploadFile($fileInputName, $publicIdPrefix) {
        global $uploadApi, $warga, $error;
        if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES[$fileInputName]['tmp_name'];
            $publicId = 'warga_' . $warga['id'] . '_' . $publicIdPrefix . '_' . time();
            try {
                // Upload dengan parameter kompresi
                $result = $uploadApi->upload($file, [
                    'public_id'    => $publicId,
                    'folder'       => 'monitoring_desa',
                    'quality'      => 'auto:good',       // Kualitas otomatis yang baik
                    'fetch_format' => 'auto',             // Format otomatis (WebP jika didukung)
                    'width'        => 1200,                // Lebar maksimal 1200px
                    'crop'         => 'limit',             // Mempertahankan aspek rasio
                    'flags'        => 'lossy'              // Kompresi lossy untuk PNG
                ]);
                return $result['public_id'];
            } catch (Exception $e) {
                $error = "Gagal upload " . $fileInputName . ": " . $e->getMessage();
                return false;
            }
        }
        return null; // Tidak ada file yang diupload
    }

    // Upload KTP
    $ktpId = uploadFile('ktp', 'ktp');
    if ($ktpId === false) {
        // error sudah diset
    } else {
        if ($ktpId !== null) {
            $updateStmt = $conn->prepare("UPDATE data_warga SET ktp_public_id = ? WHERE id = ?");
            $updateStmt->bind_param("si", $ktpId, $id);
            $updateStmt->execute();
            $updateStmt->close();
            $uploaded = true;
        }
    }

    // Upload KK
    $kkId = uploadFile('kk', 'kk');
    if ($kkId === false) {
        // error
    } else {
        if ($kkId !== null) {
            $updateStmt = $conn->prepare("UPDATE data_warga SET kk_public_id = ? WHERE id = ?");
            $updateStmt->bind_param("si", $kkId, $id);
            $updateStmt->execute();
            $updateStmt->close();
            $uploaded = true;
        }
    }

    // Upload Dokumen Lain
    $dokumenId = uploadFile('dokumen', 'dokumen');
    if ($dokumenId === false) {
        // error
    } else {
        if ($dokumenId !== null) {
            $updateStmt = $conn->prepare("UPDATE data_warga SET dokumen_public_id = ? WHERE id = ?");
            $updateStmt->bind_param("si", $dokumenId, $id);
            $updateStmt->execute();
            $updateStmt->close();
            $uploaded = true;
        }
    }

    if (empty($error) && $uploaded) {
        $success = "Dokumen berhasil diupload.";
        // Refresh data warga
        $stmt = $conn->prepare("SELECT * FROM data_warga WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $warga = $result->fetch_assoc();
        $stmt->close();
    } elseif (empty($error) && !$uploaded) {
        $error = "Tidak ada file yang dipilih untuk diupload.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Dokumen Warga - Monitoring Warga  Rahmanullah - Desa Kincang</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans">

<!-- Navbar -->
<nav class="bg-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center">
                <i class="fas fa-upload text-green-600 text-2xl mr-2"></i>
                <span class="font-bold text-xl text-gray-800">Upload Dokumen</span>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-gray-600"><i class="fas fa-user mr-1"></i> <?= htmlspecialchars($user['nama']) ?></span>
                <a href="../dashboard/index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm transition">
                    <i class="fas fa-arrow-left mr-1"></i> Dashboard
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Konten Utama -->
<main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-xl shadow-md p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Upload Dokumen Warga</h1>
        <p class="text-gray-600 mb-6">NIK: <?= htmlspecialchars($warga['nik']) ?> - <?= htmlspecialchars($warga['nama']) ?></p>

        <!-- Pesan Sukses/Error -->
        <?php if ($success): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg flex items-center">
                <i class="fas fa-check-circle mr-2"></i> <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg">
                <i class="fas fa-exclamation-circle mr-2"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Form Upload -->
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <!-- KTP -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-id-card mr-1 text-gray-500"></i> Foto KTP
                </label>
                <?php if (!empty($warga['ktp_public_id'])): ?>
                    <div class="mb-2 flex items-center">
                        <span class="text-green-600"><i class="fas fa-check-circle mr-1"></i> Sudah diupload</span>
                        <a href="https://res.cloudinary.com/<?= $cloudinaryConfig['cloud_name'] ?>/image/upload/<?= $warga['ktp_public_id'] ?>" target="_blank" class="ml-3 text-blue-600 hover:underline">Lihat</a>
                    </div>
                <?php endif; ?>
                <input type="file" name="ktp" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                <p class="text-xs text-gray-500 mt-1">Format JPG, PNG, dll. Maksimal 2MB (akan dikompres otomatis).</p>
            </div>

            <!-- KK -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-address-card mr-1 text-gray-500"></i> Foto KK
                </label>
                <?php if (!empty($warga['kk_public_id'])): ?>
                    <div class="mb-2 flex items-center">
                        <span class="text-green-600"><i class="fas fa-check-circle mr-1"></i> Sudah diupload</span>
                        <a href="https://res.cloudinary.com/<?= $cloudinaryConfig['cloud_name'] ?>/image/upload/<?= $warga['kk_public_id'] ?>" target="_blank" class="ml-3 text-blue-600 hover:underline">Lihat</a>
                    </div>
                <?php endif; ?>
                <input type="file" name="kk" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>

            <!-- Dokumen Lain -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-file-alt mr-1 text-gray-500"></i> Dokumen Lain (opsional)
                </label>
                <?php if (!empty($warga['dokumen_public_id'])): ?>
                    <div class="mb-2 flex items-center">
                        <span class="text-green-600"><i class="fas fa-check-circle mr-1"></i> Sudah diupload</span>
                        <a href="https://res.cloudinary.com/<?= $cloudinaryConfig['cloud_name'] ?>/image/upload/<?= $warga['dokumen_public_id'] ?>" target="_blank" class="ml-3 text-blue-600 hover:underline">Lihat</a>
                    </div>
                <?php endif; ?>
                <input type="file" name="dokumen" accept="image/*,.pdf" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                <p class="text-xs text-gray-500 mt-1">Format gambar atau PDF.</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 pt-4">
                <button type="submit" name="upload" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg shadow flex items-center justify-center transition">
                    <i class="fas fa-cloud-upload-alt mr-2"></i> Upload Dokumen
                </button>
            </div>
        </form>
    </div>
</main>

</body>
</html>