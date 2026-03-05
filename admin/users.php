<?php
error_reporting(E_ALL & ~E_DEPRECATED);
session_start();
include "../config/database.php";

// Cek login dan role admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];

// Pencarian
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Query dengan prepared statement
if ($search !== '') {
    $stmt = $conn->prepare("SELECT * FROM users WHERE nama LIKE ? ORDER BY created_at DESC");
    $like = "%$search%";
    $stmt->bind_param("s", $like);
} else {
    $stmt = $conn->prepare("SELECT * FROM users ORDER BY created_at DESC");
}
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - Monitoring Desa</title>
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
                <span class="font-bold text-xl text-gray-800">Manajemen User</span>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-gray-600"><i class="fas fa-user mr-1"></i> <?= htmlspecialchars($user['nama']) ?> (Admin)</span>
                <a href="../dashboard/index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm transition">
                    <i class="fas fa-arrow-left mr-1"></i> Dashboard
                </a>
                <a href="../auth/logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm transition">
                    <i class="fas fa-sign-out-alt mr-1"></i> Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Konten Utama -->
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Flash Message -->
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

    <!-- Header dan Tombol Aksi -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-4 sm:mb-0">
            <i class="fas fa-list mr-2 text-green-600"></i>Daftar User
        </h1>
        <div class="flex space-x-2">
            <a href="../auth/register.php" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg shadow flex items-center transition">
                <i class="fas fa-plus-circle mr-2"></i> Tambah Akun
            </a>
        </div>
    </div>

    <!-- Form Pencarian -->
    <div class="mb-6">
        <form method="GET" action="" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                       placeholder="Cari berdasarkan nama..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center justify-center transition">
                <i class="fas fa-search mr-2"></i> Cari
            </button>
            <?php if ($search !== ''): ?>
                <a href="users.php" class="bg-gray-400 hover:bg-gray-500 text-white px-6 py-2 rounded-lg flex items-center justify-center transition">
                    <i class="fas fa-times mr-2"></i> Reset
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Form Hapus Massal (mengelilingi tabel) -->
    <form id="massDeleteForm" method="POST" action="hapus_massal_users.php" onsubmit="return confirm('Yakin ingin menghapus user yang dipilih?')">
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIK</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terdaftar</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (count($users) > 0): ?>
                            <?php foreach ($users as $u): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" name="selected_ids[]" value="<?= $u['id'] ?>" class="row-checkbox rounded border-gray-300 text-green-600 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($u['nik'] ?: '-') ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($u['nama']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($u['email']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $roleClass = '';
                                        if ($u['role'] == 'admin') $roleClass = 'bg-purple-100 text-purple-800';
                                        elseif ($u['role'] == 'perangkat') $roleClass = 'bg-blue-100 text-blue-800';
                                        else $roleClass = 'bg-green-100 text-green-800';
                                        ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $roleClass ?>">
                                            <?= $u['role'] ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= date('d-m-Y', strtotime($u['created_at'])) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="edit_user.php?id=<?= $u['id'] ?>" class="text-blue-600 hover:text-blue-900 mr-3" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="hapus_user.php?id=<?= $u['id'] ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Yakin ingin menghapus user ini?')" title="Hapus">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                    <?php if ($search !== ''): ?>
                                        Tidak ada user yang cocok dengan pencarian "<?= htmlspecialchars($search) ?>".
                                    <?php else: ?>
                                        Belum ada user terdaftar.
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tombol Hapus Terpilih (di luar tabel) -->
        <?php if (count($users) > 0): ?>
            <div class="mt-4 flex justify-end">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg shadow flex items-center transition">
                    <i class="fas fa-trash-alt mr-2"></i> Hapus Terpilih
                </button>
            </div>
        <?php endif; ?>
    </form>
</main>

<!-- Script untuk Check All -->
<script>
    document.getElementById('selectAll').addEventListener('change', function() {
        let checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });
</script>

</body>
</html>