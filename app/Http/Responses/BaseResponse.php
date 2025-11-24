<?php

namespace App\Http\Responses;


abstract class BaseResponse
{
    protected $singleResponse = false;

    protected abstract function singleRecordToArray($record): array;

    protected function listRecordToArray($record): array
    {
        return $this->singleRecordToArray($record);
    }

    public static function list($records)
    {
        $instance = new static;

        $instance->singleResponse = false;

        return collect($records)->map(
            fn($record) => $instance->toArray($record)
        );
    }

    public static function single($record)
    {
        $instance = new static;

        $instance->singleResponse = true;

        return $instance->toArray($record);
    }

    protected function toArray($record)
    {
        if ($this->singleResponse) {
            return $this->singleRecordToArray($record);
        }

        return $this->listRecordToArray($record);
    }
}
