<?php
declare(strict_types=1);

$escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

$fieldErrors = static function (array $errors, string $field): array {
    $messages = $errors[$field] ?? [];

    if (is_string($messages) && $messages !== '') {
        return [$messages];
    }

    if (!is_array($messages)) {
        return [];
    }

    return array_values(array_filter($messages, static fn ($message): bool => is_string($message) && $message !== ''));
};

$globalErrors = $errors['form'] ?? $errors['general'] ?? [];

if (is_string($globalErrors) && $globalErrors !== '') {
    $globalErrors = [$globalErrors];
}

if (!is_array($globalErrors)) {
    $globalErrors = [];
}

$paperSizeErrors = $fieldErrors($errors, 'paper_size');
$orientationErrors = $fieldErrors($errors, 'orientation');
$marginPresetErrors = $fieldErrors($errors, 'margin_preset');
$marginTopErrors = $fieldErrors($errors, 'margin_top');
$marginRightErrors = $fieldErrors($errors, 'margin_right');
$marginBottomErrors = $fieldErrors($errors, 'margin_bottom');
$marginLeftErrors = $fieldErrors($errors, 'margin_left');
$customMarginsErrors = $fieldErrors($errors, 'custom_margins');
$customWidthErrors = $fieldErrors($errors, 'custom_width');
$customHeightErrors = $fieldErrors($errors, 'custom_height');
$customSizeErrors = $fieldErrors($errors, 'custom_size');
$imageDirectoryErrors = $fieldErrors($errors, 'image_directory');
$objectWidthErrors = $fieldErrors($errors, 'object_width');
$objectHeightErrors = $fieldErrors($errors, 'object_height');
$horizontalGapErrors = $fieldErrors($errors, 'horizontal_gap');
$verticalGapErrors = $fieldErrors($errors, 'vertical_gap');

$values = array_merge([
    'paper_size' => '',
    'orientation' => 'portrait',
    'margin_preset' => 'normal',
    'margin_top' => '',
    'margin_right' => '',
    'margin_bottom' => '',
    'margin_left' => '',
    'custom_width' => '',
    'custom_height' => '',
    'image_directory' => '',
    'object_width' => '6',
    'object_height' => '3',
    'horizontal_gap' => '0',
    'vertical_gap' => '0',
], $values);

$orientationOptions = [
    'portrait' => 'Portrait',
    'landscape' => 'Landscape',
];

$marginPresetOptions = [
    'narrow' => 'Narrow',
    'normal' => 'Normal',
    'centered' => 'Centered',
    'custom' => 'Custom',
];

