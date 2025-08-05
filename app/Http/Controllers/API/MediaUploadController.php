<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MediaUploadController extends Controller
{
    use ApiResponse;

    private $validKeys = [
        'profile_image',
        'user_image',
        'banner',
        'post_media',
        'gallery',
        'audio',
        'video',
    ];

    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key'   => 'required|string|in:' . implode(',', $this->validKeys),
            'media' => 'required|file|mimes:jpeg,png,jpg,mp4,mov,avi,mp3,wav,ogg|max:10240', // max 10MB
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        $key = $request->key;
        $file = $request->file('media');

        $path = $file->store("{$key}", 'public');

 
        $url = asset("storage/" . $path);

        return $this->success([
            'key' => $key,
            'url' => $url,
            'filename' => $file->getClientOriginalName()
        ], 'Media uploaded successfully');
    }
}
