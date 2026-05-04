<?php

declare(strict_types=1);

namespace Layout;

final class LabelPdfGenerator
{
    public const ORIENTATION_LANDSCAPE = 'L';
    public const ORIENTATION_PORTRAIT = 'P';
    public const MARGIN_PRESET_NARROW = 'narrow';
    public const MARGIN_PRESET_NORMAL = 'normal';
    public const MARGIN_PRESET_CENTERED = 'centered';
    public const MARGIN_PRESET_CUSTOM = 'custom';

    private const LEFT_MARGIN = 0.2;
    private const TOP_MARGIN = 0.3;
    private const RIGHT_MARGIN = 0.1;
    private const BOTTOM_MARGIN = 0.0;
    private const BORDER_WIDTH = 0.1;
    private const LABEL_WIDTH = 6.0;
    private const LABEL_HEIGHT = 3.0;
    private const COLUMNS = 4;
    private const ROWS = 6;
    private const LABELS_PER_PAGE = 24;
    private const STANDARD_PAPER_DIMENSIONS = [
        'A4' => [21.0, 29.7],
        'LETTER' => [21.59, 27.94],
        'LEGAL' => [21.59, 35.56],
    ];

    public const MINIMUM_PAPER_WIDTH = self::LEFT_MARGIN + (self::COLUMNS * self::LABEL_WIDTH) + self::RIGHT_MARGIN;
    public const MINIMUM_PAPER_HEIGHT = self::TOP_MARGIN + (self::ROWS * self::LABEL_HEIGHT) + self::BOTTOM_MARGIN;

    public static function normalizeOrientation(string $orientation): ?string
    {
        $normalized = strtolower(trim($orientation));

        return match ($normalized) {
            'l', 'landscape' => self::ORIENTATION_LANDSCAPE,
            'p', 'portrait' => self::ORIENTATION_PORTRAIT,
            default => null,
        };
    }

    public static function normalizeMarginPreset(string $marginPreset): ?string
    {
        $normalized = strtolower(trim($marginPreset));

        return match ($normalized) {
            self::MARGIN_PRESET_NARROW,
            self::MARGIN_PRESET_NORMAL,
            self::MARGIN_PRESET_CENTERED,
            self::MARGIN_PRESET_CUSTOM => $normalized,
            default => null,
        };
    }

    /**
     * @return array{top: float, right: float, bottom: float, left: float}|null
     */
    public static function resolveMargins(
        string $marginPreset,
        ?float $top = null,
        ?float $right = null,
        ?float $bottom = null,
        ?float $left = null
    ): ?array {
        $normalizedPreset = self::normalizeMarginPreset($marginPreset);

        if ($normalizedPreset === null) {
            return null;
        }

        return match ($normalizedPreset) {
            self::MARGIN_PRESET_NARROW => [
                'top' => 0.1,
                'right' => 0.1,
                'bottom' => 0.1,
                'left' => 0.1,
            ],
            self::MARGIN_PRESET_NORMAL,
            self::MARGIN_PRESET_CENTERED => [
                'top' => self::TOP_MARGIN,
                'right' => self::RIGHT_MARGIN,
                'bottom' => self::BOTTOM_MARGIN,
                'left' => self::LEFT_MARGIN,
            ],
            self::MARGIN_PRESET_CUSTOM => $top !== null && $right !== null && $bottom !== null && $left !== null
                ? [
                    'top' => $top,
                    'right' => $right,
                    'bottom' => $bottom,
                    'left' => $left,
                ]
                : null,
        };
    }

    public static function usesCenteredPlacement(string $marginPreset): bool
    {
        return self::normalizeMarginPreset($marginPreset) === self::MARGIN_PRESET_CENTERED;
    }

    public static function gridWidth(float $horizontalGap = 0.0): float
    {
        return (self::COLUMNS * self::LABEL_WIDTH) + ((self::COLUMNS - 1) * $horizontalGap);
    }

    public static function gridHeight(float $verticalGap = 0.0): float
    {
        return (self::ROWS * self::LABEL_HEIGHT) + ((self::ROWS - 1) * $verticalGap);
    }

    public static function minimumPaperWidth(
        float $horizontalGap = 0.0,
        float $leftMargin = self::LEFT_MARGIN,
        float $rightMargin = self::RIGHT_MARGIN
    ): float {
        return $leftMargin + self::gridWidth($horizontalGap) + $rightMargin;
    }

    public static function minimumPaperHeight(
        float $verticalGap = 0.0,
        float $topMargin = self::TOP_MARGIN,
        float $bottomMargin = self::BOTTOM_MARGIN
    ): float {
        return $topMargin + self::gridHeight($verticalGap) + $bottomMargin;
    }

