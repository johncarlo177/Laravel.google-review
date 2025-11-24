<?php

namespace App\Models;

use App\Events\QRCodeSaved;
use App\Models\Traits\HasMetaFields;
use App\Repositories\FileManager;
use App\Support\AI\PNGQRCodeGenerator;
use App\Support\ConfigHelper;
use App\Support\QRCodeProcessors\AdvancedShapeProcessors\BaseAdvancedShapeProcessor;
use App\Support\QRCodeProcessors\AdvancedShapeProcessors\CouponAdvancedShape;
use App\Support\QRCodeProcessors\GradientProcessor;
use App\Support\QRCodeStorage;
use App\Support\QRCodeTypes\BaseType;
use App\Support\QRCodeTypes\QRCodeTypeManager;
use App\Support\QRCodeWebPageDesignManager;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;


/**
 * @property int id
 * @property User user
 * @property QRCodeRedirect redirect
 * @property bool archived
 * @property string pincode
 * @property string name
 * @property string type
 * @property object data
 * @property bool is_template
 * @property int scans_count
 * @property int folder_id
 * @property int allowed_scans
 * @property int qrcode_screenshot_id
 * @property string tags searchable text which deosnt show in the UI
 * @property string status
 * @property bool is_created_by_bulk_operation
 * @property Folder folder
 * @property Carbon archived_at
 */
class QRCode extends Model
{
    use WriteLogs;

    use HasMetaFields;
    use HasFactory;

    public const STATUS_ENABLED = 'enabled';
    public const STATUS_DISABLED = 'disabled';

    protected $fillable = ['name', 'type', 'design', 'data', 'folder_id', 'qrcode_screenshot_id'];

    protected $appends = [
        'svg_url',
        'simple_png_url',
        'qrcode_screenshot_url'
    ];

    protected $table = 'qrcodes';

    protected $casts = [
        'data' => 'object',
        'design' => 'object',
        'archived' => 'boolean',
        'is_created_by_bulk_operation' => 'boolean',
        'archived_at' => 'datetime'
    ];

    protected $dispatchesEvents = [
        'saved' => QRCodeSaved::class,
    ];

    private $white = '#ffffff';

    private $primaryColor = '#1c57cb';

    private $black = '#000000';

    public static function getTypes()
    {
        return (new QRCodeTypeManager)->slugs();
    }

    protected static function booted()
    {
        static::creating([static::class, 'onCreating']);

        static::saving([static::class, 'onSaving']);
    }

    static function onCreating($model)
    {
        $model->generateFileName();
    }

    static function onSaving($model)
    {
        $model->generateNameIfNeeded();
    }

    private function generateNameIfNeeded()
    {
        if (!empty($this->name)) {
            return;
        }

        $type = (new QRCodeTypeManager)->find($this->type);

        $this->name = $type->generateName($this);
    }

    public function generateFileName()
    {
        $fileName = '';

        $found = true;

        do {
            $fileName = uniqid();

            $found = QRCode::where('file_name', $fileName)->first();
        } while ($found);

        $this->file_name = $fileName;
    }

    public function redirect()
    {
        return $this->hasOne(QRCodeRedirect::class, 'qrcode_id');
    }

