<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'gurukal_id',
        'teacher_id',
    ];

    public function gurukal()
    {
        return $this->belongsTo(Gurukal::class);
    }
    public function teacher()
{
    return $this->belongsTo(User::class, 'teacher_id', 'id'); 
}

}
