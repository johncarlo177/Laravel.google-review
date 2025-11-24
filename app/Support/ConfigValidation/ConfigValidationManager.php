<?php

namespace App\Support\ConfigValidation;

use App\Listeners\OnConfigWillChange;
use App\Models\Config;
use App\Support\System\Traits\ClassListLoader;

class ConfigValidationManager
{
    use ClassListLoader;

    private static $validators = [];

    private function getDir()
    {
        return __DIR__;
    }

    private function getNamespace()
    {
        return __NAMESPACE__;
    }

    public static function boot()
    {
        $instance = new static;

        static::$validators =  $instance->makeInstancesOfInstantiableClassesInCurrentDirectory();

        OnConfigWillChange::listen([$instance, 'onConfigWillChange']);
    }

    public function onConfigWillChange($key, $value)
    {
        $validators = $this->getValidatorsOfKey($key);

        foreach ($validators as $validator) {
            $validator->validate($value);
        }
    }

    private function getValidatorsOfKey($key)
    {
        return array_values(
            array_filter(
                static::$validators,
                fn ($validator) => $validator->getKey() == $key
            )
        );
    }
}
