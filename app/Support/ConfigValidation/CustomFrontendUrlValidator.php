<?php

namespace App\Support\ConfigValidation;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CustomFrontendUrlValidator extends BaseValidator
{
    public function getKey()
    {
        return 'app.frontend_custom_url';
    }

    protected function doValidate()
    {
        if (empty($this->value)) return;

        $isValidUrl = $this->checkIfValidUrl();

        if (!$isValidUrl) {
            $this->errors[] = t('Must be a valid URL');
            return;
        }

        if ($this->customFrontEndUrlEqualsCurrentHost()) {
            $this->errors[] = t('Should point to a page other than the home page of the script, or can be left empty.');
        }
    }

    private function customFrontEndUrlEqualsCurrentHost()
    {
        $valueUrlParts = parse_url($this->value);

        $baseUrlParts = parse_url(url('/'));

        $valueHost = str_replace('www', '', $valueUrlParts['host']);

        $baseHost = str_replace('www', '', $baseUrlParts['host']);

        return $valueHost == $baseHost;
    }

    private function checkIfValidUrl()
    {
        $validator = Validator::make([
            'url' => $this->value
        ], [
            'url' => 'url'
        ]);

        try {
            $validator->validate();
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }
}
