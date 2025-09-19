<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'student_id',
        'teacher_id',
        'attendance_date',
        'status',
        'participation_points',
        'homework_points',
    ];

    const STATUSES = [
        'not_recorded' => 'Not Recorded',
        'present' => 'Present',
        'excused_absence' => 'Excused Absence',
        'unexcused_absence' => 'Unexcused Absence',
    ];

    const MAX_POINTS = 3;

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
