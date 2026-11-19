<?php

declare(strict_types=1);

namespace Okarabudka\PhpMvcCore\Tests;

use Okarabudka\PhpMvcCore\Exception\ForbiddenException;
use Okarabudka\PhpMvcCore\Exception\NotFoundException;
use Okarabudka\PhpMvcCore\Request;
use Okarabudka\PhpMvcCore\Response;
use Okarabudka\PhpMvcCore\Tests\Fixtures\PageController;
use UnexpectedValueException;

final class RouterTest extends TestCase
{
    public function testStringCallbackRendersViewInLayout(): void
    {
        $app = $this->createApplication(self::request('GET', '/'));
        $app->router->get('/', 'home');

        $html = $app->router->resolve();

        self::assertStringContainsString('<main><title>Home</title>', $html);
        self::assertStringContainsString('<h1>Hello guest</h1>', $html);
    }

    public function testControllerCallbackSetsActionAndUsesControllerLayout(): void
    {
        $app = $this->createApplication(self::request('GET', '/page?x=1'));
        $app->router->get('/page', [PageController::class, 'home']);

        $html = $app->router->resolve();

        self::assertInstanceOf(PageController::class, $app->controller);
        self::assertSame('home', $app->controller->action);
        self::assertStringStartsWith('<auth><h1>Hello controller</h1>', $html);
    }

    public function testCallbackReceivesRequestAndResponse(): void
    {
        $app = $this->createApplication(self::request('POST', '/save', ['name' => 'Ann']));
        $app->router->post('/save', [PageController::class, 'save']);

        self::assertSame('', $app->router->resolve());
        self::assertSame('/saved?name=Ann', $app->response->getHeader('Location'));
    }

    public function testClosureCallback(): void
    {
        $app = $this->createApplication(self::request('GET', '/ping'));
        $app->router->get('/ping', static fn(Request $request, Response $response): string => 'pong ' . $request->getPath());

        self::assertSame('pong /ping', $app->router->resolve());
    }

    public function testRoutesAreMatchedByMethod(): void
    {
        $app = $this->createApplication(self::request('POST', '/ping'));
        $app->router->get('/ping', static fn(): string => 'pong');

        $this->expectException(NotFoundException::class);
        $app->router->resolve();
    }

    public function testUnknownPathThrowsNotFound(): void
    {
        $app = $this->createApplication(self::request('GET', '/missing'));

        $this->expectException(NotFoundException::class);
        $this->expectExceptionCode(404);
        $app->router->resolve();
    }

    public function testMiddlewareRunsBeforeAction(): void
    {
        $app = $this->createApplication(self::request('GET', '/secret'));
        $app->router->get('/secret', [PageController::class, 'secret']);

        $this->expectException(ForbiddenException::class);
        $app->router->resolve();
    }

    public function testNonStringResultIsRejected(): void
    {
        $app = $this->createApplication(self::request('GET', '/number'));
        // @phpstan-ignore argument.type (the wrong return type is what this test is about)
        $app->router->get('/number', static fn(): int => 42);

        $this->expectException(UnexpectedValueException::class);
        $app->router->resolve();
    }
}
