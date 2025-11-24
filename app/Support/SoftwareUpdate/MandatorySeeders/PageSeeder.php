<?php

namespace App\Support\SoftwareUpdate\MandatorySeeders;

use App\Models\Page;
use App\Models\Config;
use App\Models\QRCode;
use App\Support\StringHelper;
use Illuminate\Support\Str;

class PageSeeder extends Seeder
{
    protected $rawFile = 'pages';

    protected $version = 'v2.105';

    protected function shouldInsertRow(array $row)
    {
        return empty(Page::whereSlug($row['slug'])->first());
    }

    protected function newModel($row)
    {
        $page = Page::whereSlug($row['slug'])->first();

        if ($page) {
            return $page;
        }

        return new Page();
    }

    protected function forceFill($model, array $data, array $row)
    {
        if ($model->id) {
            // Only update the content if the model is found.
            $model->html_content = $data['html_content'];
            return;
        }

        $model->forceFill(array_merge($row, [
            'id' => null
        ]));
    }

    protected function createQRCodeTypesMenues()
    {
        collect(QRCode::getTypes())->each(function ($type) {
            $title = StringHelper::title($type) . ' QR Code Generator';
            $slug = Str::slug($title);

            if (!Config::get('qrType.' . $type . '.url')) {
                Config::set('qrType.' . $type . '.url', $slug);
            }
        });
    }

    protected function afterRun()
    {
        $this->createQRCodeTypesMenues();
    }
}
