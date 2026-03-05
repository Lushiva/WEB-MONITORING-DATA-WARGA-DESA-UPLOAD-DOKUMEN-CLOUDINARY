<?php
session_start();
include "../config/database.php";

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user'])) {
    header("Location: ../dashboard/index.php");
    exit;
}

$error = '';

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validasi dasar
    if (empty($email) || empty($password)) {
        $error = "Email dan password harus diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } else {
        // Gunakan prepared statement untuk menghindari SQL injection
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            header("Location: ../dashboard/index.php");
            exit;
        } else {
            $error = "Email atau password salah!";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Monitoring Warga Rahmanullah - Desa Kincang</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome untuk ikon (opsional) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .login-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.9);
        }
    </style>
</head>
<body class="font-sans antialiased">

<div class="min-h-screen flex flex-col items-center justify-center px-4 py-8">
    <!-- Container untuk dua kolom (form login + info) -->
    <div class="grid md:grid-cols-2 gap-6 max-w-5xl w-full items-stretch">
        
        <!-- Kolom Kiri: Form Login -->
        <div class="login-card rounded-2xl shadow-2xl p-8 border border-white/20 w-full">
            <!-- Header dengan ikon/logo -->
            <div class="text-center mb-8">
                <div class="bg-gradient-to-r from-green-400 to-blue-500 w-20 h-20 rounded-full mx-auto flex items-center justify-center shadow-lg">
                    <i class="fas fa-users text-white text-3xl"></i>
                </div>
                <h2 class="text-3xl font-bold text-gray-800 mt-4">Monitoring Warga Rahmanullah - Desa Kincang</h2>
                <p class="text-gray-600">Silakan login untuk melanjutkan</p>
            </div>

            <!-- Tampilkan pesan error jika ada -->
            <?php if ($error): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <!-- Form Login -->
            <form method="POST" action="">
                <div class="mb-6">
                    <label for="email" class="block text-gray-700 text-sm font-semibold mb-2">
                        <i class="fas fa-envelope mr-1"></i> Email
                    </label>
                    <input type="email" name="email" id="email" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                           placeholder="contoh@email.com" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>

                <div class="mb-6">
                    <label for="password" class="block text-gray-700 text-sm font-semibold mb-2">
                        <i class="fas fa-lock mr-1"></i> Password
                    </label>
                    <input type="password" name="password" id="password" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                           placeholder="••••••••" required>
                </div>

                <button type="submit" name="login" 
                        class="w-full bg-gradient-to-r from-green-400 to-blue-500 hover:from-green-500 hover:to-blue-600 text-white font-bold py-3 px-4 rounded-lg shadow-lg transform transition hover:scale-105 duration-200">
                    <i class="fas fa-sign-in-alt mr-2"></i> Login
                </button>
            </form>

            <!-- Link ke halaman register -->
            <div class="text-center mt-6">
                <p class="text-gray-600">
                    Belum punya akun? 
                    <a href="https://wa.me/+6285700997818?text=Saya%20Mau%20Daftar%20Akun%20Website%20Warga%20Rahmanulah" target="_blank" class="text-blue-600 hover:text-blue-800 font-semibold hover:underline transition">
                        Daftar sekarang <i class="fas fa-arrow-right ml-1 text-xs"></i>
                    </a>
                </p>
            </div>
        </div>

        <!-- Kolom Kanan: Kotak Informasi Website -->
        <div class="login-card rounded-2xl shadow-2xl p-8 border border-white/20 w-full flex flex-col">
            <div class="text-center mb-6">
                <div class="bg-gradient-to-r from-purple-400 to-pink-500 w-16 h-16 rounded-full mx-auto flex items-center justify-center shadow-lg">
                    <i class="fas fa-info-circle text-white text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mt-4">Tentang Aplikasi</h3>
            </div>

            <div class="space-y-4 text-gray-700">
                <p class="text-justify">
                    <span class="font-semibold">Monitoring Warga Rahmanullah - Desa Kincang</span> adalah sistem informasi berbasis web yang digunakan untuk memantau data kependudukan warga, serta sebagai database dokumen penting warga di Dusun Rahmanulah.
                </p>

                <div>
                    <h4 class="font-semibold text-lg mb-2 flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>Fitur Utama:
                    </h4>
                    <ul class="list-none space-y-2 pl-6">
                        <li class="flex items-start">
                            <i class="fas fa-database text-blue-500 mr-2 mt-1"></i>
                            <span>Pendataan warga terintegrasi (KK, KTP, dll)</span>
                        </li>

                        <li class="flex items-start">
                            <i class="fas fa-chart-line text-blue-500 mr-2 mt-1"></i>
                            <span>Grafik dan statistik kependudukan</span>
                        </li>
                    </ul>
                </div>

                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200 mt-4">
                    <p class="text-sm">
                        <i class="fas fa-phone-alt text-blue-600 mr-2"></i>
                        <strong>Butuh bantuan? atau Daftar Akun. Gratis!</strong> Hubungi melalui WhatsApp 
                        <a href="https://wa.me/+6285700997818" target="_blank" class="text-blue-600 font-semibold hover:underline">Asifa Ahmad</a>
                    </p>
                </div>

                <p class="text-xs text-gray-500 italic mt-4">
                    *Akses diberikan setelah akun disetujui oleh admin.
                </p>
            </div>
            
        </div>
        
    </div>
    

    <!-- Footer -->
    <p class="text-center text-white text-sm mt-8 opacity-80">
        &copy; 2026 Monitoring Warga Rahmanullah - Desa Kincang. <br> All rights reserved by:
        <a href="https://asifaahmad.web.app" target="_blank" class="text-white font-semibold hover:underline">Asifa Ahmad</a>
    </p>

    <!-- Kotak Peta Lokasi -->
<div class="login-card rounded-2xl shadow-2xl p-6 border border-white/20 w-full max-w-5xl mx-auto mt-6">
    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
        <i class="fas fa-map-marked-alt text-blue-600 mr-2"></i> Lokasi Dusun Rahmanulah
    </h3>
    <div class="relative w-full h-96 rounded-lg overflow-hidden">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d4868.283407987664!2d109.55454302588186!3d-7.41576847303912!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e6554af7ebbdce9%3A0x3ce4b601e4985b94!2sRahmanulah%2C%20Kincang%2C%20Rakit%2C%20Banjarnegara%2C%20Central%20Java!5e1!3m2!1sen!2sid!4v1772685393330!5m2!1sen!2sid" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" class="absolute top-0 left-0 w-full h-full"></iframe>
    </div>
</div>
</div>
</body>
</html>