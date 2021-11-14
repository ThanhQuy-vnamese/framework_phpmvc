<?php

namespace App\Core\Form;


use App\Core\Model;

class Field
{
    public const TYPE_TEXT = 'text';
    public const TYPE_PASSWORD = 'password';
    public const TYPE_EMAIL = 'email';

    public Model $model;
    public string $attribute;
    public string $type;

    /**
     * Field constructor.
     *
     * @param  Model  $model
     * @param  string  $attribute
     */
    public function __construct(Model $model, string $attribute)
    {
        $this->model     = $model;
        $this->attribute = $attribute;
        $this->type      = self::TYPE_TEXT;
    }

    public function __toString()
    {
        return sprintf(
            '
               <div class="mb-3">
                    <label for="%s">%s</label>
                    <input type="%s"  name="%s" id="%s" value="%s" class="form-control%s" />
                    <div class="invalid-feedback">
                        %s
                    </div>
                </div>',
            $this->attribute,
            $this->model->getLabel($this->attribute),
            $this->type,
            $this->attribute,
            $this->attribute,
            $this->model->{$this->attribute},
            $this->model->hasError($this->attribute) ? ' is-invalid' : '',
            $this->model->getError($this->attribute),
        );
    }

    public function passwordField(): Field
    {
        $this->type = self::TYPE_PASSWORD;

        return $this;
    }

    public function emailField(): Field
    {
        $this->type = self::TYPE_EMAIL;

        return $this;
    }
}
