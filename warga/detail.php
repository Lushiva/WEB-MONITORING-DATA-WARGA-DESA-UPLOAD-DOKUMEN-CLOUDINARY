<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user = $_SESSION['user'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['error'] = "ID tidak valid.";
    header("Location: list.php");
    exit;
}

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

// Konfigurasi Cloudinary untuk membuat URL gambar
$cloudinaryConfig = include '../config/cloudinary.php';
$cloudName = $cloudinaryConfig['cloud_name'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Warga - Monitoring Warga  Rahmanullah - Desa Kincang</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans">

<!-- Navbar -->
<nav class="bg-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center">
                <i class="fas fa-eye text-green-600 text-2xl mr-2"></i>
                <span class="font-bold text-xl text-gray-800">Detail Warga</span>
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
<main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <!-- Header dengan Nama dan NIK -->
        <div class="bg-gradient-to-r from-green-500 to-blue-600 px-6 py-4">
            <h1 class="text-2xl font-bold text-white"><?= htmlspecialchars($warga['nama']) ?></h1>
            <p class="text-green-100">NIK: <?= htmlspecialchars($warga['nik']) ?></p>
        </div>

        <!-- Data Detail -->
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-info-circle mr-2 text-green-600"></i>Informasi Pribadi</h2>
                    <table class="w-full">
                        <tr class="border-b">
                            <td class="py-2 text-gray-600 w-1/3">NIK</td>
                            <td class="py-2 font-medium"><?= htmlspecialchars($warga['nik']) ?></td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-2 text-gray-600">Nama</td>
                            <td class="py-2 font-medium"><?= htmlspecialchars($warga['nama']) ?></td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-2 text-gray-600">Jenis Kelamin</td>
                            <td class="py-2 font-medium"><?= $warga['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-2 text-gray-600">Pekerjaan</td>
                            <td class="py-2 font-medium"><?= htmlspecialchars($warga['pekerjaan'] ?: '-') ?></td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-2 text-gray-600">Penghasilan</td>
                            <td class="py-2 font-medium">Rp <?= number_format($warga['penghasilan'], 0, ',', '.') ?></td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-2 text-gray-600">Jumlah Tanggungan</td>
                            <td class="py-2 font-medium"><?= $warga['jumlah_tanggungan'] ?> orang</td>
                        </tr>
                        <tr class="border-b">
    <td class="py-2 text-gray-600">RT / RW</td>
    <td class="py-2 font-medium">
        <?= htmlspecialchars($warga['rt'] ?: '-') ?> / <?= htmlspecialchars($warga['rw'] ?: '-') ?>
    </td>
</tr>
                        <tr class="border-b">
                            <td class="py-2 text-gray-600">Status Kemiskinan</td>
                            <td class="py-2 font-medium">
                                <?php
                                $badgeColor = '';
                                if ($warga['status_kemiskinan'] == 'Miskin') $badgeColor = 'bg-red-100 text-red-800';
                                elseif ($warga['status_kemiskinan'] == 'Rentan') $badgeColor = 'bg-yellow-100 text-yellow-800';
                                else $badgeColor = 'bg-green-100 text-green-800';
                                ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $badgeColor ?>">
                                    <?= $warga['status_kemiskinan'] ?>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Dokumen yang diupload -->
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-file-image mr-2 text-green-600"></i>Dokumen Terupload</h2>
                    <div class="space-y-4">
                      <!-- KTP -->
<div class="border rounded-lg p-4">
    <div class="flex items-center justify-between mb-2">
        <h3 class="font-medium text-gray-700"><i class="fas fa-id-card mr-1 text-blue-500"></i> KTP</h3>
        <?php if (!empty($warga['ktp_public_id'])): ?>
            <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Tersedia</span>
        <?php else: ?>
            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">Belum ada</span>
        <?php endif; ?>
    </div>
    <?php if (!empty($warga['ktp_public_id'])): ?>
        <img src="https://res.cloudinary.com/<?= $cloudName ?>/image/upload/c_scale,w_300/<?= $warga['ktp_public_id'] ?>" 
             alt="KTP" class="mt-2 rounded border max-w-full h-auto">
        <div class="mt-2 flex space-x-3">
            <a href="https://res.cloudinary.com/<?= $cloudName ?>/image/upload/<?= $warga['ktp_public_id'] ?>" target="_blank" 
               class="text-blue-600 hover:underline text-sm">
                <i class="fas fa-external-link-alt mr-1"></i> Lihat
            </a>
            <a href="hapus_dokumen.php?id_warga=<?= $warga['id'] ?>&jenis=ktp" 
               class="text-red-600 hover:text-red-800 text-sm"
               onclick="return confirm('Yakin ingin menghapus file KTP ini?')">
                <i class="fas fa-trash-alt mr-1"></i> Hapus
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- KK -->
<div class="border rounded-lg p-4">
    <div class="flex items-center justify-between mb-2">
        <h3 class="font-medium text-gray-700"><i class="fas fa-address-card mr-1 text-blue-500"></i> KK</h3>
        <?php if (!empty($warga['kk_public_id'])): ?>
            <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Tersedia</span>
        <?php else: ?>
            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">Belum ada</span>
        <?php endif; ?>
    </div>
    <?php if (!empty($warga['kk_public_id'])): ?>
        <img src="https://res.cloudinary.com/<?= $cloudName ?>/image/upload/c_scale,w_300/<?= $warga['kk_public_id'] ?>" 
             alt="KK" class="mt-2 rounded border max-w-full h-auto">
        <div class="mt-2 flex space-x-3">
            <a href="https://res.cloudinary.com/<?= $cloudName ?>/image/upload/<?= $warga['kk_public_id'] ?>" target="_blank" 
               class="text-blue-600 hover:underline text-sm">
                <i class="fas fa-external-link-alt mr-1"></i> Lihat
            </a>
            <a href="hapus_dokumen.php?id_warga=<?= $warga['id'] ?>&jenis=kk" 
               class="text-red-600 hover:text-red-800 text-sm"
               onclick="return confirm('Yakin ingin menghapus file KK ini?')">
                <i class="fas fa-trash-alt mr-1"></i> Hapus
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Dokumen Lain -->
<div class="border rounded-lg p-4">
    <div class="flex items-center justify-between mb-2">
        <h3 class="font-medium text-gray-700"><i class="fas fa-file-alt mr-1 text-blue-500"></i> Dokumen Lain</h3>
        <?php if (!empty($warga['dokumen_public_id'])): ?>
            <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Tersedia</span>
        <?php else: ?>
            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">Belum ada</span>
        <?php endif; ?>
    </div>
    <?php if (!empty($warga['dokumen_public_id'])): ?>
        <img src="https://res.cloudinary.com/<?= $cloudName ?>/image/upload/c_scale,w_300/<?= $warga['dokumen_public_id'] ?>" 
             alt="Dokumen" class="mt-2 rounded border max-w-full h-auto">
        <div class="mt-2 flex space-x-3">
            <a href="https://res.cloudinary.com/<?= $cloudName ?>/image/upload/<?= $warga['dokumen_public_id'] ?>" target="_blank" 
               class="text-blue-600 hover:underline text-sm">
                <i class="fas fa-external-link-alt mr-1"></i> Lihat
            </a>
            <a href="hapus_dokumen.php?id_warga=<?= $warga['id'] ?>&jenis=dokumen" 
               class="text-red-600 hover:text-red-800 text-sm"
               onclick="return confirm('Yakin ingin menghapus file dokumen ini?')">
                <i class="fas fa-trash-alt mr-1"></i> Hapus
            </a>
        </div>
    <?php endif; ?>
</div>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="edit.php?id=<?= $warga['id'] ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg shadow flex items-center transition">
                    <i class="fas fa-edit mr-2"></i> Edit Data
                </a>
                <a href="upload.php?id=<?= $warga['id'] ?>" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg shadow flex items-center transition">
                    <i class="fas fa-cloud-upload-alt mr-2"></i> Upload/Perbarui Dokumen
                </a>
                <a href="list.php" class="bg-gray-500 hover:bg-gray-600 text-white px-5 py-2 rounded-lg shadow flex items-center transition">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>
</main>

</body>
</html>