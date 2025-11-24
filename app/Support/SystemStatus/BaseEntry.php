<?php

namespace App\Support\SystemStatus;

abstract class BaseEntry implements EntryInterface
{
    public function information()
    {
        return $this->isSuccess() ? $this->informationText() : '';
    }

    public function instructions()
    {
        return !$this->isSuccess() ? $this->instructionsText() : '';
    }

    protected abstract function instructionsText();

    protected abstract function informationText();

    protected abstract function isSuccess();

    public function sortOrder()
    {
        return 100;
    }

    public function type()
    {
        return $this->isSuccess() ? $this::TYPE_SUCCESS : $this::TYPE_FAIL;
    }
}
