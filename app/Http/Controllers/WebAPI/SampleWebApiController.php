<?php

namespace App\Http\Controllers\WebAPI;

use App\Http\Controllers\Controller;

class SampleWebApiController extends Controller
{
    public function ping()
    {
        return response()->json(['message' => 'WebAPI Route Working!']);
    }
}
