<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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

    // Relations
    public function association()
    {
        return $this->belongsTo(User::class, 'association_id')->where('role', 'association');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function slot()
    {
        return $this->belongsTo(Slot::class);
    }

    // Scopes
    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
    }

    // Accesseurs
    public function getEidDayAttribute()
    {
        // Les dates spécifiques pour 2025
        $firstDay = Carbon::parse('2025-05-31');
        $secondDay = Carbon::parse('2025-06-01');
        
        if ($this->date && $this->date->isSameDay($firstDay)) {
            return '1er jour de l\'Aïd';
        } elseif ($this->date && $this->date->isSameDay($secondDay)) {
            return '2ème jour de l\'Aïd';
        } else {
            return 'jour de l\'Aïd'; // Cas par défaut
        }
    }

    // Méthode pour obtenir le nom du statut traduit
    public function getStatusNameAttribute(): string
    {
        return match($this->status) {
            'pending' => 'En attente',
            'confirmed' => 'Confirmée',
            'canceled' => 'Annulée',
            default => ucfirst($this->status),
        };
    }

    // Méthode pour formater les informations des propriétaires
    public function getOwnersListAttribute(): string
    {
        // Vérifier si owners_data est vide
        if (empty($this->owners_data)) {
            return 'Non spécifié';
        }
        
        // Convertir en tableau si c'est une chaîne
        $data = $this->owners_data;
        if (is_string($data)) {
            $data = json_decode($data, true);
        }
        
        // Vérifier si on a bien un tableau maintenant
        if (!is_array($data)) {
            return 'Format invalide';
        }
        
        // Créer la liste des propriétaires
        $ownersList = [];
        foreach ($data as $owner) {
            if (isset($owner['firstname']) && isset($owner['lastname'])) {
                $ownersList[] = $owner['firstname'] . ' ' . $owner['lastname'];
            }
        }
        
        return count($ownersList) > 0 ? implode(', ', $ownersList) : 'Non spécifié';
    }
}