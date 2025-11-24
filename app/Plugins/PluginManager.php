<?php

namespace App\Plugins;

use Throwable;

use App\Support\System\Traits\WriteLogs;

class PluginManager
{
    use WriteLogs;

    /**
     * ACTIONS
     */
    const ACTION_BIOLINKS_AFTER_LAYOUT  = 'Biolinks: After Layout';
    const ACTION_BODY_BEFORE_CLOSE      = 'Body: Before Close';
    const ACTION_ACCOUNT_REGISTERED = 'Account: Registerd';
    const ACTION_LEAD_FORM_BEFORE_RENDER = 'Lead Form: Before Render';
    const ACTION_HOME_PAGE_AFTER_BLOG_SECTION = 'Home Page: After Blog Section';
    const ACTION_AFTER_QRCODE_RESET = 'QR Code Scan Manager: After QR Code Reset';
    const ACTION_SUBSCRIPTION_PLAN_BEFORE_SAVE = 'Subscription Plan: Before Save';


    /**
     * FILTERS
     */
    const FILTER_IMPORT_URL_QRCODE_OPERATION_EXTEND_QRCODE              = 'ImportUrlQRCodeOperation: Extend QR Code';
    const FILTER_SHOULD_PROTECT_QRCODE_BY_PINCODE                       = 'ProtectByPincode: Should Protect';
    const FILTER_PAYMENT_PROCESSOR_PAY_LINK                             = 'PaymentProcessor: PayLink';
    const FILTER_PAYMENT_PROCESSOR_SHOULD_GENERATE_PAY_LINK             = 'PaymentProcessor: Should Generate PayLink';
    const FILTER_PAYMENT_PROCESSOR_PLAN_PRICE                           = 'PaymentProcessor: Plan Price';
    const FILTER_PAYMENT_PROCESSOR_PLAN_DESCRIPTION                     = 'PaymentProcessor: Plan Description';
    const FILTER_PAYMENT_PROCESSOR_PLAN_NAME                            = 'PaymentProcessor: Plan Name';
    const FILTER_PAYMENT_PROCESSOR_PENDING_SUBSCRIPTION                 = 'PaymentProcessor: Pending Subscription';
    const FILTER_DYNAMIC_BIOLINK_SHOULD_RENDER_FIELD                    = 'DynamicBlock: Should Render Field';
    const FILTER_SHOULD_VERIFY_EMAIL                                    = 'User: Should Verify Email';
    const FILTER_URL_SIGNATURE_VALIDATE_URL                             = 'UrlSignature: Validate Url';
    const FILTER_DEFAULT_SUBSCRIPTION_PLAN                              = 'Default Subscription Plan';
    const FILTER_MIDDLEWARE_GROUPS                                      = 'HttpKernel: Filter Middleware Groups';
    const FILTER_WEBSITE_BUILDER_PAGE_PATH                              = 'Wesbite Builder: Page Path';
    const FILTER_HEAD_CONFIG_ARRAY                                      = 'Head Configs Array';
    const FILTER_GOOGLE_PLACE_URL_TYPE                                  = 'Google Place: URL Type';
    const FILTER_QRCODE_TYPES_INSTANCES                                 = 'QR Code Types Instances';
    const FILTER_QRCODE_TYPES_COMPOSER_CLASSES                          = 'QR Code Types Composer Classes';
    const FILTER_PAYMENT_PROCESSOR_WEBHOOK_IS_CUSTOM_WEBHOOK            = 'Payment Processor Webhook: Is Custom Webhook';
    const FILTER_PAYMENT_PROCESSOR_WEBHOOK_CUSTOM_WEBHOOK_RESPONSE      = 'Payment Processor Webhook: Custom Webhook Response';
    const FILTER_SHOULD_APPLY_QRCODE_SUBSCRIPTION_RULES                 = 'Should Apply QR Code Subscription Rules';
    const FILTER_QRCODE_UPDATE_REQUEST                                  = 'QRCode: Filter the Update Request';
    const FILTER_USER_RESPONSE                                          = 'User: Filter Response';
    const FILTER_UPDATE_IS_AVAILABLE                                    = 'System Status: Update Is Available';
    const FILTER_HOMEPAGE_PATH                                          = 'Home Page: Path';

    private static $actions = [];

    private static $filters = [];

    public function __construct() {}

    public function register()
    {
        $this->getEnabledPlugins()->each(function (BasePlugin $instance) {
            try {
                $instance->register();
            } catch (Throwable $th) {
                $this->logError(
                    sprintf(
                        'Error registering plugin %s %s',
                        $instance::class,
                        $th->getMessage()
                    )
                );
            }
        });
    }

    public function boot()
    {
        $this->getEnabledPlugins()->each(function (BasePlugin $instance) {
            try {
                $instance->boot();
            } catch (Throwable $th) {
                $this->logError(
                    sprintf(
                        'Error booting plugin %s %s',
                        $instance::class,
                        $th->getMessage()
                    )
                );
            }
        });
    }

    public function getEnabledPlugins()
    {
        return $this->getInstances()->filter(fn($plugin) => $plugin->isEnabled());
    }

    public function find($slug)
    {
        return $this->getEnabledPlugins()->first(fn(BasePlugin $plugin) => $plugin->slug() === $slug);
    }

    private function getPluginClasses()
    {
        return collect(glob(__DIR__ . '/*/Plugin.php'))
            ->map(fn($str) => str_replace(__DIR__, '', $str))
            ->map(fn($str) => str_replace('/', '\\', $str))
            ->map(fn($str) => __NAMESPACE__ . $str)
            ->map(fn($str) => str_replace('.php', '', $str));
    }

    private function getInstances()
    {
        return $this->getPluginClasses()->filter(function ($class) {
            try {
                $instance = app($class);

                if (!($instance instanceof BasePlugin)) {
                    return false;
                }

                return true;
            } catch (Throwable $th) {
                $this->logError("Error creating instance of $class " . $th->getMessage());

                return false;
            }
        })

            ->map(fn($c) => app($c))->values();
    }

    /**
     * Similar to wordpress action
     */
    public static function doAction($actionName, ...$rest)
    {
        $registeredActions = @static::$actions[$actionName];

        if (!is_array($registeredActions)) return;

        return implode('', array_map(fn($cb) => call_user_func_array($cb, $rest), $registeredActions));
    }

    public static function addAction($name, $callback)
    {
        static::$actions[$name][] = $callback;
    }

    public static function addFilter($name, $callback)
    {
        static::$filters[$name][] = $callback;
    }

    public static function doFilter($name, $value, ...$params)
    {
        if (empty(static::$filters[$name])) return $value;

        $value = array_reduce(
            static::$filters[$name],
            function ($value, $callback) use ($params) {
                return call_user_func_array(
                    $callback,
                    [
                        $value,
                        ...$params
                    ]
                );
            },
            $value
        );

        return $value;
    }
}
