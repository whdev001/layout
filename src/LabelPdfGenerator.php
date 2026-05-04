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
    private const LABELS_PER_PAGE = 24;

    /**
     * @param list<string> $imagePaths
     */
    public function outputInline(array $imagePaths, string $paperSize, string $fileName): void
    {
        if (!class_exists('FPDF')) {
            require_once __DIR__ . '/../vendor/setasign/fpdf/fpdf.php';
        }

        $pdf = (new \ReflectionClass('FPDF'))->newInstanceArgs(['L', 'cm', $paperSize]);
        $this->invoke($pdf, 'SetMargins', [self::LEFT_MARGIN, self::TOP_MARGIN, self::RIGHT_MARGIN]);
        $this->invoke($pdf, 'SetAutoPageBreak', [false]);

        foreach ($imagePaths as $index => $imagePath) {
            if ($index % self::LABELS_PER_PAGE === 0) {
                $this->invoke($pdf, 'AddPage');
            }

            $slot = $index % self::LABELS_PER_PAGE;
            $column = $slot % self::COLUMNS;
            $row = intdiv($slot, self::COLUMNS);
            $x = self::LEFT_MARGIN + ($column * self::LABEL_WIDTH);
            $y = self::TOP_MARGIN + ($row * self::LABEL_HEIGHT);

            $this->invoke($pdf, 'Rect', [$x, $y, self::LABEL_WIDTH, self::LABEL_HEIGHT]);
            $this->invoke($pdf, 'Image', [
                $imagePath,
                $x + self::BORDER_WIDTH,
                $y + self::BORDER_WIDTH,
                self::LABEL_WIDTH - (2 * self::BORDER_WIDTH),
                self::LABEL_HEIGHT - (2 * self::BORDER_WIDTH),
                'PNG',
            ]);
        }

        $this->invoke($pdf, 'Output', ['I', $fileName, true]);
    }

    /**
     * @param array<int, mixed> $arguments
     */
    private function invoke(object $instance, string $method, array $arguments = []): mixed
    {
        return $instance->{$method}(...$arguments);
    }
}
