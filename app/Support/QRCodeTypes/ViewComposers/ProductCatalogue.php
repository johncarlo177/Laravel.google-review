<?php

namespace App\Support\QRCodeTypes\ViewComposers;

use App\Support\QRCodeTypes\ViewComposers\Cache\ViewCacheManager;
use App\Support\System\Traits\WriteLogs;
use App\Support\QRCodeTypes\ViewComposers\Traits\GeneratesGradientBackground;
use App\Support\QRCodeTypes\ViewComposers\Traits\HasBusinessHours;
use App\Support\QRCodeTypes\ViewComposers\Traits\HasReviewSites;
use App\Support\QRCodeTypes\ViewComposers\Traits\HasSocialIcons;
use App\Support\QRCodeTypes\ViewComposers\Components\ImageCarousel\Component as ImageCarousel;
use App\Support\QRCodeTypes\ViewComposers\Components\WhatsAppOrder\BuyButton;

class ProductCatalogue extends Base
{
    use WriteLogs;
    use HasSocialIcons;
    use GeneratesGradientBackground;
    use HasBusinessHours;
    use HasReviewSites;

    public static function type()
    {
        return 'product-catalogue';
    }

    public function renderCategories()
    {
        return ViewCacheManager::withPath(
            'qrcode.types.product-catalogue.categories'
        )
            ->withComposer($this)
            ->render();
    }

    public function categories()
    {
        $categories = $this->designValue('categories');

        if (!is_array($categories)) {
            return [];
        }

        $sorted = collect($categories)->sort(function ($c1, $c2) {
            $s1 = is_numeric(@$c1['sort_order']) ? @$c1['sort_order'] : 100;
            $s2 = is_numeric(@$c2['sort_order']) ? @$c2['sort_order'] : 100;

            return $s1 - $s2;
        });

        return $sorted->values()->all();
    }

    private function menuItemInCategory($item, $category)
    {
        $subCategories = collect($this->subCategories($category));

        if ($subCategories->isEmpty()) return $item['category'] == $category['id'];

        $categories = (clone $subCategories)->add($category);

        return null != $categories->first(fn($category) => $category['id'] == $item['category']);
    }

    public function subCategories($category)
    {
        return collect($this->categories())
            ->filter(
                fn($c) => @$c['parent_id'] == $category['id']
            )->values()->all();
    }

    public function topLevelCategories()
    {
        return collect($this->categories())->filter(
            fn($c) => empty($c['parent_id'])
        );
    }

    public function items($category)
    {
        $items = $this->designValue('menuItems');

        if (!is_array($items)) return [];

        $categoryItems = array_filter($items, fn($item) => $this->menuItemInCategory($item, $category));

        $sorted = collect($categoryItems)->sort(function ($i1, $i2) {
            $s1 = is_numeric(@$i1['sort_order']) ? @$i1['sort_order'] : 100;
            $s2 = is_numeric(@$i2['sort_order']) ? @$i2['sort_order'] : 100;

            return $s1 - $s2;
        });

        return $sorted->values()->all();
    }

    public function shouldShowItemImage($item)
    {
        $option = $this->designValue('showProductItemImage');

        if (empty($option) || $option === 'always') return true;

        if ($option === 'do-not-show-images') return false;

        if ($option === 'only-if-uploaded') {
            return !empty($this->findFileUrl(@$item['image']));
        }

        $this->logError("Invalid showProductItemImage option ($option).");

        return true;
    }

    public function menuItemHorizontalLayout($item)
    {
        if (@$item['layout'] === 'vertical') {
            return false;
        }

        return true;
    }

    public function productButtonText()
    {
        $default = t('Order Now');

        return empty($value = $this->designValue('product_button_text')) ?  $default : $value;
    }

    public function backButtonStyles()
    {
        return $this->select('.close-button.button')
            ->rule('background-color', $this->style('back_button_background_color'))
            ->rule('color', $this->style('back_button_text_color'))
            ->generate();
    }

    public function productButtonTarget()
    {
        $value = $this->designValue('product_button_target');

        if (empty($value)) return;

        if ($value === 'self') return;

        return 'target="_blank"';
    }

    public function productButtonStyles()
    {
        $backgroundColor = $this->designValue('product_button_color');

        $textColor = $this->designValue('product_button_text_color');

        $rules = [];

        if (!empty($backgroundColor)) {
            $rules[] = "background-color: $backgroundColor;";
        }

        if (!empty($textColor)) {
            $rules[] = "color: $textColor;";
        }

        $joinedRules = implode(' ', $rules);

        $selector = '.qrcode-type-product-catalogue .order-button';

        return "$selector { border: 0; $joinedRules }";
    }

    public function itemLayoutClass($item)
    {
        return $this->menuItemHorizontalLayout($item) ? 'horizontal' : '';
    }

