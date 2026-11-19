<?php

declare(strict_types=1);

namespace Okarabudka\PhpMvcCore\Tests;

use Okarabudka\PhpMvcCore\Session;

final class SessionTest extends TestCase
{
    public function testValues(): void
    {
        $session = new Session();

        self::assertNull($session->get('user'));
        $session->set('user', 7);
        self::assertSame(7, $session->get('user'));
        $session->unset('user');
        self::assertNull($session->get('user'));
    }

    public function testFlashLivesForExactlyOneFollowingRequest(): void
    {
        $session = new Session();
        $session->setFlash('success', 'Saved');
        self::assertSame('Saved', $session->getFlash('success'), 'readable in the same request');
        unset($session);

        $next = new Session();
        self::assertSame('Saved', $next->getFlash('success'), 'readable in the next request');
        unset($next);

        $afterNext = new Session();
        self::assertNull($afterNext->getFlash('success'), 'gone after that');
    }

    public function testBrokenFlashDataIsIgnored(): void
    {
        $_SESSION['flash_messages'] = ['a' => 'not an array', 'b' => ['remove' => false]];

        self::assertNull(new Session()->getFlash('a'));
    }
}
