<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'is_active'];

    public function fathers()
    {
        return $this->belongsToMany(User::class, 'father_activity_user');
    }

    public function mothers()
    {
        return $this->belongsToMany(User::class, 'mother_activity_user');
    }
}
