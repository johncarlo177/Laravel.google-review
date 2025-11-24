<?php

namespace App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks;

use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\Style\CssRuleGenerator;
use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\Style\TextFontStyle;

class FAQsBlock extends BaseBlock
{
    public static function slug()
    {
        return 'faqs';
    }

    protected function shouldRender(): bool
    {
        return $this->model->notEmpty('faqs');
    }

    public function blockStyles()
    {
        return CssRuleGenerator::withSelector(
            $this->blockSelector('.faqs-card')
        )
            ->withModel($this->model)
            ->rule('background-color', 'background_color')
            ->rule('color', 'text_color')
            ->rule('border-radius', 'border_radius', 'px')
            ->rule('padding', 'block_padding', 'px')
            ->generate();
    }

    public function faqItemStyles()
    {
        return CssRuleGenerator::withSelector(
            $this->blockSelector('.faqs-card .faq-item')
        )
            ->withModel($this->model)
            ->rule('border-color', 'border_color')
            ->generate();
    }

    public function iconStyles()
    {
        return CssRuleGenerator::withSelector(
            $this->blockSelector('.faqs-card .faq-title svg path')
        )
            ->withModel($this->model)
            ->rule('fill', 'icon_color')
            ->generate();
    }

    public function questionTextStyles()
    {
        return TextFontStyle::withSelector(
            $this->blockSelector('.faqs-card .faq-title')
        )
            ->withModel($this->model)
            ->generate();
    }

    public function answerTextStyles()
    {
        return TextFontStyle::withSelector(
            $this->blockSelector('.faqs-card .faq-description')
        )
            ->withModel($this->model)
            ->withPrefix('answer')
            ->generate();
    }

    public function answerColorStyles()
    {
        return CssRuleGenerator::withSelector(
            $this->blockSelector('.faqs-card .faq-description')
        )
            ->withModel($this->model)
            ->rule('color', 'answer_color')
            ->generate();
    }

    public function titleTextStyles()
    {
        return TextFontStyle::withSelector(
            $this->blockSelector('.faqs-card .faq-main-title')
        )
            ->withPrefix('title')
            ->withModel($this->model)
            ->generate();
    }

    public function subTitleTextStyles()
    {
        return TextFontStyle::withSelector(
            $this->blockSelector('.faqs-card .faq-main-subtitle')
        )
            ->withPrefix('subtitle')
            ->withModel($this->model)
            ->generate();
    }

    public function subTitleColorStyles()
    {
        return CssRuleGenerator::withSelector(
            $this->blockSelector('.faqs-card header .faq-main-subtitle')
        )
            ->withModel($this->model)
            ->rule('color', 'subtitle_color')
            ->generate();
    }

    public function titleColorStyles()
    {
        return CssRuleGenerator::withSelector(
            $this->blockSelector('.faqs-card header')
        )
            ->withModel($this->model)
            ->rule('color', 'title_color')
            ->rule('border-color', 'title_border_color')
            ->generate();
    }

    public function renderLinkStyles()
    {
        return CssRuleGenerator::withSelector(
            $this->blockSelector('.faqs-card .button')
        )->withModel($this->model)
            ->rule('background-color', 'link_background_color')
            ->rule('border-color', 'link_background_color')
            ->rule('color', 'link_text_color')
            ->rule('border-radius', 'link_border_radius', 'px')
            ->rule('width', 'link_width', '%')
            ->generate();
    }

    public function renderLinkFontStyles()
    {
        return TextFontStyle::withSelector(
            $this->blockSelector('.faqs-card .button')
        )->withModel($this->model)
            ->withPrefix('link')
            ->generate();
    }
}
