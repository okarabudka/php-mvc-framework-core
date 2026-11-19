<?php

declare(strict_types=1);

namespace Okarabudka\PhpMvcCore\Tests\Fixtures;

use Okarabudka\PhpMvcCore\UserModel;

final class User extends UserModel
{
    public int $id = 0;
    public string $email = '';
    public string $name = '';
    public int $status = 0;
    public string $password = '';
    public string $confirmPassword = '';

    public const string TABLE_SQL = 'CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email VARCHAR(255) NOT NULL,
        name VARCHAR(255) NOT NULL,
        status INTEGER NOT NULL DEFAULT 0
    )';

    public function tableName(): string
    {
        return 'users';
    }

    public function attributes(): array
    {
        return ['email', 'name', 'status'];
    }

    public function primaryKey(): string
    {
        return 'id';
    }

    public function rules(): array
    {
        return [
            'email' => [self::RULE_REQUIRED, self::RULE_EMAIL, [self::RULE_UNIQUE, 'class' => self::class]],
            'name' => [self::RULE_REQUIRED, [self::RULE_MIN, 'min' => 2], [self::RULE_MAX, 'max' => 10]],
            'confirmPassword' => [[self::RULE_MATCH, 'match' => 'password']],
        ];
    }

    public function labels(): array
    {
        return ['email' => 'Email', 'password' => 'Password'];
    }

    public function getDisplayName(): string
    {
        return $this->name;
    }
}
