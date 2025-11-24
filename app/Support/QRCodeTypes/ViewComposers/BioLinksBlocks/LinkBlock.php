<?php

namespace App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks;

use App\Rules\UrlRule;
use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\Renderers\IconRenderer;
use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\Style\CssGradientGenerator;
use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\Style\CssRuleGenerator;
use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\Style\TextFontStyle;
use App\Support\System\Traits\WriteLogs;

class LinkBlock extends BaseBlock
{
    use WriteLogs;

    public static function slug()
    {
        return 'link';
    }

    protected function shouldRender(): bool
    {
        return $this->model->notEmpty('text');
    }

    protected function getFileKeys()
    {
        return [
            'icon_file',
            'background_image',
        ];
    }

    protected function getBorderColor()
    {
        $type = $this->model->field('border_type');

        if ($type === 'no-border') {
            return $this->model->field('backgroundColor');
        }

        if ($type === 'gradient') {
            return 'transparent';
        }

        return $this->model->field('border_color');
    }

    protected function getBorderStyle()
    {
        $type = $this->model->field('border_type');

        if ($type === 'no-border' || $type === 'gradient') {
            return 'none';
        }

        return $this->model->field('border_type');
    }

    protected function getLinkBackgroundImage()
    {
        $type = $this->model->field('background_type');

        switch ($type) {

            case 'image':
                return sprintf(
                    'url(%s)',
                    $this->model->fileUrl('background_image')
                );

            case 'gradient':
                return CssGradientGenerator::withValue(
                    $this->model->field('background_gradient')
                )
                    ->getCssValue();

            default:
                return 'none';
        }
    }

    protected function getLinkBackgroundColor()
    {
        if ($this->model->field('background_type') === 'no-background') {
            return 'transparent';
        }

        return $this->model->field('backgroundColor');
    }

    protected function linkHoverStyles()
    {
        $generated = CssRuleGenerator::withSelector(
            $this->linkSelector(':hover')
        )
            ->withModel($this->model)
            ->rule('background-color', fn() => $this->getLinkBackgroundColor())
            ->generate();

        return $generated;
    }

    protected function getBlockBackgroundImage()
    {
        $borderType = $this->model->field('border_type');

        if ($borderType !== 'gradient') return 'none';

        return CssGradientGenerator::withValue(
            $this->model->field('border_gradient')
        )->getCssValue();
    }


    protected function linkStyles()
    {
        return CssRuleGenerator::withSelector(
            $this->linkSelector()
        )
            ->withModel($this->model)
            ->rule('background-color', fn() => $this->getLinkBackgroundColor())
            ->rule('border-color', fn() => $this->getBorderColor())
            ->rule('border-width', 'border_width', 'px')
            ->rule('border-style', fn() => $this->getBorderStyle())
            ->rule('background-image', fn() => $this->getLinkBackgroundImage())
            ->rule('color', 'textColor')
            ->rule('padding', 'padding', 'rem')
            ->rule('border-radius', 'borderRadius', 'rem')
            ->rule('--icon-color', 'icon-color')
            ->rule('--icon-size', 'icon-size', 'rem')
            ->generate();
    }


    protected function getBlockPadding()
    {
        if ($this->model->field('border_type') !== 'gradient') {
            return '0';
        }

        return $this->model->field('border_width');
    }

    protected function blockStyles()
    {
        return CssRuleGenerator::withSelector(
            $this->blockSelector()
        )
            ->withModel($this->model)
            ->rule('background-image', fn() => $this->getBlockBackgroundImage())
            ->rule('padding', fn() => $this->getBlockPadding(), 'px')
            ->rule('border-radius', 'borderRadius', 'rem')
            ->rule('margin-top', 'margin-top', 'rem')
            ->rule('margin-bottom', 'margin-bottom', 'rem')
            ->rule('width', 'width', '%')
            ->generate();
    }

    protected function fontStyles()
    {
        return TextFontStyle::withSelector($this->linkSelector())
            ->withModel($this->model)
            ->generate();
    }

    private function linkSelector($selector = '')
    {
        return sprintf('%s a%s', $this->blockSelector(), $selector);
    }

    public function url()
    {
        $url = $this->model->field('url', '#');

        if ($url == '#') return $url;

        return UrlRule::forValue($url)->parse();
    }

    public function linkTarget()
    {
        if ($this->model->empty('target')) return;

        if ($this->model->equals('target', 'self')) return;

        return 'target="_blank"';
    }

    protected function getContentText()
    {
        return $this->model->field('text');
    }

    public function html()
    {
        $t = $this->getContentText();

        $lines = explode("\n", $t);

        return collect($lines)
            ->map(fn($line) => htmlentities($line))
            ->join('<br>');
    }

    public function icon()
    {
        return IconRenderer::withBlockModel($this->model)->render();
    }
}
