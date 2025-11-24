<?php

namespace App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\DynamicBlocks;

use App\Models\DynamicBioLinkBlock;
use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\BlocksManager;

class Manager
{
    public function registerBlocks()
    {
        $blocks = DynamicBioLinkBlock::get();

        $blocks->map(
            function ($block) {
                return $this->generateClass($block);
            }
        )->each(
            fn($class) => BlocksManager::registerBlock($class)
        );
    }

    private function generateClass(DynamicBioLinkBlock $block)
    {
        $className = sprintf('DynamicBlock%s', $block->id);

        $fullName = sprintf('%s\\%s', __NAMESPACE__, $className);

        if (file_exists(__DIR__ . '/' . $className . '.php')) {
            return $fullName;
        }

        $stub = file_get_contents(__DIR__ . '/DynamicBlockStub.stub');

        $stub = str_replace('__id__', $block->id, $stub);

        file_put_contents(__DIR__ . '/' . $className . '.php', $stub);

        return $fullName;
    }
}
