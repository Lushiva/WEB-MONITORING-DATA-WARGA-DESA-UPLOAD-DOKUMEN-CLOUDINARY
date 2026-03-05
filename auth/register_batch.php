<?php
session_start();
error_reporting(E_ALL & ~E_DEPRECATED);
include "../config/database.php";
require_once '../vendor/autoload.php';

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

// Cek login dan role admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$errors = [];
$hasil = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file_excel'])) {
    $file = $_FILES['file_excel']['tmp_name'];
    $ext = strtolower(pathinfo($_FILES['file_excel']['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, ['xlsx', 'xls'])) {
        $errors[] = "Format file harus .xlsx atau .xls";
    } else {
        try {
            // Pilih reader sesuai ekstensi
            if ($ext == 'xlsx') {
                $reader = ReaderEntityFactory::createXLSXReader();
            } else {
                $reader = ReaderEntityFactory::createXLSReader();
            }
            $reader->open($file);

            $barisKe = 0;
            // Hanya proses sheet pertama
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                    $barisKe++;
                    if ($barisKe == 1) continue; // skip header

                    $cells = $row->getCells();
                    if (count($cells) < 5) continue; // pastikan minimal 5 kolom

                    $nik    = trim($cells[0]->getValue());
                    $nama   = trim($cells[1]->getValue());
                    $email  = trim($cells[2]->getValue());
                    $pass   = trim($cells[3]->getValue());
                    $role   = trim($cells[4]->getValue());

                    // Validasi per baris
                    $errorBaris = [];

                    // NIK: 16 digit angka
                    if (!preg_match('/^\d{16}$/', $nik)) {
                        $errorBaris[] = "NIK harus 16 digit angka";
                    }

                    if (empty($nama)) {
                        $errorBaris[] = "Nama tidak boleh kosong";
                    }

                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $errorBaris[] = "Email tidak valid";
                    }

                    if (strlen($pass) < 6) {
                        $errorBaris[] = "Password minimal 6 karakter";
                    }

                    if (!in_array($role, ['admin', 'perangkat', 'warga'])) {
                        $errorBaris[] = "Role harus admin/perangkat/warga";
                    }

                    // Cek duplikat NIK dan Email
                    if (empty($errorBaris)) {
                        $stmt = $conn->prepare("SELECT id FROM users WHERE nik = ? OR email = ?");
                        $stmt->bind_param("ss", $nik, $email);
                        $stmt->execute();
                        $stmt->store_result();
                        if ($stmt->num_rows > 0) {
                            $errorBaris[] = "NIK atau Email sudah terdaftar";
                        }
                        $stmt->close();
                    }

                    if (!empty($errorBaris)) {
                        $hasil[] = [
                            'baris'  => $barisKe,
                            'nik'    => $nik,
                            'nama'   => $nama,
                            'status' => 'Gagal',
                            'pesan'  => implode(", ", $errorBaris)
                        ];
                    } else {
                        $hashed = password_hash($pass, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("INSERT INTO users (nik, nama, email, password, role) VALUES (?, ?, ?, ?, ?)");
                        $stmt->bind_param("sssss", $nik, $nama, $email, $hashed, $role);
                        if ($stmt->execute()) {
                            $hasil[] = [
                                'baris'  => $barisKe,
                                'nik'    => $nik,
                                'nama'   => $nama,
                                'status' => 'Sukses',
                                'pesan'  => 'Berhasil disimpan'
                            ];
                        } else {
                            $hasil[] = [
                                'baris'  => $barisKe,
                                'nik'    => $nik,
                                'nama'   => $nama,
                                'status' => 'Gagal',
                                'pesan'  => 'Error database: ' . $conn->error
                            ];
                        }
                        $stmt->close();
                    }
                }
                break; // hanya sheet pertama
            }
            $reader->close();
        } catch (Exception $e) {
            $errors[] = "Gagal membaca file: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Batch - Monitoring Desa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans">

<!-- Navbar -->
<nav class="bg-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center">
                <i class="fas fa-users-cog text-green-600 text-2xl mr-2"></i>
                <span class="font-bold text-xl text-gray-800">Registrasi Batch</span>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-gray-600"><i class="fas fa-user mr-1"></i> <?= htmlspecialchars($user['nama']) ?> (Admin)</span>
                <a href="../dashboard/index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm transition">
                    <i class="fas fa-arrow-left mr-1"></i> Dashboard
                </a>
                <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm transition">
                    <i class="fas fa-sign-out-alt mr-1"></i> Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Konten Utama -->
<main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-xl shadow-md p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Registrasi Akun via Excel</h1>
        <p class="text-gray-600 mb-6">Upload file Excel dengan format kolom: <strong>NIK, Nama, Email, Password, Role</strong> (header wajib).</p>

        <!-- Download Template -->
        <div class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-400 rounded">
            <div class="flex items-center">
                <i class="fas fa-download text-blue-600 text-xl mr-3"></i>
                <div>
                    <p class="font-medium text-blue-800">Download template contoh</p>
                    <a href="download_template.php" class="text-blue-600 hover:underline text-sm">
                        <i class="fas fa-file-excel mr-1"></i> template_register.xlsx
                    </a>
                </div>
            </div>
        </div>

        <!-- Form Upload -->
        <form method="POST" enctype="multipart/form-data" class="mb-8">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-file-excel mr-1 text-green-600"></i> Pilih File Excel
                </label>
                <input type="file" name="file_excel" accept=".xls,.xlsx" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg shadow flex items-center transition">
                <i class="fas fa-upload mr-2"></i> Upload & Proses
            </button>
        </form>

        <!-- Tampilkan error umum -->
        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg">
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Tampilkan hasil proses -->
        <?php if (!empty($hasil)): ?>
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Hasil Proses</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 border">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Baris</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">NIK</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($hasil as $h): ?>
                            <tr class="<?= $h['status'] == 'Sukses' ? 'bg-green-50' : 'bg-red-50' ?>">
                                <td class="px-4 py-2 text-sm"><?= $h['baris'] ?></td>
                                <td class="px-4 py-2 text-sm"><?= htmlspecialchars($h['nik']) ?></td>
                                <td class="px-4 py-2 text-sm"><?= htmlspecialchars($h['nama']) ?></td>
                                <td class="px-4 py-2 text-sm">
                                    <?php if ($h['status'] == 'Sukses'): ?>
                                        <span class="text-green-600 font-semibold"><i class="fas fa-check-circle"></i> Sukses</span>
                                    <?php else: ?>
                                        <span class="text-red-600 font-semibold"><i class="fas fa-times-circle"></i> Gagal</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-2 text-sm"><?= htmlspecialchars($h['pesan']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

</body>
</html>