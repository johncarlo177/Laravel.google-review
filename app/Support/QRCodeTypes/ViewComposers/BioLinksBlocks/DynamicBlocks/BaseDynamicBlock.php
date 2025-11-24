<?php

namespace App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\DynamicBlocks;

use App\Interfaces\FileManager;
use App\Models\DynamicBioLinkBlock;
use App\Models\File;
use App\Plugins\PluginManager;
use App\Support\QRCodeTypes\ViewComposers\Base as BaseQRCodeComposer;
use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\BaseBlock;
use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\Style\CssRuleGenerator;
use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\Style\TextFontStyle;

abstract class BaseDynamicBlock extends BaseBlock
{
    private FileManager $files;

    public abstract static function id();

    public function __construct()
    {
        $this->files = app(FileManager::class);
    }

    public static function dynamicBlock(): ?DynamicBioLinkBlock
    {
        return DynamicBioLinkBlock::find(static::id());
    }

    public static function slug()
    {
        return sprintf('dynamic-%s', static::id());
    }

    public static function name()
    {
        return static::dynamicBlock()->name;
    }

    public static function path(): string
    {
        return sprintf('qrcode.types.biolinks.dynamic-block');
    }

    public function blockStyles()
    {
        return CssRuleGenerator::withSelector($this->blockSelector('.inner-block'))
            ->withModel($this->model)
            ->rule('background-color', 'background_color')
            ->rule('color', 'text_color')
            ->generate();
    }

    public function fieldValueStyles()
    {
        return CssRuleGenerator::withSelector($this->blockSelector('.inner-block .field-value'))
            ->withModel($this->model)
            ->rule('color', 'text_color')
            ->generate();
    }

    public function textStyles()
    {
        return collect(['.inner-block .field-value', '.inner-block .field-name'])
            ->map(function ($selector) {
                return TextFontStyle::withSelector(
                    $this->blockSelector($selector)
                )
                    ->withModel($this->model)
                    ->generate();
            })->join(' ');
    }

    public function fields()
    {
        $fields = $this->dynamicBlock()->fields;

        if (empty($fields)) return collect([]);

        return collect($fields)->sort(function ($a, $b) {
            return $this->fieldSortOrder($a) - $this->fieldSortOrder($b);
        });
    }

    public function icon($field)
    {
        if (!@$field['icon_id']) {
            return;
        }

        $file = File::find($field['icon_id']);

        if (!$file) return;

        return $this->files->url($file);
    }

    public function fileUrl($field)
    {
        $fileId = $this->value($field);

        $file = File::find($fileId);

        if (!$file) return;

        return $this->files->url($file);
    }

    public function value($field)
    {
        $value = $this->model->field(@$field['name']);

        return $value;
    }

    public function shouldRenderField(
        $field,
        BaseQRCodeComposer $composer
    ) {

        $qrcode = $composer->getQRCode();

        $shouldRender = !empty($this->value($field));

        $shouldRender = PluginManager::doFilter(
            PluginManager::FILTER_DYNAMIC_BIOLINK_SHOULD_RENDER_FIELD,
            $shouldRender,
            $field,
            static::dynamicBlock(),
            $qrcode
        );

        return $shouldRender;
    }

    public function type($field)
    {
        return @$field['type'] ?? 'text';
    }

    public function customCode()
    {
        return $this::dynamicBlock()->custom_code;
    }

    private function fieldSortOrder($field)
    {
        $s = @$field['sort_order'];

        if ($s === null) {
            return 100;
        }

        return $s;
    }

    protected function shouldRender(): bool
    {
        return !empty($this->dynamicBlock());
    }
}
