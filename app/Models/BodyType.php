<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BodyType extends Model
{
    public const CODE_MALE = 'A';
    public const CODE_FEMALE = 'B';

    protected $fillable = ['code', 'label', 'sex', 'image_path'];

    public function hairstyles()
    {
        return $this->hasMany(Hairstyle::class);
    }

    public function wearables()
    {
        return $this->hasMany(Wearable::class);
    }
}
