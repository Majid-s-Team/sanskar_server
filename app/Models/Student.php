<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'dob',
        'student_email',
        'student_mobile_number',
        'join_the_club',
        'school_name',
        'hobbies_interest',
        'is_school_year_around',
        'last_year_class',
        'any_allergies',
        'teeshirt_size_id',
        'gurukal_id',
        'school_grade_id',
        'profile_image',
        'is_payment_done'
        // 'address', 'city', 'state', 'zip_code'
    ];


    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function teeshirtSize()
    {
        return $this->belongsTo(TeeshirtSize::class);
    }

    public function gurukal()
    {
        return $this->belongsTo(Gurukal::class);
    }

    public function schoolGrade()
    {
        return $this->belongsTo(Grade::class, 'school_grade_id');
    }
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

}
