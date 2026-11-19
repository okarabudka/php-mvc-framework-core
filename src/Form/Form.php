<?php

declare(strict_types=1);

namespace Okarabudka\PhpMvcCore\Form;

use Okarabudka\PhpMvcCore\Model;

class Form
{
    public static function begin(string $action, string $method): self
    {
        echo sprintf('<form action="%s" method="%s">', self::escape($action), self::escape($method));

        return new self();
    }

    public static function end(): string
    {
        return '</form>';
    }

    public function field(Model $model, string $attribute): InputField
    {
        return new InputField($model, $attribute);
    }

    public function textareaField(Model $model, string $attribute): TextareaField
    {
        return new TextareaField($model, $attribute);
    }

    public static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
