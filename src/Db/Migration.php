<?php

declare(strict_types=1);

namespace Okarabudka\PhpMvcCore\Db;

/**
 * A migration lives in <root>/migrations/<name>.php as class Migrations\<name>.
 */
interface Migration
{
    public function up(): void;

    public function down(): void;
}
