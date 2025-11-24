<?php

namespace App\Support\ConfigValidation;

use Exception;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

abstract class BaseValidator
{
    protected $value;

    protected $errors = [];

    public function validate($value)
    {
        $this->value = $value;

        $this->doValidate();

        if (empty($this->errors)) return;

        $validator = Validator::make([], []);

        $validator->after(function () use ($validator) {
            foreach ($this->errors as $error) {
                $validator->errors()->add($this->getKey(), $error);
            }
        });

        $validator->validate();
    }

    protected abstract function doValidate();

    public abstract function getKey();
}
