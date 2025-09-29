<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes, HasRoles;

    protected $fillable = [
        'primary_email',
        'secondary_email',
        'mobile_number',
        'secondary_mobile_number',
        'father_name',
        'mother_name',
        'father_volunteering',
        'mother_volunteering',
        'is_hsnc_member',
        'address',
        'city',
        'state',
        'zip_code',
        'is_active',
        'is_payment_done',
        'password',
        'otp',
        'otp_expires_at',
        'is_otp_verified',
        'profile_image',
        'role',
        'is_payment_done',


    ];
    protected $guard_name = 'sanctum';

    protected $hidden = ['password', 'remember_token'];

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function fatherActivities()
    {
        return $this->belongsToMany(Activity::class, 'father_activity_user');
    }

    public function motherActivities()
    {
        return $this->belongsToMany(Activity::class, 'mother_activity_user');
    }
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    public function teacher()
{
    return $this->hasOne(Teacher::class);
}


}