    public function setDesignAttribute($designParam)
    {
        $designParam = (object) $designParam;

        $defaultDesign = array_merge(
            [
                'fillType' => 'solid',
                'foreground_image_id' => null,
                'foregroundColor' => $this->black,
                'eyeInternalColor' => $this->black,
                'eyeExternalColor' => $this->black,
                'gradientFill' => GradientProcessor::defaultGradient(),
                'module' => 'square',
                'shape' => 'none',
                'frameColor' => $this->black,
                'logo_id' => null,
                'logoScale' => 0.2, // default
                'logoPositionX' => 0.5,
                'logoPositionY' => 0.5,
                'logoRotate' => 0,
                'logoBackground' => true,
                'logoBackgroundFill' => $this->white,
                'logoUrl' => null,
                'logoType' => 'preset',
                'logoBackgroundScale' => 1.5,
                'logoBackgroundShape' => 'circle',
                'backgroundEnabled' => true,
                'backgroundColor' => $this->white,
                'finder' => 'default',
                'finderDot' => 'default',
                'advancedShape' => 'none',
                'advancedShapeDropShadow' => true,
                'advancedShapeFrameColor' => $this->black,
                'reviewCollectorCircleColor' => null,
                'reviewCollectorStarsColor' => null,
                'reviewCollectorLogo' => null,
                'reviewCollectorLogoSrc' => null,
                'healthcareFrameColor' => null,
                'healthcareHeartColor' => null,
                'is_ai' => false,
                'ai_prompt' => '',
                'ai_strength' => 1.8,
                'ai_steps' => 20,
                'ai_model' => '4real',
                'sticker_logo' => null,
            ],
            BaseAdvancedShapeProcessor::defaultTextRelatedValues(),
            CouponAdvancedShape::defaultValues(),
        );

        $design = (object) [];

        if (!$designParam) {
            $designParam = (object) [];
        }

        foreach ($defaultDesign as $key => $defaultValue) {
            $design->$key = !isset($designParam->$key) ? $defaultValue : $designParam->$key;
        }

        $this->attributes['design'] = json_encode($design);

        $this->logDebug('design attribute %s', $this->attributes['design']);
    }

    protected function getLegacyLogo()
    {
        return File::where('attachable_type', $this::class)
            ->where('attachable_id', $this->id)
            ->where('type', FileManager::FILE_TYPE_QRCODE_LOGO)
            ->first();
    }

    public function getLogo()
    {
        if ($id = @$this->design?->logo_id) {
            return File::find($id);
        }

        /**
         * Fallback to legacy logo
         */
        return $this->getLegacyLogo();
    }

    public function getLogoAttribute()
    {
        return $this->getLogo();
    }

    public function getForegroundImage()
    {
        if ($id = @$this->design?->foreground_image_id) {
            return File::find($id);
        }

        /**
         * Fallback to legacy logo
         */
        return $this->getLegacyForegroundImage();
    }

    public function getLegacyForegroundImage()
    {
        return File::where('attachable_type', $this::class)
            ->where('attachable_id', $this->id)
            ->where('type', FileManager::FILE_TYPE_QRCODE_FOREGROUND_IMAGE)
            ->first();
    }


    /**
     * @deprecated
     * @use svg_url()
     */
    public function url()
    {
        return $this->svg_url();
    }

    public function svg_url()
    {
        return QRCodeStorage::ofQRCode($this)->getSvgUrl();
    }

    public function getSvgContent()
    {
        return QRCodeStorage::ofQRCode($this)->getContent();
    }

    public function getSvgUrlAttribute()
    {
        return $this->svg_url();
    }

    public function getSimplePngUrlAttribute()
    {
        if (!ConfigHelper::shouldSavePngFile()) {
            return null;
        }

        return (new PNGQRCodeGenerator($this))->getPngUrl();
    }

    public function getPngFilePath()
    {
        return (new PNGQRCodeGenerator($this))->getPath();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function resolveType(): ?BaseType
    {
        return (new QRCodeTypeManager)->find($this->type);
    }

    public function getContent()
    {
        return $this->resolveType()->makeData($this);
    }

    public static function bySlug($slug): ?static
    {
        $redirect = QRCodeRedirect::where('slug', $slug)->first();

        if (!$redirect) {
            static::logDebug('Redirect for slug %s not found', $slug);
        }

        return $redirect?->qrcode;
    }

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

    public function getDirectSvgUrl()
    {
        return QRCodeStorage::ofQRCode($this)->getTemporaryDirectUrl();
    }

    public function getWebPageDesign()
    {
        return (new QRCodeWebPageDesignManager)->getDesign($this);
    }

    public function getQrCodeScreenshotUrlAttribute()
    {
        return file_url($this->qrcode_screenshot_id);
    }
}
