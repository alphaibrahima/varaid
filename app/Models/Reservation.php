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

    protected $casts = [
        'date' => 'datetime',
        'association_id' => 'integer',
    ];

    // Corriger la relation association
    public function association()
    {
        return $this->belongsTo(User::class, 'association_id')->where('role', 'association');
    }

    // Ajouter la relation user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function slot()
    {
        return $this->belongsTo(Slot::class);
    }


    // public function association()
    // {
    //     return $this->belongsTo(Association::class); // MODIFIER (au lieu de User)
    // }
}
