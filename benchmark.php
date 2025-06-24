<?php

require 'vendor/autoload.php';

use FPDEV\Images\CompressAndConvert;

ini_set('memory_limit', '512M');

$inputDir = __DIR__ . '/input_files';
$outputDir = __DIR__ . '/output_files';

$formatsToTest = ['jpg', 'webp', 'png'];
$qualities = [100, 90, 80, 70];

echo "Benchmarking cross-format compression/conversion...\n\n";

printf(
    "%-20s %-6s %-6s %-6s %-15s %-15s %-10s\n",
    'Filename', 'From', 'To', 'Q', 'Orig. Size (KB)', 'New Size (KB)', '% Saved'
);
echo str_repeat('-', 90) . "\n";

// Scansione iniziale per trovare immagini valide
$initialTool = new CompressAndConvert('jpg', 100); // estensione temporanea
[$acceptedFiles] = $initialTool->getFileArraysFromDir($inputDir);

foreach ($formatsToTest as $targetExt) {
    foreach ($qualities as $quality) {
        $tool = new CompressAndConvert($targetExt, $quality);

        foreach ($acceptedFiles as $fileArray) {
            $originalPath = $inputDir . '/' . $fileArray['filename'];

            $outputFilename = $tool->compressConvertAndSave($fileArray, $inputDir, $outputDir);
            $compressedPath = $outputDir . '/' . $outputFilename;

            $originalSize = filesize($originalPath) / 1024;
            $compressedSize = filesize($compressedPath) / 1024;
            $percentSaved = (($originalSize - $compressedSize) / $originalSize) * 100;

            printf(
                "%-20s %-6s %-6s %-6s %-15.2f %-15.2f %-10.1f%%\n",
                $fileArray['filename'],
                strtolower($fileArray['ext']),
                $targetExt,
                $quality,
                $originalSize,
                $compressedSize,
                $percentSaved
            );
        }
    }
}

echo "\nBenchmark completato.\n";