<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadLiferProfileImageRequest;
use App\Models\LiferImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class LiferProfileImageController extends Controller
{
    public function store(UploadLiferProfileImageRequest $request): JsonResponse
    {
        $lifer = $this->activeLifer();
        $file = $request->file('image');
        $extension = strtolower($file->extension() ?: 'jpg');
        $path = $file->storeAs(
            "lifer-profiles/{$lifer->id}",
            Str::uuid().'.'.$extension,
            'public',
        );

        $image = $lifer->profileImages()->create(['image_path' => $path]);

        return response()->json([
            'id' => $image->id,
            'url' => '/storage/'.$path,
        ], 201);
    }

    public function destroy(LiferImage $image): JsonResponse
    {
        abort_unless($image->lifer_id === $this->activeLifer()->id, 403);
        $image->delete();

        return response()->json(null, 204);
    }
}
