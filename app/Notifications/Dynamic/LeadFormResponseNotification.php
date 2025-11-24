<?php

namespace App\Notifications\Dynamic;

use App\Models\LeadFormResponse;
use Illuminate\Support\Str;

class LeadFormResponseNotification extends Base
{
    private LeadFormResponse $leadFormResponse;

    public static function instance(LeadFormResponse $leadFormResponse)
    {
        $instance = new static;

        $instance->leadFormResponse = $leadFormResponse;

        return $instance;
    }

    public function slug()
    {
        return 'lead-form-response';
    }

    protected function configVariables()
    {
        return [
            'FORM_RESPONSE' => $this->buildFormResponse(),
        ];
    }

    private function buildFormResponse()
    {
        $fields = $this->leadFormResponse->fields;

        $fields = array_map(function ($field) {

            return sprintf('**%s** %s', trim(@$field['question']), trim(@$field['value']));
        }, $fields);

        $markdown = implode("\n\n", $fields);

        return Str::markdown($markdown);
    }

    public function defaultEmailSubject()
    {
        return 'You have received a new lead :)';
    }

    protected function getEmailSubject()
    {
        return sprintf(
            '[%s] %s',
            $this->leadFormResponse->lead_form->resolveQRCode()->name,
            parent::getEmailSubject()
        );
    }

    public function defaultEmailBody()
    {
        return <<<END_TEMPLATE
# Hello,

You have received a new lead response, details can be found below:

FORM_RESPONSE


END_TEMPLATE;
    }

    public function defaultSmsBody()
    {
        return <<<TEMPLATE
# Hello,

You have received a new lead response, details can be found below:

FORM_RESPONSE

TEMPLATE;
    }
}
