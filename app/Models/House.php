<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class House extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'is_active'];

    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
