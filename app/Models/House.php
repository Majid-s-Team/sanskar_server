<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class House extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'is_active', 'house_image'];

    public function students()
    {
        return $this->hasMany(Student::class);
    }
    public function getHouseImageUrlAttribute()
    {
        return $this->house_image ? asset('storage/' . $this->house_image) : null;
    }

}
