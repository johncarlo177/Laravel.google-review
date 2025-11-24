<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @property int template_category_id
 * @property string description
 * @property int screenshot_id
 * @property int video_id
 * @property string template_access_level
 * @property TemplateCategory category
 */
class QRCodeTemplate extends QRCode
{
    public const TEMPLATE_ACCESS_LEVEL_PUBLIC = 'public';

    public const TEMPLATE_ACCESS_LEVEL_PRIVATE = 'private';

    protected $with = [];

    protected $appends = [];

    protected $casts = [
        'is_template' => 'boolean'
    ];

    protected $dispatchesEvents = [];

    public function description(): Attribute
    {
        return Attribute::make(
            fn() => $this->getMeta('template_description'),
            fn($value) => $this->setMeta('template_description', $value)
        );
    }

    public function screenshotId(): Attribute
    {
        $metaKey = 'template_screenshot_id';

        return Attribute::make(
            get: fn() => $this->getMeta($metaKey),

            set: fn($v) => $this->setMeta($metaKey, $v)
        );
    }

    public function videoId(): Attribute
    {
        $metaKey = 'template_video_id';

        return Attribute::make(
            get: fn() => $this->getMeta($metaKey),

            set: fn($v) => $this->setMeta($metaKey, $v)
        );
    }

    public function category()
    {
        return $this->belongsTo(TemplateCategory::class, 'template_category_id');
    }
}
