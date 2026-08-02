<?php

namespace App;

use App\Support\UserSignature;
use App\Traits\NormalizesWhatsAppPhones;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable;
    use HasRoles;
    use NormalizesWhatsAppPhones;

    protected $whatsappPhoneAttributes = ['phone', 'additional_phone'];

    protected $fillable = [
        'name', 'email', 'password',"phone", "additional_phone", "company_name", "role_id", "biller_id", "warehouse_id", "is_active", "is_deleted", "sign", "stemp", "approve", "otp", "otp_time", "otp_verify"
    ];

    protected $hidden = [
        'password', 'remember_token', 'signature_token',
    ];

    protected $dates = ['signature_requested_at', 'signature_signed_at'];

    public function isActive()
    {
        return $this->is_active;
    }

    public function holiday() {
        return $this->hasMany('App\Holiday');
    }

    public function order() {
        return $this->hasMany('App\Order');
    }

    public function customer() {
        return $this->hasOne('App\Customer', 'user_id', 'id');
    }

    public function hasSignature()
    {
        return ! empty($this->sign) && UserSignature::path($this->sign) !== null;
    }

    public function signatureUrl()
    {
        return UserSignature::url($this->sign);
    }

    /**
     * Issue a fresh signing token, invalidating any link already sent.
     */
    public function rotateSignatureToken()
    {
        $this->signature_token = Str::random(48);
        $this->signature_requested_at = now();
        $this->save();

        return $this->signature_token;
    }

    public function signatureRequestUrl()
    {
        if (empty($this->signature_token)) {
            $this->rotateSignatureToken();
        }

        return url('sign-your-signature/'.$this->signature_token);
    }

    public function isOpenForSignatureRequest()
    {
        return ! empty($this->signature_token) && ! $this->is_deleted;
    }
}
