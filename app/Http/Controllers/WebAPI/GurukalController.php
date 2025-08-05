<?php

namespace App\Http\Controllers\WebAPI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gurukal;
use App\Traits\ApiResponse;

class GurukalController extends Controller
{
    use ApiResponse;

    public function index()
    {
        return $this->success(Gurukal::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|max:255']);
        $gurukal = Gurukal::create($validated + ['is_active' => true]);
        return $this->success($gurukal, 'Gurukal created successfully.');
    }

    public function show($id)
    {
        $gurukal = Gurukal::findOrFail($id);
        return $this->success($gurukal);
    }

    public function update(Request $request, $id)
    {
        $gurukal = Gurukal::findOrFail($id);
        $validated = $request->validate(['name' => 'required|string|max:255']);
        $gurukal->update($validated);
        return $this->success($gurukal, 'Gurukal updated successfully.');
    }

    public function destroy($id)
    {
        $gurukal = Gurukal::findOrFail($id);
        $gurukal->delete();
        return $this->success(null, 'Gurukal deleted successfully.');
    }

    public function changeStatus($id)
    {
        $gurukal = Gurukal::findOrFail($id);
        $gurukal->is_active = !$gurukal->is_active;
        $gurukal->save();
        return $this->success($gurukal, 'Gurukal status updated.');
    }
}