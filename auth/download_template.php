<?php
require_once '../vendor/autoload.php';

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;

// Set header untuk download file Excel
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="template_register.xlsx"');

// Buat writer XLSX
$writer = WriterEntityFactory::createXLSXWriter();
$writer->openToBrowser('template_register.xlsx');

// Header kolom
$headerRow = WriterEntityFactory::createRowFromArray(['NIK', 'Nama', 'Email', 'Password', 'Role']);
$writer->addRow($headerRow);

// Contoh data baris 1
$row1 = WriterEntityFactory::createRowFromArray([
    '1234567890123456',
    'John Doe',
    'john@example.com',
    'rahasia123',
    'warga'
]);
$writer->addRow($row1);

// Contoh data baris 2
$row2 = WriterEntityFactory::createRowFromArray([
    '1234567890123457',
    'Jane Smith',
    'jane@example.com',
    'rahasia456',
    'perangkat'
]);
$writer->addRow($row2);

$writer->close();
exit;