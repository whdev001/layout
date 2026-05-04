<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Layout\ImageDirectory;
use Layout\ImageFinder;
use Layout\LabelPdfGenerator;
use Layout\PaperSizes;

$errors = [];
$old = [];
$paperSizes = PaperSizes::options();
$formAction = '';
$pageTitle = 'PDF Generator';

$parseCustomDimension = static function (string $value, string $field, array &$errors): ?float {
    $value = trim($value);

    if ($value === '') {
        $errors[$field][] = 'This value is required for a custom paper size.';
        return null;
    }

    if (!is_numeric($value)) {
        $errors[$field][] = 'Enter a numeric value in centimeters.';
        return null;
    }

    $dimension = (float) $value;

    if ($dimension <= 0) {
        $errors[$field][] = 'Enter a value greater than zero.';
        return null;
    }

    return $dimension;
};

$values = [
    'paper_size' => PaperSizes::default(),
    'custom_width' => '',
    'custom_height' => '',
    'image_directory' => 'Label Berlaku/',
];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $minimumPaperWidth = 24.3;
    $minimumPaperHeight = 18.3;

    $old = [
        'paper_size' => (string) ($_POST['paper_size'] ?? ''),
        'custom_width' => trim((string) ($_POST['custom_width'] ?? '')),
        'custom_height' => trim((string) ($_POST['custom_height'] ?? '')),
        'image_directory' => trim((string) ($_POST['image_directory'] ?? '')),
    ];

    $values = $old;

    $paperSize = null;
    $downloadName = null;

    if (PaperSizes::isCustom($old['paper_size'])) {
        $customWidth = $parseCustomDimension($old['custom_width'], 'custom_width', $errors);
        $customHeight = $parseCustomDimension($old['custom_height'], 'custom_height', $errors);

        if ($customWidth !== null && $customHeight !== null) {
            if ($customWidth < $minimumPaperWidth || $customHeight < $minimumPaperHeight) {
                $errors['custom_size'][] = sprintf(
                    'Custom paper must be at least %s cm wide and %s cm high to fit the current label layout.',
                    number_format($minimumPaperWidth, 1, '.', ''),
                    number_format($minimumPaperHeight, 1, '.', '')
                );
            } else {
                $paperSize = [$customWidth, $customHeight];
                $downloadName = 'labels-custom.pdf';
            }
        }
    } else {
        $paperSize = PaperSizes::normalize($old['paper_size']);

        if ($paperSize === null) {
            $errors['paper_size'][] = 'Choose a supported paper size.';
        } else {
            $downloadName = sprintf('labels-%s.pdf', strtolower($paperSize));
        }
    }

    try {
        $resolvedDirectory = ImageDirectory::resolve(dirname(__DIR__), $old['image_directory']);
    } catch (\RuntimeException $exception) {
        $errors['image_directory'][] = $exception->getMessage();
    }

    if (!isset($resolvedDirectory)) {
        $errors['form'][] = 'Fix the highlighted fields before generating the PDF.';
    } else {
        $imagePaths = ImageFinder::findPngFiles($resolvedDirectory);

        if ($imagePaths === []) {
            $errors['image_directory'][] = 'No PNG images were found in the selected directory.';
            $errors['form'][] = 'Add at least one PNG image before generating the PDF.';
        }
    }

    if ($errors === []) {
        $generator = new LabelPdfGenerator();
        $generator->outputInline($imagePaths, $paperSize, $downloadName);
        exit;
    }
}

require dirname(__DIR__) . '/templates/form.php';
