<?php
declare(strict_types=1);

$escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

$fieldErrors = static function (array $errors, string $field): array {
    $messages = $errors[$field] ?? [];

    if (is_string($messages) && $messages !== '') {
        return [$messages];
    }

    if (! is_array($messages)) {
        return [];
    }

    return array_values(array_filter($messages, static fn ($message): bool => is_string($message) && $message !== ''));
};

$globalErrors = $errors['form'] ?? $errors['general'] ?? [];

if (is_string($globalErrors) && $globalErrors !== '') {
    $globalErrors = [$globalErrors];
}

if (! is_array($globalErrors)) {
    $globalErrors = [];
}

$paperSizeErrors = $fieldErrors($errors, 'paper_size');
$imageDirectoryErrors = $fieldErrors($errors, 'image_directory');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $escape($pageTitle) ?></title>
    <style>
        :root {
            --color-background: #f5f5f0;
            --color-surface: #ffffff;
            --color-border: #d6d3cb;
            --color-text: #1f2933;
            --color-muted: #52606d;
            --color-accent: #1d4ed8;
            --color-accent-soft: #dbeafe;
            --color-danger: #b42318;
            --color-danger-soft: #fef3f2;
            --shadow-soft: 0 18px 40px rgba(15, 23, 42, 0.08);
            --radius-panel: 18px;
            --radius-control: 12px;
            --space-1: 0.25rem;
            --space-2: 0.5rem;
            --space-3: 0.75rem;
            --space-4: 1rem;
            --space-5: 1.5rem;
            --space-6: 2rem;
            --space-7: 3rem;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            padding: var(--space-6);
            background: linear-gradient(180deg, #fafaf8 0%, var(--color-background) 100%);
            color: var(--color-text);
            font-family: Georgia, "Times New Roman", serif;
        }

        .page {
            width: min(100%, 760px);
            margin: 0 auto;
        }

        .panel {
            background: var(--color-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-panel);
            box-shadow: var(--shadow-soft);
            overflow: hidden;
        }

        .panel__header,
        .panel__body {
            padding: var(--space-6);
        }

        .panel__header {
            border-bottom: 1px solid var(--color-border);
        }

        h1 {
            margin: 0 0 var(--space-3);
            font-size: clamp(2rem, 4vw, 2.75rem);
            line-height: 1.1;
            font-weight: 600;
        }

        .intro,
        .helper,
        .field__hint {
            margin: 0;
            color: var(--color-muted);
            line-height: 1.6;
        }

        .helper {
            margin-top: var(--space-2);
        }

        .alert {
            margin-bottom: var(--space-5);
            padding: var(--space-4);
            border: 1px solid #f5c2c0;
            border-radius: var(--radius-control);
            background: var(--color-danger-soft);
            color: var(--color-danger);
        }

        .alert ul {
            margin: var(--space-2) 0 0;
            padding-left: 1.25rem;
        }

        form {
            display: grid;
            gap: var(--space-5);
        }

        .field {
            display: grid;
            gap: var(--space-2);
        }

        .field label {
            font-weight: 600;
            font-size: 1rem;
        }

        .field input,
        .field select,
        .button {
            width: 100%;
            border-radius: var(--radius-control);
            font: inherit;
        }

        .field input,
        .field select {
            min-height: 3rem;
            padding: 0 var(--space-4);
            border: 1px solid var(--color-border);
            background: #fff;
            color: var(--color-text);
        }

        .field input:focus,
        .field select:focus,
        .button:focus {
            outline: 3px solid var(--color-accent-soft);
            outline-offset: 2px;
        }

        .field--error input,
        .field--error select {
            border-color: var(--color-danger);
        }

        .field__error {
            color: var(--color-danger);
            font-size: 0.95rem;
        }

        .button {
            min-height: 3.25rem;
            border: 0;
            padding: 0 var(--space-5);
            background: var(--color-accent);
            color: #fff;
            font-weight: 600;
            cursor: pointer;
        }

        @media (max-width: 640px) {
            body {
                padding: var(--space-4);
            }

            .panel__header,
            .panel__body {
                padding: var(--space-5);
            }
        }
    </style>
</head>
<body>
    <main class="page">
        <section class="panel">
            <header class="panel__header">
                <h1>Generate a PDF sheet</h1>
                <p class="intro">Choose the paper size, point the app at the image directory, and submit the form to let the backend generate the PDF.</p>
                <p class="helper">This view uses a normal POST request and is ready for simple server-side validation feedback.</p>
            </header>

            <div class="panel__body">
                <?php if ($globalErrors !== []) : ?>
                    <div class="alert" role="alert" aria-live="polite">
                        <strong>Please fix the following:</strong>
                        <ul>
                            <?php foreach ($globalErrors as $message) : ?>
                                <li><?= $escape((string) $message) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" action="<?= $escape($formAction) ?>">
                    <div class="field <?= $paperSizeErrors !== [] ? 'field--error' : '' ?>">
                        <label for="paper_size">Paper size</label>
                        <select id="paper_size" name="paper_size" aria-describedby="paper-size-hint<?= $paperSizeErrors !== [] ? ' paper-size-error' : '' ?>">
                            <?php foreach ($paperSizes as $value => $label) : ?>
                                <option value="<?= $escape((string) $value) ?>" <?= $values['paper_size'] === (string) $value ? 'selected' : '' ?>><?= $escape((string) $label) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="field__hint" id="paper-size-hint">Select the sheet size the PDF generator should use when the backend handles the request.</p>
                        <?php if ($paperSizeErrors !== []) : ?>
                            <div class="field__error" id="paper-size-error">
                                <?php foreach ($paperSizeErrors as $message) : ?>
                                    <div><?= $escape($message) ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="field <?= $imageDirectoryErrors !== [] ? 'field--error' : '' ?>">
                        <label for="image_directory">Image directory path</label>
                        <input
                            id="image_directory"
                            name="image_directory"
                            type="text"
                            value="<?= $escape($values['image_directory']) ?>"
                            inputmode="text"
                            spellcheck="false"
                            autocomplete="off"
                            aria-describedby="image-directory-hint<?= $imageDirectoryErrors !== [] ? ' image-directory-error' : '' ?>"
                        >
                        <p class="field__hint" id="image-directory-hint">Enter the folder that contains the images to place in the PDF, for example <code>Label Berlaku/</code>.</p>
                        <?php if ($imageDirectoryErrors !== []) : ?>
                            <div class="field__error" id="image-directory-error">
                                <?php foreach ($imageDirectoryErrors as $message) : ?>
                                    <div><?= $escape($message) ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <button class="button" type="submit">Generate PDF</button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
