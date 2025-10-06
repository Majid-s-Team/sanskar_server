<?php

namespace App\Traits;

use App\Models\WeeklyUpdate;
use App\Models\Announcement;
use App\Models\Student;

trait UserStatsTrait
{
    private function getStatsForUser($user)
    {
        if ($user->hasRole('admin')) {
            return [
                'total_weekly_updates' => WeeklyUpdate::count(),
                'total_announcements'  => Announcement::count(),
                'total_multimedia'     => WeeklyUpdate::whereNotNull('media')->count(),
                'total_students'       => Student::where('is_payment_done', 1)->whereNull('deleted_at')->count(),
            ];
        }

        if ($user->hasRole('teacher')) {
            return [
                'total_weekly_updates' => WeeklyUpdate::where('teacher_id', $user->id)->count(),
                'total_announcements'  => Announcement::where('teacher_id', $user->id)->count(),
                'total_multimedia'     => WeeklyUpdate::where('teacher_id', $user->id)->whereNotNull('media')->count(),
                'total_students'       => Student::where('gurukal_id', optional($user->teacher)->gurukal_id)
                    ->where('is_payment_done', 1)
                    ->count(),
            ];
        }

        if ($user->hasRole('user')) {
            return [
                'total_weekly_updates' => WeeklyUpdate::whereIn('gurukal_id', $user->students->pluck('gurukal_id'))->count(),
                'total_announcements'  => Announcement::whereIn('gurukal_id', $user->students->pluck('gurukal_id'))->count(),
                'total_multimedia'     => WeeklyUpdate::whereIn('gurukal_id', $user->students->pluck('gurukal_id'))
                    ->whereNotNull('media')
                    ->count(),
                'total_students'       => $user->students()->where('is_payment_done', 1)->count(),
            ];
        }

        return [];
    }
}
