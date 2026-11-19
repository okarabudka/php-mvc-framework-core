<?php

declare(strict_types=1);

namespace Okarabudka\PhpMvcCore\Exception;

class ForbiddenException extends HttpException
{
    protected $message = 'You don\'t have permission to access this page';
    protected $code = 403;
}
