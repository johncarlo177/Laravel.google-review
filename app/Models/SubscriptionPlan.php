<?php

namespace App\Models;

use App\Events\SavingSubscriptionPlan;
use App\Events\SubscriptionPlanDeleted;
use App\Events\SubscriptionPlanSaved;
use App\Models\Traits\HasMetaFields;
use App\Support\QRCodeTypes\QRCodeTypeManager;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LogicException;
use Throwable;

/**
 * @property string name
 * @property string frequency
 * @property string description
 * @property string button_text
 * @property float price
 * @property float monthly_price
 * @property float yearly_price
 * @property int number_of_dynamic_qrcodes
 * @property int number_of_scans
 * @property int number_of_custom_domains
 * @property int number_of_users
 * @property int number_of_restaurant_menu_items
 * @property int number_of_product_catalogue_items
 * @property int number_of_ai_generations
 * @property int number_of_bulk_created_qrcodes
 * @property int scan_retention_days
 * @property int file_size_limit
 * @property int sort_order
 * @property bool is_popular
 * @property bool is_hidden
 * @property bool is_trial
 * @property bool is_reseller
 * @property int trial_days
 * @property array qr_types Allowed types by this plan
 * @property array features
 * @property array checkpoints
 * @property array dynamic_type_limits
 * @property string show_ads Show ads flag, either "enabled" or "disabled"
 * @property integer ads_timeout
 * @property string ads_code
 * @property string unavailable_types_behaviour
 * @property string auto_renew_qrcodes
 */
class SubscriptionPlan extends Model
{
    use HasFactory, HasMetaFields;

    /**
     * Available values: DAY WEEK MONTH YEAR
     */
    const FREQUENCY_YEARLY = 'yearly';

    const FREQUENCY_MONTHLY = 'monthly';

    const FREQUENCY_LIFETIME = 'life-time';

    protected $fillable = [
        'name',
        'price',
        'frequency',
        'monthly_price',
        'number_of_ai_generations',
        'number_of_dynamic_qrcodes',
        'number_of_scans',
        'number_of_restaurant_menu_items',
        'number_of_product_catalogue_items',
        'number_of_bulk_created_qrcodes',
        'scan_retention_days',
        'is_popular',
        'is_hidden',
        'is_trial',
        'is_reseller',
        'trial_days',
        'qr_types',
        'features',
        'number_of_custom_domains',
        'number_of_users',
        'checkpoints',
        'file_size_limit',
        'sort_order',
        'show_ads',
        'ads_timeout',
        'ads_code',
        'dynamic_type_limits',
        'unavailable_types_behaviour',
        'button_text'
    ];

    protected $appends = [
        'yearly_price'
    ];

    protected $casts = [
        'is_popular' => 'boolean',
        'is_hidden' => 'boolean',
        'is_trial' => 'boolean',
        'is_reseller' => 'boolean',
        'qr_types' => 'array',
        'features' => 'array',
        'checkpoints' => 'array',
        'dynamic_type_limits' => 'array'
    ];

    protected $dispatchesEvents = [
        'saving' => SavingSubscriptionPlan::class,
        'saved' => SubscriptionPlanSaved::class,
        'deleted' => SubscriptionPlanDeleted::class
    ];

    public static function boot()
    {
        parent::boot();

        static::saving(function (SubscriptionPlan $model) {
            $model->qr_types = $model->qr_types ?? [];

            $model->features = $model->features ?? [];

            $model->frequency = $model->frequency ?? $model::FREQUENCY_MONTHLY;
        });
    }

    public function yearlyPrice(): Attribute
    {
        return new Attribute(fn() => bcmul(12, $this->monthly_price, 1));
    }

    public function description(): Attribute
    {
        return new Attribute(fn() => implode(', ', [
            $this->name . ' ' . t('Plan'),
            t($this->frequency),
            $this->formatNumber($this->number_of_dynamic_qrcodes) . ' ' . t('Dynamic QR Codes'),
            $this->formatNumber($this->number_of_scans) . ' ' . t('Scans')
        ]));
    }

    private function formatNumber($number)
    {
        if ($number == -1) {
            return t('Unlimited');
        }

        return $number;
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function exiprationDays()
    {
        if ($this->is_trial) {
            return $this->trial_days;
        }

        if ($this->isFree()) {
            return 365 * 10;
        }

        if ($this->frequency === $this::FREQUENCY_MONTHLY)
            return 30;

        if ($this->frequency === $this::FREQUENCY_YEARLY)
            return 365;

        if ($this->frequency === $this::FREQUENCY_LIFETIME) {
            return 365 * 10;
        }

        throw new LogicException(
            "Frequency must be either " . $this::FREQUENCY_MONTHLY . " or " . $this::FREQUENCY_YEARLY . ' or ' . $this::FREQUENCY_LIFETIME
        );
    }

    public function isMonthly()
    {
        return $this->frequency === $this::FREQUENCY_MONTHLY;
    }

    public function isYearly()
    {
        return $this->frequency === $this::FREQUENCY_YEARLY;
    }

    public function isFree()
    {
        return $this->price == 0;
    }

    /**
     * @return string[]
     */
    public function getAllTypesWithLimitedAllowance()
    {
        $dynamicTypes = (new QRCodeTypeManager)->dynamicSlugs();

        return collect($dynamicTypes)->filter(function ($slug) {
            return $this->hasSpecificTypeLimit($slug);
        });
    }

    public function hasSpecificTypeLimit($type)
    {
        return $this->getSpecificTypeLimits($type) !== null;
    }

    public function getSpecificTypeLimits($type)
    {
        try {
            $limit = @$this->dynamic_type_limits[$type];

            if (is_null($limit) || $limit === '') {
                return null;
            }

            return $limit;
        } catch (Throwable $th) {
            return null;
        }
    }

    public function getDynamicTypeLimits($slug)
    {
        if (!$slug) {
            return $this->number_of_dynamic_qrcodes;
        }

        $allowance = $this->getSpecificTypeLimits($slug) ??
            $this->number_of_dynamic_qrcodes;

        return $allowance;
    }

    public function isLifetime()
    {
        return $this->frequency === $this::FREQUENCY_LIFETIME;
    }
}
