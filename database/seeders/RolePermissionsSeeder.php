<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class RolePermissionsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->seedClientRolePermissions();

        $this->seedSubUserRolePermissions();

        $this->seedResellerRolePermissions();
    }

    protected function seedResellerRolePermissions()
    {
        // 
        $role = Role::whereName('Reseller')->first();

        $this->clearRolePermissions($role);

        $premissions = [
            'qrcode.list',
            'qrcode.show',
            'qrcode.update',
            'qrcode.store',
            'qrcode.archive',
            'qrcode.showStats',
            'qrcode.destroy',
            'qrcode.pin-code',
            'subscription.store',
            'subscription.update',
            'file.show',
            'file.destroy',
            'qrcode-redirect.update',
            'domain.add',
            'domain.destroy',
            'domain.show',
            'domain.updateStatus',
            'folder.list',
            'folder.store',
            'folder.update',
            'folder.destroy',
            'user.invite',
            'lead-form.show',
            'lead-form.store',
            'lead-form.update',
            'lead-form.list',
            'affiliate.refer',
            'qrcode-template.list',
            'qrcode-template.manage',
            'qrcode-template.use',
            'template-category.list-all',
            'user.list-all',
            'user.store',
            'user.show-any',
            'user.update-any',
            'user.destroy-any',

            'subscription.list-all',
            'subscription.store',
            'subscription.show-any',
            'subscription.update-any',
            'subscription.destroy-any',
        ];

        $permissions = Permission::whereIn('slug', $premissions)->get();

        foreach ($permissions as $permission) {
            $role->permissions()->save($permission);
        }
    }

    private function seedSubUserRolePermissions()
    {
        $role = Role::whereName('Sub User')->first();

        $this->clearRolePermissions($role);

        $premissions = [
            'qrcode.list',
            'qrcode.show',
            'qrcode.update',
            'qrcode.store',
            'qrcode.showStats',
            'folder.list',
        ];

        $permissions = Permission::whereIn('slug', $premissions)->get();

        foreach ($permissions as $permission) {
            $role->permissions()->save($permission);
        }
    }

    private function seedClientRolePermissions()
    {
        $defaultRole = Role::where('is_default_role_for_new_signup', true)->first();

        $this->clearRolePermissions($defaultRole);

        $defaultPermissions = Permission::whereIn('slug', [
            'qrcode.list',
            'qrcode.show',
            'qrcode.update',
            'qrcode.store',
            'qrcode.archive',
            'qrcode.showStats',
            'qrcode.destroy',
            'qrcode.pin-code',
            'subscription.store',
            'subscription.update',
            'file.show',
            'file.destroy',
            'qrcode-redirect.update',
            'domain.add',
            'domain.destroy',
            'domain.show',
            'domain.updateStatus',
            'folder.list',
            'folder.store',
            'folder.update',
            'folder.destroy',
            'user.invite',
            'lead-form.show',
            'lead-form.store',
            'lead-form.update',
            'lead-form.list',
            'affiliate.refer',
            'qrcode-template.list',
            'qrcode-template.manage',
            'qrcode-template.use',
            'template-category.list-all',
        ])->get();

        foreach ($defaultPermissions as $permission) {
            $defaultRole->permissions()->save($permission);
        }
    }

    private function clearRolePermissions(Role $role)
    {
        $role->permissions()->detach($role->permissions->pluck('id'));
    }
}
