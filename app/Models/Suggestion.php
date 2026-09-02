<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Suggestion extends Model
{
    use SoftDeletes;

    protected $fillable = ['lifer_id', 'content', 'status'];

    public function lifer()
    {
        return $this->belongsTo(Lifer::class);
    }
}
