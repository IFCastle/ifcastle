<?php

declare(strict_types=1);

namespace IfCastle\Application;

enum ExecutionRolesEnum: string
{
    case WEB_SERVER                 = 'web_server';

    case JOB_SERVER                 = 'job_server';
}
