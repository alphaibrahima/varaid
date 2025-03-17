<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
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
}