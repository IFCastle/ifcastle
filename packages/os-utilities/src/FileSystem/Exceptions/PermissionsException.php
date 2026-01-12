<?php

declare(strict_types=1);

namespace IfCastle\OsUtilities\FileSystem\Exceptions;

class PermissionsException extends FileSystemException
{
    protected string $template      = 'Required permissions {permissions} for {file} missing';

    public function __construct(string $file, string $permissions)
    {
        parent::__construct([
            'file'                  => $file,
            'permissions'           => $permissions,
        ]);
    }
}
