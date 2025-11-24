<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\QRCode;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function permission($slug)
    {
        return Permission::where('slug', $slug)->first();
    }

    protected function addPerm($role, $slug)
    {
        $role->permissions()->save($this->permission($slug));
    }

    protected function newUserWithTestRole()
    {
        $user = User::factory(1)->create()[0];

        $role = $this->makeRole();

        $user->roles()->save($role);

        return [$user, $role];
    }

    protected function makeRole()
    {
        $role = Role::where('name', 'Test role')->first();

        if ($role) {
            $role->delete();
        } else {
            $role = new Role();
        }

        $role->name = 'Test role';

        $role->save();

        return $role;
    }

    protected function useChromeUserAgent()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/106.0.0.0 Safari/537.36';
    }
}
