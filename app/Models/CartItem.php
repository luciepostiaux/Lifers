<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = ['perso_id', 'item_id', 'quantity']; // Assurez-vous que ces champs sont corrects selon votre base de données

    // Définir la relation avec Perso
    public function perso()
    {
        return $this->belongsTo(Perso::class); // Assurez-vous que le modèle Perso existe et est correctement configuré
    }

    // Définir la relation avec Item
    public function item()
    {
        return $this->belongsTo(Item::class); // Assurez-vous que le modèle Item existe et est correctement configuré
    }
}
