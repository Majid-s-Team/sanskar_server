<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\Teacher;
use App\Models\WeeklyUpdate;
use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
class AuthController extends Controller
{
    use ApiResponse;

    public function signup(Request $request)
    {
        $validated = $request->validate([
            'primary_email' => 'required|email|unique:users,primary_email',
            'secondary_email' => 'nullable|email',
            'mobile_number' => 'required|unique:users,mobile_number|string|max:20',
            'secondary_mobile_number' => 'nullable|string|max:20',
            'father_name' => 'required|string|max:255',
            'mother_name' => 'required|string|max:255',
            'father_volunteering' => 'required|boolean',
            'mother_volunteering' => 'required|boolean',
            'father_activity_ids' => 'nullable|array',
            'father_activity_ids.*' => 'exists:activities,id',
            'mother_activity_ids' => 'nullable|array',
            'mother_activity_ids.*' => 'exists:activities,id',
            'is_hsnc_member' => 'required|boolean',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'profile_image' => 'nullable|url',

            'students' => 'required|array|min:1',
            'students.*.first_name' => 'required|string|max:255',
            'students.*.last_name' => 'required|string|max:255',
            'students.*.dob' => 'required|date',
            'students.*.student_email' => 'nullable|email',
            'students.*.student_mobile_number' => 'nullable|string|max:20',
            'students.*.join_the_club' => 'required|boolean',
            'students.*.school_name' => 'nullable|string|max:255',
            'students.*.hobbies_interest' => 'nullable|string',
            'students.*.is_school_year_around' => 'required|boolean',
            'students.*.any_allergies' => 'nullable|string',
            'students.*.teeshirt_size_id' => 'required|exists:teeshirt_sizes,id',
            'students.*.gurukal_id' => 'required|exists:gurukals,id',
            'students.*.school_grade_id' => 'required|exists:grades,id',
            'students.*.profile_image' => 'nullable|url',
            'students.*.is_new_student' => 'nullable|boolean',

        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'primary_email' => $validated['primary_email'],
                'secondary_email' => $validated['secondary_email'] ?? null,
                'mobile_number' => $validated['mobile_number'],
                'secondary_mobile_number' => $validated['secondary_mobile_number'] ?? null,
                'father_name' => $validated['father_name'],
                'mother_name' => $validated['mother_name'],
                'father_volunteering' => $validated['father_volunteering'],
                'mother_volunteering' => $validated['mother_volunteering'],
                'is_hsnc_member' => $validated['is_hsnc_member'],
                'address' => $validated['address'] ?? null,
                'city' => $validated['city'] ?? null,
                'state' => $validated['state'] ?? null,
                'zip_code' => $validated['zip_code'] ?? null,
                'is_active' => true,
                'is_payment_done' => false,
                'password' => Hash::make($validated['password']),
                'profile_image' => $validated['profile_image'] ?? null,
                'role' => 'user',

            ]);


            if (!empty($validated['father_activity_ids'])) {
                $user->fatherActivities()->attach($validated['father_activity_ids']);
            }


            if (!empty($validated['mother_activity_ids'])) {
                $user->motherActivities()->attach($validated['mother_activity_ids']);
            }


            foreach ($validated['students'] as $studentData) {
                $studentData['user_id'] = $user->id;
                Student::create($studentData);
            }
            $user->assignRole('user');

            DB::commit();
            return $this->success($user->load('students', 'fatherActivities', 'motherActivities'), 'Signup completed successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error('Signup failed: ' . $e->getMessage(), 500);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('primary_email', $request->login)
            ->orWhere('mobile_number', $request->login)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->error('Invalid credentials', 401);
        }

        if (!$user->is_active) {
            return $this->error('Your account is inactive', 403);
        }

        if ($user->hasRole('user') && !$user->is_payment_done) {
            return $this->error(
                'Payment not completed. Please complete your payment to proceed.',
                403,
                [
                    'id' => $user->id,
                    'primary_email' => $user->primary_email,
                    'mobile_number' => $user->mobile_number,
                    'student_count' => $user->students()->count(),
                ]
            );
        }

        $token = $user->createToken('API Token')->plainTextToken;

        $relations = ['students', 'fatherActivities', 'motherActivities'];

        $responseData = [
            'user'  => $user->load($relations),
            'roles' => $user->getRoleNames(),
            'token' => $token,
            'stats' => $this->getStatsForUser($user), 
        ];

        if ($user->hasRole('teacher')) {
            $teacher = Teacher::where('user_id', $user->id)
                ->with(['gurukal', 'attendances']) 
                ->first();

            $responseData['teacher'] = $teacher;
        }

        return $this->success($responseData, 'Login successful');
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return $this->success([], 'Logged out successfully');
    }


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
            $studentIds = $user->students()->pluck('id');

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
