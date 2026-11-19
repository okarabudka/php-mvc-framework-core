<?php

declare(strict_types=1);

namespace Okarabudka\PhpMvcCore;

class Response
{
    private int $statusCode = 200;

    /** @var array<string, string> */
    private array $headers = [];

    public function setStatusCode(int $code): void
    {
        $this->statusCode = $code;
        if (!headers_sent()) {
            http_response_code($code);
        }
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function setHeader(string $name, string $value): void
    {
        $this->headers[$name] = $value;
        if (!headers_sent()) {
            header($name . ': ' . $value);
        }
    }

    public function getHeader(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }

    public function redirect(string $url, int $code = 302): void
    {
        $this->setStatusCode($code);
        $this->setHeader('Location', $url);
    }
}
