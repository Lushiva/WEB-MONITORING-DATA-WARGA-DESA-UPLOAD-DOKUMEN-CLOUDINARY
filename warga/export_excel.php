<?php
error_reporting(E_ALL & ~E_DEPRECATED);
session_start();
include "../config/database.php";
require_once '../vendor/autoload.php';

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Type;

// Cek login
if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Ambil semua data warga
$query = "SELECT nik, nama, jenis_kelamin, pekerjaan, penghasilan, jumlah_tanggungan, rt, rw, status_kemiskinan, created_at FROM data_warga ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

// Buat writer Excel (XLSX)
$writer = WriterEntityFactory::createXLSXWriter();

// Set header untuk download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="data_warga_' . date('Y-m-d') . '.xlsx"');

$writer->openToBrowser('data_warga_' . date('Y-m-d') . '.xlsx');

// Buat header kolom
$headerRow = WriterEntityFactory::createRowFromArray([
    'NIK', 'Nama', 'Jenis Kelamin', 'Pekerjaan', 'Penghasilan', 
    'Jumlah Tanggungan', 'RT', 'RW', 'Status Kemiskinan', 'Tanggal Input'
]);
$writer->addRow($headerRow);

// Tambahkan data per baris
while ($row = mysqli_fetch_assoc($result)) {
    $jk = ($row['jenis_kelamin'] == 'L') ? 'Laki-laki' : 'Perempuan';
    $penghasilan = 'Rp ' . number_format($row['penghasilan'], 0, ',', '.');
    $tanggal = date('d-m-Y', strtotime($row['created_at']));
    
    $dataRow = WriterEntityFactory::createRowFromArray([
        $row['nik'],
        $row['nama'],
        $jk,
        $row['pekerjaan'] ?: '-',
        $penghasilan,
        $row['jumlah_tanggungan'] . ' orang',
        $row['rt'] ?: '-',
        $row['rw'] ?: '-',
        $row['status_kemiskinan'],
        $tanggal
    ]);
    $writer->addRow($dataRow);
}

$writer->close();
exit;