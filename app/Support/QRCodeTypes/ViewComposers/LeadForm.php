<?php

namespace App\Support\QRCodeTypes\ViewComposers;


use App\Support\LeadFormManager;
use App\Support\System\Traits\WriteLogs;

class LeadForm extends Base
{
    use WriteLogs;

    private LeadFormManager $leadForms;

    public function __construct()
    {
        parent::__construct();

        $this->leadForms = app(LeadFormManager::class);
    }

    public static function type()
    {
        return 'lead-form';
    }

    public function leadFormId()
    {
        $id = (int) $this->designValue('lead_form_id');

        return $id;
    }
}
