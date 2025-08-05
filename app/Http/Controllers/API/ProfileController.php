<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
}
