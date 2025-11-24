<?php

namespace App\Support\QRCodeTypes\ViewComposers\Traits;

trait CombinesStylesMethods
{
    public function styles()
    {
        $rules = array_filter(
            $this->getStylesMethods()->map(
                fn ($method) => $this->{$method}()
            )->all()
        );

        if (empty($rules)) return;

        return sprintf('<style>%s</style>', implode("\n", $rules));
    }

    private function getStylesMethods()
    {
        $methods = get_class_methods($this);

        return collect($methods)
            ->filter(
                fn ($m) => preg_match('/.*Styles$/', $m)
            )->values();
    }
}
