<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Teacher;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AnnouncementController extends Controller
{
    use ApiResponse;

    // List announcements
public function index(Request $request)
{
    $perPage = $request->get('per_page', 10);
    $user = $request->user();

    $query = Announcement::with('gurukal')->latest();

    if ($user->role === 'teacher') {
        $query->where('teacher_id', $user->id);
    }

    if ($request->has('gurukal_id')) {
        $query->where('gurukal_id', $request->gurukal_id);
    }

    $totalAnnouncements = $query->count();
    $items = $query->paginate($perPage);

    return response()->json([
        'status'  => true,
        'message' => 'Announcements fetched successfully',
        'data'    => $items->items(),
        'total_announcements' => $totalAnnouncements,
        'pagination' => [
            'count'        => $items->total(),
            'pageCount'    => $items->lastPage(),
            'perPage'      => $items->perPage(),
            'currentPage'  => $items->currentPage(),
        ],
    ]);
}


    // Show single
    public function show($id)
    {
        $announcement = Announcement::with('gurukal')->find($id);

        if (! $announcement) {
            return $this->error('Announcement not found', 404);
        }

        return $this->success($announcement);
    }

    // Create announcement
    public function store(Request $request)
    {
        $user = $request->user();

        $teacher = Teacher::where('user_id', $user->id)->first();
        if (! $teacher) {
            return $this->error('Only teachers can create announcements', 403);
        }

        if (! $teacher->gurukal_id) {
            return $this->error('Cannot create announcement as no class assigned', 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        $announcement = Announcement::create([
            'title' => $request->title,
            'description' => $request->description,
            'gurukal_id' => $teacher->gurukal_id,
            'teacher_id' => $user->id,
        ]);

        return $this->success($announcement->load('gurukal'), 'Announcement created successfully', 201);
    }

    // Update announcement
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $announcement = Announcement::find($id);

        if (! $announcement) {
            return $this->error('Announcement not found', 404);
        }

        $teacher = Teacher::where('user_id', $user->id)->first();
        $isOwner = $teacher && $teacher->gurukal_id == $announcement->gurukal_id;

    // Only owner teacher or admin
    $isOwner = $user->role === 'teacher' && $announcement->teacher_id === $user->id;

    if (! $isOwner && ! $user->hasRole('admin')) {
        return $this->error('Unauthorized', 403);
    }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        $announcement->update($request->only(['title', 'description']));

        return $this->success($announcement->load('gurukal'), 'Announcement updated successfully');
    }

    // Soft delete
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $announcement = Announcement::find($id);

        if (! $announcement) {
            return $this->error('Announcement not found', 404);
        }

        $teacher = Teacher::where('user_id', $user->id)->first();
        $isOwner = $teacher && $teacher->gurukal_id == $announcement->gurukal_id;

        if (! $isOwner && ! $user->hasRole('admin')) {
            return $this->error('Unauthorized to delete this announcement', 403);
        }

        $announcement->delete();

        return $this->success(null, 'Announcement deleted successfully (soft)');
    }
public function forStudents(Request $request)
{
    $user = $request->user();
    $perPage = $request->get('per_page', 10);

    $studentId = $request->get('student_id');

    if ($studentId) {
        $student = $user->students()->find($studentId);

        if (! $student) {
            return $this->error('Student not found for this user', 404);
        }

        $gurukalIds = [$student->gurukal_id];
    } else {
        $students = $user->students;

        if ($students->isEmpty()) {
            return $this->error('No students found for this user', 404);
        }

        $gurukalIds = $students->pluck('gurukal_id')->unique()->toArray();
    }

    $query = Announcement::with(['gurukal'])
        ->whereIn('gurukal_id', $gurukalIds)
        ->orderBy('created_at', 'desc');

    if ($request->has('start_date') && $request->has('end_date')) {
        $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
    } elseif ($request->has('date')) {
        $query->whereDate('created_at', $request->date);
    }

    $totalAnnouncements = $query->count();
    $items = $query->paginate($perPage);

    return response()->json([
        'status'  => true,
        'message' => 'Announcements for student(s) fetched',
        'data'    => $items->items(),
        'total_announcements' => $totalAnnouncements,
        'pagination' => [
            'count'        => $items->total(),
            'pageCount'    => $items->lastPage(),
            'perPage'      => $items->perPage(),
            'currentPage'  => $items->currentPage(),
        ],
    ]);
}

}
