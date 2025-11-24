<?php

namespace App\Support;

class QRCodeErrorPage
{

    public const TYPE_DISABLED = 'disabled';
    public const TYPE_EXPIRED = 'expired';
    public const TYPE_ARCHIVED = 'archived';
    public const TYPE_STATIC = 'static';
    public const TYPE_SERVER = 'server';
    public const TYPE_ALLOWED_SCANS_LIMIT_REACHED = 'allowed_scans_limit_reached';

    protected $title, $content, $type = '';

    public static function withTitle($title)
    {
        $instance = new static;

        $instance->title = $title;

        return $instance;
    }

    public function withContent($content)
    {
        $this->content = $content;

        return $this;
    }

    public function withType($type)
    {
        $this->type = $type;

        return $this;
    }


    protected function getTitle()
    {
        if (!$this->type) {
            return $this->title;
        }

        return config(
            sprintf(
                'qrcode.%s_page_title',
                $this->type
            )
        ) ?:  $this->title;
    }

    protected function getContent()
    {
        if (!$this->type) {
            return $this->content;
        }

        return config(
            sprintf(
                'qrcode.%s_page_content',
                $this->type
            )
        ) ?: $this->content;
    }

    public function render()
    {
        return view('blue.pages.qrcode-error', [
            'title' => $this->getTitle(),
            'meta_description' => $this->getTitle(),
            'content' => $this->getContent(),
            'error_type' => $this->type,
        ]);
    }
}
