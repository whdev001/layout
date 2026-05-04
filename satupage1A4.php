<?php

declare(strict_types=1);

$_GET['paper_size'] ??= 'A4';
$_GET['image_directory'] ??= 'Label Berlaku';

require __DIR__ . '/generate.php';

