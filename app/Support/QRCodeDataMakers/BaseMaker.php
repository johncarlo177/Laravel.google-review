<?php

namespace App\Support\QRCodeDataMakers;

use Illuminate\Support\Str;
use App\Interfaces\QRCodeDataMaker;
use App\Models\QRCode;
use InvalidArgumentException;
use App\Exceptions\NotImplementedException;
use Illuminate\Support\Facades\Validator;


class BaseMaker implements QRCodeDataMaker
{
    protected QRCode $qrcode;

    public static function instance(QRCode $qrcode): static
    {
        $base = new static;

        $base->init($qrcode);

        $base->verifyType();

        $maker = $base->getTypeClass();

        $makerObject = new $maker;

        $makerObject->init($qrcode);

        return $makerObject;
    }

    public function init(QRCode $qrcode): QRCodeDataMaker
    {
        $this->qrcode = $qrcode;

        return $this;
    }

    public function make(): string
    {
        if (!$this->verify()) return '';

        $this->validate();

        return $this->makeData();
    }

    protected function makeData(): string
    {
        throw new NotImplementedException();
    }

    protected function validate()
    {
        if (empty($this->rules())) return;

        $validator = Validator::make(
            (array)$this->qrcode->data,
            $this->rules()
        );

        $validator->validate();
    }

    protected function rules()
    {
        return [];
    }

    /** @deprecated use validated instead */
    protected function verify()
    {
        return true;
    }

    private function verifyType()
    {
        $maker = $this->getTypeClass();

        if (!class_exists($maker)) {
            throw new InvalidArgumentException("Type is not supported ({$this->qrcode->type})");
        }
    }

    private function getTypeClass()
    {
        $type = $this->qrcode->type;

        $maker = __NAMESPACE__ . '\\' . Str::studly($type) . 'Maker';

        return $maker;
    }
}
