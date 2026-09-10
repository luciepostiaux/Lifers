<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadLiferProfileImageRequest;
use App\Models\LiferImage;
use App\Services\UploadedImageOptimizer;
use Illuminate\Http\JsonResponse;

class LiferProfileImageController extends Controller
{
    public function store(
        UploadLiferProfileImageRequest $request,
        UploadedImageOptimizer $imageOptimizer,
    ): JsonResponse {
        $lifer = $this->activeLifer();
        $path = $imageOptimizer->store(
            $request->file('image'),
            "lifer-profiles/{$lifer->id}",
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
