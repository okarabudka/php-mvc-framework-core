<?php

declare(strict_types=1);

namespace Okarabudka\PhpMvcCore\Tests;

use Okarabudka\PhpMvcCore\Tests\Fixtures\User;

final class DbModelTest extends TestCase
{
    public function testSaveInsertsRowAndFillsPrimaryKey(): void
    {
        $app = $this->createApplication();

        $first = $this->createUser('ann@example.com', 'Ann');
        $second = $this->createUser('bob@example.com', 'Bob');

        self::assertSame(1, $first->id);
        self::assertSame(2, $second->id);
        self::assertSame(2, $this->countUsers($app));
    }

    public function testFindOneByOneCondition(): void
    {
        $this->createApplication();
        $this->createUser('ann@example.com', 'Ann');

        $found = new User()->findOne(['email' => 'ann@example.com']);

        self::assertInstanceOf(User::class, $found);
        self::assertSame(1, $found->id);
        self::assertSame('Ann', $found->name);
    }

    public function testFindOneCombinesConditionsWithAnd(): void
    {
        $this->createApplication();
        $this->createUser('ann@example.com', 'Ann');
        $this->createUser('bob@example.com', 'Ann');

        $found = new User()->findOne(['name' => 'Ann', 'email' => 'bob@example.com']);

        self::assertSame(2, $found?->id);
        self::assertNull(new User()->findOne(['name' => 'Bob', 'email' => 'ann@example.com']));
    }

    public function testFindOneReturnsNullWhenNothingMatches(): void
    {
        $this->createApplication();

        self::assertNull(new User()->findOne(['email' => 'nobody@example.com']));
    }

    private function countUsers(\Okarabudka\PhpMvcCore\Application $app): int
    {
        $statement = $app->db->prepare('SELECT COUNT(*) FROM users');
        $statement->execute();

        return (int) $statement->fetchColumn();
    }
}
