<?php

namespace App\Support\ViewComposers\SocialLinksComposer;

use App\Support\System\ClassFinder;

class Manager
{
    private $url = null;

    public static function withUrl($url)
    {
        $instance = new static;

        $instance->url = $url;

        return $instance;
    }

    public function resolve()
    {
        return $this->list()
            ->reduce(
                function ($result, DefaultResolver $resolver) {
                    if ($result) {
                        return $result;
                    }

                    return $resolver->resolve($this->url);
                },
                null
            );
    }

    private function list()
    {
        return ClassFinder::in(__DIR__)
            ->subClassesOf(DefaultResolver::class)
            ->find()
            ->push(new DefaultResolver)
            ->sort(function (DefaultResolver $r1, DefaultResolver $r2) {
                return $r2->priority() - $r1->priority();
            });
    }
}
