<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de Réservation</title>
</head>
<body>
    <h2>Bonjour {{ $reservation->user->name }},</h2>
    <p>Votre réservation a bien été enregistrée.</p>
    <ul>
        <li>Date : {{ $reservation->slot->date }}</li>
        <li>Heure : {{ $reservation->slot->start_time }}</li>
        <p><strong>Sélection sur place:</strong> 
            @if($reservation->skip_selection)
                Non (l'agneau sera attribué par l'association)
            @else
                Oui (viendra choisir l'agneau)
            @endif
        </p>
    </ul>
    <p>Merci et à bientôt !</p>
</body>
</html>
