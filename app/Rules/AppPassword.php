<?php

namespace App\Rules;

use App\Models\Config;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Support\Collection;

class AppPassword implements InvokableRule
{
    private int $minLength;

    private Collection $requiredCharacters;

    private array $characterTypePatterns;

    private array $characterTypeMessages;

    public function __construct()
    {
        $this->minLength = Config::get('security.password_min_length') ?? 6;

        $requiredCharacters = Config::get('security.password_characters') ?? [];

        $this->requiredCharacters = collect($requiredCharacters);

        $this->characterTypePatterns = [
            'UPPER_CASE' => '/[A-Z]/',
            'LOWER_CASE' => '/[a-z]/',
            'NUMBER' => '/\d/',
            'SPECIAL_CHARACTER' => '/["\'{}\[\]*\^&()~$+%<>#@\-_\.\/\\?!|]/'
        ];

        $this->characterTypeMessages = [
            'UPPER_CASE' => t('upper case letter'),
            'LOWER_CASE' => t('lower case letter'),
            'NUMBER' => t('a number'),
            'SPECIAL_CHARACTER' => sprintf('%s %s', t('special character (any of'), '~!@#$%^&*()_+{}[].<>?/|\\"\')')
        ];
    }

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail)
    {
        if (strlen($value) < $this->minLength) {
            $fail(sprintf(
                '%s %s %s',
                t('Password must be at least'),
                $this->minLength,
                t('characthers.')
            ));
        }

        $failedTypes = [];

        $this->requiredCharacters->each(function ($characterType) use ($value, &$failedTypes) {
            if (
                !preg_match($this->characterTypePatterns[$characterType], $value)
            ) {
                $failedTypes[] = $characterType;
            }
        });

        if (empty($failedTypes)) return;

        $message = t('Password must contain ') . collect($failedTypes)->map(function ($type) {
            return $this->characterTypeMessages[$type];
        })->join(', ') . '.';

        $fail($message);
    }
}
