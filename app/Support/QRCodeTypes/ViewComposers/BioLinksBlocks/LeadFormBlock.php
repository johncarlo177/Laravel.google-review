<?php

namespace App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks;

class LeadFormBlock extends LinkBlock
{
    public static function slug()
    {
        return 'lead-form';
    }

    protected function shouldRender(): bool
    {
        return $this->model->notEmpty('lead_form_id');
    }
}
