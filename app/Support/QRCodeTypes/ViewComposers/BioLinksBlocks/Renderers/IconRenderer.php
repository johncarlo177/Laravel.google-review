<?php

namespace App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\Renderers;

use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\BlockModel;

class IconRenderer extends BaseRenderer
{
    protected $icon = null;

    protected $customIconId = null;

    public static function withBlockModel(BlockModel $model, $iconKey = 'icon', $customIconKey = 'icon_file')
    {
        return parent::withModel($model)
            ->withIcon($model->field($iconKey))
            ->withCustomIconId(
                $model->field($customIconKey)
            );
    }

    public function withIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    public function withCustomIconId($id)
    {
        $this->customIconId = $id;

        return $this;
    }

    public function render()
    {
        if ($icon = $this->renderSvgIcon()) {
            return $icon;
        }

        $url = file_url(
            $this->customIconId
        );

        if (!$url) {
            return;
        }

        return sprintf(
            '<img class="icon" src="%s" />',
            $url
        );
    }

    protected function renderSvgIcon()
    {
        $icon = $this->icon;

        if (empty($icon)) return;

        $iconsFolder = 'resources/views/blue/components/icons/';

        $iconPath = base_path($iconsFolder . $icon . '.blade.php');

        if (!file_exists($iconPath)) return;

        $viewPath = 'blue.components.icons.' . $icon;

        return view($viewPath)->render();
    }
}
