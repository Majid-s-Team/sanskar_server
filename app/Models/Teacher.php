<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Teacher extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'full_name',
        'phone_number',
        'gurukal_id',
        'profile_picture',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function gurukal()
    {
        return $this->belongsTo(Gurukal::class);
    }
    public function students()
{
    return Student::where('gurukal_id', $this->gurukal_id)->get();
}

public function attendances()
{
    return $this->hasMany(Attendance::class);
}

}
