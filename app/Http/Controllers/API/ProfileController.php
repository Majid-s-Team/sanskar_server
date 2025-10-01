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
            'primary_email' => 'nullable|email',
            'secondary_email' => 'nullable|email',
            'mobile_number' => 'nullable|string|max:20',
            'secondary_mobile_number' => 'nullable|string|max:20',
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'father_volunteering' => 'nullable|boolean',
            'mother_volunteering' => 'nullable|boolean',
            'father_activity_ids' => 'nullable|array',
            'mother_activity_ids' => 'nullable|array',
            'is_hsnc_member' => 'nullable|boolean',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:10',
            'is_active' => 'nullable|boolean',
            'is_payment_done' => 'nullable|boolean',
            'profile_image' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        $user = $request->user();
        $data = $validator->validated();

        $user->update($data);

        if (!empty($data['father_activity_ids'])) {
            $user->fatherActivities()->sync($data['father_activity_ids'] ?? []);
        } else {
            $user->fatherActivities()->sync([]);
        }

        if (!empty($data['mother_activity_ids'])) {
            $user->motherActivities()->sync($data['mother_activity_ids'] ?? []);
        } else {
            $user->motherActivities()->sync([]);
        }

        $user->load(['fatherActivities', 'motherActivities']);

        return $this->success($user, 'Profile updated successfully');
    }


    //   public function getUserPdf(Request $request)
//     {
//         $key = $request->input('key', 'pdf'); // default: pdf

    //         // Validate format
//         if (!in_array($key, ['pdf', 'xlsx', 'csv'])) {
//             return response()->json(['error' => 'Invalid format. Allowed: pdf, xlsx, csv'], 422);
//         }


    //         // Fetch data
//         $data = DB::table('users')
//             ->select(
//                 'users.primary_email',
//                 'users.mobile_number',
//                 'users.father_name',
//                 'users.mother_name',
//                 DB::raw("CONCAT(students.first_name, ' ', students.last_name) as student_name"),
//                 'students.student_email',
//                 'students.student_mobile_number',
//                 DB::raw("CONCAT(users.city, ', ', users.state) as address"),
//                 DB::raw("SUM(payments.amount) as total_payment"),
//                 'payments.currency'
//             )
//             ->join('students', 'users.id', '=', 'students.user_id')
//             ->join('payments', 'payments.user_id', '=', 'users.id')
//             ->where('payments.status', '=', 'completed')
//             ->where('users.is_payment_done', '=', '1')
//             ->groupBy(
//                 'users.primary_email',
//                 'users.mobile_number',
//                 'users.father_name',
//                 'users.mother_name',
//                 'students.first_name',
//                 'students.last_name',
//                 'students.student_email',
//                 'students.student_mobile_number',
//                 'users.city',
//                 'users.state',
//                 'payments.currency'
//             )
//             ->orderBy('users.id')
//             ->offset(2)
//             ->limit(1000)
//             ->get();

    //         // Format data
//         $formatted = $data->map(function ($item, $index) {
//             return [
//                 'S.No' => $index + 1,
//                 'Primary Email' => $item->primary_email,
//                 'Primary Mobile Number' => $item->mobile_number,
//                 'Father Full Name' => $item->father_name,
//                 'Mother Full Name' => $item->mother_name,
//                 'Student Name' => $item->student_name,
//                 'Student Email' => $item->student_email,
//                 'Student Mobile Number' => $item->student_mobile_number,
//                 'Address' => $item->address,
//                 'Total Payment' => $item->total_payment,
//                 'Currency' => $item->currency,
//             ];
//         })->toArray();

    //         $filename = 'Student_Report_' . now()->format('Ymd_His');

    //         // Excel or CSV
//         if (in_array($key, ['xlsx', 'csv'])) {
//             return Excel::download(new UserReportExport($formatted), $filename . '.' . $key);
//         }

    //         // PDF — generate HTML directly (no view)
//         $html = '<h2>Student Report</h2>';
//         $html .= '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%; font-size: 10px;">';
//         $html .= '<thead><tr>
//             <th>S.No</th>
//             <th>Primary Email</th>
//             <th>Primary Mobile</th>
//             <th>Father Name</th>
//             <th>Mother Name</th>
//             <th>Student Name</th>
//             <th>Student Email</th>
//             <th>Student Mobile</th>
//             <th>Address</th>
//             <th>Total Payment</th>
//             <th>Currency</th>
//         </tr></thead><tbody>';

    //         foreach ($formatted as $row) {
//             $html .= '<tr>';
//             foreach ($row as $cell) {
//                 $html .= '<td>' . htmlspecialchars($cell) . '</td>';
//             }
//             $html .= '</tr>';
//         }

    //         $html .= '</tbody></table>';

    //         return Pdf::loadHTML($html)->setPaper('a3', 'landscape')->download($filename . '.pdf');
