<?php

namespace App\Support;

class WidgetManager
{
    public function getWidgetScriptVersion()
    {
        $path = public_path('integrations/widget.js');

        if (!file_exists($path)) {
            return '1';
        }

        return filemtime($path);
    }
}
