<?php

namespace App\Traits;

trait ApiResponse
{
    public function success($data = [], $message = 'Success', $code = 200)
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    public function error($message = 'Error', $code = 400, $data = [])
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    public function paginated($data, $message = 'Data fetched successfully')
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $data->items(),
                'pagination' => [
                'count'        => $data->total(),        
                'pageCount'    => $data->lastPage(),     
                'perPage'      => $data->perPage(),      
                'currentPage'  => $data->currentPage(),  
            ],
        ]);
    }
    public function successWithPagination($data = [], $pagination = [], $message = 'Success', $code = 200)
    {
        return response()->json([
            'status'     => true,
            'message'    => $message,
            'data'       => $data,
            'pagination' => $pagination,
        ], $code);
    }

    
}
