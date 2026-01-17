<?php

declare(strict_types=1);

namespace IfCastle\OsUtilities\FileSystem\Exceptions;

use IfCastle\Exceptions\SystemException;

class FileSystemException extends SystemException
{
    final public const string FILE_SYSTEM  = 'fileSystem';

    /**
     * @param array<string, mixed>|string $message
     * @param array<string, mixed> $debug
     */
    public function __construct(array|string $message, array $debug = [], ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->setDebugData($debug);

        $this->tags[]               = self::FILE_SYSTEM;
    }
}
