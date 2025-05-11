<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Slot extends Model
{
    use HasFactory;

    protected $fillable = [
        'date', 
        'start_time',
        'end_time',
        'max_reservations', 
        'available' ,
        'block_reason'
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



    // Méthode pour formater la plage horaire
    public function getTimeRangeAttribute(): string
    {
        return $this->start_time->format('H:i').' - '.$this->end_time->format('H:i');
    }


    // Ajouter un accesseur pour la date formatée
    protected function formattedDate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->date->isoFormat('dddd D MMMM YYYY')
        );
    }

    // Modifier la méthode isAvailable()
    public function isAvailableFor(int $quantity): bool
    {
        return $this->available 
            && ($this->reservations()->count() + $quantity) <= $this->max_reservations;
    }

    // Ajoutez ceci dans la classe Slot
    public function scopeAvailable($query)
    {
        return $query->where('available', true)
            ->whereRaw('(SELECT COUNT(*) FROM reservations WHERE slot_id = slots.id) < slots.max_reservations');
    }

    // Modifiez la méthode isAvailable() existante :
    public function isAvailable(): bool
    {
        return $this->available && ($this->reservations_count < $this->max_reservations);
    }

    public function getAvailablePlacesAttribute()
    {
        $reservedCount = $this->reservations()->where('status', '!=', 'canceled')->sum('quantity');
        return max(0, $this->max_reservations - $reservedCount);
    }
}