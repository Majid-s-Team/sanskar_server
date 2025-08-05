<?php

namespace App\Http\Controllers\WebAPI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Grade;
use App\Traits\ApiResponse;

class GradeController extends Controller
{
    use ApiResponse;

    public function index()
    {
        return $this->success(Grade::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|max:255']);
        $grade = Grade::create($validated + ['is_active' => true]);
        return $this->success($grade, 'Grade created successfully.');
    }

    public function show($id)
    {
        $grade = Grade::findOrFail($id);
        return $this->success($grade);
    }

    public function update(Request $request, $id)
    {
        $grade = Grade::findOrFail($id);
        $validated = $request->validate(['name' => 'required|string|max:255']);
        $grade->update($validated);
        return $this->success($grade, 'Grade updated successfully.');
    }

    public function destroy($id)
    {
        $grade = Grade::findOrFail($id);
        $grade->delete();
        return $this->success(null, 'Grade deleted successfully.');
    }

    public function changeStatus($id)
    {
        $grade = Grade::findOrFail($id);
        $grade->is_active = !$grade->is_active;
        $grade->save();
        return $this->success($grade, 'Grade status updated.');
    }
}
