<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createPaymentIntent(Request $request)
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            
            // Pour déboguer, vérifiez si la clé est correctement chargée
            \Log::info('Stripe API Key: ' . substr(config('services.stripe.secret'), 0, 5) . '...');
            
            $amount = $request->input('quantity', 1) * 10000; // 100€ par agneau en centimes
            
            $paymentIntent = PaymentIntent::create([
                'amount' => $amount,
                'currency' => 'eur',
                'metadata' => [
                    'user_id' => auth()->id(),
                    'slot_id' => $request->input('slotId'),
                    'quantity' => $request->input('quantity'),
                ],
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);
    
            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
                'paymentIntentId' => $paymentIntent->id,
            ]);
        } catch (\Exception $e) {
            // Journaliser l'erreur avec plus de détails
            \Log::error('Stripe error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function handleSuccess(Request $request)
    {
        $ownersData = json_decode($request->input('owners'), true);
        
        // Si on a été redirigé par Stripe, on doit récupérer le payment_intent
        if ($request->has('payment_intent')) {
            $paymentIntentId = $request->input('payment_intent');
            
            try {
                // Vérifier le statut du paiement
                $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
                
                if ($paymentIntent->status === 'succeeded') {
                    // Créer la réservation
                    $reservation = Reservation::create([
                        'user_id' => auth()->id(),
                        'slot_id' => $paymentIntent->metadata->slot_id,
                        'association_id' => auth()->user()->association_id,
                        'size' => 'grand',
                        'quantity' => $paymentIntent->metadata->quantity,
                        'code' => 'R-' . rand(100000, 999999),
                        'status' => 'confirmed',
                        'date' => now(),
                        'owners_data' => json_encode($ownersData),
                        'payment_intent_id' => $paymentIntentId
                    ]);
                    
                    return redirect()->route('reservation.receipt', ['code' => $reservation->code]);
                }
            } catch (\Exception $e) {
                \Log::error('Payment confirmation error: ' . $e->getMessage());
                return redirect()->route('dashboard')->with('error', 'Une erreur est survenue lors de la confirmation du paiement.');
            }
        }
        
        return redirect()->route('dashboard')->with('error', 'Paiement non complété');
    }
}