<?php

namespace App\Rules;

use App\Support\System\Traits\WriteLogs;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class GoogleReCaptcha implements ValidationRule
{
    use WriteLogs;

    public function validate(
        string $attribute,
        mixed $value,
        Closure $fail
    ): void {
        // 

        if (isLocal()) {
            return;
        }

        if (empty($value)) {
            $fail(t('Google ReCaptcha challenge must be provided.'));
            return;
        }

        $result = Http::asForm()
            ->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => config('google_recaptcha.secret_key'),
                'response' => $value,
            ])->json();

        if (!@$result['success']) {
            // 
            $this->logDebug('Wrong challenge %s', $result);

            $fail(
                t('Google ReCaptcha challenge is invalid.')
            );
        } else {
            $this->logDebug('Google recaptcha passed successfully %s', $result);
        }
    }
}
