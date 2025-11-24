<?php

namespace App\Support\QRCodeTypes\ViewComposers;

use App\Models\QRCode;
use App\Support\ViewComposers\BaseComposer;
use App\Interfaces\FileManager;
use App\Interfaces\SubscriptionManager;
use App\Interfaces\TranslationManager;
use App\Models\Config;
use App\Models\CustomForm;
use App\Models\File;
use App\Models\QRCodeRedirect;
use App\Models\QRCodeWebPageDesign;
use App\Models\Translation;
use App\Support\QRCodeScanManager;
use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\Style\CssRuleGenerator;
use App\Support\QRCodeTypes\ViewComposers\Components\QRCodeFavicon\Component as QRCodeFaviconComponent;
use App\Support\QRCodeTypes\ViewComposers\Traits\CombinesStylesMethods;
use Throwable;

abstract class Base extends BaseComposer
{
    use CombinesStylesMethods;

    protected QRCode $qrcode;

    protected ?QRCodeWebPageDesign $design;

    protected FileManager $files;

    private static ?QRCode $resolvedQRCode = null;

    private SubscriptionManager $subscriptions;

    public QRCodeFaviconComponent $favicon;

    protected ?Translation $translation = null;

    public static function resolveQRCode(QRCode $qrcode)
    {
        static::$resolvedQRCode = $qrcode;
    }

    public static function withQRCode(QRCode $qrcode)
    {
        static::$resolvedQRCode = $qrcode;

        return new static;
    }

    private function resolvedQRCode(): ?QRCode
    {
        if (static::$resolvedQRCode) {
            return static::$resolvedQRCode;
        }

        $slug = request()->route('slug');

        $redirect = QRCodeRedirect::where('slug', $slug)->first();

        if (!$redirect) {
            abort(404);
        }

        return $redirect->qrcode;
    }

    public function __construct()
    {
        $this->qrcode = $this->resolvedQRCode();

        $this->design = QRCodeWebPageDesign::where(
            'qrcode_id',
            $this->qrcode->id
        )->first();

        $this->files = app(FileManager::class);

        $this->subscriptions = app(SubscriptionManager::class);

        $this->favicon = new QRCodeFaviconComponent($this);

        $this->loadQRCodeTranslationIfNeeded();
    }

    public static function path(): string
    {
        return 'qrcode.types.' . static::type();
    }

    public abstract static function type();

    protected function templateColors($key)
    {
        return [];
    }

    protected function loadQRCodeTranslationIfNeeded()
    {
        $id = $this->designValue('qrcode_language');

        if (!$id) {
            return;
        }

        $translation = Translation::find($id);

        if (!$translation) {
            return;
        }

        $this->translation = $translation;

        /**
         * @var TranslationManager
         */
        $manager = app(TranslationManager::class);

        $manager->setTranslationForThisRequest($translation);
    }

    public function locale()
    {
        return $this->translation?->locale ?? 'en';
    }

    public function dir()
    {
        return $this->translation?->direction ?? 'ltr';
    }

    public function shouldShowPoweredBy()
    {
        if (app()->environment('local')) return true;

        $user = $this->qrcode->user;

        $remove = $this->subscriptions
            ->userHasAccessToSubscriptionPlanFeature(
                $user,
                'qrcode.remove_powered_by'
            );

        return !$remove;
    }

    public function getLanguageCollectionUrl()
    {
        return (new QRCodeScanManager)->getLanguageCollectionUrl();
    }

    public function poweredByName()
    {
        if (!empty(trim($name = Config::get('app.powered_by_name')))) {
            return $name;
        }

        return config('app.name');
    }

    public function poweredByText()
    {
        if (!$this->shouldShowPoweredBy()) return;

        return sprintf('%s %s', t('Powered by'), $this->poweredByName());
    }


    public function designValue($key, $default = null)
    {
        $defaultValue = @$this->templateColors($key) ?? $default;

        if (!$this->design) {
            return $defaultValue;
        }

        return $this->design->value($key, $defaultValue);
    }

