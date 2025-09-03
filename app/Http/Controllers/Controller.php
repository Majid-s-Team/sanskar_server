<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;


public function paginate($paginator)
{
    return [
        'count' => $paginator->total(),
        'pageCount' => (int) ceil($paginator->total() / $paginator->perPage()),
        'perPage' => (int) $paginator->perPage(),
        'currentPage' => (int) $paginator->currentPage(),
    ];
}


}
