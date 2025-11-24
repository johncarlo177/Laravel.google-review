<?php

namespace App\Rules;

use App\Interfaces\CaptchaManager;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Validator;

class Captcha implements Rule, ValidatorAwareRule
{
    protected Validator $validator;

    protected CaptchaManager $captcha;

    private $minLength = 4, $maxLength = 5;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(CaptchaManager $captcha)
    {
        $this->captcha = $captcha;
    }


    public static function rule()
    {
        return app(static::class);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $data = $value;

        $session_key = @$data['session_key'];

        $code = @$data['code'];

        $validator = ValidatorFacade::make([
            'code'          => $code,
            'session_key'   => $session_key
        ], [
            'code'          => 'required|min:' . $this->minLength . '|max:' . $this->maxLength,
            'session_key'   => 'required',
        ]);

        if ($validator->fails()) {
            return false;
        }

        return $this->captcha->validate($code, $session_key);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return t('Captcha code is invalid.');
    }

    /**
     * Set the current validator.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return $this
     */
    public function setValidator($validator)
    {
        $this->validator = $validator;

        return $this;
    }
}
