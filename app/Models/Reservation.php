<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'slot_id', 
        'association_id', // AJOUTER
        'size', 
        'quantity', // AJOUTER (si manquant)
        'code', 
        'status',
        'date', // AJOUTER (si manquant)
        'payment_intent_id' // AJOUTER (si manquant)
    ];

    public function slot()
    {
        return $this->belongsTo(Slot::class);
    }


    public function association()
    {
        return $this->belongsTo(Association::class); // MODIFIER (au lieu de User)
    }
}
