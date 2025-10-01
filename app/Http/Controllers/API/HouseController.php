<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\House;
use App\Models\Student;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth; 

class HouseController extends Controller
{
    use ApiResponse;

    public function index()
    {
        return $this->success(House::all(), 'Houses fetched successfully');
    }

    public function store(Request $request)
    {
        if (!Auth::user() || Auth::user()->role !== 'admin') {
            return $this->error('Only admin can create houses', 403);
        }
        // $validated = $request->validate([
        //     'name' => 'required|string|unique:houses|max:255',
        // ]);
        $validated = $request->validate([
            'name' => 'required|string|unique:houses|max:255',
            'house_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('house_image')) {
            $path = $request->file('house_image')->store('houses', 'public');
            $validated['house_image'] = $path;
        }



        $house = House::create($validated + ['is_active' => true]);

        return $this->success($house, 'House created successfully');
    }

     public function show($id)
    {
        $house = House::with('students')->find($id);

        if (!$house) {
            return $this->error('House does not exist', 404);
        }

        return $this->success($house, 'House details fetched successfully');
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user() || Auth::user()->role !== 'admin') {
            return $this->error('Only admin can update houses', 403);
        }

        $house = House::find($id);

        if (!$house) {
            return $this->error('House does not exist', 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:houses,name,' . $id,
            'house_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('house_image')) {
            $path = $request->file('house_image')->store('houses', 'public');
            $validated['house_image'] = $path;
        }

        $house->update($validated);

        return $this->success($house, 'House updated successfully');
    }

    public function destroy($id)
    {
        $house = House::find($id);

        if (!$house) {
            return $this->error('House does not exist', 404);
        }

        $house->delete();

        return $this->success(null, 'House deleted successfully');
    }
    public function changeStatus($id)
    {
        $house = House::find($id);

        if (!$house) {
            return $this->error('House does not exist', 404);
        }

        $house->is_active = !$house->is_active;
        $house->save();

        return $this->success($house, 'House status updated successfully');
    }

   public function assignHouse(Request $request, $studentId)
    {
        $validated = $request->validate([
            'house_id' => 'required|exists:houses,id',
        ]);

        $student = Student::find($studentId);

        if (!$student) {
            return $this->error('Student does not exist', 404);
        }

        $student->house_id = $validated['house_id'];
        $student->save();

        return $this->success($student->load('house'), 'House assigned to student successfully');
    }

    public function getStudentsByHouse($houseId)
    {
        $house = House::with('students.user')->find($houseId);

        if (!$house) {
            return $this->error('House does not exist', 404);
        }

        return $this->success($house, 'Students fetched successfully by house');
    }
}
