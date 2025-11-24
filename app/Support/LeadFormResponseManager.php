<?php

namespace App\Support;

use App\Events\LeadFormResponseReceived;
use App\Models\LeadForm;
use App\Models\LeadFormResponse;


class LeadFormResponseManager
{
    private LeadFormManager $forms;

    public function __construct()
    {
        $this->forms = new LeadFormManager();
    }

    public function store($lead_form_id, $fields, $fingerprint = null, $ip = null, $user_agent = null)
    {
        $response = new LeadFormResponse();

        $response->lead_form_id = $lead_form_id;

        $response->fields = $fields;

        $response->fingerprint = $fingerprint;

        $response->ip = $ip;

        $response->user_agent = $user_agent;

        $response->save();

        event(new LeadFormResponseReceived($response));

        return $response;
    }

    public function findFingerprint($lead_form_id, $fingerprint): ?LeadFormResponse
    {
        $response = LeadFormResponse::where('lead_form_id', $lead_form_id)
            ->where('fingerprint', $fingerprint)
            ->first();

        return $response;
    }

    public function ofForm(LeadForm $leadForm)
    {
        return LeadFormResponse::where('lead_form_id', $leadForm->id)
            ->orderBy('id', 'desc')
            ->get();
    }

    public function getQRCode(LeadFormResponse $response)
    {
        return $this->forms->getQRCode($response->lead_form);
    }
}
