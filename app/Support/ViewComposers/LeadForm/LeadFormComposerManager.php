<?php

namespace App\Support\ViewComposers\LeadForm;

use App\Support\System\Traits\ClassListLoader;
use App\Support\ViewComposers\ViewComposerManager;

class LeadFormComposerManager extends ViewComposerManager
{
    use ClassListLoader;

    protected function buildClassList()
    {
        return [
            ...$this->makeInstantiableListOfClasses(__DIR__),
            ...$this->makeInstantiableListOfClasses(directory: __DIR__ . '/Answers', excludePattern: 'BaseAnswer')
        ];
    }
}
