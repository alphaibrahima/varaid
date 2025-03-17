<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Association extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'registration_number',
        'address',
        'contact_email',
        'is_active',
    ];

    // Garder la relation existante (une association a plusieurs utilisateurs)
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Ajouter la nouvelle relation (une association a un quota)
    public function quota()
    {
        return $this->hasOne(Quota::class);
    }
}