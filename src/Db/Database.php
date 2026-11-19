<?php

declare(strict_types=1);

namespace Okarabudka\PhpMvcCore\Db;

use Okarabudka\PhpMvcCore\Application;
use PDO;
use PDOStatement;
use RuntimeException;

class Database
{
    public PDO $pdo;

    /**
     * @param array{dsn?: string, user?: string, password?: string} $config
     */
    public function __construct(array $config)
    {
        $this->pdo = new PDO($config['dsn'] ?? '', $config['user'] ?? '', $config['password'] ?? '');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function applyMigrations(): void
    {
        $this->createMigrationsTable();
        $appliedMigrations = $this->getAppliedMigrations();

        $directory = Application::$ROOT_DIR . '/migrations';
        $files = glob($directory . '/*.php');
        if ($files === false) {
            throw new RuntimeException("Cannot read migrations from $directory");
        }

        $newMigrations = [];
        foreach (array_diff(array_map('basename', $files), $appliedMigrations) as $migration) {
            require_once $directory . '/' . $migration;
            $className = 'Migrations\\' . pathinfo($migration, PATHINFO_FILENAME);
            $instance = class_exists($className) ? new $className() : null;
            if (!$instance instanceof Migration) {
                throw new RuntimeException("$className must exist and implement " . Migration::class);
            }

            $this->log("Applying migration $migration");
            $instance->up();
            $this->log("Applied migration $migration");

            $newMigrations[] = $migration;
        }

        if ($newMigrations !== []) {
            $this->saveMigrations($newMigrations);
        } else {
            $this->log('All migrations are applied');
        }
    }

    public function createMigrationsTable(): void
    {
        $id = $this->driver() === 'sqlite' ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id $id,
                migration VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    /**
     * @return list<string>
     */
    public function getAppliedMigrations(): array
    {
        $statement = $this->pdo->query('SELECT migration FROM migrations');

        return array_values(array_filter($statement->fetchAll(PDO::FETCH_COLUMN), is_string(...)));
    }

    /**
     * @param list<string> $migrations
     */
    public function saveMigrations(array $migrations): void
    {
        $values = implode(',', array_map(static fn(string $migration): string => "('$migration')", $migrations));
        $this->pdo->exec("INSERT INTO migrations (migration) VALUES $values");
    }

    public function prepare(string $sql): PDOStatement
    {
        return $this->pdo->prepare($sql);
    }

    public function driver(): string
    {
        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        return is_string($driver) ? $driver : '';
    }

    protected function log(string $message): void
    {
        echo '[' . date('Y-m-d H:i:s') . '] - ' . $message . PHP_EOL;
    }
}
