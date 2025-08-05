<?php

namespace App\Http\Controllers\WebAPI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Activity;
use App\Traits\ApiResponse;

class ActivityController extends Controller
{
    use ApiResponse;

    public function index()
    {
        return $this->success(Activity::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|max:255']);
        $activity = Activity::create($validated + ['is_active' => true]);
        return $this->success($activity, 'Activity created successfully.');
    }

    public function show($id)
    {
        $activity = Activity::findOrFail($id);
        return $this->success($activity);
    }

    public function update(Request $request, $id)
    {
        $activity = Activity::findOrFail($id);
        $validated = $request->validate(['name' => 'required|string|max:255']);
        $activity->update($validated);
        return $this->success($activity, 'Activity updated successfully.');
    }

    public function destroy($id)
    {
        $activity = Activity::findOrFail($id);
        $activity->delete();
        return $this->success(null, 'Activity deleted successfully.');
    }

    public function changeStatus($id)
    {
        $activity = Activity::findOrFail($id);
        $activity->is_active = !$activity->is_active;
        $activity->save();
        return $this->success($activity, 'Activity status updated.');
    }
}
