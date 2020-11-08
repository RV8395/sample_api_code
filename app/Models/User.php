<?php

namespace App\Models;

use Laravel\Passport\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Zizaco\Entrust\Traits\EntrustUserTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens;
    use EntrustUserTrait {
        restore as private restoreA;
    }
    use SoftDeletes {
        restore as private restoreB;
    }

    public function restore()
    {
        $this->restoreA();
        $this->restoreB();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'is_register', 'is_guardian', 'is_password_set', 'is_payment_done', 'otp', 'first_name', 'last_name', 'email', 'corporate_id', 'username', 'nickname', 'gender', 'contact_number', 'secondary_contact_number', 'address_line_1', 'address_line_2', 'postcode', 'city', 'country', 'status', 'membership_id', 'referral_code', 'google2fa_secret'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Ecrypt the user's google_2fa secret.
     *
     * @param  string  $value
     * @return string
     */
    // public function setGoogle2faSecretAttribute($value)
    // {
    //     $this->attributes['google2fa_secret'] = encrypt($value);
    // }

    /**
     * Decrypt the user's google_2fa secret.
     *
     * @param  string  $value
     * @return string
     */
    // public function getGoogle2faSecretAttribute($value)
    // {
    //     return decrypt($value);
    // }
}
