<?php

declare(strict_types=1);

namespace Okarabudka\PhpMvcCore\Exception;

use Throwable;

class ForbiddenException extends HttpException
{
    public function __construct(string $message = 'You don\'t have permission to access this page', ?Throwable $previous = null)
    {
        parent::__construct($message, 403, $previous);
    }
}
