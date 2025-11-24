<?php

namespace App\Models;

use App\Http\Resources\RoleResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * @property int id
 * @property string name
 * @property string home_page
 * @property bool super_admin
 * @property bool is_default_role_for_new_signup
 * @property Collection<Permission> permissions
 * @property Carbon created_at
 */
class Role extends Model
{
    use HasFactory;

    protected $casts = [
        'super_admin' => 'boolean',
        'is_default_role_for_new_signup' => 'boolean'
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }

    public function isReadOnly()
    {
        return preg_match('/^(admin|client|sub user|reseller)$/i', $this->name);
    }

    public function resource()
    {
        return new RoleResource($this);
    }

    public function setPermssions($ids)
    {
        $this->clearPermissions();
        $this->addPermissions($ids);
    }

    public function clearPermissions()
    {
        DB::table('role_permissions')->where('role_id', $this->id)->delete();
    }

    public function addPermissions($ids)
    {
        DB::table('role_permissions')->insert(
            collect($ids)->map(function ($permissionId) {
                return [
                    'permission_id' => $permissionId,
                    'role_id' => $this->id,
                ];
            })->all()
        );
    }

    public static function clientRole()
    {
        return static::where('name', 'Client')->first();
    }

    public static function superAdminRole()
    {
        return static::where('name', 'Admin')->first();
    }

    public static function subUserRole()
    {
        return static::where('name', 'Sub User')->first();
    }
}
