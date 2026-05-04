<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Layout\ImageDirectory;
use Layout\ImageFinder;
use Layout\LabelPdfGenerator;
use Layout\PaperSizes;

$paperSizeInput = (string) ($_GET['paper_size'] ?? '');
$imageDirectoryInput = trim((string) ($_GET['image_directory'] ?? ''));
$paperSize = PaperSizes::normalize($paperSizeInput);

if ($paperSize === null) {
    redirectToForm('Choose a supported paper size.', $paperSizeInput, $imageDirectoryInput);
}

try {
    $resolvedDirectory = ImageDirectory::resolve(__DIR__, $imageDirectoryInput);
    $imagePaths = ImageFinder::findPngFiles($resolvedDirectory);

    if ($imagePaths === []) {
        throw new RuntimeException('No PNG images were found in the selected directory.');
    }
} catch (\RuntimeException $exception) {
    redirectToForm($exception->getMessage(), $paperSize, $imageDirectoryInput);
}

$generator = new LabelPdfGenerator();
$generator->outputInline($imagePaths, $paperSize, sprintf('labels-%s.pdf', strtolower($paperSize)));

function redirectToForm(string $errorMessage, string $paperSize, string $imageDirectory): never
{
    header(
        'Location: index.php?' . http_build_query([
            'error' => $errorMessage,
            'paper_size' => $paperSize,
            'image_directory' => $imageDirectory,
        ])
    );

    exit;
}
