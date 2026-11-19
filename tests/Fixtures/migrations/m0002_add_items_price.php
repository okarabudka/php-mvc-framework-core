<?php

declare(strict_types=1);

namespace Migrations;

use Okarabudka\PhpMvcCore\Application;
use Okarabudka\PhpMvcCore\Db\Migration;

class m0002_add_items_price implements Migration
{
    public function up(): void
    {
        Application::$app->db->pdo->exec('ALTER TABLE items ADD COLUMN price INTEGER');
    }

    public function down(): void
    {
        Application::$app->db->pdo->exec('ALTER TABLE items DROP COLUMN price');
    }
}
