<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user = $_SESSION['user'];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Data Warga - Monitoring Warga  Rahmanullah - Desa Kincang</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans">

<!-- Navbar -->
<nav class="bg-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center">
                <i class="fas fa-user-plus text-green-600 text-2xl mr-2"></i>
                <span class="font-bold text-xl text-gray-800">Input Warga</span>
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
        <h1 class="text-2xl font-bold text-gray-800 mb-6">
            <i class="fas fa-edit mr-2 text-green-600"></i>Form Input Data Warga
        </h1>

        <!-- Form Input -->
        <form action="simpan.php" method="POST" class="space-y-5">
            <!-- NIK -->
            <div>
                <label for="nik" class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-id-card mr-1 text-gray-500"></i> NIK <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nik" id="nik" maxlength="16" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="16 digit NIK" pattern="\d{16}" title="NIK harus 16 digit angka">
                <p class="text-xs text-gray-500 mt-1">Masukkan 16 digit angka.</p>
            </div>

            <!-- Nama Lengkap -->
            <div>
                <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-user mr-1 text-gray-500"></i> Nama Lengkap <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nama" id="nama" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="Contoh: John Doe">
            </div>

            <!-- Jenis Kelamin -->
            <div>
                <label for="jenis_kelamin" class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-venus-mars mr-1 text-gray-500"></i> Jenis Kelamin <span class="text-red-500">*</span>
                </label>
                <select name="jenis_kelamin" id="jenis_kelamin" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <option value="">-- Pilih --</option>
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                </select>
            </div>

            <!-- Pekerjaan -->
            <div>
                <label for="pekerjaan" class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-briefcase mr-1 text-gray-500"></i> Pekerjaan
                </label>
                <input type="text" name="pekerjaan" id="pekerjaan"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="Contoh: Petani, Buruh, Wiraswasta">
            </div>

            <!-- Penghasilan -->
            <div>
                <label for="penghasilan" class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-money-bill-wave mr-1 text-gray-500"></i> Penghasilan (per bulan) <span class="text-red-500">*</span>
                </label>
                <input type="number" name="penghasilan" id="penghasilan" required min="0" step="1000"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="Contoh: 2000000">
                <p class="text-xs text-gray-500 mt-1">Status kemiskinan akan ditentukan otomatis berdasarkan penghasilan.</p>
            </div>

            <!-- Jumlah Tanggungan -->
            <div>
                <label for="jumlah_tanggungan" class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-users mr-1 text-gray-500"></i> Jumlah Tanggungan
                </label>
                <input type="number" name="jumlah_tanggungan" id="jumlah_tanggungan" min="0"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="Contoh: 3">
            </div>
<!-- Di dalam form, setelah jumlah_tanggungan -->
<div>
    <label for="rt" class="block text-sm font-medium text-gray-700 mb-1">
        <i class="fas fa-map-pin mr-1 text-gray-500"></i> RT
    </label>
    <input type="text" name="rt" id="rt" maxlength="5"
           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
           placeholder="Contoh: 001">
    <p class="text-xs text-gray-500 mt-1">Opsional, maksimal 5 karakter.</p>
</div>

<div>
    <label for="rw" class="block text-sm font-medium text-gray-700 mb-1">
        <i class="fas fa-map-pin mr-1 text-gray-500"></i> RW
    </label>
    <input type="text" name="rw" id="rw" maxlength="5"
           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
           placeholder="Contoh: 002">
    <p class="text-xs text-gray-500 mt-1">Opsional, maksimal 5 karakter.</p>
</div>
            <!-- Tombol Submit -->
            <div class="flex flex-col sm:flex-row gap-3 pt-4">
                <button type="submit" name="simpan" 
                        class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg shadow flex items-center justify-center transition">
                    <i class="fas fa-save mr-2"></i> Simpan Data
                </button>
            </div>
        </form>
    </div>

    <!-- Informasi tambahan -->
    <div class="mt-6 bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700">
                    <strong>Keterangan status kemiskinan:</strong><br>
                    - Miskin: Penghasilan < Rp1.000.000<br>
                    - Rentan: Rp1.000.000 ≤ Penghasilan < Rp3.000.000<br>
                    - Sejahtera: Penghasilan ≥ Rp3.000.000
                </p>
            </div>
        </div>
    </div>
</main>

</body>
</html>