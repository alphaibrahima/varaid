<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'slot_id', 
        'association_id', 
        'size', 
        'quantity', 
        'code', 
        'status',
        'date',
        'skip_selection',
        'owners_data',
        'payment_intent_id' 
        
    ];

    protected $casts = [
        'date' => 'datetime',
        'association_id' => 'integer',
        'skip_selection' => 'boolean',
        'owners_data' => 'array',
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

    /**
     * Scope a query to only include reservations from the current month.
     */
    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
    }


    // Dans app/Models/Reservation.php

    /**
     * Détermine le jour de l'Aïd correspondant à la date de réservation
     */
    public function getEidDayAttribute()
    {
        // Les dates spécifiques pour 2025
        $firstDay = \Carbon\Carbon::parse('2025-05-31');
        $secondDay = \Carbon\Carbon::parse('2025-06-01');
        
        if ($this->date && $this->date->isSameDay($firstDay)) {
            return '1er jour de l\'Aïd';
        } elseif ($this->date && $this->date->isSameDay($secondDay)) {
            return '2ème jour de l\'Aïd';
        } else {
            return 'jour de l\'Aïd'; // Cas par défaut
        }
    }
}
