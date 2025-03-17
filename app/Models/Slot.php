<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Slot extends Model
{
    use HasFactory;

    protected $fillable = [
        'date', 
        'start_time',
        'end_time',
        'max_reservations', 
        'available' 
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'available' => 'boolean',
    ];



    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    // Nouvelle méthode pour vérifier la disponibilité
    public function isAvailable(): bool
    {
        return $this->available && $this->reservations()->count() < $this->max_reservations;
    }

    // Méthode pour formater la plage horaire
    public function getTimeRangeAttribute(): string
    {
        return $this->start_time->format('H:i').' - '.$this->end_time->format('H:i');
    }
}