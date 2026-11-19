<?php

declare(strict_types=1);

namespace Okarabudka\PhpMvcCore;

class Request
{
    /** @var array<mixed> */
    private array $server;

    /** @var array<mixed> */
    private array $query;

    /** @var array<mixed> */
    private array $post;

    /**
     * Defaults to the PHP superglobals; pass arrays to build a request in tests or CLI scripts.
     *
     * @param array<mixed>|null $server
     * @param array<mixed>|null $query
     * @param array<mixed>|null $post
     */
    public function __construct(?array $server = null, ?array $query = null, ?array $post = null)
    {
        $this->server = $server ?? $_SERVER;
        $this->query = $query ?? $_GET;
        $this->post = $post ?? $_POST;
    }

    public function getPath(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        $path = is_string($uri) ? $uri : '/';
        $position = strpos($path, '?');

        return $position === false ? $path : substr($path, 0, $position);
    }

    public function method(): string
    {
        $method = $this->server['REQUEST_METHOD'] ?? 'GET';

        return is_string($method) ? strtolower($method) : 'get';
    }

    public function isGet(): bool
    {
        return $this->method() === 'get';
    }

    public function isPost(): bool
    {
        return $this->method() === 'post';
    }

    /**
     * Query parameters for GET, form fields for POST. Only string values are returned; escape them on output.
     *
     * @return array<string, string>
     */
    public function getBody(): array
    {
        $source = match ($this->method()) {
            'get' => $this->query,
            'post' => $this->post,
            default => [],
        };

        $body = [];
        foreach ($source as $key => $value) {
            if (is_string($value)) {
                $body[(string) $key] = $value;
            }
        }

        return $body;
    }
}
