<?php

declare(strict_types=1);

namespace Okarabudka\PhpMvcCore\Tests;

use Okarabudka\PhpMvcCore\Exception\ForbiddenException;
use Okarabudka\PhpMvcCore\Middlewares\AuthMiddleware;
use Okarabudka\PhpMvcCore\Tests\Fixtures\PageController;

final class MiddlewareTest extends TestCase
{
    public function testGuestIsForbiddenOnListedAction(): void
    {
        $this->createApplication()->controller = $this->controller('secret');

        $this->expectException(ForbiddenException::class);
        $this->expectExceptionCode(403);
        new AuthMiddleware(['secret'])->execute();
    }

    public function testGuestPassesOnOtherAction(): void
    {
        $this->createApplication()->controller = $this->controller('home');

        new AuthMiddleware(['secret'])->execute();

        $this->addToAssertionCount(1);
    }

    public function testEmptyListProtectsEveryAction(): void
    {
        $this->createApplication()->controller = $this->controller('home');

        $this->expectException(ForbiddenException::class);
        new AuthMiddleware()->execute();
    }

    public function testLoggedInUserPasses(): void
    {
        $app = $this->createApplication();
        $app->login($this->createUser());
        $app->controller = $this->controller('secret');

        new AuthMiddleware(['secret'])->execute();

        $this->addToAssertionCount(1);
    }

    private function controller(string $action): PageController
    {
        $controller = new PageController();
        $controller->action = $action;

        return $controller;
    }
}