    /**
     * Avoid returning empty arrays
     */
    public function designField($key, $default = null)
    {
        $value = $this->designValue($key);

        return empty($value) ? $default : $value;
    }

    public function notEmptyDesignValue($key, $default = '')
    {
        $value = $this->designValue($key, $default);

        if (empty($value)) {
            $value = $default;
        }

        return $value;
    }

    public function fileUrl($key)
    {
        if (!$this->design) return;

        $id = $this->design->value($key);


        return $this->findFileUrl($id);
    }

    public function findFileUrl($fileId, $default = null)
    {
        return file_url($fileId) ?? $default;
    }

    protected function getDefaultBannerImageUrl()
    {
        $relativePath = sprintf(
            '/assets/images/%s/%s/bg.jpg',
            $this::type(),
            $this->getTemplate()
        );

        $absolutePath = base_path("public/$relativePath");

        if (file_exists($absolutePath)) {
            $mtime = filemtime($absolutePath);

            return asset(
                "$relativePath?v=$mtime"
            );
        }

        return url('/assets/images/biolinks/default/bg.jpg');
    }

    public function bg()
    {
        return $this->fileUrl('backgroundImage') ?? $this->getDefaultBannerImageUrl();
    }

    protected function getTemplate()
    {
        return $this->qrcodeData('businessType', 'bakery');
    }

    public function dataField($key, $default = null)
    {
        return $this->qrcodeData($key, $default);
    }

    public function qrcodeData($key, $default = null)
    {
        try {
            $value = $this->qrcode->data->{$key};

            if (empty($value)) {
                return $default;
            }

            return $value;
        } catch (Throwable $th) {
            return $default;
        }
    }

    public function getQRCode()
    {
        return $this->qrcode;
    }

    public function renderFaviconIfNeeded()
    {
        $url = $this->fileUrl('favicon');

        $enabled = $this->designValue('favicon_enabled');

        if (empty($url) || $enabled === 'disabled') return;

        return sprintf('<link rel="icon" href="%s" />', $url);
    }

    public function getAutomaticFormPopupDelay()
    {
        $form = $this->getAutomaticFormModel();

        if (!$form) {
            return 0;
        }

        $value = $form->getSettings('delay', 0);

        return $value < 0 ? 0 : $value  * 1000;
    }

    public function getAutomaticFormHeaderImage()
    {
        $form = $this->getAutomaticFormModel();

        if (!$form) {
            return null;
        }

        $id = $form->getSettings('header_image');

        return file_url($id);
    }

    /**
     * @return CustomForm
     */
    public function getAutomaticFormModel()
    {
        $id = $this->designValue('automatic_form_popup', null);

        if (empty($id)) {
            return null;
        }
        /**
         * @var CustomForm
         */
        $form = CustomForm::find($id);

        return $form;
    }

    public function getAutomaticFormPopupButtonText()
    {
        $form = $this->getAutomaticFormModel();

        $default = t('Add to contacts');

        if (!$form) {
            return $default;
        }

        return $form->getSettings('button_text', $default);
    }

    public function hasBanner()
    {
        $type = $this->designValue('banner_type');

        return $type === 'image' ||  $type === 'video';
    }

    public function bannerClass()
    {
        if (!$this->hasBanner()) return '';

        return 'has-banner banner-' . $this->designValue('banner_type');
    }

    public function bodyClasses()
    {
        return sprintf(
            'qrcode-type-%s qrcode-type %s',
            $this->type(),
            $this->bannerClass(),
        );
    }

    protected function typeSelector($selector = '')
    {
        return trim(sprintf('.qrcode-type.qrcode-type-%s %s', $this->type(), $selector));
    }

    /**
     * @param string|array $selector if not provided it will select the block itself
     * @return CssRuleGenerator
     */
    protected function select($selector = '')
    {
        $selector = collect($selector)
            ->map(fn($s) => $this->typeSelector($s))
            ->join(', ');

        return CssRuleGenerator::withSelector(
            $selector
        );
    }

    protected function style($designKey)
    {
        return fn() => $this->designField($designKey);
    }
}
