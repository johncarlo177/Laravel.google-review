<?php

namespace Tests\Feature;

use App\Models\Config;
use App\Rules\AppPassword;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * @group tested
 */
class AppPasswordRuleTest extends TestCase
{
    private $minLength;

    public function test_min_length()
    {
        $this->minLength = 10;

        Config::set('security.password_min_length', $this->minLength);

        Config::set('security.password_characters', []);

        $validator = Validator::make([
            'password' => 'test'
        ], [
            'password' => [
                'required',
                new AppPassword
            ]
        ]);

        $this->assertThrows(
            function () use ($validator) {
                $validator->validate();
            },
            ValidationException::class,
            sprintf(
                '%s %s %s',
                t('Password must be at least'),
                $this->minLength,
                t(' characthers.')
            )
        );

        $validator = Validator::make([
            'password' => implode('', array_fill(0, $this->minLength, '1')),
        ], [
            'password' => [
                'required',
                new AppPassword
            ]
        ]);

        $validator->validate(); // should pass
    }

    public function test_uppercase()
    {
        Config::set('security.password_min_length', 3);

        $record = Config::set('security.password_characters', [
            'UPPER_CASE'
        ]);

        $validator = Validator::make([
            'password' => 'test'
        ], [
            'password' => [
                'required',
                new AppPassword
            ]
        ]);

        $this->assertThrows(
            function () use ($validator) {
                $validator->validate();
            },
            ValidationException::class,
            t('Password must contain upper case letter')
        );

        $validator = Validator::make([
            'password' => 'Test',
        ], [
            'password' => [
                'required',
                new AppPassword
            ]
        ]);

        $validator->validate(); // should pass
    }

    public function test_lowercase()
    {
        Config::set('security.password_min_length', 3);

        $record = Config::set('security.password_characters', [
            'LOWER_CASE'
        ]);

        $validator = Validator::make([
            'password' => 'TEST'
        ], [
            'password' => [
                'required',
                new AppPassword
            ]
        ]);

        $this->assertThrows(
            function () use ($validator) {
                $validator->validate();
            },
            ValidationException::class,
            t('Password must contain lower case letter')
        );

        $validator = Validator::make([
            'password' => 'Test',
        ], [
            'password' => [
                'required',
                new AppPassword
            ]
        ]);

        $validator->validate(); // should pass
    }

    public function test_number()
    {
        Config::set('security.password_min_length', 3);

        $record = Config::set('security.password_characters', [
            'NUMBER'
        ]);

        $validator = Validator::make([
            'password' => 'TEST'
        ], [
            'password' => [
                'required',
                new AppPassword
            ]
        ]);

        $this->assertThrows(
            function () use ($validator) {
                $validator->validate();
            },
            ValidationException::class,
            t('Password must contain a number')
        );

        $validator = Validator::make([
            'password' => 'Test123',
        ], [
            'password' => [
                'required',
                new AppPassword
            ]
        ]);

        $validator->validate(); // should pass
    }

    public function test_special_character()
    {
        Config::set('security.password_min_length', 3);

        Config::set('security.password_characters', [
            'SPECIAL_CHARACTER'
        ]);

        $validator = Validator::make([
            'password' => 'TEST'
        ], [
            'password' => [
                'required',
                new AppPassword
            ]
        ]);

        $this->assertThrows(
            function () use ($validator) {
                $validator->validate();
            },
            ValidationException::class,
            sprintf(
                '%s %s',
                t('special character (any of'),
                '~!@#$%^&*()_+{}[].<>?/|\\"\')'
            )
        );

        $validator = Validator::make([
            'password' => 'Test123@',
        ], [
            'password' => [
                'required',
                new AppPassword
            ]
        ]);

        $validator->validate(); // should pass
    }

    public function test_multiple_character_types()
    {
        Config::set('security.password_min_length', 3);

        Config::set('security.password_characters', [
            'NUMBER',
            'SPECIAL_CHARACTER'
        ]);

        $validator = Validator::make([
            'password' => 'TEST'
        ], [
            'password' => [
                'required',
                new AppPassword
            ]
        ]);

        $this->assertThrows(
            function () use ($validator) {
                $validator->validate();
            },
            ValidationException::class,
            sprintf(
                '%s %s %s',
                t('a number,'),
                t('special character (any of'),
                '~!@#$%^&*()_+{}[].<>?/|\\"\')'
            )
        );

        Config::set('security.password_characters', [
            'NUMBER',
            'SPECIAL_CHARACTER',
            'UPPER_CASE'
        ]);

        $validator = Validator::make([
            'password' => 'test',
        ], [
            'password' => [
                'required',
                new AppPassword
            ]
        ]);

        $this->assertThrows(
            function () use ($validator) {
                $validator->validate();
            },
            ValidationException::class,
            'Password must contain a number, special character (any of ~!@#$%^&*()_+{}[].<>?/|\"\'), upper case letter'
        );
    }
}
