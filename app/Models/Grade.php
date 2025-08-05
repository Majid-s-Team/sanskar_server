<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $table = 'grades';
    protected $fillable = ['name', 'is_active'];

    public function students()
    {
        return $this->hasMany(Student::class, 'school_grade_id');
    }
}
