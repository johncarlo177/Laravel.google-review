<?php

namespace App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks;

use App\Models\File;
use App\Repositories\FileManager;
use App\Support\QRCodeTypes\ViewComposers\Base as BaseComposer;
use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\Style\CssRuleGenerator;
use App\Support\QRCodeTypes\ViewComposers\Traits\CombinesStylesMethods;
use App\Support\System\Traits\WriteLogs;

abstract class BaseBlock
{
    use WriteLogs;

    use CombinesStylesMethods;

    protected BlockModel $model;

    public static abstract function slug();

    protected abstract function shouldRender(): bool;

    protected function shouldRenderBlock()
    {
        return $this->isEnabled() && $this->shouldRender();
    }

    protected function isEnabled()
    {
        return $this->model->empty('is_enabled') || $this->model->equals('is_enabled', 'enabled');
    }

    public static function path(): string
    {
        return sprintf('qrcode.types.biolinks.%s-block', static::slug());
    }

    public function withModel(BlockModel $model)
    {
        $this->model = $model;

        return $this;
    }

    protected function getFileKeys()
    {
        return [];
    }

    protected function duplicateBlockFiles(BlockModel $newModel)
    {
        foreach ($this->getFileKeys() as $key) {
            // 
            $file = File::find($this->model->field($key));

            if (!$file) {
                continue;
            }

            $manager = new FileManager;

            $newFile = $manager->duplicate($file);

            $newModel->setField($key, $newFile->id);
        }
    }

    public function duplicate(): BlockModel
    {
        $data = $this->model->toArray();

        unset($data['id']);

        $newModel = new BlockModel($data);

        $newModel->setSortOrder($this->model->getSortOrder() + 1);

        $this->duplicateBlockFiles($newModel);

        return $newModel;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function render(BaseComposer $composer)
    {
        if (!$this->shouldRenderBlock()) return;

        return view($this::path(), [
            'model' => $this->model,
            'block' => $this,
            'composer' => $composer
        ]);
    }

    protected function blockSelector($selector = '')
    {
        if (!empty($selector)) {
            $selector = sprintf('#%s %s', $this->model->getId(), $selector);
        } else {
            $selector = '#' . $this->model->getId();
        }

        return sprintf('html body %s', $selector);
    }

    public function insideStack($stackId = null)
    {
        $currentStack = $this->model->field('stack');

        if ($currentStack === '*') {
            return empty($stackId);
        }

        return $currentStack === $stackId;
    }

    /**
     * @param string|array $selector if not provided it will select the block itself
     * @return CssRuleGenerator
     */
    protected function select($selector = '')
    {
        $selector = collect($selector)
            ->map(fn($s) => $this->blockSelector($s))
            ->join(', ');

        return CssRuleGenerator::withSelector(
            $selector
        )
            ->withModel($this->model);
    }
}
