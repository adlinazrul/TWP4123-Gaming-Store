<?php
require 'vendor/autoload.php';  // Make sure path is correct

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();

// Set document properties (optional)
$spreadsheet->getProperties()->setCreator('Your Name')
    ->setTitle('Sample Excel File');

// Add some data
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', 'Name');
$sheet->setCellValue('B1', 'Email');
$sheet->setCellValue('A2', 'John Doe');
$sheet->setCellValue('B2', 'john@example.com');
$sheet->setCellValue('A3', 'Jane Smith');
$sheet->setCellValue('B3', 'jane@example.com');

// Write to file (Xlsx)
$writer = new Xlsx($spreadsheet);

// Output directly to browser for download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="sample.xlsx"');
header('Cache-Control: max-age=0');

$writer->save('php://output');
exit;
