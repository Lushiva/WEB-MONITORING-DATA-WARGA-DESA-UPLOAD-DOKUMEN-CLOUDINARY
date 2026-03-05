<?php
session_start();
include "../config/database.php";

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

// Ambil data warga berdasarkan ID
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

// Proses update jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $nik = trim($_POST['nik']);
    $nama = trim($_POST['nama']);
    $jk = $_POST['jenis_kelamin'];
    $pekerjaan = trim($_POST['pekerjaan']);
    $penghasilan = (float)$_POST['penghasilan'];
    $tanggungan = (int)$_POST['jumlah_tanggungan'];
    $rt = trim($_POST['rt']);
    $rw = trim($_POST['rw']);

    // Validasi sederhana
    $errors = [];
    if (strlen($nik) !== 16 || !ctype_digit($nik)) {
        $errors[] = "NIK harus 16 digit angka.";
    }
    if (empty($nama)) {
        $errors[] = "Nama tidak boleh kosong.";
    }
    if ($penghasilan < 0) {
        $errors[] = "Penghasilan tidak boleh negatif.";
    }
    if ($tanggungan < 0) {
        $errors[] = "Jumlah tanggungan tidak boleh negatif.";
    }

    // Tentukan status kemiskinan (sama seperti di simpan.php)
    if ($penghasilan < 1000000) {
        $status = "Miskin";
    } elseif ($penghasilan < 3000000) {
        $status = "Rentan";
    } else {
        $status = "Sejahtera";
    }

    if (empty($errors)) {
        $update_stmt = $conn->prepare("UPDATE data_warga SET nik=?, nama=?, jenis_kelamin=?, pekerjaan=?, penghasilan=?, jumlah_tanggungan=?, rt=?, rw=?, status_kemiskinan=? WHERE id=?");
        $update_stmt->bind_param("ssssdisssi", $nik, $nama, $jk, $pekerjaan, $penghasilan, $tanggungan, $rt, $rw, $status, $id);
        if ($update_stmt->execute()) {
            $_SESSION['success'] = "Data warga berhasil diperbarui.";
            header("Location: list.php");
            exit;
        } else {
            $errors[] = "Terjadi kesalahan database: " . $conn->error;
        }
        $update_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Warga - Monitoring Desa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans">

<!-- Navbar -->
<nav class="bg-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center">
                <i class="fas fa-edit text-green-600 text-2xl mr-2"></i>
                <span class="font-bold text-xl text-gray-800">Edit Data Warga</span>
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
            <i class="fas fa-pen mr-2 text-green-600"></i>Form Edit Data Warga
        </h1>

        <!-- Tampilkan error jika ada -->
        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg">
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Form Edit -->
        <form method="POST" action="" class="space-y-5">
            <!-- NIK -->
            <div>
                <label for="nik" class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-id-card mr-1 text-gray-500"></i> NIK <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nik" id="nik" maxlength="16" required
                       value="<?= htmlspecialchars($warga['nik']) ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="16 digit NIK" pattern="\d{16}" title="NIK harus 16 digit angka">
            </div>

            <!-- Nama Lengkap -->
            <div>
                <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-user mr-1 text-gray-500"></i> Nama Lengkap <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nama" id="nama" required
                       value="<?= htmlspecialchars($warga['nama']) ?>"
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
                    <option value="L" <?= $warga['jenis_kelamin'] == 'L' ? 'selected' : '' ?>>Laki-laki</option>
                    <option value="P" <?= $warga['jenis_kelamin'] == 'P' ? 'selected' : '' ?>>Perempuan</option>
                </select>
            </div>

            <!-- Pekerjaan -->
            <div>
                <label for="pekerjaan" class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-briefcase mr-1 text-gray-500"></i> Pekerjaan
                </label>
                <input type="text" name="pekerjaan" id="pekerjaan"
                       value="<?= htmlspecialchars($warga['pekerjaan']) ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="Contoh: Petani, Buruh, Wiraswasta">
            </div>

            <!-- Penghasilan -->
            <div>
                <label for="penghasilan" class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-money-bill-wave mr-1 text-gray-500"></i> Penghasilan (per bulan) <span class="text-red-500">*</span>
                </label>
                <input type="number" name="penghasilan" id="penghasilan" required min="0" step="1000"
                       value="<?= htmlspecialchars($warga['penghasilan']) ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="Contoh: 2000000">
            </div>

            <!-- Jumlah Tanggungan -->
            <div>
                <label for="jumlah_tanggungan" class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-users mr-1 text-gray-500"></i> Jumlah Tanggungan
                </label>
                <input type="number" name="jumlah_tanggungan" id="jumlah_tanggungan" min="0"
                       value="<?= htmlspecialchars($warga['jumlah_tanggungan']) ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="Contoh: 3">
            </div>

            <!-- RT -->
            <div>
                <label for="rt" class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-map-pin mr-1 text-gray-500"></i> RT
                </label>
                <input type="text" name="rt" id="rt" maxlength="5"
                       value="<?= htmlspecialchars($warga['rt']) ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="Contoh: 001">
            </div>

            <!-- RW -->
            <div>
                <label for="rw" class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-map-pin mr-1 text-gray-500"></i> RW
                </label>
                <input type="text" name="rw" id="rw" maxlength="5"
                       value="<?= htmlspecialchars($warga['rw']) ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="Contoh: 002">
            </div>

            <!-- Tombol Submit -->
            <div class="flex flex-col sm:flex-row gap-3 pt-4">
                <button type="submit" name="update" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg shadow flex items-center justify-center transition">
                    <i class="fas fa-save mr-2"></i> Update Data
                </button>
                <a href="list.php" 
                   class="bg-gray-400 hover:bg-gray-500 text-white px-6 py-3 rounded-lg shadow flex items-center justify-center transition">
                    <i class="fas fa-times mr-2"></i> Batal
                </a>
            </div>
        </form>
    </div>

    <!-- Informasi tambahan (sama seperti di form input) -->
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