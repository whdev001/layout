<?php

declare(strict_types=1);

namespace Layout;

use FilesystemIterator;
use SplFileInfo;

final class ImageFinder
{
    /**
     * @return list<string>
     */
    public static function findPngFiles(string $directory): array
    {
        $images = [];

        $iterator = new FilesystemIterator(
            $directory,
            FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS
        );

        foreach ($iterator as $entry) {
            if (!$entry instanceof SplFileInfo || !$entry->isFile()) {
                continue;
            }

            if (strcasecmp($entry->getExtension(), 'png') !== 0) {
                continue;
            }

            $realPath = $entry->getRealPath();

            if ($realPath !== false) {
                $images[] = $realPath;
            }
        }

        usort(
            $images,
            static fn (string $left, string $right): int => strnatcasecmp(basename($left), basename($right))
        );

        return $images;
    }
}
