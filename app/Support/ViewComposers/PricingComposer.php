<?php

namespace App\Support\ViewComposers;

use App\Interfaces\CurrencyManager;
use App\Models\SubscriptionPlan;
use App\Support\AI\AIQRCodeGenerator;
use App\Support\Billing\AccountCreditBillingManager;
use App\Support\Billing\BillingManager;
use App\Support\DropletManager;
use App\Support\QRCodeTypes\BaseType;
use App\Support\QRCodeTypes\QRCodeTypeManager;
use App\Support\System\Traits\WriteLogs;
use App\Support\System\Translation\LineTranslator;
use Illuminate\View\View;
use Stripe\Plan;

class PricingComposer extends BaseComposer
{
    use WriteLogs;

    private CurrencyManager $currencies;

    private DropletManager $dropletManager;

    private QRCodeTypeManager $qrcodeTypeManager;

    private BillingManager $billingManager;

    private AccountCreditBillingManager $accountCredit;

    private SubscriptionPlan $checkpointPlan;

    private LineTranslator $lineTranslator;

    public function __construct(CurrencyManager $currencies)
    {
        parent::__construct();

        $this->currencies = $currencies;

        $this->dropletManager = new DropletManager();

        $this->billingManager = new BillingManager();

        $this->qrcodeTypeManager = new QRCodeTypeManager();

        $this->accountCredit = new AccountCreditBillingManager;

        $this->lineTranslator = new LineTranslator();
    }

    public static function path(): string
    {
        return 'blue.sections.pricing|blue.sections.pricing.account-credit|blue.sections.pricing.plans';
    }

    private function getPlans()
    {
        return SubscriptionPlan::where('is_hidden', false)->orderBy('sort_order', 'asc')->get();
    }

    public function isAccountCreditBilling()
    {
        return $this->billingManager->isAccountCreditBilling();
    }

    public function dynamicQRCodePrice()
    {
        return $this->accountCredit->dynamicQRCodePrice();
    }

    public function staticQRCodePrice()
    {
        return $this->accountCredit->staticQRCodePrice();
    }

    public function getDynamicTypes()
    {
        return $this->qrcodeTypeManager->types()->filter(function (BaseType $type) {
            return $type->isDynamic();
        });
    }

    public function getStaticTypes()
    {
        return $this->qrcodeTypeManager->types()->filter(function (BaseType $type) {
            return !$type->isDynamic();
        });
    }

    public function planIsFree(SubscriptionPlan $plan)
    {
        return $plan->price == 0;
    }

    public function price(SubscriptionPlan $plan)
    {
        return $this->currencies->formatNumber($plan->price);
    }

    public function outlinedShapes(SubscriptionPlan $plan)
    {
        return collect($plan->features)->filter(
            fn($f) => preg_match("/^shape\./", $f)
        );
    }

    public function stickers(SubscriptionPlan $plan)
    {
        return collect($plan->features)->filter(
            fn($f) => preg_match("/^advancedShape\./", $f)
        );
    }

    public function name(SubscriptionPlan $plan)
    {
        if (!$this->translations->multilingualEnabled()) {
            return $plan->name;
        }

        $lineTranslator = new LineTranslator;

        return $lineTranslator->translateLine($plan, 'name');
    }

    public function hasCopyFeature(SubscriptionPlan $plan)
    {
        return collect($plan->features)->filter(
            fn($f) => preg_match('/qrcode.copy/', $f)
        )->count() > 0;
    }

    public function hasLogoFeature(SubscriptionPlan $plan)
    {
        return collect($plan->features)->filter(
            fn($f) => preg_match('/qrcode.logo/', $f)
        )->count() > 0;
    }

    public function formatTotalValue($value)
    {
        return $value == -1 ? t('Unlimited') : $value;
    }

    public function currencyBefore()
    {
        $position = $this->currencies->enabledCurrency()->symbol_position;

        return empty($position) || $position == 'before';
    }

    public function shouldShowNumberOfUsers(SubscriptionPlan $plan)
    {
        if (!$this->dropletManager->isLarge()) return false;

        if ($plan->number_of_users == -1) {
            return true;
        }

        return $plan->number_of_users > 0;
    }

    public function checkpoints($plan)
    {
        $this->checkpointPlan = $plan;

        return $this->checkpointPlan->checkpoints ?? [];
    }

    public function checkpoint($i)
    {
        $text = @$this->checkpointPlan->checkpoints[$i]['text'];

        $translated = $this->lineTranslator->translateLine(
            $this->checkpointPlan,
            "checkpoints.$i.text"
        );

        return $translated ?? $text;
    }

    public function checkpointClass($i)
    {
        $available = $this->checkpointPlan->checkpoints[$i]['available'];

        return $available ? 'check' : 'no-check';
    }

    public function numberOfUsers($plan)
    {
        $num = $this->formatTotalValue($plan->number_of_users);

        if ($num == 1) {
            return t('one user can be invited.');
        } else {
            $text = t('users can be invited.');
        }

        return "$num $text";
    }

    public function isLarge()
    {
        return $this->dropletManager->isLarge();
    }

    private function isChangePlanRequest()
    {
        return request()->input('action') === 'change-plan';
    }

    public function checkoutUrl(SubscriptionPlan $plan)
    {
        $changePlanParam = '';

        if ($this->isChangePlanRequest()) {
            $changePlanParam = '&action=change-plan';
        }

        return sprintf(
            '%s?plan-id=%s%s',
            url('/checkout'),
            $plan->id,
            $changePlanParam
        );
    }


    public function aiGenerationEnabled()
    {
        return (new AIQRCodeGenerator)->isEnabled();
    }

    public function compose(View $view)
    {
        parent::compose($view);

        $view->with('plans', $this->getPlans());

        $view->with('currency', $this->currencies->enabledCurrency()->symbol);
    }

    public function tableMenuItem($number, $text)
    {
        if ($number == 0) {
            return;
        }

        return sprintf('<li>%s %s</li>', $number, $text);
    }

    public function hasLifetimePlans()
    {
        return SubscriptionPlan::get()->filter(function (SubscriptionPlan $plan) {
            return $plan->frequency == $plan::FREQUENCY_LIFETIME;
        })->isNotEmpty();
    }

    public function getPlanFrequencyText(SubscriptionPlan $plan)
    {
        switch ($plan->frequency) {
            case SubscriptionPlan::FREQUENCY_MONTHLY:
                return t('Monthly');

            case SubscriptionPlan::FREQUENCY_YEARLY:
                return t('Yearly');

            case SubscriptionPlan::FREQUENCY_LIFETIME:
                return t('One Time');

            default:
                return '';
        }
    }

    public function scanRetention(SubscriptionPlan $plan)
    {
        $days = $plan->scan_retention_days;

        if ($days == -1) {
            return t('Unlimited');
        }

        if ($days == 365) {
            return t('One year');
        }

        return sprintf('%s %s', $days, t('days'));
    }

    public function getButtonText(SubscriptionPlan $plan)
    {
        if ($this->isChangePlanRequest()) {
            return t('Change Plan');
        }

        if ($plan->button_text) {
            return $plan->button_text;
        }

        if ($plan->price == '0') {
            return t('Sign Up');
        }

        return t('Subscribe Now');
    }
}
