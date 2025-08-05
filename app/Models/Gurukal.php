<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gurukal extends Model
{
    protected $fillable = ['name', 'is_active'];

    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
