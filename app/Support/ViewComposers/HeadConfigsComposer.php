<?php

namespace App\Support\ViewComposers;

use App\Plugins\BasePlugin;
use App\Plugins\PluginManager;
use App\Rules\UploadFileSize;
use App\Support\WidgetManager;

class HeadConfigsComposer extends BaseComposer
{
    private PluginManager $plugins;

    private function configArray()
    {
        $value = [
            'app.url' => config("app.url"),
            'app.env' => config("app.env"),
            'app.name' => $this->appName(),
            'app.authentication_type' => config('app.authentication_type'),
            'app.marketplace' => $this->marketplace(),
            'frontend.slogan' => $this->slogan(),
            'app.frontend_custom_url' => config("app.frontend_custom_url"),
            'qrcode.storage_path' => config("qrcode.storage_path"),
            'frontend.header_logo_url' => config("frontend.header_logo_url"),
            'frontend.header_logo_inverse_url' => config("frontend.header_logo_inverse_url"),
            'frontend.login_logo_url' => config("frontend.login_logo_url"),
            'account_page.background_image_url' => config('account_page.background_image_url'),
            'account_page.gradient' => config('account_page.gradient'),
            'account_page.image_round_corner' => config('account_page.image_round_corner'),
            'account_page.image_position' => config('account_page.image_position'),
            'content-manager.positions' => config("content-manager.positions"),
            'app.email_verification_after_sign_up' => config("app.email_verification_after_sign_up"),
            'qrcode.skip_install_step_1' => config("qrcode.skip_install_step_1"),
            'currency' => config("currency"),
            'app.after_logout_action' => config("app.after_logout_action"),
            'app.wplus_integration_enabled' => config("app.wplus_integration_enabled"),
            'app.frontend_links' => config("app.frontend_links"),
            'app.dashboard-client-menu' => config("app.dashboard-client-menu"),
            'app.new_user_registration' => config("app.new_user_registration"),
            'theme.default_scrollbar' => config("theme.default_scrollbar"),
            'preview.canvasTextRender' => config("preview.canvasTextRender"),
            'app.paid_subscriptions' => config("app.paid_subscriptions"),
            'app.available_qrcode_types' => config("app.available_qrcode_types"),
            'app.frontend_pricing_plans_url' => config("app.frontend_pricing_plans_url"),
            'droplet.is_large' => config("droplet.is_large"),
            'app.allow_iframe_embed' => config("app.allow_iframe_embed"),
            'app.mobile_number_field' => config("app.mobile_number_field"),
            'dashboard.view_stats_link_in_edit_qrcode_page' => config("dashboard.view_stats_link_in_edit_qrcode_page"),
            'frontpage.show_customize_design_button' => config("frontpage.show_customize_design_button"),
            'dashboard.select_qrcodes_of_currently_logged_in_user_by_default' => config(
                "dashboard.select_qrcodes_of_currently_logged_in_user_by_default"
            ),
            'billing.mode' => config("billing.mode"),
            'account_credit.dynamic_qrcode_price' => config("account_credit.dynamic_qrcode_price"),
            'account_credit.static_qrcode_price' => config("account_credit.static_qrcode_price"),
            'dashboard.help_button_in_dashboard_header' => config("dashboard.help_button_in_dashboard_header"),
            'auth0.enabled' => config("auth0.enabled"),
            'homepage.homepage-generator' => config("homepage.homepage-generator"),
            'account.cancel_subscription_button' => config("account.cancel_subscription_button"),
            'app.color-picker-palettes' => config('app.color-picker-palettes'),
            'plugins.enabled' => $this->enabledPlugins(),
            'quickqr_art.available_workflows' => config('quickqr_art.available_workflows'),
            'customer.short_link_change' => config('customer.short_link_change'),
            'bulk_operation.export-qrcode-size' => config('bulk_operation.export-qrcode-size'),
            'cookie_consent_enabled' => config('cookie_consent_enabled'),
            'users_can_delete_qrcodes' => config('users_can_delete_qrcodes'),
            'qrcode.searchbox_in_qrcode_selection_page' => config('qrcode.searchbox_in_qrcode_selection_page'),
            'menu.show_paying_non_paying_users' => config('menu.show_paying_non_paying_users'),
            'dashboard.top_banner_option' => config('dashboard.top_banner_option'),
            'dashboard.top_banner_image' => config('dashboard.top_banner_image'),
            'dashboard.top_banner_image_url' => file_url(config('dashboard.top_banner_image')),
            'dashboard.top_banner_video' => config('dashboard.top_banner_video'),
            'dashboard.top_banner_video_url' => file_url(config('dashboard.top_banner_video')),
            'dashboard.top_banner_title' => config('dashboard.top_banner_title'),
            'dashboard.top_banner_subtitle' => config('dashboard.top_banner_subtitle'),
            'dashboard.top_banner_text_color' => config('dashboard.top_banner_text_color'),
            'website-banner-background-color' => config('website-banner-background-color'),
            'website-banner-color-1' => config('website-banner-color-1'),
            'website-banner-color-2' => config('website-banner-color-2'),
            'website-banner-color-3' => config('website-banner-color-3'),
            'website-banner-color-4' => config('website-banner-color-4'),
            'dashboard.qrcode_list_mode' => config('dashboard.qrcode_list_mode'),
            'dashboard.welcome_popup_enabled' => config('dashboard.welcome_popup_enabled'),
            'dashboard.welcome_modal_video_url' => file_url(config('dashboard.welcome_modal_video')),
            'dashboard.welcome_modal_text' => config('dashboard.welcome_modal_text'),
            'dashboard.use_template_button' => config('dashboard.use_template_button'),
            'dashboard.top_banner_height' => config('dashboard.top_banner_height'),
            'dashboard.welcome_modal_show_times' => config('dashboard.welcome_modal_show_times'),
            'dashboard.sidebar_account_widget_style' => config('dashboard.sidebar_account_widget_style'),
            'google_recaptcha.site_key' => config('google_recaptcha.site_key'),
            'google_recaptcha.secret_key' => config('google_recaptcha.secret_key'),
            'plan.allowed_file_size' => (new UploadFileSize())->getAllowedFileSize(),
            'qrcode.pincode_type' => config('qrcode.pincode_type'),
            'qrcode.pincode_length' => config('qrcode.pincode_length'),
            'app.show_signup_link_in_login_screen' => config('app.show_signup_link_in_login_screen'),
            'widget_script_version' => (new WidgetManager)->getWidgetScriptVersion(),
        ];

        $value = PluginManager::doFilter(
            PluginManager::FILTER_HEAD_CONFIG_ARRAY,
            $value
        );

        return $value;
    }

