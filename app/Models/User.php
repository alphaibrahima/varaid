<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use App\Notifications\ResetPassword;
use App\Models\Reservation;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'firstname',
        'email',
        'password',
        'phone',
        'full_address',
        'contact_phone',
        'address',
        'registration_number',
        'role',
        'association_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    protected static function booted(): void
    {
        static::saving(function (User $user) {
            $user->phone = preg_replace('/[^\d]/', '', $user->phone);
        });
    }

    protected function formattedPhone(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->phone ? $this->format_phone_number($this->phone) : null
        );
    }

    private function format_phone_number($phone): string
    {
        foreach (config('countries.phone_codes') as $country) {
            if (str_starts_with($phone, $country['code'])) {
                $local = substr($phone, strlen($country['code']));
                return '+' . $country['code'] . ' ' . chunk_split($local, 2, ' ');
            }
        }
        return $phone;
    }

    public function association(): BelongsTo
    {
        return $this->belongsTo(User::class, 'association_id')
            ->where('role', 'association');
    }

    public function buyers(): HasMany
    {
        return $this->hasMany(User::class, 'association_id')
            ->where('role', 'buyer');
    }

    /**
     * Relation avec les réservations de l'utilisateur
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
    
    /**
     * Scope a query to only include active users.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Spécifie le destinataire pour les notifications Brevo
     */
    
    public function routeNotificationForBrevo($notification)
    {
        // Formatage du numéro de téléphone pour Brevo
        $phone = preg_replace('/[^0-9]/', '', $this->phone);
        
        // Si le numéro commence par '0', le remplacer par '+33' (pour la France)
        if (substr($phone, 0, 1) === '0') {
            $phone = '+33' . substr($phone, 1);
        }
        
        // Si le numéro ne commence pas par '+', l'ajouter
        if (substr($phone, 0, 1) !== '+') {
            $phone = '+' . $phone;
        }
        
        return $phone;
    }
}