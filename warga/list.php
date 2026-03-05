<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user = $_SESSION['user'];

// Ambil parameter pencarian, halaman, dan limit
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$limitOptions = [10, 25, 50, 100];
if (!in_array($limit, $limitOptions)) $limit = 10;

$offset = ($page - 1) * $limit;

// Query untuk menghitung total data (dengan atau tanpa pencarian)
if ($search !== '') {
    $countStmt = $conn->prepare("SELECT COUNT(*) as total FROM data_warga WHERE nik LIKE ? OR nama LIKE ?");
    $like = "%$search%";
    $countStmt->bind_param("ss", $like, $like);
} else {
    $countStmt = $conn->prepare("SELECT COUNT(*) as total FROM data_warga");
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalData = $countResult->fetch_assoc()['total'];
$countStmt->close();

$totalPages = ceil($totalData / $limit);

// Pastikan page tidak melebihi total halaman
if ($page < 1) $page = 1;
if ($page > $totalPages && $totalPages > 0) $page = $totalPages;

// Query data dengan limit dan offset
if ($search !== '') {
    $stmt = $conn->prepare("SELECT * FROM data_warga WHERE nik LIKE ? OR nama LIKE ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->bind_param("ssii", $like, $like, $limit, $offset);
} else {
    $stmt = $conn->prepare("SELECT * FROM data_warga ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$data = $stmt->get_result();
$wargaList = $data->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Warga - Monitoring Desa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans">

<!-- Navbar -->
<nav class="bg-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center">
                <i class="fas fa-address-book text-green-600 text-2xl mr-2"></i>
                <span class="font-bold text-xl text-gray-800">Data Warga</span>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-gray-600"><i class="fas fa-user mr-1"></i> <?= htmlspecialchars($user['nama']) ?></span>
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
            <i class="fas fa-list mr-2 text-green-600"></i>Daftar Warga
        </h1>
        <div class="flex flex-wrap gap-2">
            <a href="form_input.php" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg shadow flex items-center transition">
                <i class="fas fa-plus-circle mr-2"></i> Tambah Data
            </a>
            <a href="import_batch.php" class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-2 rounded-lg shadow flex items-center transition">
                <i class="fas fa-upload mr-2"></i> Import Excel
            </a>
            <a href="export_excel.php" class="bg-orange-600 hover:bg-orange-700 text-white px-5 py-2 rounded-lg shadow flex items-center transition">
    <i class="fas fa-file-excel mr-2"></i> Export Excel
</a>
        </div>
    </div>

    <!-- Form Pencarian dan Pengaturan Limit -->
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <form method="GET" action="" class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
            <div class="flex-1">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                       placeholder="Cari berdasarkan NIK atau Nama..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center justify-center transition">
                <i class="fas fa-search mr-2"></i> Cari
            </button>
            <?php if ($search !== ''): ?>
                <a href="list.php" class="bg-gray-400 hover:bg-gray-500 text-white px-6 py-2 rounded-lg flex items-center justify-center transition">
                    <i class="fas fa-times mr-2"></i> Reset
                </a>
            <?php endif; ?>
        </form>

        <!-- Dropdown Limit -->
        <form method="GET" action="" class="flex items-center gap-2">
            <label for="limit" class="text-sm text-gray-600">Tampilkan:</label>
            <select name="limit" id="limit" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <?php foreach ($limitOptions as $opt): ?>
                    <option value="<?= $opt ?>" <?= $limit == $opt ? 'selected' : '' ?>><?= $opt ?></option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
            <input type="hidden" name="page" value="1">
        </form>
    </div>

    <!-- Form Hapus Massal -->
    <?php if ($totalData > 0): ?>
    <form id="massDeleteForm" method="POST" action="hapus_massal_warga.php" onsubmit="return confirm('Yakin ingin menghapus data warga yang dipilih?')">
    <?php endif; ?>

    <!-- Tabel Data Warga -->
    <table class="min-w-full table-auto divide-y divide-gray-200">
    <thead class="bg-gray-50">
        <tr>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">
                <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50">
            </th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">No</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">NIK</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-40">Nama</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">JK</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Pekerjaan</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Penghasilan</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">Tanggungan</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">RT/RW</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Status</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Aksi</th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        <?php if ($totalData > 0): ?>
            <?php $no = 1; ?>
            <?php foreach ($wargaList as $row): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2 whitespace-nowrap text-sm">
                        <input type="checkbox" name="selected_ids[]" value="<?= $row['id'] ?>" class="row-checkbox rounded border-gray-300 text-green-600 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50">
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900"><?= $no++ ?></td>
                    <td class="px-3 py-2 text-sm text-gray-900 truncate max-w-[8rem]"><?= htmlspecialchars($row['nik']) ?></td>
                    <td class="px-3 py-2 text-sm text-gray-900 truncate max-w-[10rem]"><?= htmlspecialchars($row['nama']) ?></td>
                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900"><?= $row['jenis_kelamin'] == 'L' ? 'L' : 'P' ?></td>
                    <td class="px-3 py-2 text-sm text-gray-900 truncate max-w-[8rem]"><?= htmlspecialchars($row['pekerjaan'] ?: '-') ?></td>
                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">Rp <?= number_format($row['penghasilan'], 0, ',', '.') ?></td>
                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900"><?= $row['jumlah_tanggungan'] ?></td>
                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($row['rt'] ?: '-') ?>/<?= htmlspecialchars($row['rw'] ?: '-') ?></td>
                    <td class="px-3 py-2 whitespace-nowrap">
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
                    <td class="px-3 py-2 whitespace-nowrap text-sm font-medium">
                        <a href="detail.php?id=<?= $row['id'] ?>" class="text-green-600 hover:text-green-900 mr-2" title="Detail">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="upload.php?id=<?= $row['id'] ?>" class="text-purple-600 hover:text-purple-900 mr-2" title="Upload Dokumen">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </a>
                        <a href="edit.php?id=<?= $row['id'] ?>" class="text-blue-600 hover:text-blue-900 mr-2" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="hapus.php?id=<?= $row['id'] ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Yakin ingin menghapus data ini?')" title="Hapus">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="11" class="px-3 py-4 text-center text-gray-500">Tidak ada data</td></tr>
        <?php endif; ?>
    </tbody>
</table>

    <!-- Tombol Hapus Terpilih dan Pagination -->
    <?php if ($totalData > 0): ?>
        <div class="mt-4 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="text-sm text-gray-600">
                Menampilkan <?= $offset + 1 ?> - <?= min($offset + $limit, $totalData) ?> dari <?= $totalData ?> data
            </div>
            <div class="flex items-center space-x-2">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg shadow flex items-center transition">
                    <i class="fas fa-trash-alt mr-2"></i> Hapus Terpilih
                </button>
            </div>
        </div>
    </form>
    <?php endif; ?>

    <!-- Pagination Links -->
    <?php if ($totalPages > 1): ?>
    <div class="mt-6 flex justify-center">
        <nav class="inline-flex rounded-md shadow">
            <?php if ($page > 1): ?>
                <a href="?search=<?= urlencode($search) ?>&limit=<?= $limit ?>&page=<?= $page-1 ?>" class="px-3 py-2 bg-white border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-l-md">
                    <i class="fas fa-chevron-left"></i>
                </a>
            <?php else: ?>
                <span class="px-3 py-2 bg-gray-100 border border-gray-300 text-sm font-medium text-gray-400 rounded-l-md cursor-not-allowed">
                    <i class="fas fa-chevron-left"></i>
                </span>
            <?php endif; ?>

            <?php
            $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);
            for ($i = $start; $i <= $end; $i++):
            ?>
                <?php if ($i == $page): ?>
                    <span class="px-4 py-2 bg-green-600 border border-green-600 text-sm font-medium text-white"><?= $i ?></span>
                <?php else: ?>
                    <a href="?search=<?= urlencode($search) ?>&limit=<?= $limit ?>&page=<?= $i ?>" class="px-4 py-2 bg-white border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?search=<?= urlencode($search) ?>&limit=<?= $limit ?>&page=<?= $page+1 ?>" class="px-3 py-2 bg-white border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-r-md">
                    <i class="fas fa-chevron-right"></i>
                </a>
            <?php else: ?>
                <span class="px-3 py-2 bg-gray-100 border border-gray-300 text-sm font-medium text-gray-400 rounded-r-md cursor-not-allowed">
                    <i class="fas fa-chevron-right"></i>
                </span>
            <?php endif; ?>
        </nav>
    </div>
    <?php endif; ?>

</main>

<!-- Script untuk Check All -->
<script>
    document.getElementById('selectAll')?.addEventListener('change', function() {
        let checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });
</script>

</body>
</html>