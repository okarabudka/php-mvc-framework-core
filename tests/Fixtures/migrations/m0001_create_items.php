<?php

declare(strict_types=1);

namespace Migrations;

use Okarabudka\PhpMvcCore\Application;
use Okarabudka\PhpMvcCore\Db\Migration;

class m0001_create_items implements Migration
{
    public function up(): void
    {
        Application::$app->db->pdo->exec('CREATE TABLE items (id INTEGER PRIMARY KEY, name VARCHAR(50))');
    }

    public function down(): void
    {
        Application::$app->db->pdo->exec('DROP TABLE items');
    }
}
