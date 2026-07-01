<?php

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

try {
    echo "Creating spreadsheet...\n";
    $spreadsheet = new Spreadsheet();
    echo "Spreadsheet created successfully!\n";
    
    $sheet = $spreadsheet->getActiveSheet();
    echo "Got active sheet!\n";
    
    $sheet->setCellValue('A1', 'Test');
    echo "Set cell value!\n";
    
    $writer = new Xlsx($spreadsheet);
    echo "Writer created successfully!\n";
    
    echo "\nPhpSpreadsheet is working correctly!\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
