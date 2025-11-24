<?php

namespace App\Http\Controllers;

use App\Models\CustomForm;
use App\Models\CustomFormResponse;
use App\Support\System\Traits\WriteLogs;

class BillingCollectionController extends Controller
{
    use WriteLogs;

    //

    public function isBillingCollectionEnabled()
    {
        $value = config('billing_collection_enabled');

        return [
            'result' => $value === 'enabled'
        ];
    }

    public function getForm($formSlug)
    {
        $key = 'billing_' . $formSlug . '_form';

        $value = config($key);

        return ['result' => $value];
    }

    public function getLatestBillingFormResponseId()
    {
        $formIds = [
            config('billing_private_form'),
            config('billing_company_form'),
        ];

        $query = CustomFormResponse::where(
            'user_id',
            $this->optionalUser()?->id
        )
            ->whereIn('custom_form_id', $formIds)
            ->orderByDesc('id');

        $ids = $query->pluck('id');

        return [
            'id' => @$ids[0]
        ];
    }
}
