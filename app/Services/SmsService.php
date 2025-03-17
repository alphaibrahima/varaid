<?php

namespace App\Services;

use Twilio\Rest\Client;

class SmsService
{
    public static function sendReservationNotification($phone, $reservation)
    {
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $from = env('TWILIO_PHONE_NUMBER');

        $client = new Client($sid, $token);
        $message = "Votre réservation est confirmée pour le " . 
                   $reservation->slot->date . " à " . 
                   $reservation->slot->start_time . ".";

        $client->messages->create($phone, [
            'from' => $from,
            'body' => $message
        ]);
    }
}
