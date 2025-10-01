<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class House extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'is_active', 'house_image'];
        protected $appends = ['house_image_url'];


    public function students()
    {
        return $this->hasMany(Student::class);
    }
    public function getHouseImageUrlAttribute()
    {
        if ($this->house_image) {
            return Storage::disk('public')->url($this->house_image);
        }
        return null;
    }

}
