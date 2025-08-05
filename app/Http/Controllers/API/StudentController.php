<?php

namespace App\Http\Controllers\API;

use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
{
    $perPage = $request->get('per_page', 10); 

    $students = Student::with(['user', 'teeshirtSize', 'gurukal', 'schoolGrade'])
        ->latest()
        ->paginate($perPage);

    return $this->paginated($students, 'Students fetched successfully');
}

    public function show($id)
    {
        $student = Student::with(['user', 'teeshirtSize', 'gurukal', 'schoolGrade'])->find($id);

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
            'student_mobile_number'=> 'required|string|max:15',
            'join_the_club'        => 'nullable|boolean',
            'school_name'          => 'nullable|string|max:255',
            'hobbies_interest'     => 'nullable|string',
            'is_school_year_around'=> 'nullable|boolean',
            'last_year_class'      => 'nullable|string|max:255',
            'any_allergies'        => 'nullable|string|max:255',
            'teeshirt_size_id'     => 'nullable|exists:teeshirt_sizes,id',
            'gurukal_id'           => 'nullable|exists:gurukals,id',
            'school_grade_id'      => 'nullable|exists:grades,id',
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
            'student_mobile_number'=> 'sometimes|string|max:15',
            'join_the_club'        => 'nullable|boolean',
            'school_name'          => 'nullable|string|max:255',
            'hobbies_interest'     => 'nullable|string',
            'is_school_year_around'=> 'nullable|boolean',
            'last_year_class'      => 'nullable|string|max:255',
            'any_allergies'        => 'nullable|string|max:255',
            'teeshirt_size_id'     => 'nullable|exists:teeshirt_sizes,id',
            'gurukal_id'           => 'nullable|exists:gurukals,id',
            'school_grade_id'      => 'nullable|exists:grades,id',
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
}
