<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

class UrlRule implements ValidationRule
{
    private $value;

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value) {
            return;
        }

        $this->value = $value;

        $this->addDefaultProtocolIfNeeded();

        $valid = $this->executeDefaultUrlValidationIfNeeded();

        if (!$valid) {
            $fail(t('Must be a vaild URL.'));
        }
    }

    public function parse()
    {
        if (empty($this->value)) {
            return '';
        }

        $this->addDefaultProtocolIfNeeded();

        return $this->value;
    }

    public static function forValue($value)
    {
        $instance = new static;

        $instance->value = $value;

        return $instance;
    }

    private function allowedProtocols()
    {
        return [
            'http',
            'https',
            'ftp',
            'sms',
            'tel',
            'mailto'
        ];
    }

    private function isProtocolAllowed($protocol)
    {
        return array_search($protocol, $this->allowedProtocols()) !== false;
    }

    private function defaultProtocol()
    {
        return 'http';
    }

    private function addDefaultProtocolIfNeeded()
    {
        if (empty($this->value)) {
            return;
        }

        $protocol = $this->getProtocol();

        if (!$this->isProtocolAllowed($protocol)) {
            $this->value = sprintf(
                '%s://%s',
                $this->defaultProtocol(),
                $this->value
            );
        }
    }

    private function getProtocol()
    {
        preg_match('/(.*?):/', $this->value, $matches);

        $protocol = @$matches[1];

        return $protocol;
    }

    private function executeDefaultUrlValidationIfNeeded()
    {
        $shouldExecuteDefaultValidation = array_search(
            $this->getProtocol(),
            ['https', 'http']
        ) !== false;

        if (!$shouldExecuteDefaultValidation) {
            return true;
        }

        $validator = Validator::make([
            'url' => $this->value
        ], [
            'url' => 'url'
        ]);

        return $validator->passes();
    }
}