//     }
    public function getUserPdf(Request $request)
    {
        $key = $request->input('key', 'pdf');

        if (!in_array($key, ['pdf', 'xlsx', 'csv'])) {
            return response()->json(['error' => 'Invalid format. Allowed: pdf, xlsx, csv'], 422);
        }

        $users = User::with([
            'students.teeshirtSize',
            'students.gurukal',
            'students.schoolGrade',
            'fatherActivities',
            'motherActivities'
        ])
            ->where('is_payment_done', 1)
            ->where('is_otp_verified', 0)
            ->orderBy('id', 'asc')
            ->get();

        $formatted = [];
        $counter = 1;

        foreach ($users as $user) {
            foreach ($user->students as $student) {
                $formatted[] = [
                    'S.No' => $counter++,
                    'Father Name' => $user->father_name,
                    'Mother Name' => $user->mother_name,
                    'Primary Email' => $user->primary_email,
                    'Secondary Email' => $user->secondary_email,
                    'Mobile Number' => $user->mobile_number,
                    'Secondary Mobile Number' => $user->secondary_mobile_number,
                    'Address' => $user->address,
                    'City' => $user->city,
                    'State' => $user->state,
                    'Zip Code' => $user->zip_code,
                    'Is HSNC Member' => $user->is_hsnc_member ? 'Yes' : 'No',
                    'Father Activities' => $user->fatherActivities->pluck('name')->implode(', '),
                    'Mother Activities' => $user->motherActivities->pluck('name')->implode(', '),
                    'Student Image' => $student->profile_image ?? '',
                    'Student Name' => $student->first_name . ' ' . $student->last_name,
                    'School Name' => $student->school_name,
                    'Date Of Birth' => $student->dob,
                    'Student Email' => $student->student_email,
                    'Student Number' => $student->student_mobile_number,
                    'Hobbies/Interests' => $student->hobbies_interest,
                    'Any Allergies' => $student->any_allergies,
                    'Join The Club' => $student->join_the_club ? 'Yes' : 'No',

                    'Teeshirt Size' => $student->teeshirtSize->name ?? '',
                    'Gurukal' => $student->gurukal->name ?? '',
                    'School Grade' => $student->schoolGrade->name ?? '',
                ];
            }
        }

        $filename = 'User_Report_' . now()->format('Ymd_His');

        if (in_array($key, ['xlsx', 'csv'])) {
            return Excel::download(new UserReportExport($formatted), $filename . '.' . $key);
        }

        $html = '<h2>User & Student Report</h2>';
        $html .= '<table border="1" cellpadding="4" cellspacing="0" style="border-collapse: collapse; width: 100%; font-size: 8px;">';
        $html .= '<thead><tr>';

        if (!empty($formatted)) {
            foreach (array_keys($formatted[0]) as $header) {
                $html .= '<th>' . htmlspecialchars($header) . '</th>';
            }
        }

        $html .= '</tr></thead><tbody>';

        foreach ($formatted as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td>' . htmlspecialchars($cell) . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        return Pdf::loadHTML($html)
            ->setPaper('a3', 'landscape')
            ->download($filename . '.pdf');
    }


    public function viewAll(Request $request)
    {
        $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
            'page' => 'sometimes|integer|min:1',
            'user_id' => 'sometimes|integer|exists:users,id',
            'primary_email' => 'sometimes|string',
            'secondary_email' => 'sometimes|string',
            'father_name' => 'sometimes|string',
            'mother_name' => 'sometimes|string',
        ]);

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        Paginator::currentPageResolver(function () use ($page) {
            return $page;
        });

        $query = User::with(['students', 'fatherActivities', 'motherActivities'])
            ->where('is_payment_done', 1)
            ->where('is_otp_verified', 0);

        if ($request->has('user_id')) {
            $query->where('id', $request->user_id);
        }

        if ($request->has('primary_email')) {
            $query->where('primary_email', 'like', "%{$request->primary_email}%");
        }

        if ($request->has('secondary_email')) {
            $query->where('secondary_email', 'like', "%{$request->secondary_email}%");
        }

        if ($request->has('father_name')) {
            $query->where('father_name', 'like', "%{$request->father_name}%");
        }

        if ($request->has('mother_name')) {
            $query->where('mother_name', 'like', "%{$request->mother_name}%");
        }

        $users = $query->orderBy('id', 'asc')->paginate($perPage);

        if ($users->isEmpty()) {
            return $this->error('No matching users found', 404);
        }

        return $this->success([
            'users' => $users->items(),
            'pagination' => $this->paginate($users),
        ], 'Users fetched successfully');
    }


    public function testPdf()
    {
        return Pdf::loadHTML('<h1>Hello PDF</h1>')
            ->setPaper('a4', 'portrait')
            ->download('test.pdf');
    }


}
