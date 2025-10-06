<?php

namespace App\Http\Controllers\API;

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
            'user_id' => 'sometimes|integer|exists:users,id',
            'name' => 'sometimes|string',
            'gurukal_id' => 'sometimes|integer|exists:gurukals,id',
        ]);

        $perPage = $request->get('per_page', 10);

        $query = Student::with(['user', 'teeshirtSize', 'gurukal', 'schoolGrade','house']);

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('name')) {
            $name = $request->name;
            $query->where(function ($q) use ($name) {
                $q->where('first_name', 'like', "%$name%")
                    ->orWhere('last_name', 'like', "%$name%")
                    ->orWhere(\DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%$name%");
            });
        }


        if ($request->has('gurukal_id')) {
            $query->where('gurukal_id', $request->gurukal_id);
        }

        $students = $query->latest()->paginate($perPage);

        return $this->paginated($students, 'Students fetched successfully');
    }


    public function show($id)
    {
        $student = Student::with(['user', 'teeshirtSize', 'gurukal', 'schoolGrade','house'])->find($id);

        if (!$student) {
            return $this->error('Student not found', 404);
        }

        return $this->success($student);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'               => 'required|exists:users,id',
            'first_name'           => 'required|string|max:255',
            'last_name'            => 'required|string|max:255',
            'dob'                  => 'required|date',
            'student_email'        => 'required|email',
            'student_mobile_number' => 'required|string|max:15',
            'join_the_club'        => 'nullable|boolean',
            'school_name'          => 'nullable|string|max:255',
            'hobbies_interest'     => 'nullable|string',
            'is_school_year_around' => 'nullable|boolean',
            'last_year_class'      => 'nullable|string|max:255',
            'any_allergies'        => 'nullable|string|max:255',
            'teeshirt_size_id'     => 'nullable|exists:teeshirt_sizes,id',
            'gurukal_id'           => 'nullable|exists:gurukals,id',
            'school_grade_id'      => 'nullable|exists:grades,id',
            'is_new_student'       => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        $student = Student::create($request->all());

        return $this->success($student, 'Student created successfully');
    }

    public function update(Request $request, $id)
    {
        $student = Student::find($id);
        if (!$student) {
            return $this->error('Student not found', 404);
        }

        $validator = Validator::make($request->all(), [
            'user_id'               => 'sometimes|exists:users,id',
            'first_name'           => 'sometimes|string|max:255',
            'last_name'            => 'sometimes|string|max:255',
            'dob'                  => 'sometimes|date',
            'student_email'        => 'sometimes|email',
            'student_mobile_number' => 'sometimes|string|max:15',
            'join_the_club'        => 'nullable|boolean',
            'school_name'          => 'nullable|string|max:255',
            'hobbies_interest'     => 'nullable|string',
            'is_school_year_around' => 'nullable|boolean',
            'last_year_class'      => 'nullable|string|max:255',
            'any_allergies'        => 'nullable|string|max:255',
            'teeshirt_size_id'     => 'nullable|exists:teeshirt_sizes,id',
            'gurukal_id'           => 'nullable|exists:gurukals,id',
            'school_grade_id'      => 'nullable|exists:grades,id',
            'house_id'             => 'nullable|exists:houses,id',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        $student->update($request->all());

        return $this->success($student, 'Student updated successfully');
    }

    public function destroy($id)
    {
        $student = Student::find($id);
        if (!$student) {
            return $this->error('Student not found', 404);
        }

        $student->delete();

        return $this->success(null, 'Student deleted successfully');
    }

    public function changeStatus($id)
    {
        $student = Student::find($id);
        if (!$student) {
            return $this->error('Student not found', 404);
        }

        $student->is_active = !$student->is_active;
        $student->save();

        return $this->success($student, 'Student status updated');
    }
    // public function getMyStudents($id)
    // {
    //     $user = User::find($id);

    //     if (!$user) {
    //         return $this->error('User not found', 404, []);
    //     }

    //     $students = $user->students()->with([
    //         'teeshirtSize',
    //         'gurukal',
    //         'schoolGrade',
    //         'house'
    //     ])->get();

    //     return $this->success($students, 'Student list fetched successfully');
    // }
    public function getMyStudents(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->error('User not found', 404);
        }

        $studentId = $request->get('student_id');

        if (!$studentId) {
            $students = $user->students()
                ->with(['teeshirtSize', 'gurukal', 'schoolGrade', 'house'])
                ->get();

            return $this->success($students, 'Student list fetched successfully');
        }

        $student = $user->students()
            ->with([
                'teeshirtSize',
                'gurukal',
                'schoolGrade',
                'house',
                'attendances' => function ($query) {
                    $query->orderBy('attendance_date', 'desc');
                },
            ])
            ->find($studentId);

        if (!$student) {
            return $this->error('Student not found for this user', 404);
        }

        $recorded = $student->attendances->filter(fn($a) => !empty($a->status))->map(function ($a) {
            return [
                'id' => $a->id,
                'attendance_date' => $a->attendance_date,
                'status' => $a->status,
                'participation_points' => $a->participation_points,
                'homework_points' => $a->homework_points,
                'remarks' => $a->remarks,
                'created_at' => $a->created_at,
                'updated_at' => $a->updated_at,
            ];
        })->values();

        $nonRecorded = $student->attendances->filter(fn($a) => empty($a->status))->map(function ($a) {
            return [
                'id' => $a->id,
                'attendance_date' => $a->attendance_date,
                'status' => 'not_recorded',
                'participation_points' => $a->participation_points ?? 0,
                'homework_points' => $a->homework_points ?? 0,
                'remarks' => $a->remarks,
                'created_at' => $a->created_at,
                'updated_at' => $a->updated_at,
            ];
        })->values();

        $data = [
            'student' => $student, 
            'attendance_summary' => [
                'total' => $student->attendances->count(),
                'recorded_count' => $recorded->count(),
                'not_recorded_count' => $nonRecorded->count(),
            ],
            'attendance_arrays' => [
                'recorded' => $recorded,
                'not_recorded' => $nonRecorded,
            ],
        ];

        return $this->success($data, 'Student with attendance details fetched successfully');
    }

   
    public function updateStudentStatus(Request $request, $id)
    {
        $request->validate([
            'is_new_student' => 'required|boolean',
        ]);

        $student = Student::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$student) {
            return $this->error('Student not found or not associated with this user', 404);
        }

        if (!is_null($student->is_new_student)) {
            return $this->error('Student is already marked as Yes or No', 422);
        }

        $student->is_new_student = $request->is_new_student;
        $student->save();

        return $this->success($student, 'Student status marked successfully');
    }


}
