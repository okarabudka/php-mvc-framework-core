<?php

declare(strict_types=1);

namespace Okarabudka\PhpMvcCore\Exception;

use Throwable;

class NotFoundException extends HttpException
{
    public function __construct(string $message = 'Page not found', ?Throwable $previous = null)
    {
        parent::__construct($message, 404, $previous);
    }
}
