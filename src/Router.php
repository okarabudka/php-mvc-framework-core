<?php

declare(strict_types=1);

namespace Okarabudka\PhpMvcCore;

use Okarabudka\PhpMvcCore\Exception\NotFoundException;
use Stringable;
use UnexpectedValueException;

/**
 * Maps method + path to a callback: a view name, [Controller::class, 'action'] or any callable.
 * The callback receives the Request and Response and returns the response body (or nothing).
 *
 * @phpstan-type Callback string|array{class-string<Controller>, string}|callable(Request, Response): (string|Stringable|null|void)
 */
class Router
{
    /** @var array<string, array<string, Callback>> */
    protected array $routes = [];

    public function __construct(
        private readonly Request $request,
        private readonly Response $response,
    ) {}

    /**
     * @param Callback $callback
     */
    public function get(string $path, string|array|callable $callback): void
    {
        $this->routes['get'][$path] = $callback;
    }

    /**
     * @param Callback $callback
     */
    public function post(string $path, string|array|callable $callback): void
    {
        $this->routes['post'][$path] = $callback;
    }

    /**
     * @throws NotFoundException
     */
    public function resolve(): string
    {
        $callback = $this->routes[$this->request->method()][$this->request->getPath()] ?? null;

        if ($callback === null) {
            throw new NotFoundException();
        }

        if (is_string($callback)) {
            return Application::$app->view->renderView($callback);
        }

        if (is_array($callback) && is_string($callback[0]) && is_a($callback[0], Controller::class, true)) {
            $controller = new $callback[0]();
            $controller->action = $callback[1];
            Application::$app->controller = $controller;

            foreach ($controller->getMiddlewares() as $middleware) {
                $middleware->execute();
            }

            $callback = [$controller, $callback[1]];
        }

        if (!is_callable($callback)) {
            throw new UnexpectedValueException('Route callback is not callable');
        }

        $result = $callback($this->request, $this->response);
        if ($result === null) {
            return '';
        }
        if (is_string($result) || $result instanceof Stringable) {
            return (string) $result;
        }

        throw new UnexpectedValueException('Route callback must return a string or nothing, got ' . get_debug_type($result));
    }
}
