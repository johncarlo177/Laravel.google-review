<?php

namespace App\Support\QRCodeTypes;

use App\Models\QRCode;
use App\Support\Crypto\Crc16;
use Normalizer;

class BrazillianPIX extends BaseType
{
    public static function slug(): string
    {
        return 'brazilpix';
    }

    public static function name(): string
    {
        return t('Brazillian PIX');
    }

    public function rules(): array
    {
        return [
            'key' => 'required',
            'name' => 'required',
            'city' => 'required',
            'amount' => 'required'
        ];
    }

    public function makeData(QRCode $qrcode): string
    {
        $vars = [
            'key',
            'name',
            'city',
            'transaction_id',
            'amount',
            'message'
        ];

        foreach ($vars as $var) {
            $$var = '';
            if (isset($qrcode->data->$var)) {
                $$var = trim($qrcode->data->$var);
            }
        }

        $version = '01';

        $countryCode = 'BR';

        $payloadKeyString = $this->generateKey($key, $message);

        $payload = [
            $this->genEMV('00', $version),
            $this->genEMV('26', $payloadKeyString),
            $this->genEMV('52', '0000'),
            $this->genEMV('53', '986'),
        ];

        if (!empty($amount)) {
            $payload[] = $this->genEMV('54', $this->toFixed($amount, 2));
        }

        $name = $this->normalize($name, 25);

        $city = $this->normalize($city, 15);

        $payload[] = $this->genEMV('58', $countryCode);

        $payload[] = $this->genEMV('59', $name);

        $payload[] = $this->genEMV('60', $city);

        $payload[] = $this->genEMV(
            '62',
            $this->genEMV(
                '05',
                $transaction_id
            )
        );

        $payload[] = '6304';

        $payloadString = implode('', $payload);

        $crc16CCITT = strtoupper(
            (new Crc16(
                $payloadString,
                0x1021,
                0xffff,
                0x0000,
                false
            ))->toHexString()
        );

        $result = "$payloadString$crc16CCITT";

        return $result;
    }

    private function generateKey($key, $message)
    {
        $payload = [
            $this->genEMV('00', 'BR.GOV.BCB.PIX'),
            $this->genEMV('01', $key)
        ];

        if ($message) {
            $payload[] = $this->genEMV('02', $message);
        }

        return implode('', $payload);
    }

    private function genEMV($id, $parameter)
    {
        $len = str_pad(strlen($parameter), 2, '0', STR_PAD_LEFT);
        return "$id$len$parameter";
    }

    private function toFixed($number, $fractionDigits)
    {
        $number = preg_replace('/[^\d\.]/', '', $number);

        return number_format($number, $fractionDigits, '.', "");
    }

    private function normalize($string, $length)
    {
        $string = strtoupper(
            substr($string, 0, $length)
        );

        $string = Normalizer::normalize(
            $string,
            Normalizer::NFD
        );

        $string = preg_replace('/[\x{0300}-\x{036f}]/u', '', $string);

        return $string;
    }
}
