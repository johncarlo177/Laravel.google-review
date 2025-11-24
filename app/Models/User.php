<?php

namespace App\Models;

use App\Events\UserCreated;
use App\Models\Traits\HasMetaFields;
use App\Notifications\ResetPassword;
use App\Notifications\VerifyEmail;
use App\Plugins\PluginManager;
use App\Support\Auth\AuthManager;
use App\Support\MobileNumberManager;
use App\Support\Sms\Contracts\HasMobileNumber;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @property integer id
 * @property string email
 * @property string name
 * @property string mobile_iso_code
 * @property string mobile_calling_code
 * @property string mobile_number
 * @property integer parent_id
 * @property boolean is_sub
 * @property integer profile_image_id
 * @property integer reseller_id
 * @property Carbon email_verified_at
 * @property Carbon created_at
 * @property User parent_user
 * 
 * @property Collection subscriptions
 * @property Collection roles
 * @property Collection transactions
 * @property Collection qrcodes
 * @property Collection permissions
 * @property Collection sub_users
 */
class User extends Authenticatable implements MustVerifyEmail, HasMobileNumber
{
    use HasApiTokens, HasFactory, Notifiable, HasMetaFields;

    use WriteLogs;

    static $count = 0;

    protected $with = ['roles', 'roles.permissions'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'mobile_number',
        'profile_image_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $dispatchesEvents = [
        'created' => UserCreated::class,
    ];


    public function setRawMobileNumber($mobile_number)
    {
        $this->attributes['mobile_number'] = $mobile_number;
    }

    public function setMobileNumberAttribute($value)
    {
        if (!is_array($value) || empty($value)) {
            return;
        }

        $keys = ['iso_code', 'mobile_number'];

        foreach ($keys as $key) {
            if (empty($value[$key])) {
                return;
            }
        }

        $mobileNumberManager = new MobileNumberManager;

        $this->attributes['mobile_iso_code'] = $value['iso_code'];
        $this->attributes['mobile_calling_code'] = $mobileNumberManager->callingCodeByIsoCode($value['iso_code']);
        $this->attributes['mobile_number'] = $value['mobile_number'];
    }


    public function getMobileNumberAttribute()
    {
        return [
            'mobile_number' => @$this->attributes['mobile_number'],
            'iso_code' => @$this->mobile_iso_code
        ];
    }

    public function getFormattedMobileNumber(): string
    {
        $code = @$this->attributes['mobile_calling_code'];

        $number = @$this->attributes['mobile_number'];

        if (empty($number)) return '';

        $number = trim($number);

        $number = str_replace(' ', '', $number);

        $number = ltrim($number, '0');

        return "+$code$number";
    }

    public function isSuperAdmin()
    {
        $superAdmin = $this->roles?->reduce(
            fn($superAdmin, $role) => $superAdmin || $role->super_admin,
            false
        );

        if ($superAdmin) return true;

        return false;
    }

    public function isClient()
    {
        $this->load('roles');

        return $this->roles?->filter(fn(Role $role) => $role->name === 'Client')->isNotEmpty();
    }

    /**
     * Determines if the user has a permission
     * 
     * @param string $slug Permission slug
     * @return boolean
     */
    public function permitted($slug)
    {
        $model = explode('.', $slug)[0];

        $capability = explode('.', $slug)[1];

        if (!preg_match("/-/", $model)) {
            $model = Str::kebab($model);

            $slug = "$model.$capability";
        }

        if ($this->isSuperAdmin()) return true;

        return $this->permissions->first(function ($permission) use ($slug) {
            return $permission->slug === $slug;
        }) ? true : false;
    }

    public function sendEmailVerificationNotification()
    {
        $shouldVerify = AuthManager::emailVerificationEnabled();

        $shouldVerify = PluginManager::doFilter(
            PluginManager::FILTER_SHOULD_VERIFY_EMAIL,
            $shouldVerify,
            $this
        );

        if ($shouldVerify) {
            $this->notify(new VerifyEmail);
        }
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    public function permissions(): Attribute
    {
        $permissions = $this
            ->roles
            ->pluck('permissions')
            ->flatten()
            ->unique('id');

        return new Attribute(
            fn() => $permissions
        );
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class)->orderBy('id', 'desc');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function setRole(Role $role)
    {
        $this->clearRoles();

        $this->addRole($role);
    }

    public function clearRoles()
    {
        DB::table('user_roles')->where('user_id', $this->id)->delete();
    }

    public function addRole(Role $role)
    {
        DB::insert(
            'insert into user_roles (user_id, role_id) values (?, ?)',
            [$this->id, $role->id]
        );
    }

    public function removeRole(Role $role)
    {
        $this->roles()->detach([$role->id]);
    }

    public function transactions()
    {
        return $this->hasManyThrough(Transaction::class, Subscription::class);
    }

    public function qrcodes()
    {
        return $this->hasMany(QRCode::class);
    }

    public function domains()
    {
        return $this->hasMany(Domain::class);
    }

    public function parent_user()
    {
        return $this->belongsTo(static::class, 'parent_id');
    }

    public function sub_users()
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    public function getProfileImageSrc()
    {
        return file_url($this->profile_image_id);
    }

    /**
     * @return User
     */
    public static function findByMobileNumber($calling_code, $mobile_number)
    {
        return static::where(
            'mobile_number',
            $mobile_number,
        )->where('mobile_calling_code', $calling_code)
            ->first();
    }

    public function isReseller()
    {
        return $this->roles->filter(
            fn($role) => $role->name === 'Reseller'
        )->isNotEmpty();
    }
}
