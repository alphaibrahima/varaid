<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;

class PaymentController extends Controller
{
    public function createPaymentIntent(Request $request)
    {
        try {
            // Récupérer et vérifier la clé API Stripe
            $stripeSecretKey = config('services.stripe.secret');
            if (empty($stripeSecretKey)) {
                throw new \Exception('Clé API Stripe non configurée');
            }
            
            // Définir la clé API (une seule fois suffit)
            Stripe::setApiKey($stripeSecretKey);
            
            // Log avec une partie de la clé pour le débogage
            \Log::info('Using Stripe key: ' . substr($stripeSecretKey, 0, 5) . '...' . substr($stripeSecretKey, -5));
            
            // Calculer le montant
            $quantity = $request->input('quantity', 1);
            $amount = $quantity * 10000; // 100€ par agneau en centimes
            
            // Récupérer les métadonnées
            $slotId = $request->input('slotId');
            
            // Créer le PaymentIntent avec les métadonnées nécessaires
            $paymentIntent = PaymentIntent::create([
                'amount' => $amount,
                'currency' => 'eur',
                'metadata' => [
                    'user_id' => auth()->id(),
                    'slot_id' => $slotId,
                    'quantity' => $quantity,
                ],
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);
    
            \Log::info('PaymentIntent created successfully: ' . $paymentIntent->id);
    
            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
                'paymentIntentId' => $paymentIntent->id,
            ]);
        } catch (ApiErrorException $e) {
            \Log::error('Stripe API error: ' . $e->getMessage());
            \Log::error('Stripe error code: ' . $e->getStripeCode());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'error' => 'Erreur Stripe: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            \Log::error('General error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'error' => 'Erreur lors de la création du paiement: ' . $e->getMessage()
            ], 500);
        }
    }

    public function handleSuccess(Request $request)
    {
        try {
            // Définir la clé API Stripe
            Stripe::setApiKey(config('services.stripe.secret'));
            
            // Décoder les données des propriétaires si disponibles
            $ownersData = $request->has('owners') 
                ? json_decode($request->input('owners'), true) 
                : [];
            
            // Si on a été redirigé par Stripe, on doit récupérer le payment_intent
            if ($request->has('payment_intent')) {
                $paymentIntentId = $request->input('payment_intent');
                
                // Vérifier le statut du paiement
                $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
                
                if ($paymentIntent->status === 'succeeded') {
                    // Vérifier si l'utilisateur est connecté
                    if (!auth()->check()) {
                        return redirect()->route('login')
                            ->with('error', 'Veuillez vous connecter pour finaliser votre réservation.');
                    }
                    
                    // S'assurer que les métadonnées sont présentes
                    if (empty($paymentIntent->metadata->slot_id) || empty($paymentIntent->metadata->quantity)) {
                        \Log::error('Métadonnées manquantes dans le PaymentIntent: ' . $paymentIntentId);
                        return redirect()->route('dashboard')
                            ->with('error', 'Informations de réservation incomplètes.');
                    }
                    
                    // Créer la réservation
                    $reservation = Reservation::create([
                        'user_id' => auth()->id(),
                        'slot_id' => $paymentIntent->metadata->slot_id,
                        'association_id' => auth()->user()->association_id ?? null,
                        'size' => 'grand',
                        'quantity' => $paymentIntent->metadata->quantity,
                        'code' => 'R-' . rand(100000, 999999),
                        'status' => 'confirmed',
                        'date' => now(),
                        'owners_data' => json_encode($ownersData),
                        'payment_intent_id' => $paymentIntentId
                    ]);
                    
                    return redirect()->route('reservation.receipt', ['code' => $reservation->code])
                        ->with('success', 'Votre réservation a été confirmée avec succès!');
                } else {
                    \Log::warning('PaymentIntent non réussi: ' . $paymentIntent->status);
                    return redirect()->route('dashboard')
                        ->with('error', 'Le paiement n\'a pas été complété. Statut: ' . $paymentIntent->status);
                }
            }
            
            return redirect()->route('dashboard')
                ->with('error', 'Aucune information de paiement trouvée.');
                
        } catch (ApiErrorException $e) {
            \Log::error('Stripe error in handleSuccess: ' . $e->getMessage());
            return redirect()->route('dashboard')
                ->with('error', 'Erreur Stripe: ' . $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Error in handleSuccess: ' . $e->getMessage());
            return redirect()->route('dashboard')
                ->with('error', 'Une erreur est survenue lors de la confirmation du paiement.');
        }
    }
}