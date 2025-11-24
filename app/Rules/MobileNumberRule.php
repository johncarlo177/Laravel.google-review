<?php

namespace App\Rules;

use App\Support\MobileNumberManager;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Contracts\Validation\Rule;

class MobileNumberRule implements Rule
{
    use WriteLogs;

    private MobileNumberManager $mobileNumberManager;

    private $isEmpty = false;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->mobileNumberManager = new MobileNumberManager;
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
        if (!is_array($value)) {
            $this->logDebug('mobile number is empty %s', $value);
            return false;
        }

        $mobileNumber = @$value['mobile_number'];

        $this->isEmpty = empty($mobileNumber);

        $isoCode = @$value['iso_code'];

        $callingCode = $this->mobileNumberManager->callingCodeByIsoCode($isoCode);

        $this->logDebug(
            'iso code = %s, calling code = %s, mobile number = %s',
            $isoCode,
            $callingCode,
            $mobileNumber
        );

        return !empty($callingCode) && strlen($mobileNumber) > 5;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if ($this->isEmpty) {
            return t('The mobile number is required.');
        }

        return t('The mobile number is invalid.');
    }
}
