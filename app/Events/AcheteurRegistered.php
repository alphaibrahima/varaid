<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AcheteurRegistered
{
    use Dispatchable, SerializesModels;

    public $acheteur;

    public function __construct(User $acheteur)
    {
        $this->acheteur = $acheteur;
    }
}