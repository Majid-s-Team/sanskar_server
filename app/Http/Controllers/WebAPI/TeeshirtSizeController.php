<?php

namespace App\Http\Controllers\WebAPI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TeeshirtSize;
use App\Traits\ApiResponse;

class TeeshirtSizeController extends Controller
{
    use ApiResponse;

    public function index()
    {
        return $this->success(TeeshirtSize::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|max:255']);
        $teeshirtSize = TeeshirtSize::create($validated + ['is_active' => true]);
        return $this->success($teeshirtSize, 'Teeshirt Size created successfully.');
    }

    public function show($id)
    {
        $teeshirtSize = TeeshirtSize::findOrFail($id);
        return $this->success($teeshirtSize);
    }

    public function update(Request $request, $id)
    {
        $teeshirtSize = TeeshirtSize::findOrFail($id);
        $validated = $request->validate(['name' => 'required|string|max:255']);
        $teeshirtSize->update($validated);
        return $this->success($teeshirtSize, 'Teeshirt Size updated successfully.');
    }

    public function destroy($id)
    {
        $teeshirtSize = TeeshirtSize::findOrFail($id);
        $teeshirtSize->delete();
        return $this->success(null, 'Teeshirt Size deleted successfully.');
    }

    public function changeStatus($id)
    {
        $teeshirtSize = TeeshirtSize::findOrFail($id);
        $teeshirtSize->is_active = !$teeshirtSize->is_active;
        $teeshirtSize->save();
        return $this->success($teeshirtSize, 'Teeshirt Size status updated.');
    }
}