$paperSectionOpen = $globalErrors !== [] || $paperSizeErrors !== [] || $orientationErrors !== [] || $customWidthErrors !== [] || $customHeightErrors !== [] || $customSizeErrors !== [];
$layoutSectionOpen = $objectWidthErrors !== [] || $objectHeightErrors !== [] || $horizontalGapErrors !== [] || $verticalGapErrors !== [];
$marginSectionOpen = $marginPresetErrors !== [] || $marginTopErrors !== [] || $marginRightErrors !== [] || $marginBottomErrors !== [] || $marginLeftErrors !== [] || $customMarginsErrors !== [];
$sourceSectionOpen = $imageDirectoryErrors !== [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $escape($pageTitle) ?></title>
    <style>
        :root {
            --bg: #eff3f8;
            --surface: rgba(255, 255, 255, 0.94);
            --surface-strong: #ffffff;
            --border: #d8e1ec;
            --border-strong: #c3d0de;
            --text: #122033;
            --muted: #5c6c80;
            --accent: #2563eb;
            --accent-soft: #dbeafe;
            --danger: #b42318;
            --danger-soft: #fef3f2;
            --shadow: 0 24px 60px rgba(15, 23, 42, 0.12);
            --radius-xl: 24px;
            --radius-lg: 18px;
            --radius-md: 14px;
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

        [hidden] {
            display: none !important;
        }

        body {
            margin: 0;
            min-height: 100vh;
            padding: var(--space-6);
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                linear-gradient(180deg, #f8fbff 0%, var(--bg) 100%);
            color: var(--text);
            font-family: Inter, "Segoe UI", sans-serif;
        }

        .page {
            width: min(100%, 960px);
            margin: 0 auto;
        }

        .shell {
            background: var(--surface);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow);
            backdrop-filter: blur(10px);
            overflow: hidden;
        }

        .hero,
        .content {
            padding: var(--space-6);
        }

        .hero {
            border-bottom: 1px solid var(--border);
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.1), rgba(255, 255, 255, 0.2));
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: var(--space-2);
            margin-bottom: var(--space-3);
            padding: 0.45rem 0.8rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.72);
            color: var(--accent);
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        h1 {
            margin: 0;
            font-size: clamp(2rem, 4vw, 3.25rem);
            line-height: 1.05;
        }

        .lead,
        .muted,
        .field__hint,
        .section__meta {
            margin: 0;
            color: var(--muted);
            line-height: 1.6;
        }

        .lead {
            margin-top: var(--space-3);
            max-width: 62ch;
            font-size: 1.02rem;
        }

        .hero-grid {
            display: grid;
            gap: var(--space-4);
            grid-template-columns: repeat(3, minmax(0, 1fr));
            margin-top: var(--space-5);
        }

        .stat {
            padding: var(--space-4);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: var(--radius-lg);
            background: rgba(255, 255, 255, 0.72);
        }

        .stat strong {
            display: block;
            margin-bottom: var(--space-1);
            font-size: 1rem;
        }

        .alert {
            margin-bottom: var(--space-5);
            padding: var(--space-4);
            border: 1px solid #f5c2c0;
            border-radius: var(--radius-md);
            background: var(--danger-soft);
            color: var(--danger);
        }

        .alert ul {
            margin: var(--space-2) 0 0;
            padding-left: 1.25rem;
        }

        form {
            display: grid;
            gap: var(--space-4);
        }

        .section {
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            background: var(--surface-strong);
            overflow: hidden;
        }

        .section[open] {
            border-color: var(--border-strong);
        }

        .section summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--space-4);
            padding: var(--space-5);
            cursor: pointer;
            list-style: none;
        }

        .section summary::-webkit-details-marker {
            display: none;
        }

        .section__title {
            display: grid;
            gap: var(--space-1);
        }

        .section__title strong {
            font-size: 1.05rem;
        }

        .section__chevron {
            flex: 0 0 auto;
            width: 2.5rem;
            height: 2.5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            border: 1px solid var(--border);
            color: var(--muted);
            transition: transform 160ms ease, background-color 160ms ease, color 160ms ease;
        }

        .section[open] .section__chevron {
            transform: rotate(180deg);
            background: var(--accent-soft);
            color: var(--accent);
        }

        .section__body {
            display: grid;
            gap: var(--space-5);
            padding: 0 var(--space-5) var(--space-5);
            border-top: 1px solid var(--border);
        }

        .field,
        .field-group,
        .field-subgroup {
            display: grid;
            gap: var(--space-2);
        }

        .field-grid {
            display: grid;
            gap: var(--space-4);
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .field-subgroup {
            padding: var(--space-4);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            background: #f8fbff;
        }

        .field label {
            font-weight: 700;
            font-size: 0.98rem;
        }

        .field input,
        .field select,
        .button {
            width: 100%;
            border-radius: var(--radius-md);
            font: inherit;
        }

        .field input,
        .field select {
            min-height: 3.2rem;
            padding: 0 var(--space-4);
            border: 1px solid var(--border);
            background: #fff;
            color: var(--text);
        }

        .field input:focus,
        .field select:focus,
        .button:focus,
        .section summary:focus {
            outline: 3px solid var(--accent-soft);
            outline-offset: 2px;
        }

        .field--error input,
        .field--error select {
            border-color: var(--danger);
        }

        .field__error {
            color: var(--danger);
            font-size: 0.94rem;
        }

        .inline-note {
            padding: var(--space-3) var(--space-4);
            border-radius: var(--radius-md);
            background: #f8fafc;
            color: var(--muted);
            font-size: 0.95rem;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: var(--space-4);
            justify-content: space-between;
            padding: var(--space-5);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            background: linear-gradient(180deg, rgba(37, 99, 235, 0.05), rgba(255, 255, 255, 0.95));
        }

        .button {
            max-width: 240px;
            min-height: 3.4rem;
            border: 0;
            padding: 0 var(--space-5);
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 12px 28px rgba(37, 99, 235, 0.28);
        }

        @media (max-width: 720px) {
            body {
                padding: var(--space-4);
            }

            .hero,
            .content {
                padding: var(--space-5);
            }

            .hero-grid,
            .field-grid,
            .actions {
                grid-template-columns: 1fr;
            }

            .actions {
                display: grid;
            }

            .button {
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <main class="page">
        <section class="shell">
            <header class="hero">
                <div class="eyebrow">PDF Label Builder</div>
                <h1>Atur layout PDF tanpa form yang ribet</h1>
                <p class="lead">Pilih ukuran kertas, tentukan ukuran objek gambar, atur margin dan jarak, lalu generate PDF langsung dari folder PNG yang ada di project ini.</p>
                <div class="hero-grid">
                    <div class="stat">
                        <strong>Grid tetap 4 x 6</strong>
                        <p class="muted">Jumlah slot per halaman tetap 24, yang berubah adalah ukuran objek dan spacing.</p>
                    </div>
                    <div class="stat">
                        <strong>Ukuran dalam cm</strong>
                        <p class="muted">Semua input numerik memakai satuan sentimeter agar sama dengan FPDF.</p>
                    </div>
                    <div class="stat">
                        <strong>Output langsung</strong>
                        <p class="muted">File PDF tetap dibuka inline seperti perilaku existing app.</p>
                    </div>
                </div>
            </header>

            <div class="content">
                <?php if ($globalErrors !== []) : ?>
                    <div class="alert" role="alert" aria-live="polite">
                        <strong>Perlu diperbaiki dulu:</strong>
                        <ul>
                            <?php foreach ($globalErrors as $message) : ?>
                                <li><?= $escape((string) $message) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" action="<?= $escape($formAction) ?>">
                    <details class="section" <?= $paperSectionOpen || !$layoutSectionOpen && !$marginSectionOpen && !$sourceSectionOpen ? 'open' : '' ?> data-section>
                        <summary>
                            <div class="section__title">
                                <strong>Kertas & orientasi</strong>
                                <p class="section__meta">Pilih paper size, orientasi, dan custom size bila diperlukan.</p>
                            </div>
                            <span class="section__chevron" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 20 20" fill="none"><path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                        </summary>
                        <div class="section__body">
                            <div class="field-grid">
                                <div class="field <?= $paperSizeErrors !== [] ? 'field--error' : '' ?>">
                                    <label for="paper_size">Paper size</label>
                                    <select id="paper_size" name="paper_size" data-custom-paper-size="CUSTOM" aria-describedby="paper-size-hint<?= $paperSizeErrors !== [] ? ' paper-size-error' : '' ?>">
                                        <?php foreach ($paperSizes as $value => $label) : ?>
                                            <option value="<?= $escape((string) $value) ?>" <?= $values['paper_size'] === (string) $value ? 'selected' : '' ?>><?= $escape((string) $label) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="field__hint" id="paper-size-hint">Pilih ukuran halaman PDF. Opsi Custom akan membuka input lebar dan tinggi manual.</p>
                                    <?php if ($paperSizeErrors !== []) : ?>
                                        <div class="field__error" id="paper-size-error">
                                            <?php foreach ($paperSizeErrors as $message) : ?>
                                                <div><?= $escape($message) ?></div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="field <?= $orientationErrors !== [] ? 'field--error' : '' ?>">
                                    <label for="orientation">Orientation</label>
                                    <select id="orientation" name="orientation" aria-describedby="orientation-hint<?= $orientationErrors !== [] ? ' orientation-error' : '' ?>">
                                        <?php foreach ($orientationOptions as $value => $label) : ?>
                                            <option value="<?= $escape($value) ?>" <?= $values['orientation'] === $value ? 'selected' : '' ?>><?= $escape($label) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="field__hint" id="orientation-hint">Landscape cocok untuk layout default saat ini, tapi tetap bisa diubah.</p>
                                    <?php if ($orientationErrors !== []) : ?>
                                        <div class="field__error" id="orientation-error">
                                            <?php foreach ($orientationErrors as $message) : ?>
                                                <div><?= $escape($message) ?></div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="field-group" id="custom-paper-size-fields">
                                <div class="field-grid">
                                    <div class="field <?= $customWidthErrors !== [] ? 'field--error' : '' ?>">
                                        <label for="custom_width">Custom width (cm)</label>
                                        <input id="custom_width" name="custom_width" type="number" value="<?= $escape((string) $values['custom_width']) ?>" inputmode="decimal" min="0.01" step="0.01" aria-describedby="custom-width-hint<?= $customWidthErrors !== [] ? ' custom-width-error' : '' ?>">
                                        <p class="field__hint" id="custom-width-hint">Dipakai hanya saat paper size = Custom.</p>
                                        <?php if ($customWidthErrors !== []) : ?>
                                            <div class="field__error" id="custom-width-error">
                                                <?php foreach ($customWidthErrors as $message) : ?>
                                                    <div><?= $escape($message) ?></div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="field <?= $customHeightErrors !== [] ? 'field--error' : '' ?>">
                                        <label for="custom_height">Custom height (cm)</label>
                                        <input id="custom_height" name="custom_height" type="number" value="<?= $escape((string) $values['custom_height']) ?>" inputmode="decimal" min="0.01" step="0.01" aria-describedby="custom-height-hint<?= $customHeightErrors !== [] ? ' custom-height-error' : '' ?>">
                                        <p class="field__hint" id="custom-height-hint">Dipakai hanya saat paper size = Custom.</p>
                                        <?php if ($customHeightErrors !== []) : ?>
                                            <div class="field__error" id="custom-height-error">
                                                <?php foreach ($customHeightErrors as $message) : ?>
                                                    <div><?= $escape($message) ?></div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if ($customSizeErrors !== []) : ?>
                                    <div class="field__error" id="custom-size-error">
                                        <?php foreach ($customSizeErrors as $message) : ?>
                                            <div><?= $escape($message) ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </details>

                    <details class="section" <?= $layoutSectionOpen ? 'open' : '' ?> data-section>
                        <summary>
                            <div class="section__title">
                                <strong>Ukuran objek & jarak</strong>
                                <p class="section__meta">Atur lebar, tinggi, dan gap antar objek di dalam grid 4 x 6.</p>
                            </div>
                            <span class="section__chevron" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 20 20" fill="none"><path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                        </summary>
                        <div class="section__body">
                            <div class="inline-note">Ukuran objek menentukan area slot gambar pada PDF. Bila terlalu besar untuk kombinasi kertas dan margin saat ini, validasi akan menolak generate.</div>

                            <div class="field-grid">
                                <div class="field <?= $objectWidthErrors !== [] ? 'field--error' : '' ?>">
                                    <label for="object_width">Object width (cm)</label>
                                    <input id="object_width" name="object_width" type="number" value="<?= $escape((string) $values['object_width']) ?>" inputmode="decimal" min="0.01" step="0.01" aria-describedby="object-width-hint<?= $objectWidthErrors !== [] ? ' object-width-error' : '' ?>">
                                    <p class="field__hint" id="object-width-hint">Lebar tiap objek gambar pada PDF.</p>
                                    <?php if ($objectWidthErrors !== []) : ?>
                                        <div class="field__error" id="object-width-error">
                                            <?php foreach ($objectWidthErrors as $message) : ?>
                                                <div><?= $escape($message) ?></div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="field <?= $objectHeightErrors !== [] ? 'field--error' : '' ?>">
                                    <label for="object_height">Object height (cm)</label>
                                    <input id="object_height" name="object_height" type="number" value="<?= $escape((string) $values['object_height']) ?>" inputmode="decimal" min="0.01" step="0.01" aria-describedby="object-height-hint<?= $objectHeightErrors !== [] ? ' object-height-error' : '' ?>">
                                    <p class="field__hint" id="object-height-hint">Tinggi tiap objek gambar pada PDF.</p>
                                    <?php if ($objectHeightErrors !== []) : ?>
                                        <div class="field__error" id="object-height-error">
                                            <?php foreach ($objectHeightErrors as $message) : ?>
                                                <div><?= $escape($message) ?></div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="field-grid">
                                <div class="field <?= $horizontalGapErrors !== [] ? 'field--error' : '' ?>">
                                    <label for="horizontal_gap">Horizontal gap (cm)</label>
                                    <input id="horizontal_gap" name="horizontal_gap" type="number" value="<?= $escape((string) ($values['horizontal_gap'] ?? '0')) ?>" inputmode="decimal" min="0" step="0.01" aria-describedby="horizontal-gap-hint<?= $horizontalGapErrors !== [] ? ' horizontal-gap-error' : '' ?>">
                                    <p class="field__hint" id="horizontal-gap-hint">Jarak antar kolom objek.</p>
                                    <?php if ($horizontalGapErrors !== []) : ?>
                                        <div class="field__error" id="horizontal-gap-error">
                                            <?php foreach ($horizontalGapErrors as $message) : ?>
                                                <div><?= $escape($message) ?></div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="field <?= $verticalGapErrors !== [] ? 'field--error' : '' ?>">
                                    <label for="vertical_gap">Vertical gap (cm)</label>
                                    <input id="vertical_gap" name="vertical_gap" type="number" value="<?= $escape((string) ($values['vertical_gap'] ?? '0')) ?>" inputmode="decimal" min="0" step="0.01" aria-describedby="vertical-gap-hint<?= $verticalGapErrors !== [] ? ' vertical-gap-error' : '' ?>">
                                    <p class="field__hint" id="vertical-gap-hint">Jarak antar baris objek.</p>
                                    <?php if ($verticalGapErrors !== []) : ?>
                                        <div class="field__error" id="vertical-gap-error">
                                            <?php foreach ($verticalGapErrors as $message) : ?>
                                                <div><?= $escape($message) ?></div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </details>

                    <details class="section" <?= $marginSectionOpen ? 'open' : '' ?> data-section>
                        <summary>
                            <div class="section__title">
                                <strong>Margin halaman</strong>
                                <p class="section__meta">Pakai preset cepat atau isi top, right, bottom, left secara manual.</p>
                            </div>
                            <span class="section__chevron" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 20 20" fill="none"><path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                        </summary>
                        <div class="section__body">
                            <div class="field <?= $marginPresetErrors !== [] ? 'field--error' : '' ?>">
                                <label for="margin_preset">Margin preset</label>
                                <select id="margin_preset" name="margin_preset" data-custom-margin-preset="custom" aria-describedby="margin-preset-hint<?= $marginPresetErrors !== [] ? ' margin-preset-error' : '' ?>">
                                    <?php foreach ($marginPresetOptions as $value => $label) : ?>
                                        <option value="<?= $escape($value) ?>" <?= $values['margin_preset'] === $value ? 'selected' : '' ?>><?= $escape($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="field__hint" id="margin-preset-hint">Preset mempercepat setup. Custom dipakai bila butuh offset lebih presisi.</p>
                                <?php if ($marginPresetErrors !== []) : ?>
                                    <div class="field__error" id="margin-preset-error">
                                        <?php foreach ($marginPresetErrors as $message) : ?>
                                            <div><?= $escape($message) ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="field-subgroup" id="custom-margin-fields">
                                <div class="field-grid">
                                    <div class="field <?= $marginTopErrors !== [] ? 'field--error' : '' ?>">
                                        <label for="margin_top">Top margin (cm)</label>
                                        <input id="margin_top" name="margin_top" type="number" value="<?= $escape((string) $values['margin_top']) ?>" inputmode="decimal" min="0" step="0.01" aria-describedby="margin-top-hint<?= $marginTopErrors !== [] ? ' margin-top-error' : '' ?>">
                                        <p class="field__hint" id="margin-top-hint">Dipakai hanya saat preset = Custom.</p>
                                        <?php if ($marginTopErrors !== []) : ?>
                                            <div class="field__error" id="margin-top-error">
                                                <?php foreach ($marginTopErrors as $message) : ?>
                                                    <div><?= $escape($message) ?></div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="field <?= $marginRightErrors !== [] ? 'field--error' : '' ?>">
                                        <label for="margin_right">Right margin (cm)</label>
                                        <input id="margin_right" name="margin_right" type="number" value="<?= $escape((string) $values['margin_right']) ?>" inputmode="decimal" min="0" step="0.01" aria-describedby="margin-right-hint<?= $marginRightErrors !== [] ? ' margin-right-error' : '' ?>">
                                        <p class="field__hint" id="margin-right-hint">Dipakai hanya saat preset = Custom.</p>
                                        <?php if ($marginRightErrors !== []) : ?>
                                            <div class="field__error" id="margin-right-error">
                                                <?php foreach ($marginRightErrors as $message) : ?>
                                                    <div><?= $escape($message) ?></div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="field-grid">
                                    <div class="field <?= $marginBottomErrors !== [] ? 'field--error' : '' ?>">
                                        <label for="margin_bottom">Bottom margin (cm)</label>
                                        <input id="margin_bottom" name="margin_bottom" type="number" value="<?= $escape((string) $values['margin_bottom']) ?>" inputmode="decimal" min="0" step="0.01" aria-describedby="margin-bottom-hint<?= $marginBottomErrors !== [] ? ' margin-bottom-error' : '' ?>">
                                        <p class="field__hint" id="margin-bottom-hint">Dipakai hanya saat preset = Custom.</p>
                                        <?php if ($marginBottomErrors !== []) : ?>
                                            <div class="field__error" id="margin-bottom-error">
                                                <?php foreach ($marginBottomErrors as $message) : ?>
                                                    <div><?= $escape($message) ?></div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="field <?= $marginLeftErrors !== [] ? 'field--error' : '' ?>">
                                        <label for="margin_left">Left margin (cm)</label>
                                        <input id="margin_left" name="margin_left" type="number" value="<?= $escape((string) $values['margin_left']) ?>" inputmode="decimal" min="0" step="0.01" aria-describedby="margin-left-hint<?= $marginLeftErrors !== [] ? ' margin-left-error' : '' ?>">
                                        <p class="field__hint" id="margin-left-hint">Dipakai hanya saat preset = Custom.</p>
                                        <?php if ($marginLeftErrors !== []) : ?>
                                            <div class="field__error" id="margin-left-error">
                                                <?php foreach ($marginLeftErrors as $message) : ?>
                                                    <div><?= $escape($message) ?></div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if ($customMarginsErrors !== []) : ?>
                                    <div class="field__error" id="custom-margins-error">
                                        <?php foreach ($customMarginsErrors as $message) : ?>
                                            <div><?= $escape($message) ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </details>

                    <details class="section" <?= $sourceSectionOpen ? 'open' : '' ?> data-section>
                        <summary>
                            <div class="section__title">
                                <strong>Sumber gambar</strong>
                                <p class="section__meta">Masukkan folder relatif project yang berisi file PNG.</p>
                            </div>
                            <span class="section__chevron" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 20 20" fill="none"><path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                        </summary>
                        <div class="section__body">
                            <div class="field <?= $imageDirectoryErrors !== [] ? 'field--error' : '' ?>">
                                <label for="image_directory">Image directory path</label>
                                <input id="image_directory" name="image_directory" type="text" value="<?= $escape($values['image_directory']) ?>" inputmode="text" spellcheck="false" autocomplete="off" aria-describedby="image-directory-hint<?= $imageDirectoryErrors !== [] ? ' image-directory-error' : '' ?>">
                                <p class="field__hint" id="image-directory-hint">Contoh: <code>Label Berlaku/</code> atau folder fixture lain yang ada di dalam project.</p>
                                <?php if ($imageDirectoryErrors !== []) : ?>
                                    <div class="field__error" id="image-directory-error">
                                        <?php foreach ($imageDirectoryErrors as $message) : ?>
                                            <div><?= $escape($message) ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </details>

                    <div class="actions">
                        <p class="muted">Generate akan membuka PDF inline di browser begitu semua validasi lolos.</p>
                        <button class="button" type="submit">Generate PDF</button>
                    </div>
                </form>
            </div>
        </section>
    </main>
    <script>
        (function () {
            const paperSizeField = document.getElementById('paper_size');
            const customPaperSizeFields = document.getElementById('custom-paper-size-fields');
            const customWidthField = document.getElementById('custom_width');
            const customHeightField = document.getElementById('custom_height');
            const marginPresetField = document.getElementById('margin_preset');
            const customMarginFields = document.getElementById('custom-margin-fields');
            const sections = document.querySelectorAll('[data-section]');

            if (paperSizeField && customPaperSizeFields && customWidthField && customHeightField) {
                const customPaperSize = paperSizeField.getAttribute('data-custom-paper-size') || 'CUSTOM';

                const syncCustomPaperSizeVisibility = function () {
                    const isCustomPaperSize = paperSizeField.value === customPaperSize;

                    customPaperSizeFields.hidden = !isCustomPaperSize;
                    customWidthField.required = isCustomPaperSize;
                    customHeightField.required = isCustomPaperSize;
                };

                syncCustomPaperSizeVisibility();
                paperSizeField.addEventListener('change', syncCustomPaperSizeVisibility);
            }

            if (marginPresetField && customMarginFields) {
                const customMarginPreset = marginPresetField.getAttribute('data-custom-margin-preset') || 'custom';
                const customMarginInputs = customMarginFields.querySelectorAll('input');

                const syncCustomMarginVisibility = function () {
                    const isCustomMarginPreset = marginPresetField.value === customMarginPreset;

                    customMarginFields.hidden = !isCustomMarginPreset;
                    customMarginInputs.forEach(function (input) {
                        input.required = isCustomMarginPreset;
                    });
                };

                syncCustomMarginVisibility();
                marginPresetField.addEventListener('change', syncCustomMarginVisibility);
            }

            sections.forEach(function (section) {
                const hasError = section.querySelector('.field__error');

                if (hasError) {
                    section.open = true;
                }
            });
        }());
    </script>
</body>
</html>
