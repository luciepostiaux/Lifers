<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Residence extends Model
{
    use HasFactory;

    protected $table = 'residences';
    protected $fillable = [
        'type',
        'prix_achat',
        'prix_mensuel',
        'taxe_mensuelle',
        'nombre_paiements',
        'periode_paiement',
        'description',
        'image_path'
    ];

    public function personnages()
    {
        return $this->belongsToMany(Perso::class, 'perso_residences')
        ->withPivot('active')
        ->withTimestamps();
    }
}
