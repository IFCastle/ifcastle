<?php

declare(strict_types=1);

namespace IfCastle\OsUtilities\FileSystem;

use IfCastle\OsUtilities\FileSystem\Exceptions\FileIsNotExistException;
use IfCastle\OsUtilities\FileSystem\Exceptions\FileSystemException;
use IfCastle\OsUtilities\FileSystem\Exceptions\PermissionsException;

/**
 * ## File.
 *
 * Functions for reading a record to a file with checks and exceptions.
 */
class File
{
    /**
     *
     *
     * @throws FileSystemException
     */
    public static function put(string $fileName, mixed $data, ?int $flags = 0): int
    {
        \set_error_handler(
            static fn($severity, $message) =>
                throw new FileSystemException([
                    'template'      => 'Error occurred while write data to file {file}: {error}',
                    'error'         => $message,
                    'file'          => $fileName,
                ])
        );

        try {
            return \file_put_contents($fileName, $data, $flags);
        } finally {
            \restore_error_handler();
        }
    }

    /**
     *
     * @throws FileIsNotExistException
     * @throws FileSystemException
     * @throws PermissionsException
     */
    public static function get(string $fileName, int $maxFileSize = 10_485_760): string
    {
        if (!\is_file($fileName)) {
            throw new FileIsNotExistException($fileName);
        }

        if (!\is_readable($fileName)) {
            throw new PermissionsException($fileName, 'read');
        }

        $size                       = \filesize($fileName);

        if (\is_int($size) && $size > $maxFileSize) {
            throw new FileSystemException([
                'template'          => 'Exceeding the maximum file size {max_size} for {file}',
                'max_size'          => $maxFileSize,
                'file'              => $fileName,
            ]);
        }

        \set_error_handler(
            static fn($severity, $message) =>
            throw new FileSystemException([
                'template'      => 'Error occurred while read file {file}: {error}',
                'error'         => $message,
                'file'          => $fileName,
            ])
        );

        try {
            return \file_get_contents($fileName);
        } finally {
            \restore_error_handler();
        }
    }

    /**
     * Create a new directory if no exist.
     *
     * @throws FileSystemException
     */
    public static function createDir(string $dir): void
    {
        if (\is_dir($dir)) {
            return;
        }

        \set_error_handler(
            static fn($severity, $message) =>
                throw new FileSystemException([
                    'template'      => 'make directory {dir} failed: {error}',
                    'error'         => $message,
                    'dir'           => $dir,
                ])
        );

        try {
            if (false === \mkdir($dir, 0o777, true)) {
                throw new FileSystemException([
                    'template'      => 'make directory {dir} failed: {error}',
                    'error'         => '',
                    'dir'           => $dir,
                ]);
            }
        } finally {
            \restore_error_handler();
        }
    }
}
