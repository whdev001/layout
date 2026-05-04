<?php

declare(strict_types=1);

if (!class_exists('FPDF')) {
    class FPDF
    {
        public function __construct(string $orientation = 'P', string $unit = 'mm', string|array $size = 'A4')
        {
        }

        public function SetMargins(float $left, float $top, ?float $right = null): void
        {
        }

        public function SetAutoPageBreak(bool $auto, float $margin = 0): void
        {
        }

        public function AddPage(string $orientation = '', string|array $size = '', int $rotation = 0): void
        {
        }

        public function Rect(float $x, float $y, float $w, float $h, string $style = ''): void
        {
        }

        public function Image(string $file, ?float $x = null, ?float $y = null, float $w = 0, float $h = 0, string $type = '', string $link = ''): void
        {
        }

        public function Output(string $dest = '', string $name = '', bool $isUTF8 = false): string
        {
            return '';
        }
    }
}
