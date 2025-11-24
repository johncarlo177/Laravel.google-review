<?php

namespace App\Notifications\Dynamic;

use App\Interfaces\UserManager;
use App\Models\User;

class WelcomeAdmin extends Base
{
    protected User $customer;

    protected User $admin;

    public static function withCustomer(User $customer)
    {
        $instance = new static;

        $instance->customer = $customer;

        return $instance;
    }

    public function withAdmin(User $user)
    {

        $this->admin = $user;

        return $this;
    }

    public function slug()
    {
        return 'welcome-admin';
    }

    public function notify()
    {
        $users = app(UserManager::class);

        $users->getSuperAdmins()
            ->each(function (User $admin) {
                $this->withAdmin($admin)
                    ->send($admin);
            });
    }

    protected function configVariables()
    {
        return [
            'CUSTOMER_EMAIL' => $this->customer->email,
            'CUSTOMER_NAME' => $this->customer->name,
            'APP_NAME' => config('app.name'),
            'ADMIN_NAME' => $this->admin->name,
            'USER_MANAGEMENT_URL' => url('/dashboard/users/edit/' . $this->customer->id),
            'USERS_SCREEN_URL' => url('/dashboard/users'),
        ];
    }

    public function defaultEmailSubject()
    {
        return 'New User Registration';
    }

    public function defaultEmailBody()
    {
        return <<<END_TEMPLATE
# 🆕 New User Registration

Hello ADMIN_NAME,

A new user has just registered on **APP_NAME**.

---

### 👤 User Details
- **Name:** CUSTOMER_NAME  
- **Email:** CUSTOMER_EMAIL
---

### 🧭 Next Steps
You can view and manage this user's account in the [Admin Dashboard](USER_MANAGEMENT_URL).  

If this registration seems suspicious, you may delete the newly regisetered user from the [Users Page](USERS_SCREEN_URL).

END_TEMPLATE;
    }

    public function defaultSmsBody()
    {
        return '';
    }
}
