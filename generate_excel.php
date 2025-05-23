<?php
require 'vendor/autoload.php'; // Path to Composer autoload

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

// DB connection settings
$host = 'localhost';
$dbname = 'gaming_store';
$username = 'root'; // change if needed
$password = '';     // change if needed

// Connect to database
$pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

// Tables to export
$tables = ['admin_list', 'customers', 'items_ordered', 'products', 'product_categories'];

$spreadsheet = new Spreadsheet();
$sheetIndex = 0;

foreach ($tables as $table) {
    // Run query to get all data
    $stmt = $pdo->query("SELECT * FROM $table");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Create or select sheet
    if ($sheetIndex > 0) {
        $spreadsheet->createSheet();
    }
    $sheet = $spreadsheet->setActiveSheetIndex($sheetIndex);
    $sheet->setTitle($table);

    // Add header row (column names)
    if (!empty($rows)) {
        $colIndex = 1;
        foreach (array_keys($rows[0]) as $columnName) {
            $cell = Coordinate::stringFromColumnIndex($colIndex) . '1';
            $sheet->setCellValue($cell, $columnName);
            $colIndex++;
        }

        // Add data rows starting from row 2
        $rowIndex = 2;
        foreach ($rows as $row) {
            $colIndex = 1;
            foreach ($row as $value) {
                $cell = Coordinate::stringFromColumnIndex($colIndex) . $rowIndex;
                $sheet->setCellValue($cell, $value);
                $colIndex++;
            }
            $rowIndex++;
        }
    }

    $sheetIndex++;
}

// Set active sheet back to first
$spreadsheet->setActiveSheetIndex(0);

// Output Excel
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="gaming_store_report.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
