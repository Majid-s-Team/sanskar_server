<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UserReportExport;
use Illuminate\Pagination\Paginator;





class ProfileController extends Controller
{
    use ApiResponse;

    public function view(Request $request)
    {
        $user = $request->user()->load(['students', 'fatherActivities', 'motherActivities']);
        return $this->success($user, 'Profile fetched');
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'primary_email'            => 'nullable|email',
            'secondary_email'          => 'nullable|email',
            'mobile_number'            => 'nullable|string|max:20',
            'secondary_mobile_number'  => 'nullable|string|max:20',
            'father_name'              => 'nullable|string|max:255',
            'mother_name'              => 'nullable|string|max:255',
            'father_volunteering'      => 'nullable|boolean',
            'mother_volunteering'      => 'nullable|boolean',
            'is_hsnc_member'           => 'nullable|boolean',
            'address'                  => 'nullable|string|max:500',
            'city'                     => 'nullable|string|max:100',
            'state'                    => 'nullable|string|max:100',
            'zip_code'                 => 'nullable|string|max:10',
            'is_active'                => 'nullable|boolean',
            'is_payment_done'          => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        $user = $request->user();
        $user->update($validator->validated());

        return $this->success($user->fresh(), 'Profile updated successfully');
    }

  public function getUserPdf(Request $request)
    {
        $key = $request->input('key', 'pdf'); // default: pdf

        // Validate format
        if (!in_array($key, ['pdf', 'xlsx', 'csv'])) {
            return response()->json(['error' => 'Invalid format. Allowed: pdf, xlsx, csv'], 422);
        }

        // Fetch data
        $data = DB::table('users')
            ->select(
                'users.primary_email',
                'users.mobile_number',
                'users.father_name',
                'users.mother_name',
                DB::raw("CONCAT(students.first_name, ' ', students.last_name) as student_name"),
                'students.student_email',
                'students.student_mobile_number',
                DB::raw("CONCAT(users.city, ', ', users.state) as address"),
                DB::raw("SUM(payments.amount) as total_payment"),
                'payments.currency'
            )
            ->join('students', 'users.id', '=', 'students.user_id')
            ->join('payments', 'payments.user_id', '=', 'users.id')
            ->where('payments.status', '=', 'completed')
            ->where('users.is_payment_done', '=', '1')
            ->groupBy(
                'users.primary_email',
                'users.mobile_number',
                'users.father_name',
                'users.mother_name',
                'students.first_name',
                'students.last_name',
                'students.student_email',
                'students.student_mobile_number',
                'users.city',
                'users.state',
                'payments.currency'
            )
            ->orderBy('users.id')
            ->offset(2)
            ->limit(1000)
            ->get();

        // Format data
        $formatted = $data->map(function ($item, $index) {
            return [
                'S.No' => $index + 1,
                'Primary Email' => $item->primary_email,
                'Primary Mobile Number' => $item->mobile_number,
                'Father Full Name' => $item->father_name,
                'Mother Full Name' => $item->mother_name,
                'Student Name' => $item->student_name,
                'Student Email' => $item->student_email,
                'Student Mobile Number' => $item->student_mobile_number,
                'Address' => $item->address,
                'Total Payment' => $item->total_payment,
                'Currency' => $item->currency,
            ];
        })->toArray();

        $filename = 'Student_Report_' . now()->format('Ymd_His');

        // Excel or CSV
        if (in_array($key, ['xlsx', 'csv'])) {
            return Excel::download(new UserReportExport($formatted), $filename . '.' . $key);
        }

        // PDF — generate HTML directly (no view)
        $html = '<h2>Student Report</h2>';
        $html .= '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%; font-size: 10px;">';
        $html .= '<thead><tr>
            <th>S.No</th>
            <th>Primary Email</th>
            <th>Primary Mobile</th>
            <th>Father Name</th>
            <th>Mother Name</th>
            <th>Student Name</th>
            <th>Student Email</th>
            <th>Student Mobile</th>
            <th>Address</th>
            <th>Total Payment</th>
            <th>Currency</th>
        </tr></thead><tbody>';

        foreach ($formatted as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td>' . htmlspecialchars($cell) . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        return Pdf::loadHTML($html)->setPaper('a3', 'landscape')->download($filename . '.pdf');
    }

   public function viewAll(Request $request)
{
    $request->validate([
        'per_page' => 'sometimes|integer|min:1|max:100',
        'page' => 'sometimes|integer|min:1',
    ]);

    $perPage = $request->input('per_page', 10);
    $page = $request->input('page', 1);

    Paginator::currentPageResolver(function () use ($page) {
        return $page;
    });

    $users = User::with(['students', 'fatherActivities', 'motherActivities'])->where('is_payment_done', 1)
        ->Where('is_otp_verified',0)
        ->orderBy('id', 'asc')
        ->paginate($perPage);

    return $this->success([
        'users' => $users->items(),
        'pagination' => $this->paginate($users),
    ], 'Users Fetched Successfully');
}



}
