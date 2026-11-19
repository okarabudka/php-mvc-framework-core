<?php

declare(strict_types=1);

namespace Okarabudka\PhpMvcCore\Form;

class TextareaField extends BaseField
{
    public function renderInput(): string
    {
        return sprintf(
            '<textarea name="%s" class="form-control%s">%s</textarea>',
            Form::escape($this->attribute),
            $this->invalidClass(),
            $this->value(),
        );
    }
}