    public function __construct()
    {
        parent::__construct();

        $this->plugins = app(PluginManager::class);
    }

    public static function path(): string
    {
        return 'blue.partials.head.configs';
    }

    public function translationFile()
    {
        $translation = $this->translations::loadCurrentTranslationFile();

        if (empty($translation)) {
            $translation = '{}';
        }

        $translation = json_encode(json_decode($translation));

        return $translation;
    }

    public function locale()
    {
        if (!config('app.installed')) {
            return 'en';
        }

        return $this->translations->getCurrentTranslation()->locale;
    }

    public function direction()
    {
        if (!config('app.installed')) {
            return 'ltr';
        }

        return $this->translations->getCurrentTranslation()->direction;
    }

    public function appName()
    {
        return str_replace("'", "\\'", config("app.name"));
    }

    public function slogan()
    {
        return str_replace("'", "\\'", config("frontend.slogan"));
    }

    public function marketplace()
    {
        return base64_encode(config('app.marketplace'));
    }

    public function configs()
    {
        return json_encode($this->encodeArray($this->configArray()));
    }

    private function encodeArray($array)
    {
        return array_reduce(

            array_keys($array),

            function ($result, $key) use ($array) {
                $value = $array[$key];

                if (!is_string($value)) {
                    $value = json_encode($value);
                }

                $result[base64_encode($key)] = base64_encode($value);

                return $result;
            },
            []
        );
    }

    private function enabledPlugins()
    {
        $slugs = $this->plugins->getEnabledPlugins()
            ->map(function (BasePlugin $plugin) {
                return $plugin->slug();
            })->values()->all();

        return json_encode($slugs);
    }
}
