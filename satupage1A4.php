<?php

declare(strict_types=1);

$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['paper_size'] ??= 'A4';
$_POST['orientation'] ??= 'landscape';
$_POST['margin_preset'] ??= 'normal';
$_POST['image_directory'] ??= 'Label Berlaku';

require __DIR__ . '/public/index.php';

