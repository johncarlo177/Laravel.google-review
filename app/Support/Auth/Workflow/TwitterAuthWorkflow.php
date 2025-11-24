<?php

namespace App\Support\Auth\Workflow;


use App\Support\System\Traits\WriteLogs;


class TwitterAuthWorkflow extends BaseWorkflow
{
    use WriteLogs;

    public static function name(): string
    {
        return 'twitter';
    }

    protected function socialiteConfigs(): array
    {
        return [
            'client_id',
            'client_secret'
        ];
    }
}
