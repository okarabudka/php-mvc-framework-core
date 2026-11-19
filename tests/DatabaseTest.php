<?php

declare(strict_types=1);

namespace Okarabudka\PhpMvcCore\Tests;

final class DatabaseTest extends TestCase
{
    public function testAppliesNewMigrationsInOrderAndRecordsThem(): void
    {
        $db = $this->createApplication(withUsers: false)->db;

        $this->expectOutputRegex(
            '/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\] - Applying migration m0001_create_items\.php\n'
            . '.*Applied migration m0001_create_items\.php\n'
            . '.*Applying migration m0002_add_items_price\.php\n'
            . '.*Applied migration m0002_add_items_price\.php\n$/',
        );

        $db->applyMigrations();

        self::assertSame(['m0001_create_items.php', 'm0002_add_items_price.php'], $db->getAppliedMigrations());
        $db->pdo->exec("INSERT INTO items (name, price) VALUES ('pen', 3)");
    }

    public function testSecondRunAppliesNothing(): void
    {
        $db = $this->createApplication(withUsers: false)->db;
        ob_start();
        $db->applyMigrations();
        ob_end_clean();

        $this->expectOutputRegex('/All migrations are applied\n$/');
        $db->applyMigrations();

        self::assertCount(2, $db->getAppliedMigrations());
    }

    public function testMigrationNamesAreBoundAsParameters(): void
    {
        $db = $this->createApplication(withUsers: false)->db;
        $db->createMigrationsTable();

        $db->saveMigrations(["m0003_it's_quoted.php"]);

        self::assertSame(["m0003_it's_quoted.php"], $db->getAppliedMigrations());
    }

    public function testLogUsesMinutesNotMonth(): void
    {
        $db = $this->createApplication(withUsers: false)->db;
        $db->createMigrationsTable();
        $db->saveMigrations(['m0001_create_items.php', 'm0002_add_items_price.php']);

        ob_start();
        $db->applyMigrations();
        $output = (string) ob_get_clean();

        self::assertMatchesRegularExpression('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $output);
        $logged = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', substr($output, 1, 19));
        self::assertNotFalse($logged);
        self::assertLessThan(120, abs(time() - $logged->getTimestamp()));
    }

    public function testDriverName(): void
    {
        self::assertSame('sqlite', $this->createApplication(withUsers: false)->db->driver());
    }
}
