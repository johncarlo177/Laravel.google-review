<?php

namespace App\Support\Sms\Drivers;

use App\Support\System\Traits\ClassListLoader;

class Manager
{
    use ClassListLoader;

    private function getDir()
    {
        return __DIR__;
    }

    private function getNamespace()
    {
        return __NAMESPACE__;
    }

    public function list()
    {
        return collect($this->makeInstancesOfInstantiableClassesInCurrentDirectory(class_basename(static::class)));
    }

    public function activate($slug)
    {
        return $this->find($slug)?->enable();
    }

    public function disable($slug)
    {
        return $this->find($slug)?->disable();
    }

    public function find($slug): ?BaseDriver
    {
        return $this->list()->first(fn ($s) => $s->slug() == $slug);
    }

    public function enabledDriver(): ?BaseDriver
    {
        return $this->list()->first(fn ($s) => $s->isEnabled());
    }
}
