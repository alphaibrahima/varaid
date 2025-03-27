<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 30px; }
        .details { margin-bottom: 20px; }
        .footer { text-align: center; margin-top: 50px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reçu de Réservation</h1>
        <h2>{{ config('app.name') }}</h2>
    </div>

    <div class="details">
        <h3>Détails de la réservation</h3>
        <p><strong>Numéro:</strong> {{ $reservation->code }}</p>
        <p><strong>Date:</strong> {{ $reservation->date->format('d/m/Y') }}</p>
        <p><strong>Créneau:</strong> {{ $reservation->slot->start_time }}</p>
        <p><strong>Quantité:</strong> {{ $reservation->quantity }}</p>
        <p><strong>Taille:</strong> {{ $reservation->size }}</p>
        <p><strong>Acompte payé:</strong> {{ $reservation->quantity * 100 }}€</p>
    </div>

    <div class="details">
        <h3>Client</h3>
        <p><strong>Nom:</strong> {{ $reservation->user->name }}</p>
        <p><strong>Email:</strong> {{ $reservation->user->email }}</p>
        @if($reservation->association)
            <p><strong>Association:</strong> {{ $reservation->association->name }}</p>
        @endif
    </div>

    <div class="footer">
        <p>Merci pour votre réservation!</p>
    </div>
</body>
</html>