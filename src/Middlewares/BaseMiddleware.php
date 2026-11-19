<?php

declare(strict_types=1);

namespace Okarabudka\PhpMvcCore\Middlewares;

abstract class BaseMiddleware
{
    abstract public function execute(): void;
}
