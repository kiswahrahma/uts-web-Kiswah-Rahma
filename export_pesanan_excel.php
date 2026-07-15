<?php
// ============================================
// FILE: export_pesanan_excel.php
// ============================================

session_start();
include "config.php";
<<<<<<< HEAD
include "auth.php";
require_admin();
=======

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
>>>>>>> a5ebe0b1735c3f14f69185f4b1a313b582a1a213

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

$query = "SELECT pesanan.*, users.username AS username_user, 
                 pesanan_detail.jumlah, pesanan_detail.harga_satuan, pesanan_detail.subtotal,
                 menu.nama_menu
          FROM pesanan
          JOIN users ON pesanan.user_id = users.id
          LEFT JOIN pesanan_detail ON pesanan.id = pesanan_detail.pesanan_id
          LEFT JOIN menu ON pesanan_detail.menu_id = menu.id
          ORDER BY pesanan.id ASC, pesanan_detail.id ASC";
$result = mysqli_query($koneksi, $query);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Semua Pesanan');

// Header Kolom
$sheet->setCellValue('A1', 'ID Pesanan');
$sheet->setCellValue('B1', 'Username Pelanggan');
$sheet->setCellValue('C1', 'Tanggal');
$sheet->setCellValue('D1', 'Status');
<<<<<<< HEAD
$sheet->setCellValue('E1', 'Metode Pembayaran');
$sheet->setCellValue('F1', 'Catatan');
$sheet->setCellValue('G1', 'Nama Menu');
$sheet->setCellValue('H1', 'Jumlah');
$sheet->setCellValue('I1', 'Harga Satuan');
$sheet->setCellValue('J1', 'Subtotal');

$sheet->getStyle('A1:J1')->getFont()->setBold(true);
$sheet->getStyle('A1:J1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
=======
$sheet->setCellValue('E1', 'Catatan');
$sheet->setCellValue('F1', 'Nama Menu');
$sheet->setCellValue('G1', 'Jumlah');
$sheet->setCellValue('H1', 'Harga Satuan');
$sheet->setCellValue('I1', 'Subtotal');

$sheet->getStyle('A1:I1')->getFont()->setBold(true);
$sheet->getStyle('A1:I1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
>>>>>>> a5ebe0b1735c3f14f69185f4b1a313b582a1a213

$rowNum = 2;
while ($row = mysqli_fetch_assoc($result)) {
    $sheet->setCellValue("A{$rowNum}", $row['id']);
    $sheet->setCellValue("B{$rowNum}", $row['username_user']);
    $sheet->setCellValue("C{$rowNum}", $row['tanggal']);
    $sheet->setCellValue("D{$rowNum}", $row['status']);
<<<<<<< HEAD
    $sheet->setCellValue("E{$rowNum}", $row['metode_pembayaran'] ?: 'Tunai');
    $sheet->setCellValue("F{$rowNum}", $row['catatan'] ?: '');
    $sheet->setCellValue("G{$rowNum}", $row['nama_menu'] ?: '');
    $sheet->setCellValue("H{$rowNum}", $row['jumlah'] ?: 0);
    $sheet->setCellValue("I{$rowNum}", $row['harga_satuan'] ?: 0);
    $sheet->setCellValue("J{$rowNum}", $row['subtotal'] ?: 0);
=======
    $sheet->setCellValue("E{$rowNum}", $row['catatan'] ?: '');
    $sheet->setCellValue("F{$rowNum}", $row['nama_menu'] ?: '');
    $sheet->setCellValue("G{$rowNum}", $row['jumlah'] ?: 0);
    $sheet->setCellValue("H{$rowNum}", $row['harga_satuan'] ?: 0);
    $sheet->setCellValue("I{$rowNum}", $row['subtotal'] ?: 0);
>>>>>>> a5ebe0b1735c3f14f69185f4b1a313b582a1a213
    $rowNum++;
}

// Styling Border & Alignment
$thinBorder = [
    'allBorders' => [
        'borderStyle' => Border::BORDER_THIN,
        'color' => ['argb' => 'FFCCCCCC'],
    ],
];
<<<<<<< HEAD
$sheet->getStyle("A1:J" . ($rowNum - 1))->applyFromArray($thinBorder);

// Autofit Kolom
foreach (range('A', 'J') as $col) {
=======
$sheet->getStyle("A1:I" . ($rowNum - 1))->applyFromArray($thinBorder);

// Autofit Kolom
foreach (range('A', 'I') as $col) {
>>>>>>> a5ebe0b1735c3f14f69185f4b1a313b582a1a213
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

$filename = 'Daftar_Semua_Pesanan.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
