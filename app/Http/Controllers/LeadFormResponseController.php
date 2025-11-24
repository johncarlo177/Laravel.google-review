<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadFormResponseRequest;
use App\Models\LeadForm;
use App\Models\LeadFormResponse;
use App\Support\LeadFormResponseManager;
use Illuminate\Http\Request;

class LeadFormResponseController extends Controller
{
    private LeadFormResponseManager $leads;

    public function __construct(LeadFormResponseManager $leads)
    {
        $this->leads = $leads;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreLeadFormResponseRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreLeadFormResponseRequest $request)
    {
        if (isLocal()) {
            sleep(100);
        }

        return $this->leads->store(
            lead_form_id: $request->input('lead_form_id'),
            fields: json_decode($request->input('fields')),
            fingerprint: $request->input('fingerprint'),
            ip: $request->ip(),
            user_agent: $request->userAgent()
        );
    }

    public function checkFingerprint(Request $request)
    {
        $found = $this->leads->findFingerprint(
            lead_form_id: $request->input('lead_form_id'),
            fingerprint: $request->input('fingerprint')
        );

        return [
            'found' => !empty($found),
        ];
    }

    public function ofLeadForm(LeadForm $leadForm)
    {
        return $this->leads->ofForm($leadForm);
    }

    public function destroy(Request $request, LeadFormResponse $response)
    {
        $this->authorizeDestroy($request, $response);

        $response->delete();

        return $response;
    }

    private function authorizeDestroy(Request $request, LeadFormResponse $response)
    {
        /**
         * @var \App\Models\User
         */
        $user = $request->user();

        if ($user->isSuperAdmin()) return true;

        $qrcode = $this->leads->getQRCode($response);

        $authorized = $user->id == $qrcode->id;

        if (!$authorized) {
            abort(401);
        }
    }
}
