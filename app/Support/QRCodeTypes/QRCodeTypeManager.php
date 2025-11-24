<?php

namespace App\Support\QRCodeTypes;

use App\Plugins\PluginManager;
use App\Support\System\Traits\ClassListLoader;
use Illuminate\Support\Collection;
use Throwable;

class QRCodeTypeManager
{
    use ClassListLoader;

    private static $filters = [];

    public static function registerFilter($fl)
    {
        static::$filters[] = $fl;
    }

    private function getDir()
    {
        return __DIR__;
    }

    private function getNamespace()
    {
        return __NAMESPACE__;
    }

    public function find($slug): ?BaseType
    {
        $type = $this->types()->first(fn($t) => $t->slug() == $slug);

        if (!$type) return new Text();

        return $type;
    }

    public function isDynamic($slug)
    {
        return !empty($this->dynamicSlugs()->first(fn($t) => $t == $slug));
    }

    public function dynamicSlugs()
    {
        return $this->types()->filter(fn($t) => $t->isDynamic())->map(fn($t) => $t->slug())->values();
    }

    public function slugs()
    {
        return $this->types()->map(fn($t) => $t->slug())->values();
    }

    public function types(): Collection
    {
        $types = $this->getClasses()->map(fn($c) => new $c);

        foreach ($this::$filters as $filter) {
            $types = $types->filter($filter);
        }

        $types = PluginManager::doFilter(
            PluginManager::FILTER_QRCODE_TYPES_INSTANCES,
            $types
        );

        $types = $this->sortTypes($types);

        return $types;
    }

    private function sortTypes($types)
    {
        return $types->sort(function (BaseType $t1, BaseType $t2) {
            $s1 = is_numeric($t1->sortOrder()) ? $t1->sortOrder() : 200;
            $s2 = is_numeric($t2->sortOrder()) ? $t2->sortOrder() : 200;
            return $s1 - $s2;
        })->values();
    }

    private function getClasses()
    {
        $classes = $this->makeInstantiableListsOfClassesInCurrentDirectory(
            $this->exludes()
        );

        return collect($classes);
    }

    private function exludes()
    {
        return class_basename(static::class);
    }

    public static function renderQrTypesSortOrder()
    {
        try {
            $instance = new static;

            $types = $instance->types()->map(function (BaseType $type) {
                return [
                    'slug' => $type::slug(),
                    'sort_order' => $type->sortOrder()
                ];
            });

            return sprintf('<script> window.QRCG_QR_TYPES_SORT_ORDER = %s; </script>', json_encode($types));
        } catch (Throwable $ex) {
        }

        return '';
    }
}
