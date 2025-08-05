<?php

namespace App\Http\Controllers\WebAPI;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Traits\ApiResponse;

class UserController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $this->authorize('user.view');

        $users = User::with(['students', 'fatherActivities', 'motherActivities'])
            ->latest()
            ->paginate(10);

        return $this->paginated($users, 'Users fetched successfully');
    }

    public function store(Request $request)
    {
        $this->authorize('user.create');

        $validated = $request->validate([
            'primary_email' => 'required|email|unique:users,primary_email',
            'secondary_email' => 'nullable|email',
            'mobile_number' => 'required|string|max:20',
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
            'password' => 'required|string|min:6',

            'students' => 'nullable|array',
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
        ]);

        DB::beginTransaction();

        try {
            $validated['password'] = Hash::make($validated['password']);
            $validated['is_active'] = true;
            $validated['is_payment_done'] = false;

            $user = User::create($validated);

            if (!empty($validated['father_activity_ids'])) {
                $user->fatherActivities()->attach($validated['father_activity_ids']);
            }

            if (!empty($validated['mother_activity_ids'])) {
                $user->motherActivities()->attach($validated['mother_activity_ids']);
            }

            if (!empty($validated['students'])) {
                foreach ($validated['students'] as $student) {
                    $student['user_id'] = $user->id;
                    Student::create($student);
                }
            }

            $user->assignRole('user');

            DB::commit();

            return $this->success($user->load(['students', 'fatherActivities', 'motherActivities']), 'User created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('User creation failed: ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        $this->authorize('user.view');

        $user = User::with(['students', 'fatherActivities', 'motherActivities'])->findOrFail($id);
        return $this->success($user, 'User fetched successfully');
    }

    public function update(Request $request, $id)
    {
        $this->authorize('user.update');

        $user = User::findOrFail($id);

        $validated = $request->validate([
            'primary_email' => 'sometimes|email|unique:users,primary_email,' . $user->id,
            'secondary_email' => 'nullable|email',
            'mobile_number' => 'sometimes|string|max:20',
            'secondary_mobile_number' => 'nullable|string|max:20',
            'father_name' => 'sometimes|string|max:255',
            'mother_name' => 'sometimes|string|max:255',
            'father_volunteering' => 'sometimes|boolean',
            'mother_volunteering' => 'sometimes|boolean',
            'father_activity_ids' => 'nullable|array',
            'father_activity_ids.*' => 'exists:activities,id',
            'mother_activity_ids' => 'nullable|array',
            'mother_activity_ids.*' => 'exists:activities,id',
            'is_hsnc_member' => 'sometimes|boolean',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        DB::beginTransaction();

        try {
            $user->update($validated);

            if (isset($validated['father_activity_ids'])) {
                $user->fatherActivities()->sync($validated['father_activity_ids']);
            }

            if (isset($validated['mother_activity_ids'])) {
                $user->motherActivities()->sync($validated['mother_activity_ids']);
            }

            DB::commit();

            return $this->success($user->load(['students', 'fatherActivities', 'motherActivities']), 'User updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('User update failed: ' . $e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        $this->authorize('user.delete');

        $user = User::findOrFail($id);
        $user->delete();

        return $this->success([], 'User deleted successfully');
    }

    public function toggleStatus($id)
    {
        $this->authorize('user.change-status');

        $user = User::findOrFail($id);
        $user->is_active = !$user->is_active;
        $user->save();

        return $this->success([
            'id' => $user->id,
            'is_active' => $user->is_active,
        ], 'User status updated successfully');
    }
}
