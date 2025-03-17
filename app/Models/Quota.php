<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;

class Quota extends Model
{
    use HasFactory;

    protected $fillable = [
        'association_id',
        'quantite',
        'grand',
        'moyen',
        'petit'
    ];

    // Relation vers l'utilisateur (association)
    public function association()
    {
        return $this->belongsTo(User::class, 'association_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($quota) {
            if (Quota::where('association_id', $quota->association_id)->exists()) {
                Notification::make()
                    ->title('Erreur')
                    ->danger()
                    ->body('Un quota existe déjà pour cette association.')
                    ->send();

                return false; // Bloquer la création sans erreur fatale
            }
        });
    }
}
