<?php

namespace App\Console\Commands;

use App\Models\QRCode;
use App\Support\CompatibleSVG\CompatibleSVGManager;
use App\Support\CompatibleSVG\Processors\Shapes\OutlinedShape;
use App\Support\EditorsCompatibleSVG\Expirement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SVGExpirement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test-svg';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dummy command to build svg files';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $qrcode = QRCode::find(130001);

        foreach (OutlinedShape::getShapes() as $shape) {
            $design = $qrcode->design;

            $design->shape = $shape;

            $qrcode->design = $design;

            $qrcode->save();

            $manager = new CompatibleSVGManager($qrcode);

            $svgString = $manager->render();

            Storage::put(
                sprintf(
                    'compatible-svg/%s.svg',
                    $qrcode->design->shape
                ),
                $svgString
            );
        }



        $this->info('Done. ...');

        return 0;
    }
}
