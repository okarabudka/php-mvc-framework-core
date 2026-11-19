<?php

declare(strict_types=1);

namespace Okarabudka\PhpMvcCore\Tests;

use Okarabudka\PhpMvcCore\Controller;
use RuntimeException;

final class ViewTest extends TestCase
{
    public function testRendersViewInsideApplicationLayout(): void
    {
        $app = $this->createApplication();

        $html = $app->view->renderView('home', ['name' => 'Ann']);

        self::assertStringContainsString('<main><title>Home</title><h1>Hello Ann</h1>', $html);
        self::assertSame('Home', $app->view->title);
    }

    public function testParamsAreEscapedByTheTemplate(): void
    {
        $html = $this->createApplication()->view->renderView('home', ['name' => '<b>']);

        self::assertStringContainsString('Hello &lt;b&gt;', $html);
    }

    public function testControllerLayoutWins(): void
    {
        $app = $this->createApplication();
        $app->setController(new Controller());
        $app->getController()?->setLayout('auth');

        self::assertStringStartsWith('<auth><h1>Hello guest</h1>', $app->view->renderView('home'));
    }

    public function testSameViewRendersTwice(): void
    {
        $app = $this->createApplication();

        $first = $app->view->renderView('home', ['name' => 'one']);
        $second = $app->view->renderView('home', ['name' => 'two']);

        self::assertStringContainsString('Hello one', $first);
        self::assertStringContainsString('Hello two', $second);
        self::assertStringContainsString('<main>', $second);
    }

    public function testMissingViewThrows(): void
    {
        $app = $this->createApplication();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('View file not found');
        $app->view->renderView('missing');
    }
}
