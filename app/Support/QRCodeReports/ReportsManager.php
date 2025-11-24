<?php

namespace App\Support\QRCodeReports;

use App\Support\System\Traits\ClassListLoader;

class ReportsManager
{
    use ClassListLoader;

    private function getDir()
    {
        return __DIR__;
    }

    private function getNameSpace()
    {
        return __NAMESPACE__;
    }

    public static function report($slug): BaseReport
    {
        $manager = new static;

        /** @var ?BaseReport */
        $instance = collect($manager->getReports())->first(function ($report) use ($slug) {
            /** @var BaseReport */
            $report = $report;

            return $report->slug() == $slug;
        });

        if (!$instance) {
            abort(404, t('Report not found'));
        }

        return $instance;
    }

    private function getReports()
    {
        return $this->makeInstancesOfInstantiableClassesInCurrentDirectory("ReportsManager");
    }
}
