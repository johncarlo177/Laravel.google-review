<?php

namespace App\Support\QRCodeReports;

use Illuminate\Support\Facades\DB;

class ScansPerOperatingSystem extends BaseReport
{
    private $initialOSNames = ['Android', 'iOS'];

    public function slug(): string
    {
        return 'scans-per-operating-system';
    }

    protected function reportColumn()
    {
        return 'os_name';
    }

    protected function padResult()
    {
        if ($this->result->isEmpty()) {
            return;
        }

        $names = collect($this->initialOSNames);

        $names->each(function ($name) {

            $found = $this->result->first(function ($item) use ($name) {
                return $item['os_name'] == $name;
            });

            if (!$found) {
                $this->result->push([
                    'os_name' => $name,
                    'scans' => 0
                ]);
            }
        });
    }
}
