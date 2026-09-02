<?php

namespace App\Http\Controllers;

use App\Models\Lifer;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function activeLifer(array $with = []): Lifer
    {
        $query = request()->user()->activeLifer();

        if ($with !== []) {
            $query->with($with);
        }

        return $query->firstOrFail();
    }
}
