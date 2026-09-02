<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GivenName extends Model
{
    protected $fillable = ['name', 'sex', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
