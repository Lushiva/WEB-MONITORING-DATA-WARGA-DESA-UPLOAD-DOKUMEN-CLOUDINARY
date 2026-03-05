<?php
require_once '../vendor/autoload.php';

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;

// Set header untuk download file Excel
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="template_warga.xlsx"');

// Buat writer XLSX
$writer = WriterEntityFactory::createXLSXWriter();
$writer->openToBrowser('template_warga.xlsx');

// Header kolom
$header = ['NIK', 'Nama', 'Jenis Kelamin (L/P)', 'Pekerjaan', 'Penghasilan', 'Jumlah Tanggungan', 'RT', 'RW'];
$headerRow = WriterEntityFactory::createRowFromArray($header);
$writer->addRow($headerRow);

// Contoh data baris 1
$row1 = WriterEntityFactory::createRowFromArray([
    '330410604060001',
    'Contoh Warga 1',
    'L',
    'Petani',
    '500000',
    '3',
    '001',
    '002'
]);
$writer->addRow($row1);

// Contoh data baris 2
$row2 = WriterEntityFactory::createRowFromArray([
    '330410604060002',
    'Contoh Warga 2',
    'P',
    'Buruh',
    '2500000',
    '2',
    '003',
    '002'
]);
$writer->addRow($row2);

$writer->close();
exit;