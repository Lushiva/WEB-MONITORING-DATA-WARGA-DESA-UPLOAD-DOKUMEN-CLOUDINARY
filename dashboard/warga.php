<?php
session_start();
include "../config/database.php";

// Cek login
if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user = $_SESSION['user'];

// Hanya role warga yang boleh mengakses halaman ini
if ($user['role'] != 'warga') {
    header("Location: index.php"); // redirect ke dashboard umum
    exit;
}

// Ambil data warga berdasarkan NIK dari user yang login
$nik = $user['nik'];
$stmt = $conn->prepare("SELECT * FROM data_warga WHERE nik = ?");
$stmt->bind_param("s", $nik);
$stmt->execute();
$result = $stmt->get_result();
$warga = $result->fetch_assoc();
$stmt->close();

// Konfigurasi Cloudinary untuk URL gambar
$cloudinaryConfig = include '../config/cloudinary.php';
$cloudName = $cloudinaryConfig['cloud_name'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Warga - Monitoring Warga  Rahmanullah - Desa Kincang</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans">

<!-- Navbar -->
<nav class="bg-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center">
                <i class="fas fa-home text-green-600 text-2xl mr-2"></i>
                <span class="font-bold text-xl text-gray-800">Monitoring Warga  Rahmanullah - Desa Kincang</span>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-gray-600"><i class="fas fa-user mr-1"></i> <?= htmlspecialchars($user['nama']) ?> (Warga)</span>
                <a href="../auth/logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm transition">
                    <i class="fas fa-sign-out-alt mr-1"></i> Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Konten Utama -->
<main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Tampilkan pesan dari session -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-lg flex items-center">
            <i class="fas fa-check-circle mr-2"></i> <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-lg">
            <i class="fas fa-exclamation-circle mr-2"></i> <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <h1 class="text-3xl font-bold text-gray-800 mb-2">Dashboard Warga</h1>
    <p class="text-gray-600 mb-6">Selamat datang, <?= htmlspecialchars($user['nama']) ?>. Berikut adalah data diri Anda.</p>

    <?php if (!$warga): ?>
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-6 mb-6 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold">Data warga belum tersedia</h3>
                    <p class="mt-2">Saat ini data diri Anda belum terdaftar di sistem. Silakan hubungi perangkat desa untuk melakukan pendataan.</p>
                    <p class="mt-2">Atau jika Anda ingin mengisi sendiri, klik tombol di bawah ini.</p>
                    <p class="mt-2">Jika Sudah mengisi data tapi tidak muncul disini, berarti NIK yang Anda masukan salah.</p>
                    <div class="mt-4">
                        <a href="../warga/form_input.php" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg shadow inline-flex items-center transition">
                            <i class="fas fa-plus-circle mr-2"></i> Isi Data Diri
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Card Data Diri -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-green-500 to-blue-600 px-6 py-4">
                <h2 class="text-xl font-bold text-white"><i class="fas fa-id-card mr-2"></i> Data Diri</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">NIK</p>
                        <p class="font-medium"><?= htmlspecialchars($warga['nik']) ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Nama Lengkap</p>
                        <p class="font-medium"><?= htmlspecialchars($warga['nama']) ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Jenis Kelamin</p>
                        <p class="font-medium"><?= $warga['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan' ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Pekerjaan</p>
                        <p class="font-medium"><?= htmlspecialchars($warga['pekerjaan'] ?: '-') ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Penghasilan</p>
                        <p class="font-medium">Rp <?= number_format($warga['penghasilan'], 0, ',', '.') ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Jumlah Tanggungan</p>
                        <p class="font-medium"><?= $warga['jumlah_tanggungan'] ?> orang</p>
                    </div>
                    <div>
    <p class="text-sm text-gray-500">RT / RW</p>
    <p class="font-medium"><?= htmlspecialchars($warga['rt'] ?: '-') ?> / <?= htmlspecialchars($warga['rw'] ?: '-') ?></p>
</div>
                    <div>
                        <p class="text-sm text-gray-500">Status Kemiskinan</p>
                        <?php
                        $badgeColor = '';
                        if ($warga['status_kemiskinan'] == 'Miskin') $badgeColor = 'bg-red-100 text-red-800';
                        elseif ($warga['status_kemiskinan'] == 'Rentan') $badgeColor = 'bg-yellow-100 text-yellow-800';
                        else $badgeColor = 'bg-green-100 text-green-800';
                        ?>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $badgeColor ?>">
                            <?= $warga['status_kemiskinan'] ?>
                        </span>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Terdaftar sejak</p>
                        <p class="font-medium"><?= date('d-m-Y', strtotime($warga['created_at'])) ?></p>
                    </div>
                </div>
                <div class="mt-6">
                    <a href="../warga/edit.php?id=<?= $warga['id'] ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg shadow inline-flex items-center transition">
                        <i class="fas fa-edit mr-2"></i> Edit Data
                    </a>
                </div>
            </div>
        </div>

        <!-- Dokumen Terupload -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="bg-gradient-to-r from-green-500 to-blue-600 px-6 py-4">
                <h2 class="text-xl font-bold text-white"><i class="fas fa-file-image mr-2"></i> Dokumen Terupload</h2>
            </div>
            <div class="p-6">
                <?php if (empty($warga['ktp_public_id']) && empty($warga['kk_public_id']) && empty($warga['dokumen_public_id'])): ?>
                    <p class="text-gray-500 text-center py-4">Belum ada dokumen yang diupload.</p>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- KTP -->
                        <?php if (!empty($warga['ktp_public_id'])): ?>
                        <div class="border rounded-lg p-4">
                            <h3 class="font-medium text-gray-700 mb-2"><i class="fas fa-id-card mr-1 text-blue-500"></i> KTP</h3>
                            <img src="https://res.cloudinary.com/<?= $cloudName ?>/image/upload/c_scale,w_300/<?= $warga['ktp_public_id'] ?>" 
                                 alt="KTP" class="rounded border max-w-full h-auto">
                            <div class="mt-2 flex space-x-3">
                                <a href="https://res.cloudinary.com/<?= $cloudName ?>/image/upload/<?= $warga['ktp_public_id'] ?>" target="_blank" 
                                   class="text-blue-600 hover:underline text-sm">
                                    <i class="fas fa-external-link-alt mr-1"></i> Lihat
                                </a>
                                <!-- Untuk warga, tombol hapus disembunyikan (hanya perangkat/admin yang boleh hapus) -->
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- KK -->
                        <?php if (!empty($warga['kk_public_id'])): ?>
                        <div class="border rounded-lg p-4">
                            <h3 class="font-medium text-gray-700 mb-2"><i class="fas fa-address-card mr-1 text-blue-500"></i> KK</h3>
                            <img src="https://res.cloudinary.com/<?= $cloudName ?>/image/upload/c_scale,w_300/<?= $warga['kk_public_id'] ?>" 
                                 alt="KK" class="rounded border max-w-full h-auto">
                            <div class="mt-2 flex space-x-3">
                                <a href="https://res.cloudinary.com/<?= $cloudName ?>/image/upload/<?= $warga['kk_public_id'] ?>" target="_blank" 
                                   class="text-blue-600 hover:underline text-sm">
                                    <i class="fas fa-external-link-alt mr-1"></i> Lihat
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Dokumen Lain -->
                        <?php if (!empty($warga['dokumen_public_id'])): ?>
                        <div class="border rounded-lg p-4">
                            <h3 class="font-medium text-gray-700 mb-2"><i class="fas fa-file-alt mr-1 text-blue-500"></i> Dokumen Lain</h3>
                            <img src="https://res.cloudinary.com/<?= $cloudName ?>/image/upload/c_scale,w_300/<?= $warga['dokumen_public_id'] ?>" 
                                 alt="Dokumen" class="rounded border max-w-full h-auto">
                            <div class="mt-2 flex space-x-3">
                                <a href="https://res.cloudinary.com/<?= $cloudName ?>/image/upload/<?= $warga['dokumen_public_id'] ?>" target="_blank" 
                                   class="text-blue-600 hover:underline text-sm">
                                    <i class="fas fa-external-link-alt mr-1"></i> Lihat
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <div class="mt-6">
                    <a href="../warga/upload.php?id=<?= $warga['id'] ?>" class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-2 rounded-lg shadow inline-flex items-center transition">
                        <i class="fas fa-cloud-upload-alt mr-2"></i> Upload Dokumen
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

</body>
</html>