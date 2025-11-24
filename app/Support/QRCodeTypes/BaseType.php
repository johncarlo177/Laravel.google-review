<?php

namespace App\Support\QRCodeTypes;

use App\Models\Config;
use App\Models\QRCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

abstract class BaseType
{

    public static abstract function slug(): string;

    public static abstract function name(): string;

    public function generateName(QRCode $qrcode): string
    {
        return ucfirst($this->slug());
    }

    public function isDynamic(): bool
    {
        return false;
    }

    public abstract function rules(): array;

    public abstract function makeData(QRCode $qrcode): string;

    public function shouldCacheView()
    {
        return false;
    }

    public function validate(Request $request)
    {
        if (empty($this->rules())) return;

        $validator = Validator::make(
            $request->input('data'),
            $this->rules()
        );

        $this->extendValidator($validator);

        $validator->validate();
    }

    protected function extendValidator(\Illuminate\Validation\Validator $validator) {}

    public function sortOrder()
    {
        return Config::get(sprintf('qrType.%s.sort_order', $this::slug()));
    }
}
