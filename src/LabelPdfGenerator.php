<?php

declare(strict_types=1);

namespace Layout;

final class LabelPdfGenerator
{
    private const LEFT_MARGIN = 0.2;
    private const TOP_MARGIN = 0.3;
    private const RIGHT_MARGIN = 0.1;
    private const BORDER_WIDTH = 0.1;
    private const LABEL_WIDTH = 6.0;
    private const LABEL_HEIGHT = 3.0;
    private const COLUMNS = 4;
    private const ROWS = 6;
    private const LABELS_PER_PAGE = 24;
    public const MINIMUM_PAPER_WIDTH = self::LEFT_MARGIN + (self::COLUMNS * self::LABEL_WIDTH) + self::RIGHT_MARGIN;
    public const MINIMUM_PAPER_HEIGHT = self::TOP_MARGIN + (self::ROWS * self::LABEL_HEIGHT);

    public static function minimumPaperWidth(float $horizontalGap = 0.0): float
    {
        return self::LEFT_MARGIN
            + (self::COLUMNS * self::LABEL_WIDTH)
            + ((self::COLUMNS - 1) * $horizontalGap)
            + self::RIGHT_MARGIN;
    }

    public static function minimumPaperHeight(float $verticalGap = 0.0): float
    {
        return self::TOP_MARGIN
            + (self::ROWS * self::LABEL_HEIGHT)
            + ((self::ROWS - 1) * $verticalGap);
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
        float $verticalGap = 0.0
    ): void
    {
        $pdf = new \FPDF('L', 'cm', $paperSize);
        $pdf->SetMargins(self::LEFT_MARGIN, self::TOP_MARGIN, self::RIGHT_MARGIN);
        $pdf->SetAutoPageBreak(false);

        foreach ($imagePaths as $index => $imagePath) {
            if ($index % self::LABELS_PER_PAGE === 0) {
                $pdf->AddPage();
            }

            $slot = $index % self::LABELS_PER_PAGE;
            $column = $slot % self::COLUMNS;
            $row = intdiv($slot, self::COLUMNS);
            $x = self::LEFT_MARGIN + ($column * (self::LABEL_WIDTH + $horizontalGap));
            $y = self::TOP_MARGIN + ($row * (self::LABEL_HEIGHT + $verticalGap));

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
