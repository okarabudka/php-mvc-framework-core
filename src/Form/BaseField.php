<?php

declare(strict_types=1);

namespace Okarabudka\PhpMvcCore\Form;

use Okarabudka\PhpMvcCore\Model;
use Stringable;

/**
 * A Bootstrap form group: label, input and the first validation error. All output is HTML-escaped.
 */
abstract class BaseField implements Stringable
{
    public function __construct(
        public Model $model,
        public string $attribute,
    ) {}

    public function __toString(): string
    {
        return sprintf(
            '
            <div class="mb-3">
                <label class="form-label">%s</label>
                %s
                <div class="invalid-feedback">
                    %s
                </div>
            </div>',
            Form::escape($this->model->getLabel($this->attribute)),
            $this->renderInput(),
            Form::escape($this->model->getFirstError($this->attribute)),
        );
    }

    abstract public function renderInput(): string;

    protected function value(): string
    {
        return Form::escape($this->model->stringValue($this->attribute));
    }

    protected function invalidClass(): string
    {
        return $this->model->hasError($this->attribute) ? ' is-invalid' : '';
    }
}
