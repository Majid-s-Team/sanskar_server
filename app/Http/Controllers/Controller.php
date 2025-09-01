<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function paginateUsers($perPage = 10)
{
    $users = User::with(['students', 'fatherActivities', 'motherActivities'])
        ->orderBy('id', 'asc')
        ->paginate($perPage);

   return $this->success($users, 'Paginated users fetched');
}

}
