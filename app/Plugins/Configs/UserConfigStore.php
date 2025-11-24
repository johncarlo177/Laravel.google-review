<?php

namespace App\Plugins\Configs;

use App\Models\User;

class UserConfigStore extends ConfigStore
{
    protected ?User $user = null;

    public function withRequestUser()
    {
        $this->user = request()->user();

        return $this;
    }

    public function withUser(?User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    protected function shouldUseGlobalConfigs()
    {
        return !$this->user || $this->user?->isSuperAdmin();
    }

    protected function key($key)
    {
        if ($this->shouldUseGlobalConfigs()) {
            return parent::key($key);
        }

        return sprintf('%s-user-%s', parent::key($key), $this->user->id);
    }
}
