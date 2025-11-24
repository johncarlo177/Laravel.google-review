<?php

namespace App\Notifications\Dynamic;

use App\Models\CustomFormResponse;

use Illuminate\Support\Str;

class CustomFormResponseNotification extends Base
{
    private CustomFormResponse $response;

    public static function instance(CustomFormResponse $response)
    {
        $instance = new static;

        $instance->response = $response;

        return $instance;
    }

    public function slug()
    {
        return 'custom-form-response';
    }

    protected function configVariables()
    {
        return [
            'FORM_RESPONSE' => $this->buildFormResponse(),
        ];
    }

    private function buildFormResponse()
    {
        $fields = $this->response->fields;

        $fields = array_map(function ($field) {

            return sprintf('**%s** %s', trim(@$field['name']), trim(@$field['value']));
        }, $fields);

        $markdown = implode("\n\n", $fields);

        return Str::markdown($markdown);
    }

    public function defaultEmailSubject()
    {
        return 'New response received.';
    }

    protected function getEmailSubject()
    {
        return sprintf(
            '[%s] %s',
            $this->response->custom_form->name,
            parent::getEmailSubject()
        );
    }

    public function defaultEmailBody()
    {
        return <<<END_TEMPLATE
# Hello,

You have received a new response, details can be found below:

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
