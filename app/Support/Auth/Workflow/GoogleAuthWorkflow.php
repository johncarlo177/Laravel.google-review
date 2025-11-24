<?php

namespace App\Support\Auth\Workflow;


use App\Support\System\Traits\WriteLogs;


class GoogleAuthWorkflow extends BaseWorkflow
{
    use WriteLogs;

    public static function name(): string
    {
        return 'google';
    }

    protected function socialiteConfigs(): array
    {
        return [
            'client_id',
            'client_secret'
        ];
    }
}
