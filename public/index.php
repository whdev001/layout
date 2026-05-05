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

$parsePositiveDimension = static function (string $value, string $field, array &$errors): ?float {
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

$parseGapDimension = static function (string $value, string $field, array &$errors): ?float {
    $value = trim($value);

    if ($value === '') {
        return 0.0;
    }

    if (!is_numeric($value)) {
        $errors[$field][] = 'Enter a numeric value in centimeters.';
        return null;
    }

    $gap = (float) $value;

    if ($gap < 0) {
        $errors[$field][] = 'Enter a value greater than or equal to zero.';
        return null;
    }

    return $gap;
};

$parseMarginDimension = static function (string $value, string $field, array &$errors): ?float {
    $value = trim($value);

    if ($value === '') {
        $errors[$field][] = 'This value is required for custom margins.';
        return null;
    }

    if (!is_numeric($value)) {
        $errors[$field][] = 'Enter a numeric value in centimeters.';
        return null;
    }

    $margin = (float) $value;

    if ($margin < 0) {
        $errors[$field][] = 'Enter a value greater than or equal to zero.';
        return null;
    }

    return $margin;
};

$formatCentimeters = static function (float $value): string {
    return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
};

$orientationMap = [
    'landscape' => 'L',
    'portrait' => 'P',
];

$marginPresetMap = [
    'narrow' => ['top' => 0.1, 'right' => 0.1, 'bottom' => 0.1, 'left' => 0.1],
    'normal' => ['top' => 0.3, 'right' => 0.1, 'bottom' => 0.0, 'left' => 0.2],
    'centered' => ['top' => 0.3, 'right' => 0.1, 'bottom' => 0.0, 'left' => 0.2],
    'custom' => null,
];

$presetPaperDimensions = [
    'A4' => ['width' => 21.0, 'height' => 29.7],
    'LETTER' => ['width' => 21.59, 'height' => 27.94],
    'LEGAL' => ['width' => 21.59, 'height' => 35.56],
];

$values = [
    'paper_size' => PaperSizes::default(),
    'orientation' => 'landscape',
    'margin_preset' => 'normal',
    'margin_top' => '',
    'margin_right' => '',
    'margin_bottom' => '',
    'margin_left' => '',
    'custom_width' => '',
    'custom_height' => '',
    'image_directory' => 'Label Berlaku/',
    'object_width' => (string) LabelPdfGenerator::DEFAULT_OBJECT_WIDTH,
    'object_height' => (string) LabelPdfGenerator::DEFAULT_OBJECT_HEIGHT,
    'horizontal_gap' => '0',
    'vertical_gap' => '0',
];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $old = [
        'paper_size' => (string) ($_POST['paper_size'] ?? PaperSizes::default()),
        'orientation' => strtolower(trim((string) ($_POST['orientation'] ?? 'landscape'))),
        'margin_preset' => strtolower(trim((string) ($_POST['margin_preset'] ?? 'normal'))),
        'margin_top' => trim((string) ($_POST['margin_top'] ?? '')),
        'margin_right' => trim((string) ($_POST['margin_right'] ?? '')),
        'margin_bottom' => trim((string) ($_POST['margin_bottom'] ?? '')),
        'margin_left' => trim((string) ($_POST['margin_left'] ?? '')),
        'custom_width' => trim((string) ($_POST['custom_width'] ?? '')),
        'custom_height' => trim((string) ($_POST['custom_height'] ?? '')),
        'image_directory' => trim((string) ($_POST['image_directory'] ?? '')),
        'object_width' => trim((string) ($_POST['object_width'] ?? (string) LabelPdfGenerator::DEFAULT_OBJECT_WIDTH)),
        'object_height' => trim((string) ($_POST['object_height'] ?? (string) LabelPdfGenerator::DEFAULT_OBJECT_HEIGHT)),
        'horizontal_gap' => trim((string) ($_POST['horizontal_gap'] ?? '')),
        'vertical_gap' => trim((string) ($_POST['vertical_gap'] ?? '')),
    ];

    $values = $old;

    $orientation = $orientationMap[$old['orientation']] ?? null;
    $paperSize = null;
    $downloadName = null;
    $paperWidth = null;
    $paperHeight = null;
    $margins = null;

    if ($orientation === null) {
        $errors['orientation'][] = 'Choose portrait or landscape.';
    }

    $objectWidth = $parsePositiveDimension($old['object_width'], 'object_width', $errors);
    $objectHeight = $parsePositiveDimension($old['object_height'], 'object_height', $errors);
    $horizontalGap = $parseGapDimension($old['horizontal_gap'], 'horizontal_gap', $errors);
    $verticalGap = $parseGapDimension($old['vertical_gap'], 'vertical_gap', $errors);

    if (!array_key_exists($old['margin_preset'], $marginPresetMap)) {
        $errors['margin_preset'][] = 'Choose a supported margin preset.';
    } elseif ($old['margin_preset'] === 'custom') {
        $marginTop = $parseMarginDimension($old['margin_top'], 'margin_top', $errors);
        $marginRight = $parseMarginDimension($old['margin_right'], 'margin_right', $errors);
        $marginBottom = $parseMarginDimension($old['margin_bottom'], 'margin_bottom', $errors);
        $marginLeft = $parseMarginDimension($old['margin_left'], 'margin_left', $errors);

        if ($marginTop !== null && $marginRight !== null && $marginBottom !== null && $marginLeft !== null) {
            $margins = [
                'top' => $marginTop,
                'right' => $marginRight,
                'bottom' => $marginBottom,
                'left' => $marginLeft,
            ];
        }
    } else {
        $margins = $marginPresetMap[$old['margin_preset']];
    }

    if (PaperSizes::isCustom($old['paper_size'])) {
        $customWidth = $parsePositiveDimension($old['custom_width'], 'custom_width', $errors);
        $customHeight = $parsePositiveDimension($old['custom_height'], 'custom_height', $errors);

        if ($customWidth !== null && $customHeight !== null) {
            $paperSize = [$customWidth, $customHeight];
            $paperWidth = $customWidth;
            $paperHeight = $customHeight;
            $downloadName = 'labels-custom.pdf';
        }
    } else {
        $paperSize = PaperSizes::normalize($old['paper_size']);

        if ($paperSize === null) {
            $errors['paper_size'][] = 'Choose a supported paper size.';
        } else {
            $paperSizeKey = strtoupper(trim($old['paper_size']));
            $paperDimensions = $presetPaperDimensions[$paperSizeKey] ?? null;

            if ($paperDimensions === null) {
                $errors['paper_size'][] = 'Choose a supported paper size.';
            } elseif ($orientation === 'L') {
                $paperWidth = $paperDimensions['height'];
                $paperHeight = $paperDimensions['width'];
            } else {
                $paperWidth = $paperDimensions['width'];
                $paperHeight = $paperDimensions['height'];
            }

            $downloadName = sprintf('labels-%s.pdf', strtolower($paperSize));
        }
    }

    if (
        $paperWidth !== null
        && $paperHeight !== null
        && $margins !== null
        && $objectWidth !== null
        && $objectHeight !== null
        && $horizontalGap !== null
        && $verticalGap !== null
    ) {
        $gridWidth = LabelPdfGenerator::gridWidth($objectWidth, $horizontalGap);
        $gridHeight = LabelPdfGenerator::gridHeight($objectHeight, $verticalGap);
        $requiredWidth = $margins['left'] + $gridWidth + $margins['right'];
        $requiredHeight = $margins['top'] + $gridHeight + $margins['bottom'];

        if ($paperWidth < $requiredWidth || $paperHeight < $requiredHeight) {
            $errors['form'][] = sprintf(
                'The selected paper, orientation, margins, gaps, and object size need at least %s cm × %s cm of page space for the current 4 × 6 layout.',
                $formatCentimeters($requiredWidth),
                $formatCentimeters($requiredHeight)
            );
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
        $generator->outputInline(
            $imagePaths,
            $paperSize,
            $downloadName,
            $objectWidth ?? LabelPdfGenerator::DEFAULT_OBJECT_WIDTH,
            $objectHeight ?? LabelPdfGenerator::DEFAULT_OBJECT_HEIGHT,
            $horizontalGap ?? 0.0,
            $verticalGap ?? 0.0,
            $orientation ?? 'L',
            $old['margin_preset'],
            $margins['top'] ?? null,
            $margins['right'] ?? null,
            $margins['bottom'] ?? null,
            $margins['left'] ?? null
        );
        exit;
    }
}

require dirname(__DIR__) . '/templates/form.php';
