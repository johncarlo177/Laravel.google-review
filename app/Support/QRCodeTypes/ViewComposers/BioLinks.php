<?php

namespace App\Support\QRCodeTypes\ViewComposers;

use App\Support\QRCodeTypes\BioLinks as QRCodeTypeBioLinks;
use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\BaseBlock;
use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\BlockModel;
use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\BlocksManager;
use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\DynamicBlocks\Manager as DynamicBlocksManager;
use App\Support\System\Traits\WriteLogs;

class BioLinks extends Base
{
    use WriteLogs;

    private BlocksManager $blocksManager;

    public function __construct()
    {
        parent::__construct();

        $this->blocksManager = new BlocksManager();

        (new DynamicBlocksManager)->registerBlocks();
    }

    public static function type()
    {
        return QRCodeTypeBioLinks::slug();
    }

    protected function getTemplate()
    {
        return $this->qrcodeData('businessType', 'default');
    }

    protected function gradientBarckgroundStyles()
    {
        if ($this->designValue('backgroundType') != 'gradient') return;

        $gradient = $this->designValue('backgroundGradient');

        if (empty($gradient)) return;

        $gradient = @json_decode($gradient, true);

        $colors = @$gradient['colors'];
        $angle = @$gradient['angle'];
        $type = @$gradient['type'];

        $defaultColor = '#000';

        if (empty($colors)) {
            return;
        }

        if (empty($angle)) {
            $angle = '180';
        }

        if (empty($type)) {
            $type = 'LINEAR';
        }

        $colorsString = collect($colors)
            ->sort(function ($a, $b) {
                return $a['stop'] - $b['stop'];
            })
            ->map(
                fn($c) => ($c['color'] ?? $defaultColor) . ' ' . $c['stop'] . '%'
            )->join(", ");



        $selector = '.qrcode-type-biolinks .layout-generated-webpage .details-bg';


        if ($type === 'LINEAR') {
            return sprintf(
                "$selector { background-image: linear-gradient(%sdeg, %s); }",
                $angle,
                $colorsString
            );
        }

        return sprintf(
            "$selector { background-image: radial-gradient( %s); }",
            $colorsString
        );
    }

    protected function imageBackgroundStyles()
    {
        if ($this->designValue('backgroundType') != 'image') return;

        $url = $this->fileUrl('biolinksBackgroundImage');

        if (empty($url)) return;

        return sprintf(
            '%s { background-image: url(%s); }',
            $this->layoutSelector('.details-container .details-bg'),
            $url
        );
    }

    protected function backgroundType()
    {
        $type = $this->designValue('backgroundType');

        return empty($type) ? 'solid' : $type;
    }

    protected function solidColorBackgroundStyles()
    {
        if ($this->backgroundType() != 'solid') return;

        $color = $this->designValue('backgroundColor');

        if (empty($color)) $color = '#fff';

        return sprintf(
            '%s { background-color: %s; }',
            $this->layoutSelector('.details-container .details-bg'),
            $color
        );
    }

    protected function layoutSelector($subselector = '')
    {
        return 'body.qrcode-type-biolinks .layout-generated-webpage' . ' ' . $subselector;
    }

    private function filterBlockStack(
        BaseBlock $block,
        $stackItemId,
        $ignoreStackId
    ) {
        if ($this->designField('stack_enabled') !== 'enabled') {
            return true;
        }

        if ($ignoreStackId) return true;

        return $block->insideStack($stackItemId);
    }

    public function hasVideoBackground()
    {
        return $this->designField('backgroundType') === 'video' && !empty($this->fileUrl('biolinksBackgroundVideo'));
    }

    public function duplicateBlock($id)
    {
        $block = $this->getBlockByModelId($id);

        $copy = $block->duplicate();

        $this->addBlock($copy);

        return $copy;
    }

    protected function getBlockByModelId($id)
    {
        return $this->blocks()
            ->first(function (BaseBlock $block) use ($id) {
                return $block->getModel()->getId() === $id;
            });
    }

    protected function addBlock(BlockModel $model)
    {
        $blocks = collect($this->designValue('blocks', []));

        $blocks->add($model->toArray());

        $this->design->design = array_merge(
            $this->design->design,
            [
                'blocks' => $blocks->all()
            ]
        );

        $this->design->save();
    }

    public function blocks($stackItemId = null, $ignoreStackId = false)
    {
        $blocks = collect(
            $this->designValue('blocks', [])
        )->map(function ($data) {
            return $this->makeBlock($data);
        })
            ->filter() // remove empty items
            ->filter(
                fn(BaseBlock $block) => $this->filterBlockStack(
                    $block,
                    $stackItemId,
                    $ignoreStackId
                )
            )
            ->sort(
                function (BaseBlock $a, BaseBlock $b) {

                    $s1 = $a->getModel()->getSortOrder();

                    $s2 = $b->getModel()->getSortOrder();

                    return $s1 - $s2;
                }
            )
            ->values();


        return $blocks;
    }

    private function makeBlock($data)
    {
        $slug = @$data['slug'];

        if (!$slug) return;

        $block = $this->blocksManager->find($slug);

        if (!$block) return;

        return $block->withModel(new BlockModel($data));
    }

    public function blockStyleTags()
    {
        return $this->blocks(
            ignoreStackId: true
        )->map(function (BaseBlock $block) {
            return $block->styles();
        })->join("\n");
    }
}
