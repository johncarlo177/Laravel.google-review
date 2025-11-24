<?php

namespace App\Interfaces;

interface EnvSaver
{
    public function save(string $key, ?string $value);

    public function saveMany(array $variables);

    public function load(array $keys);

    public function rollback();
}