    public function imageContainerStyleAttribute($item)
    {
        if (!$this->menuItemHorizontalLayout($item)) {
            return '';
        }

        return sprintf(
            'background-size: cover; background-image: url(%s); background-position: center;',
            $this->itemImage($item)
        );
    }

    public function itemImage($item)
    {
        return $this->findFileUrl(
            @$item['image'],
            override_asset('/assets/images/image-placeholder.svg', true)
        );
    }

    public function categoryPageStyles()
    {
        return collect($this->categories())
            ->map(function ($category) {
                $categoryPageSelector = sprintf(
                    '.qrcode-type-product-catalogue .layout-generated-webpage .category-page[slug="%1$s"]',
                    $category['id']
                );

                $color = $category['textColor'];

                $backgroundColor = $category['backgroundColor'];

                $showAllSelector = "$categoryPageSelector .sub-categories .sub-category.show-all";

                $subCategorySelector = sprintf(
                    '.qrcode-type-product-catalogue .layout-generated-webpage .category-page .sub-categories .sub-category[slug="%s"]',
                    $category['id']
                );

                $textRule =  "$categoryPageSelector .menu-item, $categoryPageSelector { color: $color; }";

                $menuItemNameRule = "$categoryPageSelector .menu-item .menu-item-title { color: $color; }";

                $variationsSelectRule = "$categoryPageSelector select { color: $color; }";

                $subCategoryRule = "$subCategorySelector { color: $color; background-color: $backgroundColor; }";

                $showAllRule = "$showAllSelector { color: $color; background-color: $backgroundColor; }";

                return implode("\n", [
                    $textRule,
                    $menuItemNameRule,
                    $variationsSelectRule,
                    $subCategoryRule,
                    $showAllRule
                ]);
            })->join("\n");
    }

    public function openingHours()
    {
        if ($this->qrcodeData('opening_hours_enabled') != 'enabled') {
            return [];
        }

        return $this->getOpeningHours(
            $this->qrcodeData('opening_hours')
        );
    }

    public function menuName()
    {
        $name = $this->designValue('catalogue_name');

        if (empty($name) || !is_string($name)) {
            return t("Our Catalogue");
        }

        return $name;
    }

    public function templateId()
    {
        return 'default';
    }

    public function logo()
    {
        return $this->fileUrl('logo') ?? override_asset(
            '/assets/images/product-catalogue/default/logo.svg'
        );
    }

    public function menuNameStyles()
    {
        $size = $this->designValue('catalogue_name_font_size');

        if (!is_numeric($size)) {
            $size = 50;
        }

        $defaultSize = 3;

        $maxSize = $defaultSize * 2;

        $fontSize = ($size / 100) * $maxSize;

        return sprintf(
            '.qrcode-type-product-catalogue .layout-generated-webpage .main-line { font-size: %srem; }',
            $fontSize
        );
    }

    public function gradientBgStyles()
    {
        return $this->generateGradientBackground(
            '.qrcode-type-product-catalogue .layout-generated-webpage'
        );
    }

    protected function socialIconsSelector()
    {
        return 'html .qrcode-type-product-catalogue .layout-generated-webpage .restaurant-details .social-icons';
    }

    public function mainPageTextStyles()
    {
        $color = $this->designValue('textColor');

        if (!$color) {
            return;
        }

        $selectors = [
            '.qrcode-type-product-catalogue .layout-generated-webpage .main-line',
            '.qrcode-type-product-catalogue .layout-generated-webpage .restaurant-details',
            '.qrcode-type-product-catalogue .layout-generated-webpage .restaurant-details a',
        ];

        return sprintf(
            implode(', ', $selectors) . ' { color: %s; }',
            $color
        );
    }

    public function categoriesStyles()
    {
        $categories = $this->categories();

        if (empty($categories)) return;

        $styles = collect($categories)->map(function ($category) {

            return sprintf(
                'html .qrcode-type-product-catalogue .layout-generated-webpage .menu-category[slug="%s"] { background-color: %s; color: %s; }',
                $category['id'],
                $category['backgroundColor'],
                $category['textColor']
            );
        });

        return $styles->join("\n");
    }

    public function shouldShowLogo()
    {
        $empty = empty(file_url($this->designField('logo')));

        if (!$empty) {
            return true;
        }

        if (
            config('qrcode.show_restaurant_menu_logo') == 'when-uploaded' && $empty
        ) {
            return false;
        }

        return true;
    }

    public function isItemEnabled($item)
    {
        return empty($item['mode']) || $item['mode'] === 'enabled';
    }

    public function renderImageCarousel()
    {
        return ImageCarousel::withData(
            $this->designField('image_carousel')
        )->render();
    }

    public function buy($item = null)
    {
        if ($item) {
            return BuyButton::withQRCode($this->qrcode)
                ->item($item);
        }

        return BuyButton::withQRCode($this->qrcode);
    }
}
