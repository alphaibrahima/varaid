<?php
require 'vendor/autoload.php';

$stripeKey = 'sk_test_nouvelle_cle_secrete';

try {
    $stripe = new \Stripe\StripeClient($stripeKey);
    $paymentIntents = $stripe->paymentIntents->all(['limit' => 1]);
    echo "Connexion à Stripe réussie\n";
    echo "Nombre d'intentions de paiement récupérées: " . count($paymentIntents->data) . "\n";
} catch (\Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}