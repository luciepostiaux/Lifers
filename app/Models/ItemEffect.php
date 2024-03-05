<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemEffect extends Model
{
    use HasFactory;

    protected $fillable = ['item_id', 'gauge', 'effect'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
