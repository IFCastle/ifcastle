<?php

declare(strict_types=1);

namespace IfCastle\OsUtilities\FileSystem\Exceptions;

class FileIsNotExistException extends FileSystemException
{
    protected string $template      = 'The file {file} is not exist';

    public function __construct(string $file)
    {
        parent::__construct([
            'file'                  => $file,
        ]);
    }
}
