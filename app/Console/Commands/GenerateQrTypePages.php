<?php

namespace App\Console\Commands;

use App\Models\Config;
use App\Models\Page;
use App\Models\QRCode;
use App\Support\StringHelper;
use Illuminate\Console\Command;

use Illuminate\Support\Str;

class GenerateQrTypePages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:qr-type-pages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new page for each QR Code type.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        collect(QRCode::getTypes())->each(function ($type) {
            $title = StringHelper::title($type) . ' QR Code Generator';

            $slug = Str::slug($title);

            $page = Page::whereSlug($slug)->first();

            if (!$page) {
                $page = new Page([
                    'title' => $title,
                    'slug' => $slug,
                    'published' => true,
                    'meta_description' => $title
                ]);
            }

            $page->html_content = $this->getDefaultPageContent($type);

            $page->save();

            Config::set('qrType.' . $type . '.url', $slug);
        });

        $this->info('Generated ' . count(QRCode::getTypes()) . ' pages.');

        return 0;
    }

    private function getDefaultPageContent($type)
    {
        ob_start();
?>
        <div class="layout-box">
            <qrcg-website-banner selected-type-id="<?php echo $type ?>"></qrcg-website-banner>

            <section class="informative-text">
                <h2>What is a QR code?</h2>

                <p>You've probably noticed a square barcode pasted to a graffitied light pole or on the back of a business card.</p>
                <p>That pixelated code, shaped in a square, is called a QR code. They help you download apps, give you contactless access to a restaurant's menu, can be found on marketing billboards, and on websites or social media to promote items and deals.</p>
                <p>Despite being a mid-90s invention, the QR code didn't gain real momentum until the era of smartphones. Mobile devices allowed the digital mark to be used in more dynamic and diverse ways, making it an easy — and in the era of a pandemic, contactless — way to connect to and share information.</p>
                <p>Here's what you need to know about QR codes.</p>

                <p>Invented in 1994 by Masahiro Hara, chief engineer of Denso Wave, a Japanese company and subsidiary of Toyota, the QR code was initially used to track vehicles and parts as they moved through the manufacturing process.</p>
                <p>Short for Quick Response, QR codes are a type of barcode easily readable with digital devices like smartphones. They store information as a series of pixels in a square grid that can be read in two directions — top to bottom and right to left — unlike standard barcodes that can only be read top to bottom.</p>
                <p>QR codes can store about 7,000 digits or around 4,000 characters, including punctuation and special characters. It can also encode information like phone numbers or internet addresses. The arrangement of each QR code varies depending on the information it contains, and that changes the arrangement of its black modules.</p>
            </section>
        </div>
<?php

        $content = ob_get_clean();

        return $content;
    }
}
