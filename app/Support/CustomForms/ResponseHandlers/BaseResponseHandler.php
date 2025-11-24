<?php

namespace App\Support\CustomForms\ResponseHandlers;

use App\Models\CustomForm;
use App\Models\CustomFormResponse;
use App\Support\System\Traits\WriteLogs;

abstract class BaseResponseHandler
{
    use WriteLogs;

    protected ?CustomFormResponse $response = null;

    protected ?CustomForm $form = null;

    /**
     * @return string type pattern which can be handled
     */
    public abstract function type();

    protected abstract function handle();

    public final function handleResponse(CustomFormResponse $response)
    {
        $this->response =  $response;

        $this->form = $response->custom_form;

        return $this->handle();
    }
}
