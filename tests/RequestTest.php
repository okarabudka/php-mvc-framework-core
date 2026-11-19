<?php

declare(strict_types=1);

namespace Okarabudka\PhpMvcCore\Tests;

use Okarabudka\PhpMvcCore\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function paths(): iterable
    {
        yield 'plain' => ['/contacts', '/contacts'];
        yield 'with query string' => ['/contacts?page=2&sort=name', '/contacts'];
        yield 'root' => ['/', '/'];
    }

    #[DataProvider('paths')]
    public function testPathIgnoresQueryString(string $uri, string $expected): void
    {
        self::assertSame($expected, new Request(['REQUEST_URI' => $uri], [], [])->getPath());
    }

    public function testDefaultsToGetAndRootPath(): void
    {
        $request = new Request([], [], []);

        self::assertSame('get', $request->method());
        self::assertTrue($request->isGet());
        self::assertFalse($request->isPost());
        self::assertSame('/', $request->getPath());
    }

    public function testMethodIsLowerCase(): void
    {
        $request = new Request(['REQUEST_METHOD' => 'POST'], [], []);

        self::assertSame('post', $request->method());
        self::assertTrue($request->isPost());
    }

    public function testGetBodyReturnsQueryForGet(): void
    {
        $request = new Request(['REQUEST_METHOD' => 'GET'], ['q' => 'mvc'], ['ignored' => 'x']);

        self::assertSame(['q' => 'mvc'], $request->getBody());
    }

    public function testGetBodyReturnsPostFieldsForPost(): void
    {
        $request = new Request(['REQUEST_METHOD' => 'POST'], ['ignored' => 'x'], ['email' => 'a@b.c', 'tags' => ['a']]);

        self::assertSame(['email' => 'a@b.c'], $request->getBody(), 'non-string values are dropped');
    }

    public function testGetBodyKeepsRawValuesForOutputEscaping(): void
    {
        $request = new Request(['REQUEST_METHOD' => 'POST'], [], ['password' => 'p<a>&"s']);

        self::assertSame(['password' => 'p<a>&"s'], $request->getBody());
    }

    public function testGetBodyIsEmptyForOtherMethods(): void
    {
        self::assertSame([], new Request(['REQUEST_METHOD' => 'DELETE'], ['a' => '1'], ['b' => '2'])->getBody());
    }

    public function testReadsSuperglobalsByDefault(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/search?q=1';
        $_GET = ['q' => '1'];

        $request = new Request();

        self::assertSame('/search', $request->getPath());
        self::assertSame(['q' => '1'], $request->getBody());
    }
}
