<?php

namespace App\Notifications\Dynamic;

use App\Models\User;
use App\Support\System\Traits\WriteLogs;

class WelcomeCustomer extends Base
{
    use WriteLogs;

    protected User $customer;

    public static function withCustomer(User $customer)
    {
        $instance = new static;

        $instance->customer = $customer;

        return $instance;
    }

    public function slug()
    {
        return 'welcome-customer';
    }

    public function notify()
    {
        $this->logDebug('Notifying %s', $this->customer->email);

        $this->send($this->customer);
    }

    protected function configVariables()
    {
        return [
            'CUSTOMER_EMAIL' => $this->customer->email,
            'CUSTOMER_NAME' => $this->customer->name,
            'APP_NAME' => config('app.name'),
            'WEBSITE_URL' => url('/'),
        ];
    }

    public function defaultEmailSubject()
    {
        return 'Welcome To APP_NAME';
    }

    public function defaultEmailBody()
    {
        return <<<END_TEMPLATE
# 🤖 Welcome to APP_NAME!

Hi CUSTOMER_NAME,

Welcome to **APP_NAME**, your new home for creating **artistic and fully customizable QR codes**! 🎉  

Our platform lets you design QR codes that truly stand out — from stylish personal designs to professional marketing materials.  
With support for **32 different QR code types**, including both **dynamic** and **static** options, you'll have the flexibility to create exactly what you need for any project or purpose.

Here's what makes us special:
- **Beautiful, customizable designs** that reflect your brand or personality  
- **32 QR code types** to cover every use case — from links and text to files and contact info  
- **Dynamic QR codes** that can be updated anytime, and **static codes** for quick, permanent sharing  

We're thrilled to have you with us and can't wait to see your creativity in action.  
If you have any questions or need assistance, feel free to reply to this email.

Thanks for joining **APP_NAME** — where functionality meets creativity, one QR code at a time.  

END_TEMPLATE;
    }

    public function defaultSmsBody()
    {
        return '';
    }
}
