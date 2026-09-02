<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LiferImage extends Model
{
    protected $fillable = ['lifer_id', 'image_path'];

    public function lifer()
    {
        return $this->belongsTo(Lifer::class);
    }

    protected static function booted(): void
    {
        static::deleted(function (LiferImage $image) {
            Storage::disk('public')->delete($image->image_path);
        });
    }
}
