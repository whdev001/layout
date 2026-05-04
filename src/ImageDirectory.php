<?php

declare(strict_types=1);

namespace Layout;

use RuntimeException;

final class ImageDirectory
{
    public static function resolve(string $projectRoot, string $directory): string
    {
        $directory = trim($directory);

        if ($directory === '') {
            throw new RuntimeException('Image directory is required.');
        }

        if (self::isAbsolutePath($directory)) {
            throw new RuntimeException('Use a directory path relative to this project.');
        }

        $rootPath = realpath($projectRoot);

        if ($rootPath === false) {
            throw new RuntimeException('Project root could not be resolved.');
        }

        $candidatePath = $rootPath . DIRECTORY_SEPARATOR . self::normalizeSeparators($directory);
        $resolvedPath = realpath($candidatePath);

        if ($resolvedPath === false || !is_dir($resolvedPath)) {
            throw new RuntimeException('Image directory was not found.');
        }

        $rootPrefix = rtrim($rootPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if ($resolvedPath !== $rootPath && !str_starts_with($resolvedPath, $rootPrefix)) {
            throw new RuntimeException('Image directory must stay inside this project.');
        }

        return $resolvedPath;
    }

    private static function isAbsolutePath(string $path): bool
    {
        if (str_starts_with($path, DIRECTORY_SEPARATOR)) {
            return true;
        }

        return (bool) preg_match('/^[A-Za-z]:[\\\\\/]/', $path);
    }

    private static function normalizeSeparators(string $path): string
    {
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    }
}
