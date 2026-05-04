<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Layout\PaperSizes;

$selectedPaperSize = PaperSizes::normalize((string) ($_GET['paper_size'] ?? '')) ?? PaperSizes::default();
$selectedImageDirectory = trim((string) ($_GET['image_directory'] ?? 'Label Berlaku'));
$errorMessage = trim((string) ($_GET['error'] ?? ''));

if ($selectedImageDirectory === '') {
    $selectedImageDirectory = 'Label Berlaku';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PDF Label Generator</title>
    <style>
        :root {
            color-scheme: light;
            font-family: Arial, sans-serif;
        }

        body {
            margin: 2rem;
            color: #1f2937;
        }

        form {
            max-width: 28rem;
            padding: 1.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.75rem;
            background: #ffffff;
        }

        label {
            display: block;
            margin-bottom: 0.375rem;
            font-weight: 600;
        }

        input,
        select,
        button {
            width: 100%;
            padding: 0.65rem 0.75rem;
            font: inherit;
            box-sizing: border-box;
        }

        input,
        select {
            margin-bottom: 1rem;
            border: 1px solid #cbd5e1;
            border-radius: 0.5rem;
        }

        button {
            border: 0;
            border-radius: 0.5rem;
            background: #1d4ed8;
            color: #ffffff;
            cursor: pointer;
        }

        .error {
            margin-bottom: 1rem;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            background: #fee2e2;
            color: #991b1b;
        }

        p {
            max-width: 42rem;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <h1>PDF Label Generator</h1>
    <p>Choose a supported paper size and a project-relative directory that contains PNG labels. The PDF is generated through a dedicated output path so the inline stream is not mixed with HTML.</p>

    <?php if ($errorMessage !== ''): ?>
        <div class="error"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form action="generate.php" method="get">
        <label for="paper_size">Paper size</label>
        <select id="paper_size" name="paper_size">
            <?php foreach (PaperSizes::options() as $paperKey => $paperLabel): ?>
                <option value="<?= htmlspecialchars($paperKey, ENT_QUOTES, 'UTF-8') ?>" <?= $selectedPaperSize === $paperLabel ? 'selected' : '' ?>>
                    <?= htmlspecialchars($paperLabel, ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="image_directory">Image directory</label>
        <input
            id="image_directory"
            name="image_directory"
            type="text"
            value="<?= htmlspecialchars($selectedImageDirectory, ENT_QUOTES, 'UTF-8') ?>"
            placeholder="Label Berlaku"
            required
        >

        <button type="submit">Generate PDF</button>
    </form>
</body>
</html>
