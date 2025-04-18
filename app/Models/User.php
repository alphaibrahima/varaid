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
        'affiliation_code',
        'affiliation_verified',
        'affiliation_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'affiliation_verified' => 'boolean',
        'affiliation_verified_at' => 'datetime',
    ];


    // Ajoutez cette méthode pour générer un code d'affiliation
    public function generateAffiliationCode()
    {
        $code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
        $this->update(['affiliation_code' => $code]);
        return $code;
    }

    // Méthode pour marquer l'affiliation comme vérifiée
    public function markAffiliationAsVerified()
    {
        return $this->update([
            'affiliation_verified' => true,
            'affiliation_verified_at' => now(),
        ]);
    }

    // Vérifier si l'affiliation est confirmée
    public function hasVerifiedAffiliation()
    {
        return $this->affiliation_verified;
    }

    

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
}