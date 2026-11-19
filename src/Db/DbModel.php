<?php

declare(strict_types=1);

namespace Okarabudka\PhpMvcCore\Db;

use Okarabudka\PhpMvcCore\Application;
use Okarabudka\PhpMvcCore\Model;
use PDO;
use PDOStatement;

/**
 * Active-record style model: the subclass names its table, columns and primary key.
 */
abstract class DbModel extends Model
{
    abstract public function tableName(): string;

    /**
     * Columns written by save().
     *
     * @return list<string>
     */
    abstract public function attributes(): array;

    abstract public function primaryKey(): string;

    public static function prepare(string $sql): PDOStatement
    {
        return Application::$app->db->prepare($sql);
    }

    public function save(): bool
    {
        $tableName = $this->tableName();
        $attributes = $this->attributes();
        $params = array_map(static fn(string $attribute): string => ":$attribute", $attributes);
        $statement = static::prepare(
            "INSERT INTO $tableName (" . implode(',', $attributes) . ') VALUES (' . implode(',', $params) . ')',
        );

        foreach ($attributes as $attribute) {
            $statement->bindValue(":$attribute", $this->{$attribute});
        }

        return $statement->execute();
    }

    /**
     * @param array<string, scalar|null> $where column => value, combined with AND
     */
    public function findOne(array $where): static|false
    {
        $tableName = $this->tableName();
        $sql = implode('AND ', array_map(static fn(string $column): string => "$column = :$column", array_keys($where)));
        $statement = static::prepare("SELECT * FROM $tableName WHERE $sql");
        foreach ($where as $column => $value) {
            $statement->bindValue(":$column", $value);
        }
        $statement->execute();

        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if (!is_array($row)) {
            return false;
        }

        $model = new static();
        $model->loadData($row);

        return $model;
    }
}
