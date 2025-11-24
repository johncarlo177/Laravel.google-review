<?php

namespace App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks;

use App\Support\System\Traits\ClassListLoader;
use Illuminate\Support\Facades\Log;

class BlocksManager
{
    use ClassListLoader;

    private static $classes = [];

    public static function registerBlock($class)
    {
        static::$classes[] = $class;
    }

    public function find($slug): ?BaseBlock
    {
        return $this->blocks()
            ->first(fn (BaseBlock $block) => $block::slug() === $slug);
    }

    public function blocks()
    {
        $blocks = collect(
            array_merge(
                $this->makeInstances(directory: __DIR__, excludePattern: 'BlockModel'),
                $this->registeredInstances()
            )
        );

        return $blocks;
    }

    private function registeredInstances()
    {
        return array_map(function ($class) {
            return new $class;
        }, $this::$classes);
    }
}
