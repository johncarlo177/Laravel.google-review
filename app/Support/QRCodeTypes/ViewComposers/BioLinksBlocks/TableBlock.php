<?php

namespace App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks;

use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\Style\CssRuleGenerator;

class TableBlock extends BaseBlock
{
    public static function slug()
    {
        return 'table';
    }

    protected function shouldRender(): bool
    {
        return $this->model->notEmpty('table_data');
    }

    public function cellStyles()
    {
        return CssRuleGenerator::withSelector(
            $this->blockSelector('table td')
        )
            ->withModel($this->model)
            ->rule('color', 'text_color')
            ->generate();
    }

    public function table_data()
    {
        $rows = explode("\n", $this->model->field('table_data'));

        return collect($rows)
            ->filter()
            ->map(function ($row) {
                return explode(",", $row);
            });
    }
}
