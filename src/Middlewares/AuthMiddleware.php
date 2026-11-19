<?php

namespace Okarabudka\PhpMvcCore\Middlewares;

use Okarabudka\PhpMvcCore\Application;
use Okarabudka\PhpMvcCore\Exception\ForbiddenException;

class AuthMiddleware extends BaseMiddleware
{
    public array $actions = [];

    /**
     * @param array $actions
     */
    public function __construct(array $actions)
    {
        $this->actions = $actions;
    }


    /**
     * @throws ForbiddenException
     */
    public function execute(): void
    {
        if (Application::isGuest()) {
            if (empty($this->actions) || in_array(Application::$app->controller->action, $this->actions)) {
                throw new ForbiddenException();
            }
        }
    }
}
