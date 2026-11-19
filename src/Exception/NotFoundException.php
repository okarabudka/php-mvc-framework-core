<?php

declare(strict_types=1);

namespace Okarabudka\PhpMvcCore\Exception;

class NotFoundException extends HttpException
{
    protected $message = 'Page not found';
    protected $code = 404;
}
