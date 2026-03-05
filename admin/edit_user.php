<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$admin = $_SESSION['user'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['error'] = "ID tidak valid.";
    header("Location: users.php");
    exit;
}

// Ambil data user
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    $_SESSION['error'] = "User tidak ditemukan.";
    header("Location: users.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $nik = trim($_POST['nik']);
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $errors = [];

    // Validasi NIK (16 digit angka)
    if (!preg_match('/^\d{16}$/', $nik)) {
        $errors[] = "NIK harus 16 digit angka.";
    }
    if (empty($nama)) {
        $errors[] = "Nama tidak boleh kosong.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email tidak valid.";
    }
    if (!in_array($role, ['admin', 'perangkat', 'warga'])) {
        $errors[] = "Role tidak valid.";
    }

    // Jika ada password baru, validasi
    $updatePassword = false;
    if (!empty($password)) {
        if (strlen($password) < 6) {
            $errors[] = "Password minimal 6 karakter.";
        } elseif ($password !== $confirm_password) {
            $errors[] = "Konfirmasi password tidak cocok.";
        } else {
            $updatePassword = true;
        }
    }

    // Cek duplikat NIK/email (kecuali miliknya sendiri)
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE (nik = ? OR email = ?) AND id != ?");
        $stmt->bind_param("ssi", $nik, $email, $id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "NIK atau Email sudah digunakan oleh user lain.";
        }
        $stmt->close();
    }

    if (empty($errors)) {
        if ($updatePassword) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET nik=?, nama=?, email=?, password=?, role=? WHERE id=?");
            $stmt->bind_param("sssssi", $nik, $nama, $email, $hashed, $role, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET nik=?, nama=?, email=?, role=? WHERE id=?");
            $stmt->bind_param("ssssi", $nik, $nama, $email, $role, $id);
        }

        if ($stmt->execute()) {
            $_SESSION['success'] = "Data user berhasil diperbarui.";
            header("Location: users.php");
            exit;
        } else {
            $errors[] = "Terjadi kesalahan database: " . $conn->error;
        }
        $stmt->close();
    }

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
    <title>Edit User - Monitoring Desa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans">

<nav class="bg-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center">
                <i class="fas fa-user-edit text-green-600 text-2xl mr-2"></i>
                <span class="font-bold text-xl text-gray-800">Edit User</span>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-gray-600"><i class="fas fa-user mr-1"></i> <?= htmlspecialchars($admin['nama']) ?> (Admin)</span>
                <a href="users.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm transition">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
                <a href="../auth/logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm transition">
                    <i class="fas fa-sign-out-alt mr-1"></i> Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-xl shadow-md p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Edit User: <?= htmlspecialchars($user['nama']) ?></h1>

        <?php if ($error): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">NIK</label>
                <input type="text" name="nik" maxlength="16" required value="<?= htmlspecialchars($user['nik']) ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                <input type="text" name="nama" required value="<?= htmlspecialchars($user['nama']) ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" required value="<?= htmlspecialchars($user['email']) ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <select name="role" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="perangkat" <?= $user['role'] == 'perangkat' ? 'selected' : '' ?>>Perangkat Desa</option>
                    <option value="warga" <?= $user['role'] == 'warga' ? 'selected' : '' ?>>Warga</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru (kosongkan jika tidak diubah)</label>
                <input type="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
                <input type="password" name="confirm_password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
            </div>
            <div class="flex gap-3">
                <button type="submit" name="update" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg shadow flex items-center">
                    <i class="fas fa-save mr-2"></i> Update
                </button>
                <a href="users.php" class="bg-gray-400 hover:bg-gray-500 text-white px-6 py-2 rounded-lg shadow flex items-center">
                    <i class="fas fa-times mr-2"></i> Batal
                </a>
            </div>
        </form>
    </div>
</main>
</body>
</html>