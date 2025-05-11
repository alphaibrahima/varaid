<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'message',
        'recipients_count',
        'status',
        'response',
        'sender_id',
    ];

    /**
     * Relation avec l'utilisateur qui a envoyé le SMS
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}