<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Maatwebsite\Excel\Concerns\FromArray;



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

public function getUserPdf()
{
    // Fetch data from DB
    $data = DB::table('users')
        ->select(
            'users.primary_email as primary_email',
            'users.mobile_number as primary_mobile_number',
            'users.father_name as father_full_name',
            'users.mother_name as mother_full_name',
            DB::raw("CONCAT(students.first_name, ' ', students.last_name) as student_name"),
            'students.student_email as student_email',
            'students.student_mobile_number as student_mobile_number',
            DB::raw("CONCAT(users.city, ', ', users.state) as address"),
            DB::raw("SUM(payments.amount) as total_payment"),
            'payments.currency as currency'
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

    // Add serial numbers
    $data = $data->map(function ($item, $index) {
        $item->serial_number = $index + 1;
        return $item;
    });

    // Build HTML for PDF
    $html = '<h2>Student Report</h2>';
    $html .= '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">';
    $html .= '<thead><tr>
        <th>S.No </th>
        <th>Primary Email</th>
        <th>Primary Mobile Number</th>
        <th>Father Full Name</th>
        <th>Mother Full Name</th>
        <th>Student Name</th>
        <th>Student Email</th>
        <th>Student Mobile Number</th>
        <th>Address</th>
        <th>Total Payment</th>
        <th>Currency</th>
    </tr></thead><tbody>';

    foreach ($data as $row) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($row->serial_number) . '</td>';
        $html .= '<td>' . htmlspecialchars($row->primary_email) . '</td>';
        $html .= '<td>' . htmlspecialchars($row->primary_mobile_number) . '</td>';
        $html .= '<td>' . htmlspecialchars($row->father_full_name) . '</td>';
        $html .= '<td>' . htmlspecialchars($row->mother_full_name) . '</td>';
        $html .= '<td>' . htmlspecialchars($row->student_name) . '</td>';
        $html .= '<td>' . htmlspecialchars($row->student_email) . '</td>';
        $html .= '<td>' . htmlspecialchars($row->student_mobile_number) . '</td>';
        $html .= '<td>' . htmlspecialchars($row->address) . '</td>';
        $html .= '<td>' . htmlspecialchars($row->total_payment) . '</td>';
        $html .= '<td>' . htmlspecialchars($row->currency) . '</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody></table>';

$pdf = Pdf::loadHTML($html)->setPaper('a2', 'landscape');
    return $pdf->download('Student_Report_' . now()->format('Ymd_His') . '.pdf');
}

}
