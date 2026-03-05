<?php
session_start();
include "../config/database.php";

// Cek login
if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user = $_SESSION['user'];

// Jika role warga, redirect ke halaman khusus warga
if ($user['role'] == 'warga') {
    header("Location: warga.php");
    exit;
}


// Ambil statistik dengan prepared statement
$stmt = $conn->prepare("SELECT status_kemiskinan, COUNT(*) as jumlah FROM data_warga GROUP BY status_kemiskinan");
$stmt->execute();
$result = $stmt->get_result();

$stat = ['Miskin' => 0, 'Rentan' => 0, 'Sejahtera' => 0];
while ($row = $result->fetch_assoc()) {
    $stat[$row['status_kemiskinan']] = $row['jumlah'];
}
$stmt->close();

// Total warga
$stmt_total = $conn->prepare("SELECT COUNT(*) as total FROM data_warga");
$stmt_total->execute();
$total_warga = $stmt_total->get_result()->fetch_assoc()['total'];
$stmt_total->close();

// Ambil 5 data terbaru untuk ditampilkan di tabel
$stmt_recent = $conn->prepare("SELECT nik, nama, status_kemiskinan FROM data_warga ORDER BY created_at DESC LIMIT 5");
$stmt_recent->execute();
$recent = $stmt_recent->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Monitoring Warga  Rahmanullah - Desa Kincang</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: #f3f4f6;
        }
        .stat-card {
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body class="font-sans antialiased">

<!-- Navbar sederhana -->
<nav class="bg-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center">
                <i class="fas fa-chart-pie text-green-600 text-2xl mr-2"></i>
                <span class="font-bold text-xl text-gray-800">Monitoring Warga  Rahmanullah - Desa Kincang</span>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-gray-600">
                    <i class="fas fa-user mr-1"></i> <?= htmlspecialchars($user['nama']) ?> (<?= $user['role'] ?>)
                </span>
                    <?php if ($user['role'] == 'admin'): ?>
    <a href="../admin/users.php" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg text-sm transition">
        <i class="fas fa-users-cog mr-1"></i> Manajemen User
    </a>
<?php endif; ?>
                
                <a href="../auth/logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm transition">
                    <i class="fas fa-sign-out-alt mr-1"></i> Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Konten Utama -->
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
        <p class="text-gray-600">Selamat datang, <?= htmlspecialchars($user['nama']) ?>!</p>
    </div>

    <!-- Statistik Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Warga -->
        <div class="stat-card bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Warga</p>
                    <p class="text-3xl font-bold text-gray-800"><?= $total_warga ?></p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-users text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>
        <!-- Miskin -->
        <div class="stat-card bg-white rounded-xl shadow-md p-6 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Miskin</p>
                    <p class="text-3xl font-bold text-red-600"><?= $stat['Miskin'] ?></p>
                </div>
                <div class="bg-red-100 p-3 rounded-full">
                    <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                </div>
            </div>
        </div>
        <!-- Rentan -->
        <div class="stat-card bg-white rounded-xl shadow-md p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Rentan</p>
                    <p class="text-3xl font-bold text-yellow-600"><?= $stat['Rentan'] ?></p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-full">
                    <i class="fas fa-chart-line text-yellow-600 text-2xl"></i>
                </div>
            </div>
        </div>
        <!-- Sejahtera -->
        <div class="stat-card bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Sejahtera</p>
                    <p class="text-3xl font-bold text-green-600"><?= $stat['Sejahtera'] ?></p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik dan Tabel Recent -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Grafik Pie -->
        <div class="bg-white rounded-xl shadow-md p-6 lg:col-span-1">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-chart-pie mr-2 text-green-600"></i>Komposisi Kemiskinan
            </h2>
            <canvas id="chartKemiskinan" width="400" height="400"></canvas>
        </div>

        <!-- Tabel Data Terbaru -->
        <div class="bg-white rounded-xl shadow-md p-6 lg:col-span-2">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-clock mr-2 text-blue-600"></i>Data Terbaru
                </h2>
                <a href="../warga/list.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIK</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if ($recent->num_rows > 0): ?>
                            <?php while ($row = $recent->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($row['nik']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($row['nama']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $badgeColor = '';
                                        if ($row['status_kemiskinan'] == 'Miskin') $badgeColor = 'bg-red-100 text-red-800';
                                        elseif ($row['status_kemiskinan'] == 'Rentan') $badgeColor = 'bg-yellow-100 text-yellow-800';
                                        else $badgeColor = 'bg-green-100 text-green-800';
                                        ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $badgeColor ?>">
                                            <?= $row['status_kemiskinan'] ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">Belum ada data warga.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tombol Aksi Cepat -->
    <div class="mt-8 flex flex-wrap gap-4">
        <a href="../warga/form_input.php" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg shadow flex items-center transition">
            <i class="fas fa-plus-circle mr-2"></i> Input Data Warga Baru
        </a>
        <a href="../warga/list.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg shadow flex items-center transition">
            <i class="fas fa-list mr-2"></i> Kelola Data Warga
        </a>
    </div>
</main>

<!-- Script Chart -->
<script>
    const ctx = document.getElementById('chartKemiskinan').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Miskin', 'Rentan', 'Sejahtera'],
            datasets: [{
                data: [<?= $stat['Miskin'] ?>, <?= $stat['Rentan'] ?>, <?= $stat['Sejahtera'] ?>],
                backgroundColor: ['#ef4444', '#facc15', '#22c55e'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
</script>

</body>
</html>