<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\WeeklyUpdate;
use App\Models\Teacher;
use App\Models\Student;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WeeklyUpdateController extends Controller
{
    use ApiResponse;

   public function index(Request $request)
{
    $perPage = $request->get('per_page', 10);

    $query = WeeklyUpdate::with(['teacher', 'gurukal'])
        ->orderBy('id', 'desc');
    $user = $request->user();

    if ($user->role === 'teacher' && $user->teacher) {
        $query->where('teacher_id', $user->teacher->user_id); 
    }

    if ($request->has('gurukal_id')) {
        $query->where('gurukal_id', $request->gurukal_id);
    }

    if ($request->has('start_date') && $request->has('end_date')) {
        $query->whereBetween('date', [$request->start_date, $request->end_date]);
    }

    $totalUpdates = $query->count();
    $updates = $query->paginate($perPage);

    return $this->successWithPagination(
        $updates->items(),
        [
            'count'        => $updates->total(),
            'pageCount'    => $updates->lastPage(),
            'perPage'      => $updates->perPage(),
            'currentPage'  => $updates->currentPage(),
            'total_updates'=> $totalUpdates
        ],
        'Weekly updates fetched successfully'
    );
}


    // Show single update
    public function show($id)
    {
        $update = WeeklyUpdate::with(['teacher.user', 'gurukal'])->find($id);

        if (! $update) {
            return $this->error('Update not found', 404);
        }

        return $this->success($update);
    }

    // Create - teacher posts an update
    public function store(Request $request)
    {
        $user = $request->user();

        $teacher = Teacher::where('user_id', $user->id)->first();
        if (! $teacher) {
            return $this->error('Only teachers can create weekly updates', 403);
        }

        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'media' => 'nullable|array',
            'media.*.type' => 'nullable|string|in:image,doc,video,pdf,excel,powerpoint,mp3,link,other,docx,mp4,jpeg,png,jpg,mp4,mov,avi,mp3,wav,ogg,pptx,xlsx,csv,doc,docx,pdf,cvs,ppt,xls',
            'media.*.url' => 'nullable|url',
            'media.*.name' => 'nullable|string',
            'media.*.file' => 'nullable|file|max:102400', 


        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }


        $gurukalId = $teacher->gurukal_id;

        $update = WeeklyUpdate::create([
            'teacher_id' => $teacher->user_id,
            'gurukal_id' => $gurukalId,
            'date' => $request->date,
            'title' => $request->title,
            'description' => $request->description,
            'media' => $request->media ?? [],
        ]);

        return $this->success($update->load(['teacher','gurukal']), 'Weekly update created', 201);
    }


    public function update(Request $request, $id)
    {
        $user = $request->user();
        $update = WeeklyUpdate::find($id);
        if (! $update) {
            return $this->error('Update not found', 404);
        }
        // dd($user->id, $update->teacher->user_id);

        $isOwner = $update->teacher && $update->teacher_id === $user->id;
        if (! $isOwner && ! $request->user()->hasRole('admin')) {
            return $this->error('Unauthorized to update this entry', 403);
        }

        $validator = Validator::make($request->all(), [
            'date' => 'sometimes|date',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'media' => 'nullable|array',
            // 'media.*.type' => 'nullable|string|in:image,video,pdf,link,other',
            'media.*.type' => 'nullable|string|in:image,doc,video,pdf,excel,powerpoint,mp3,link,other,docx',

            'media.*.url' => 'nullable|url',
            'media.*.name' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        $update->update([
            'date' => $request->date ?? $update->date,
            'title' => $request->title ?? $update->title,
            'description' => $request->description ?? $update->description,
            'media' => $request->has('media') ? $request->media : $update->media,
        ]);

        return $this->success($update->load(['teacher','gurukal']), 'Weekly update updated');
    }

    // Soft delete
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $update = WeeklyUpdate::find($id);
        if (! $update) {
            return $this->error('Update not found', 404);
        }

        $isOwner = $update->teacher && $update->teacher->user_id === $user->id;
        if (! $isOwner && ! $request->user()->hasRole('admin')) {
            return $this->error('Unauthorized to delete this entry', 403);
        }

        $update->delete();

        return $this->success(null, 'Weekly update deleted (soft)');
    }

    // List trashed
    public function trashed(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $query = WeeklyUpdate::onlyTrashed()->with(['teacher.user','gurukal'])->orderBy('deleted_at', 'desc');

        if ($request->has('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        $items = $query->paginate($perPage);
        return $this->paginated($items, 'Trashed weekly updates fetched');
    }

    // Restore
    public function restore(Request $request, $id)
    {
        $update = WeeklyUpdate::onlyTrashed()->find($id);
        if (! $update) {
            return $this->error('Update not found', 404);
        }

        // only owner teacher or admin
        $user = $request->user();
        $isOwner = $update->teacher && $update->teacher->user_id === $user->id;
        if (! $isOwner && ! $user->hasRole('admin')) {
            return $this->error('Unauthorized to restore', 403);
        }

        $update->restore();
        return $this->success($update->load(['teacher.user','gurukal']), 'Weekly update restored');
    }

    // Force delete
    public function forceDelete(Request $request, $id)
    {
        $update = WeeklyUpdate::onlyTrashed()->find($id);
        if (! $update) {
            return $this->error('Update not found', 404);
        }

        $user = $request->user();
        $isOwner = $update->teacher && $update->teacher->user_id === $user->id;
        if (! $isOwner && ! $user->hasRole('admin')) {
            return $this->error('Unauthorized to permanently delete', 403);
        }

        $update->forceDelete();
        return $this->success(null, 'Weekly update permanently deleted');
    }

    /**
     * Students view endpoint:
     * returns updates for the student's gurukal (class)
     */
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

    $query = WeeklyUpdate::with(['teacher', 'gurukal'])
        ->whereIn('gurukal_id', $gurukalIds)
        ->orderBy('date', 'desc');

    if ($request->has('start_date') && $request->has('end_date')) {
        $query->whereBetween('date', [$request->start_date, $request->end_date]);
    } elseif ($request->has('date')) {
        $query->whereDate('date', $request->date);
    }

    $totalUpdates = $query->count();
    $items = $query->paginate($perPage);

    return $this->successWithPagination(
        $items->items(),
        [
            'count'        => $items->total(),
            'pageCount'    => $items->lastPage(),
            'perPage'      => $items->perPage(),
            'currentPage'  => $items->currentPage(),
            'total_updates'=> $totalUpdates
        ],
        'Weekly updates for student(s) fetched'
    );
}


}