    /**
     * @param string|array{0: float, 1: float} $paperSize
     * @return array{width: float, height: float}|null
     */
    public static function paperDimensions(string|array $paperSize, string $orientation = self::ORIENTATION_LANDSCAPE): ?array
    {
        $normalizedOrientation = self::normalizeOrientation($orientation);

        if ($normalizedOrientation === null) {
            return null;
        }

        if (is_array($paperSize)) {
            return [
                'width' => $paperSize[0],
                'height' => $paperSize[1],
            ];
        }

        $dimensions = self::STANDARD_PAPER_DIMENSIONS[strtoupper($paperSize)] ?? null;

        if ($dimensions === null) {
            return null;
        }

        if ($normalizedOrientation === self::ORIENTATION_LANDSCAPE) {
            return [
                'width' => $dimensions[1],
                'height' => $dimensions[0],
            ];
        }

        return [
            'width' => $dimensions[0],
            'height' => $dimensions[1],
        ];
    }

    /**
     * @param string|array{0: float, 1: float} $paperSize
     * @return string|array{0: float, 1: float}
     */
    private static function paperSizeForFpdf(string|array $paperSize, string $orientation): string|array
    {
        if (!is_array($paperSize)) {
            return $paperSize;
        }

        if ($orientation === self::ORIENTATION_LANDSCAPE) {
            return [$paperSize[1], $paperSize[0]];
        }

        return [$paperSize[0], $paperSize[1]];
    }

    /**
     * @param array{top: float, right: float, bottom: float, left: float} $margins
     * @return array{0: float, 1: float}
     */
    private static function startPosition(
        \FPDF $pdf,
        array $margins,
        bool $centeredPlacement,
        float $horizontalGap,
        float $verticalGap
    ): array {
        $startX = $margins['left'];
        $startY = $margins['top'];

        if (!$centeredPlacement) {
            return [$startX, $startY];
        }

        $printableWidth = $pdf->GetPageWidth() - $margins['left'] - $margins['right'];
        $printableHeight = $pdf->GetPageHeight() - $margins['top'] - $margins['bottom'];
        $remainingWidth = $printableWidth - self::gridWidth($horizontalGap);
        $remainingHeight = $printableHeight - self::gridHeight($verticalGap);

        return [
            $margins['left'] + max(0.0, $remainingWidth / 2),
            $margins['top'] + max(0.0, $remainingHeight / 2),
        ];
    }

    /**
     * @param list<string> $imagePaths
     * @param string|array{0: float, 1: float} $paperSize
     */
    public function outputInline(
        array $imagePaths,
        string|array $paperSize,
        string $fileName,
        float $horizontalGap = 0.0,
        float $verticalGap = 0.0,
        string $orientation = self::ORIENTATION_LANDSCAPE,
        string $marginPreset = self::MARGIN_PRESET_NORMAL,
        ?float $marginTop = null,
        ?float $marginRight = null,
        ?float $marginBottom = null,
        ?float $marginLeft = null
    ): void {
        $resolvedOrientation = self::normalizeOrientation($orientation) ?? self::ORIENTATION_LANDSCAPE;
        $resolvedPreset = self::normalizeMarginPreset($marginPreset) ?? self::MARGIN_PRESET_NORMAL;
        $resolvedMargins = self::resolveMargins($resolvedPreset, $marginTop, $marginRight, $marginBottom, $marginLeft)
            ?? self::resolveMargins(self::MARGIN_PRESET_NORMAL);
        $centeredPlacement = self::usesCenteredPlacement($resolvedPreset);
        $pdf = new \FPDF($resolvedOrientation, 'cm', self::paperSizeForFpdf($paperSize, $resolvedOrientation));
        $pdf->SetMargins($resolvedMargins['left'], $resolvedMargins['top'], $resolvedMargins['right']);
        $pdf->SetAutoPageBreak(false);
        $startX = $resolvedMargins['left'];
        $startY = $resolvedMargins['top'];

        foreach ($imagePaths as $index => $imagePath) {
            if ($index % self::LABELS_PER_PAGE === 0) {
                $pdf->AddPage();
                [$startX, $startY] = self::startPosition(
                    $pdf,
                    $resolvedMargins,
                    $centeredPlacement,
                    $horizontalGap,
                    $verticalGap
                );
            }

            $slot = $index % self::LABELS_PER_PAGE;
            $column = $slot % self::COLUMNS;
            $row = intdiv($slot, self::COLUMNS);
            $x = $startX + ($column * (self::LABEL_WIDTH + $horizontalGap));
            $y = $startY + ($row * (self::LABEL_HEIGHT + $verticalGap));

            $pdf->Rect($x, $y, self::LABEL_WIDTH, self::LABEL_HEIGHT);
            $pdf->Image(
                $imagePath,
                $x + self::BORDER_WIDTH,
                $y + self::BORDER_WIDTH,
                self::LABEL_WIDTH - (2 * self::BORDER_WIDTH),
                self::LABEL_HEIGHT - (2 * self::BORDER_WIDTH),
                'PNG'
            );
        }

        $pdf->Output('I', $fileName, true);
    }
}
