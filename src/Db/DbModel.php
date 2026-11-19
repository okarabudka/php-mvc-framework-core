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

    /**
     * Inserts the model and fills the primary key property, if the model declares one.
     */
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
        $statement->execute();

        $primaryKey = $this->primaryKey();
        $id = Application::$app->db->pdo->lastInsertId();
        if (property_exists($this, $primaryKey) && $id !== false) {
            $this->loadData([$primaryKey => $id]);
        }

        return true;
    }

    /**
     * @param array<string, scalar|null> $where column => value, combined with AND
     *
     * @return static|null null when no row matches
     */
    public function findOne(array $where): ?static
    {
        $tableName = $this->tableName();
        $sql = implode(' AND ', array_map(static fn(string $column): string => "$column = :$column", array_keys($where)));
        $statement = static::prepare("SELECT * FROM $tableName WHERE $sql");
        foreach ($where as $column => $value) {
            $statement->bindValue(":$column", $value);
        }
        $statement->execute();

        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if (!is_array($row)) {
            return null;
        }

        $model = new static();
        $model->loadData($row);

        return $model;
    }
}
