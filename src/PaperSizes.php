<?php

declare(strict_types=1);

namespace Layout;

final class PaperSizes
{
    private const OPTIONS = [
        'A4' => 'A4',
        'LETTER' => 'Letter',
        'LEGAL' => 'Legal',
    ];

    public static function default(): string
    {
        return 'A4';
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return self::OPTIONS;
    }

    public static function normalize(string $paperSize): ?string
    {
        $normalized = strtoupper(trim($paperSize));

        if ($normalized === '') {
            return null;
        }

        return self::OPTIONS[$normalized] ?? null;
    }
}
