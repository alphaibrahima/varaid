<?php

namespace App\Listeners;

use App\Events\AcheteurRegistered;
use App\Notifications\AffiliationCodeNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendAffiliationCode implements ShouldQueue
{
    public function handle(AcheteurRegistered $event)
    {
        $event->acheteur->notify(new AffiliationCodeNotification());
    }
}