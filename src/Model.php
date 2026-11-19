<?php

declare(strict_types=1);

namespace Okarabudka\PhpMvcCore;

use ReflectionNamedType;
use ReflectionObject;
use ReflectionProperty;

/**
 * Base for form and database models: mass assignment of public properties and rule-based validation.
 *
 * A rule is either a rule name (`self::RULE_REQUIRED`) or an array with the name first and its options:
 * `[self::RULE_MIN, 'min' => 8]`, `[self::RULE_MATCH, 'match' => 'password']`,
 * `[self::RULE_UNIQUE, 'class' => User::class, 'attribute' => 'email']`.
 *
 * @phpstan-type Rule string|array<int|string, mixed>
 * @phpstan-consistent-constructor
 */
abstract class Model
{
    public const string RULE_REQUIRED = 'required';
    public const string RULE_EMAIL = 'email';
    public const string RULE_MIN = 'min';
    public const string RULE_MAX = 'max';
    public const string RULE_MATCH = 'match';
    public const string RULE_UNIQUE = 'unique';

    /** @var array<string, list<string>> */
    public array $errors = [];

    /**
     * @return array<string, list<Rule>>
     */
    abstract public function rules(): array;

    /**
     * Assigns values to public properties declared by the concrete model. Unknown keys are ignored and
     * scalar values are converted to the property's scalar type, so request data cannot cause a TypeError.
     *
     * @param array<array-key, mixed> $data
     */
    public function loadData(array $data): void
    {
        foreach ((new ReflectionObject($this))->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $name = $property->getName();
            if ($property->isStatic() || $property->isReadOnly() || $property->getDeclaringClass()->getName() === self::class) {
                continue;
            }
            if (!array_key_exists($name, $data)) {
                continue;
            }

            $this->assign($property, $data[$name]);
        }
    }

    public function validate(): bool
    {
        foreach ($this->rules() as $attribute => $rules) {
            $value = $this->stringValue($attribute);
            foreach ($rules as $rule) {
                $ruleName = is_string($rule) ? $rule : $rule[0];
                $options = is_array($rule) ? $rule : [];

                if ($ruleName === self::RULE_REQUIRED && $value === '') {
                    $this->addErrorForRule($attribute, self::RULE_REQUIRED);
                }

                if ($ruleName === self::RULE_EMAIL && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                    $this->addErrorForRule($attribute, self::RULE_EMAIL);
                }

                if ($ruleName === self::RULE_MIN && strlen($value) < $this->intOption($options, 'min')) {
                    $this->addErrorForRule($attribute, self::RULE_MIN, $options);
                }

                if ($ruleName === self::RULE_MAX && strlen($value) > $this->intOption($options, 'max')) {
                    $this->addErrorForRule($attribute, self::RULE_MAX, $options);
                }

                if ($ruleName === self::RULE_MATCH) {
                    $other = $this->stringOption($options, 'match');
                    if ($value !== $this->stringValue($other)) {
                        $this->addErrorForRule($attribute, self::RULE_MATCH, ['match' => $this->getLabel($other)]);
                    }
                }

                if ($ruleName === self::RULE_UNIQUE && $this->existsInTable($attribute, $value, $options)) {
                    $this->addErrorForRule($attribute, self::RULE_UNIQUE, ['field' => $this->getLabel($attribute)]);
                }
            }
        }

        return $this->errors === [];
    }

    /**
     * @return array<string, string>
     */
    public function errorMessages(): array
    {
        return [
            self::RULE_REQUIRED => 'This field is required',
            self::RULE_EMAIL => 'This field must be a valid email address',
            self::RULE_MIN => 'Min length of this field must be {min}',
            self::RULE_MAX => 'Max length of this field must be {max}',
            self::RULE_MATCH => 'This field must be the same as {match}',
            self::RULE_UNIQUE => 'Record with this {field} already exists',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function labels(): array
    {
        return [];
    }

    public function getLabel(string $attribute): string
    {
        return $this->labels()[$attribute] ?? $attribute;
    }

    public function addError(string $attribute, string $message): void
    {
        $this->errors[$attribute][] = $message;
    }

    public function hasError(string $attribute): bool
    {
        return ($this->errors[$attribute] ?? []) !== [];
    }

    public function getFirstError(string $attribute): string
    {
        return $this->errors[$attribute][0] ?? '';
    }

    /**
     * The attribute's value as a string for validation and rendering; non-scalar values become ''.
     */
    public function stringValue(string $attribute): string
    {
        $value = $this->attributeValue($attribute);

        return is_scalar($value) ? (string) $value : '';
    }

    /**
     * The raw value of a property by name, null when the model has no such property.
     */
    public function attributeValue(string $attribute): mixed
    {
        return get_object_vars($this)[$attribute] ?? null;
    }

    /**
     * @param array<int|string, mixed> $params
     */
    private function addErrorForRule(string $attribute, string $rule, array $params = []): void
    {
        $message = $this->errorMessages()[$rule] ?? '';

        foreach ($params as $key => $value) {
            if (is_string($key) && is_scalar($value)) {
                $message = str_replace('{' . $key . '}', (string) $value, $message);
            }
        }

        $this->addError($attribute, $message);
    }

    /**
     * @param array<int|string, mixed> $options
     */
    private function existsInTable(string $attribute, string $value, array $options): bool
    {
        $className = $options['class'] ?? null;
        if (!is_string($className) || !is_subclass_of($className, Db\DbModel::class)) {
            throw new \InvalidArgumentException("Rule 'unique' on '$attribute' needs a 'class' that extends DbModel");
        }

        $uniqueAttribute = array_key_exists('attribute', $options) ? $this->stringOption($options, 'attribute') : $attribute;
        $tableName = (new $className())->tableName();
        $statement = Application::$app->db->prepare("SELECT * FROM $tableName WHERE $uniqueAttribute = :attr");
        $statement->bindValue(':attr', $value);
        $statement->execute();

        return $statement->fetch() !== false;
    }

    /**
     * @param array<int|string, mixed> $options
     */
    private function intOption(array $options, string $key): int
    {
        $value = $options[$key] ?? null;

        return is_numeric($value) ? (int) $value : 0;
    }

    /**
     * @param array<int|string, mixed> $options
     */
    private function stringOption(array $options, string $key): string
    {
        $value = $options[$key] ?? null;

        return is_string($value) ? $value : '';
    }

    /**
     * Values that cannot be converted to the property type (an array for a string, "abc" for an int) are skipped;
     * validation rules report the missing value.
     */
    private function assign(ReflectionProperty $property, mixed $value): void
    {
        $type = $property->getType();
        if ($type === null || ($type instanceof ReflectionNamedType && $type->getName() === 'mixed')) {
            $property->setValue($this, $value);

            return;
        }
        if (!$type instanceof ReflectionNamedType) {
            return;
        }
        if ($value === null) {
            if ($type->allowsNull()) {
                $property->setValue($this, null);
            }

            return;
        }
        if (!is_scalar($value)) {
            return;
        }

        $converted = match ($type->getName()) {
            'string' => (string) $value,
            'int' => is_numeric($value) ? (int) $value : null,
            'float' => is_numeric($value) ? (float) $value : null,
            'bool' => (bool) $value,
            default => null,
        };
        if ($converted !== null) {
            $property->setValue($this, $converted);
        }
    }
}
