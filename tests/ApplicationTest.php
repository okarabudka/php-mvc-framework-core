<?php

declare(strict_types=1);

namespace Okarabudka\PhpMvcCore\Tests;

use Okarabudka\PhpMvcCore\Application;
use Okarabudka\PhpMvcCore\Tests\Fixtures\User;
use PDOException;
use RuntimeException;

final class ApplicationTest extends TestCase
{
    private string $databaseFile = '';

    protected function tearDown(): void
    {
        if ($this->databaseFile !== '' && is_file($this->databaseFile)) {
            unlink($this->databaseFile);
        }
    }

    public function testRunPrintsResolvedRoute(): void
    {
        $app = $this->createApplication(self::request('GET', '/'));
        $app->router->get('/', static fn(): string => 'home page');

        $this->expectOutputString('home page');
        $app->run();

        self::assertSame(200, $app->response->getStatusCode());
    }

    public function testNotFoundRendersErrorViewWith404(): void
    {
        $app = $this->createApplication(self::request('GET', '/missing'));

        $this->expectOutputRegex('/<h1>404 - Page not found<\/h1>/');
        $app->run();

        self::assertSame(404, $app->response->getStatusCode());
    }

    public function testForbiddenUses403(): void
    {
        $app = $this->createApplication(self::request('GET', '/'));
        $app->router->get('/', static fn(): never => throw new \Okarabudka\PhpMvcCore\Exception\ForbiddenException());

        $this->expectOutputRegex('/403 - You don&#039;t have permission/');
        $app->run();

        self::assertSame(403, $app->response->getStatusCode());
    }

    public function testUnexpectedErrorsBecome500EvenWithStringCodes(): void
    {
        $app = $this->createApplication(self::request('GET', '/'));
        $app->router->get('/', static function (): never {
            $exception = new PDOException('no such table');
            // PDOException carries SQLSTATE strings as code.
            new \ReflectionProperty(\Exception::class, 'code')->setValue($exception, '42S02');
            throw $exception;
        });
        $log = tempnam(sys_get_temp_dir(), 'log');
        self::assertIsString($log);
        $previous = ini_set('error_log', $log);

        try {
            ob_start();
            $app->run();
            ob_end_clean();
        } finally {
            ini_set('error_log', (string) $previous);
        }

        self::assertSame(500, $app->response->getStatusCode());
        self::assertStringContainsString('no such table', (string) file_get_contents($log));
        unlink($log);
    }

    public function testEventsWrapTheRequest(): void
    {
        $app = $this->createApplication(self::request('GET', '/'));
        $app->router->get('/', static fn(): string => 'body');
        $app->on(Application::EVENT_BEFORE_REQUEST, static function (): void {
            echo '[before]';
        });
        $app->on(Application::EVENT_AFTER_REQUEST, static function (): void {
            echo '[after]';
        });

        $this->expectOutputString('[before]body[after]');
        $app->run();
    }

    public function testLoginAndLogout(): void
    {
        $app = $this->createApplication();
        $user = $this->createUser();

        self::assertTrue(Application::isGuest());
        self::assertTrue($app->login($user));
        self::assertFalse(Application::isGuest());
        self::assertSame($user->id, $_SESSION['user']);

        $app->logout();
        self::assertTrue(Application::isGuest());
        self::assertArrayNotHasKey('user', $_SESSION);
    }

    public function testUserIsRestoredFromSession(): void
    {
        $dsn = $this->fileDsn();
        $app = $this->createApplication(dsn: $dsn);
        $app->login($this->createUser('ann@example.com', 'Ann'));

        $next = $this->createApplication(dsn: $dsn);

        self::assertInstanceOf(User::class, $next->user);
        self::assertSame('Ann', $next->user->getDisplayName());
    }

    public function testDeletedSessionUserBecomesGuest(): void
    {
        $dsn = $this->fileDsn();
        $app = $this->createApplication(dsn: $dsn);
        $app->login($this->createUser());
        $app->db->pdo->exec('DELETE FROM users');

        $next = $this->createApplication(dsn: $dsn);

        self::assertNull($next->user);
        self::assertTrue(Application::isGuest());
    }

    public function testMissingErrorViewIsNotSwallowed(): void
    {
        $app = new Application(sys_get_temp_dir(), ['userClass' => User::class, 'db' => ['dsn' => 'sqlite::memory:']], self::request('GET', '/x'));

        $this->expectException(RuntimeException::class);
        $app->run();
    }

    private function fileDsn(): string
    {
        $this->databaseFile = (string) tempnam(sys_get_temp_dir(), 'db');

        return 'sqlite:' . $this->databaseFile;
    }
}
