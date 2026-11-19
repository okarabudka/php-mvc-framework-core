<?php

declare(strict_types=1);

namespace Okarabudka\PhpMvcCore\Tests;

use Okarabudka\PhpMvcCore\Application;
use Okarabudka\PhpMvcCore\Request;
use Okarabudka\PhpMvcCore\Tests\Fixtures\User;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected const string ROOT = __DIR__ . '/Fixtures';

    protected function setUp(): void
    {
        $_SESSION = [];
    }

    /**
     * An application on an in-memory SQLite database with a users table and the fixture views.
     */
    protected function createApplication(?Request $request = null, bool $withUsers = true, string $dsn = 'sqlite::memory:'): Application
    {
        $app = new Application(self::ROOT, [
            'userClass' => User::class,
            'db' => ['dsn' => $dsn],
        ], $request ?? new Request([], [], []));

        if ($withUsers) {
            $app->db->pdo->exec(User::TABLE_SQL);
        }

        return $app;
    }

    protected function createUser(string $email = 'ann@example.com', string $name = 'Ann'): User
    {
        $user = new User();
        $user->email = $email;
        $user->name = $name;
        $user->save();

        return $user;
    }

    /**
     * @param array<string, string> $post
     */
    protected static function request(string $method, string $uri, array $post = []): Request
    {
        $query = [];
        parse_str((string) parse_url($uri, PHP_URL_QUERY), $query);

        return new Request(['REQUEST_METHOD' => $method, 'REQUEST_URI' => $uri], $query, $post);
    }
}
