<?php

declare(strict_types=1);

namespace Okarabudka\PhpMvcCore\Tests;

use Okarabudka\PhpMvcCore\Response;
use PHPUnit\Framework\TestCase;

final class ResponseTest extends TestCase
{
    public function testDefaultsToOk(): void
    {
        self::assertSame(200, new Response()->getStatusCode());
    }

    public function testStoresStatusCode(): void
    {
        $response = new Response();
        $response->setStatusCode(404);

        self::assertSame(404, $response->getStatusCode());
    }

    public function testRedirectSetsLocationAndFound(): void
    {
        $response = new Response();
        $response->redirect('/login');

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('/login', $response->getHeader('Location'));
        self::assertNull($response->getHeader('X-Missing'));
    }
}
