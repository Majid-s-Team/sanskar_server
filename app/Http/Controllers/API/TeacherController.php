<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse;

class TeacherController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $request->validate([
            'user_id' => 'sometimes|integer|exists:users,id',
            'full_name' => 'sometimes|string',
            'gurukal_id' => 'sometimes|integer|exists:gurukals,id',
        ]);

        $query = Teacher::with(['user', 'gurukal']);

        if ($request->filled('user_id')) {
        $query->where('user_id', (int) $request->user_id);
        }


        if ($request->filled('full_name')) {
            $query->where('full_name', 'like', '%' . $request->full_name . '%');
        }

        if ($request->filled('gurukal_id')) {
        $query->where('gurukal_id', (int) $request->gurukal_id);
        }
// dd($query->toSql(), $query->getBindings());

        $teachers = $query->get();

        return $this->success($teachers, 'Teachers fetched successfully');
    }


    public function store(Request $request)
    {
        if (!$request->user()->hasRole('admin')) {
            return $this->error('Unauthorized. Only admin can create teachers.', 403);
        }

        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,primary_email',
            'password' => 'required|string|min:6',
            'phone_number' => 'nullable|string',
            'gurukal_id' => 'required|exists:gurukals,id',
            'profile_picture' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        $user = User::create([
            'primary_email' => $request->email,
            'password' => Hash::make($request->password),
            'mobile_number' => $request->phone_number,
            'profile_image' => $request->profile_picture,
            'role' => 'teacher',
            'is_active' => 1,
        ]);

        $user->assignRole('teacher');

        $teacher = Teacher::create([
            'user_id' => $user->id,
            'full_name' => $request->full_name,
            'phone_number' => $request->phone_number,
            'gurukal_id' => $request->gurukal_id,
            'profile_picture' => $request->profile_picture,
        ]);
        $teacher = Teacher::with(['user', 'gurukal'])->find($teacher->id);


        return $this->success($teacher, 'Teacher created successfully', 201);
    }

    public function show($id)
    {
        $teacher = Teacher::with(['user', 'gurukal'])->find($id);

        if (!$teacher) {
            return $this->error("Teacher with ID {$id} not found", 404);
        }

        return $this->success($teacher, 'Teacher details fetched successfully');
    }

    public function update(Request $request, $id)
    {
        if (!$request->user()->hasRole('admin')) {
            return $this->error('Unauthorized. Only admin can update teachers.', 403);
        }

        $teacher = Teacher::find($id);
        if (!$teacher) {
            return $this->error("Teacher with ID {$id} not found", 404);
        }

        $user = $teacher->user;

        $validator = Validator::make($request->all(), [
            'full_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,primary_email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'phone_number' => 'nullable|string',
            'gurukal_id' => 'nullable|exists:gurukals,id',
            'profile_picture' => 'nullable|string',

        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        $user->update([
            'primary_email' => $request->email ?? $user->primary_email,
            'mobile_number' => $request->phone_number ?? $user->mobile_number,
            'profile_image' => $request->profile_picture ?? $user->profile_image,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
            'is_active' => $request->is_active ?? $user->is_active,

        ]);

        $teacher->update([
            'full_name' => $request->full_name ?? $teacher->full_name,
            'phone_number' => $request->phone_number ?? $teacher->phone_number,
            'gurukal_id' => $request->gurukal_id ?? $teacher->gurukal_id,
            'profile_picture' => $request->profile_picture ?? $teacher->profile_picture,
        ]);
        $teacher = Teacher::with(['user', 'gurukal'])->find($teacher->id);


        return $this->success($teacher, 'Teacher updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        if (!$request->user()->hasRole('admin')) {
            return $this->error('Unauthorized. Only admin can delete teachers.', 403);
        }

        $teacher = Teacher::find($id);
        if (!$teacher) {
            return $this->error("Teacher with ID {$id} not found", 404);
        }

        $teacher->user->delete();
        $teacher->delete();

        return $this->success([], 'Teacher deleted successfully');
    }
    public function getStatuses()
    {
        return response()->json([
            'statuses' => Attendance::STATUSES
        ]);
    }

public function getStudents(Request $request, $teacherId)
{
    try {
        $teacher = Teacher::find($teacherId);

        if (! $teacher) {
            return $this->error('Teacher not found', 404);
        }

        $request->validate([
            'per_page'   => 'sometimes|integer|min:1|max:100',
            'date'       => 'sometimes|date',
            'user_id'    => 'sometimes|integer|exists:users,id',
            'name'       => 'sometimes|string',
            'gurukal_id' => 'sometimes|integer|exists:gurukals,id',
        ]);

        $perPage    = $request->get('per_page', 10);
        $targetDate = $request->get('date', now()->toDateString());

        $query = Student::with('user')
            ->where('gurukal_id', $teacher->gurukal_id);

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('name')) {
            $name = $request->name;
            $query->where(function ($q) use ($name) {
                $q->where('first_name', 'like', "%$name%")
                  ->orWhere('last_name', 'like', "%$name%");
            });
        }

        if ($request->has('gurukal_id')) {
            $query->where('gurukal_id', $request->gurukal_id);
        }

        $students = $query->paginate($perPage);

        $studentsData = $students->map(function ($student) use ($teacher, $targetDate) {
            $attendance = Attendance::where('teacher_id', $teacher->id)
                ->where('student_id', $student->id)
                ->whereDate('attendance_date', $targetDate)
                ->first();

            return [
                'student' => $student,
                'attendance' => [
                    'date'                 => $targetDate,
                    'status'               => $attendance->status ?? 'not_recorded',
                    'participation_points' => $attendance->participation_points ?? 0,
                    'homework_points'      => $attendance->homework_points ?? 0,
                ]
            ];
        });

    return $this->successWithPagination(
        [
            'teacher'        => $teacher->full_name,
            'date'           => $targetDate,
            'students_count' => $students->total(),
            'students'       => $studentsData,
        ],
        [
            'count'        => $students->total(),
            'pageCount'    => $students->lastPage(),
            'perPage'      => $students->perPage(),
            'currentPage'  => $students->currentPage(),
        ],
        'Students fetched successfully'
    );


    } catch (\Exception $e) {
        return $this->error('Something went wrong', 500, [
            'error' => $e->getMessage()
        ]);
    }
}




    public function markAttendance(Request $request, $id)
    {
        $request->validate([
            'attendance_date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.student_id' => 'required|exists:students,id',
            'attendance.*.status' => 'required|in:not_recorded,present,excused_absence,unexcused_absence',
            'attendance.*.participation_points' => 'nullable|integer|min:0|max:3',
            'attendance.*.homework_points' => 'nullable|integer|min:0|max:3',
        ]);


        $teacher = Teacher::findOrFail($id);

        $results = [];
        foreach ($request->attendance as $att) {
            $student = Student::findOrFail($att['student_id']);

            if ($student->gurukal_id !== $teacher->gurukal_id) {
                return response()->json([
                    'status' => false,
                    'message' => "Student {$student->id} does not belong to this teacher's class.",
                ], 403);
            }

            $attendance = Attendance::updateOrCreate(
                [
                    'student_id' => $att['student_id'],
                    'attendance_date' => $request->attendance_date,
                ],
                [
                    'teacher_id' => $id,
                    'status' => $att['status'],
                    'participation_points' => $att['participation_points'] ?? 0,
                    'homework_points' => $att['homework_points'] ?? 0,
                ]
            );


            $results[] = $attendance;
        }

        return response()->json([
            'status' => true,
            'message' => 'Attendance marked successfully',
            'records' => $results,
        ]);
    }
  
    public function getAttendances(Request $request, $id)
    {
        $teacher = Teacher::with('gurukal')->find($id);
    
        if (!$teacher) {
            return $this->error('Teacher not found', 404);
        }
    
        $students = Student::with('user')->where('gurukal_id', $teacher->gurukal_id)->get();
    
        $defaultDate = $request->date ?? now()->toDateString();
    
        $records = $students->map(function ($student) use ($teacher, $defaultDate) {
            $attendance = Attendance::where('teacher_id', $teacher->id)
                ->where('student_id', $student->id)
                ->whereDate('attendance_date', $defaultDate)
                ->first();
    
            return [
                'student'              => $student, 
                'status'               => $attendance->status ?? 'not_recorded',
                'date'                 => $defaultDate,
                'participation_points' => $attendance->participation_points ?? 0,
                'homework_points'      => $attendance->homework_points ?? 0,
            ];
        });
    
        $recorded    = $records->filter(fn($r) => $r['status'] !== 'not_recorded')->values();
        $notRecorded = $records->filter(fn($r) => $r['status'] === 'not_recorded')->values();
    
        $counts = [
            'total_students'    => $students->count(),
            'present'           => $recorded->where('status', 'present')->count(),
            'excused_absence'   => $recorded->where('status', 'excused_absence')->count(),
            'unexcused_absence' => $recorded->where('status', 'unexcused_absence')->count(),
            'not_recorded'      => $notRecorded->count(),
        ];
    
        $responseData = [
            'teacher_id' => $teacher->id,
            'filters'    => [
                'date' => $defaultDate,
            ],
            'counts'     => $counts,
            'arrays'     => [
                'all'          => $records,
                'recorded'     => $recorded,
                'not_recorded' => $notRecorded,
            ],
        ];
    
        return $this->success($responseData, 'Attendances fetched successfully');
    }



}
