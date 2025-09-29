<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WeeklyUpdate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'teacher_id',
        'gurukal_id',
        'date',
        'title',
        'description',
        'media',
        'name'
    ];

    protected $casts = [
        'date' => 'date',
        'media' => 'array', 
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class,'id');
    }

    public function gurukal()
    {
        return $this->belongsTo(Gurukal::class);
    }
}
