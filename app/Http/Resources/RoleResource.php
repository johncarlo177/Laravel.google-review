<?php

namespace App\Http\Resources;

use App\Models\Role;


class RoleResource extends BaseResource
{
    protected function _toArray()
    {
        return [
            'id' => $this->role()->id,
            'name' => $this->role()->name,
            'permission_ids' => $this->role()->permissions->pluck('id'),
            'read_only' => $this->role()->isReadOnly(),
            'created_at' => $this->role()->created_at,
            'permission_count' => $this->role()->permissions()->count(),
            'user_count' => $this->role()->users()->count(),
            'super_admin' => $this->role()->super_admin,
            'home_page' => $this->role()->home_page
        ];
    }

    protected function role(): Role
    {
        return $this->resource;
    }
}
