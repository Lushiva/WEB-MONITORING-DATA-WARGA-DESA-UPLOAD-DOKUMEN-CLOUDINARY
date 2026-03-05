<?php
session_start();
include "../config/database.php";

// Proteksi halaman: hanya admin yang boleh mengakses
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';

if (isset($_POST['register'])) {
    $nik = trim($_POST['nik']);
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    // Validasi dasar
    $errors = [];

    // NIK: harus 16 digit angka
    if (!preg_match('/^\d{16}$/', $nik)) {
        $errors[] = "NIK harus 16 digit angka.";
    }

    // Nama tidak boleh kosong
    if (empty($nama)) {
        $errors[] = "Nama tidak boleh kosong.";
    }

    // Email valid
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
    }

    // Password minimal 6 karakter
    if (strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter.";
    }

    // Konfirmasi password
    if ($password !== $confirm_password) {
        $errors[] = "Konfirmasi password tidak cocok.";
    }

    // Role harus dipilih
    if (!in_array($role, ['admin', 'perangkat', 'warga'])) {
        $errors[] = "Role tidak valid.";
    }

    // Jika tidak ada error, cek duplikat email dan NIK
    if (empty($errors)) {
        // Cek email sudah terdaftar?
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Email sudah terdaftar.";
        }
        $stmt->close();

        // Cek NIK sudah terdaftar?
        if (empty($errors)) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE nik = ?");
            $stmt->bind_param("s", $nik);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $errors[] = "NIK sudah terdaftar.";
            }
            $stmt->close();
        }
    }

    // Jika masih tidak ada error, simpan ke database
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (nik, nama, email, password, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $nik, $nama, $email, $hashed_password, $role);

        if ($stmt->execute()) {
            $success = "Akun berhasil dibuat. Silakan login.";
            // Kosongkan form setelah sukses (opsional)
            $_POST = [];
        } else {
            $errors[] = "Terjadi kesalahan database: " . $conn->error;
        }
        $stmt->close();
    }

    // Gabungkan pesan error jika ada
    if (!empty($errors)) {
        $error = implode("<br>", $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Akun - Monitoring Warga  Rahmanullah - Desa Kincang</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .register-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
    </style>
</head>
<body class="font-sans antialiased">

<div class="min-h-screen flex items-center justify-center px-4 py-8">
    <div class="max-w-md w-full">
        <!-- Card Register -->
        <div class="register-card rounded-2xl shadow-2xl p-8 border border-white/20">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="bg-gradient-to-r from-green-400 to-blue-500 w-20 h-20 rounded-full mx-auto flex items-center justify-center shadow-lg">
                    <i class="fas fa-user-plus text-white text-3xl"></i>
                </div>
                <h2 class="text-3xl font-bold text-gray-800 mt-4">Buat Akun Baru</h2>
                <p class="text-gray-600">Isi data dengan lengkap</p>
            </div>

            <!-- Pesan Sukses/Error -->
            <?php if ($success): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span><?= htmlspecialchars($success) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span><?= $error ?></span>
                </div>
            <?php endif; ?>

            <!-- Form Register -->
            <form method="POST" action="">
                <!-- NIK -->
                <div class="mb-4">
                    <label for="nik" class="block text-gray-700 text-sm font-semibold mb-2">
                        <i class="fas fa-id-card mr-1"></i> NIK
                    </label>
                    <input type="text" name="nik" id="nik" maxlength="16"
                           value="<?= isset($_POST['nik']) ? htmlspecialchars($_POST['nik']) : '' ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                           placeholder="16 digit NIK" required pattern="\d{16}" title="NIK harus 16 digit angka">
                </div>

                <!-- Nama Lengkap -->
                <div class="mb-4">
                    <label for="nama" class="block text-gray-700 text-sm font-semibold mb-2">
                        <i class="fas fa-user mr-1"></i> Nama Lengkap
                    </label>
                    <input type="text" name="nama" id="nama"
                           value="<?= isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : '' ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                           placeholder="Masukkan nama lengkap" required>
                </div>

                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 text-sm font-semibold mb-2">
                        <i class="fas fa-envelope mr-1"></i> Email
                    </label>
                    <input type="email" name="email" id="email"
                           value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                           placeholder="contoh@email.com" required>
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label for="password" class="block text-gray-700 text-sm font-semibold mb-2">
                        <i class="fas fa-lock mr-1"></i> Password
                    </label>
                    <input type="password" name="password" id="password"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                           placeholder="Minimal 6 karakter" required minlength="6">
                </div>

                <!-- Konfirmasi Password -->
                <div class="mb-4">
                    <label for="confirm_password" class="block text-gray-700 text-sm font-semibold mb-2">
                        <i class="fas fa-lock mr-1"></i> Konfirmasi Password
                    </label>
                    <input type="password" name="confirm_password" id="confirm_password"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                           placeholder="Ulangi password" required minlength="6">
                </div>

                <!-- Role -->
                <div class="mb-6">
                    <label for="role" class="block text-gray-700 text-sm font-semibold mb-2">
                        <i class="fas fa-user-tag mr-1"></i> Role
                    </label>
                    <select name="role" id="role" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200">
                        <option value="">-- Pilih Role --</option>
                        <option value="admin" <?= (isset($_POST['role']) && $_POST['role'] == 'admin') ? 'selected' : '' ?>>Admin</option>
                        <option value="perangkat" <?= (isset($_POST['role']) && $_POST['role'] == 'perangkat') ? 'selected' : '' ?>>Perangkat Desa</option>
                        <option value="warga" <?= (isset($_POST['role']) && $_POST['role'] == 'warga') ? 'selected' : '' ?>>Warga</option>
                    </select>
                </div>

                <button type="submit" name="register"
                        class="w-full bg-gradient-to-r from-green-400 to-blue-500 hover:from-green-500 hover:to-blue-600 text-white font-bold py-3 px-4 rounded-lg shadow-lg transform transition hover:scale-105 duration-200">
                    <i class="fas fa-save mr-2"></i> Daftarkan Akun
                </button>
                <!-- Di akhir form, sebelum atau sesudah tombol -->
<div class="text-center mt-4">
    <a href="register_batch.php" class="text-indigo-600 hover:text-indigo-800 font-semibold hover:underline transition">
        <i class="fas fa-upload mr-1"></i> Ingin mendaftarkan banyak akun sekaligus? Upload Excel
    </a>
</div>
            </form>

            <!-- Link kembali ke Dashboard -->
            <div class="text-center mt-6">
                <a href="../dashboard/index.php" class="text-blue-600 hover:text-blue-800 font-semibold hover:underline transition">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>

        <!-- Footer -->
        <p class="text-center text-white text-sm mt-6 opacity-80">
            &copy; 2026 Monitoring Warga  Rahmanullah - Desa Kincang. All rights reserved.
        </p>
    </div>
</div>

</body>
</html>