<?php
declare(strict_types=1);

$paperSizes = $paperSizes ?? [
    'A4' => 'A4',
    'Letter' => 'Letter',
    'Legal' => 'Legal',
];

$errors = is_array($errors ?? null) ? $errors : [];
$old = is_array($old ?? null) ? $old : [];

$values = [
    'paper_size' => (string) ($old['paper_size'] ?? $_POST['paper_size'] ?? 'A4'),
    'image_directory' => (string) ($old['image_directory'] ?? $_POST['image_directory'] ?? 'Label Berlaku/'),
];

$formAction = isset($formAction) && is_string($formAction) ? $formAction : '';
$pageTitle = isset($pageTitle) && is_string($pageTitle) ? $pageTitle : 'PDF Generator';

require dirname(__DIR__) . '/templates/form.php';
