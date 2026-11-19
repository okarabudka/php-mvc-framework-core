<?php

declare(strict_types=1);

namespace Okarabudka\PhpMvcCore\Form;

use Okarabudka\PhpMvcCore\Model;

class InputField extends BaseField
{
    public const string TYPE_TEXT = 'text';
    public const string TYPE_PASSWORD = 'password';
    public const string TYPE_NUMBER = 'number';

    public string $type = self::TYPE_TEXT;

    public function __construct(Model $model, string $attribute)
    {
        parent::__construct($model, $attribute);
    }

    public function passwordField(): static
    {
        $this->type = self::TYPE_PASSWORD;

        return $this;
    }

    public function renderInput(): string
    {
        return sprintf(
            '<input type="%s" name="%s" value="%s" class="form-control%s">',
            $this->type,
            Form::escape($this->attribute),
            $this->value(),
            $this->invalidClass(),
        );
    }
}
