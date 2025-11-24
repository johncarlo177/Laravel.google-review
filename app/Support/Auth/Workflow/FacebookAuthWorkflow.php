<?php

namespace App\Support\Auth\Workflow;


class FacebookAuthWorkflow extends BaseWorkflow
{

    public static function name(): string
    {
        return 'facebook';
    }


    protected function socialiteConfigs(): array
    {
        return [
            'client_id',
            'client_secret'
        ];
    }
}
