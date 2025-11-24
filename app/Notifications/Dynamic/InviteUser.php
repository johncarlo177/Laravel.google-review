<?php

namespace App\Notifications\Dynamic;

use InvalidArgumentException;

class InviteUser extends Base
{
    private string $accountOwner;
    private string $generatedPassword;
    private string $folderName;

    public static function instance(string $accountOwner, string $generatedPassword, string $folderName)
    {
        $instance = new static;
        $instance->accountOwner = $accountOwner;
        $instance->generatedPassword = $generatedPassword;
        $instance->folderName = $folderName;

        return $instance;
    }

    public function slug()
    {
        return 'invite-user';
    }

    protected function configVariables()
    {
        if (empty($this->generatedPassword)) {
            throw new InvalidArgumentException(class_basename(static::class) . ' must be created using ::instance function.');
        }

        return [
            'LOGIN_URL' => url('/account/login'),
            'ACCOUNT_OWNER' => $this->accountOwner,
            'APP_NAME' => config('app.name'),
            'INVITED_EMAIL_ADDRESS' => $this->notifiable->email,
            'GENERATED_PASSWORD' => $this->generatedPassword,
            'FOLDER_NAME' => $this->folderName,
            'INVITED_USER_NAME' => $this->notifiable->name,
        ];
    }

    public function defaultEmailSubject()
    {
        return 'You are invited to APP_NAME';
    }

    public function defaultEmailBody()
    {
        return <<<END_TEMPLATE
# Hello INVITED_USER_NAME,

ACCOUNT_OWNER invited you to participate in **FOLDER_NAME** folder. 

Here is your login details:

Login URL: LOGIN_URL

Email: **INVITED_EMAIL_ADDRESS**

Password: **GENERATED_PASSWORD**

END_TEMPLATE;
    }

    public function defaultSmsBody()
    {
        return <<<TEMPLATE
ACCOUNT_OWNER invited you to participate in FOLDER_NAME folder.

Login URL: LOGIN_URL

Email: INVITED_EMAIL_ADDRESS

Password: GENERATED_PASSWORD
TEMPLATE;
    }
}
