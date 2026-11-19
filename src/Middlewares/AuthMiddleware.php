<?php

declare(strict_types=1);

namespace Okarabudka\PhpMvcCore\Middlewares;

use Okarabudka\PhpMvcCore\Application;
use Okarabudka\PhpMvcCore\Exception\ForbiddenException;

/**
 * Lets only logged-in users reach the listed controller actions (all actions when the list is empty).
 */
class AuthMiddleware extends BaseMiddleware
{
    /**
     * @param list<string> $actions
     */
    public function __construct(public array $actions = []) {}

    /**
     * @throws ForbiddenException
     */
    public function execute(): void
    {
        if (!Application::isGuest()) {
            return;
        }

        $action = Application::$app->controller?->action;
        if ($this->actions === [] || in_array($action, $this->actions, true)) {
            throw new ForbiddenException();
        }
    }
}
