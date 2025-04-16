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
}
