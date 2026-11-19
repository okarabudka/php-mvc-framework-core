<?php

namespace Okarabudka\PhpMvcCore;

class Response
{
    public function setStatusCode(int $code): void
    {
        http_response_code($code);
    }
}
