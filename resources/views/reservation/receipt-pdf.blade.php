<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { 
            font-family: Arial, sans-serif; 
            padding: 20px;
        }
        .header { 
            text-align: center; 
            margin-bottom: 30px; 
            border-bottom: 1px solid #ccc;
            padding-bottom: 20px;
        }
        .details { 
            margin-bottom: 20px; 
        }
        .footer { 
            text-align: center; 
            margin-top: 50px; 
            font-size: 12px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .important {
            color: #e74c3c;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reçu de Réservation</h1>
        <h2>{{ config('app.name') }}</h2>
    </div>

    <div class="details">
        <h3>Détails de la réservation</h3>
        <table>
            <tr>
                <th>Numéro</th>
                <td>{{ $reservation->code }}</td>
            </tr>
            <tr>
                <th>Date</th>
                <td>{{ $reservation->slot->date->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <th>Créneau</th>
                <td>{{ $reservation->slot->start_time }}</td>
            </tr>
            <tr>
                <th>Quantité</th>
                <td>{{ $reservation->quantity }}</td>
            </tr>
            <tr>
                <th>Sélection sur place</th>
                <td> 
                    @if($reservation->skip_selection)
                        Non (l'agneau sera attribué par l'association)
                    @else
                        Oui (viendra choisir l'agneau)
                    @endif
                </td>
            </tr>
            <tr>
                <th>Acompte payé</th>
                <td>{{ $reservation->quantity * 100 }}€</td>
            </tr>
            {{-- <tr>
                <th>Solde à payer</th>
                <td class="important">{{ $reservation->quantity * 100 }}€ (à régler sur place)</td>
            </tr> --}}
        </table>
    </div>

    <div class="details">
        <h3>Informations propriétaires</h3>
        <table>
            <tr>
                <th>#</th>
                <th>Prénom</th>
                <th>Nom</th>
            </tr>
            @if($reservation->owners_data && is_string($reservation->owners_data))
                @php $owners = json_decode($reservation->owners_data); @endphp
                @if(is_array($owners) || is_object($owners))
                    @foreach($owners as $index => $owner)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $owner->firstname }}</td>
                            <td>{{ $owner->lastname }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr><td colspan="3">Aucune information de propriétaire disponible</td></tr>
                @endif
            @else
                <tr><td colspan="3">Aucune information de propriétaire disponible</td></tr>
            @endif
        </table>
    </div>

    <div class="details">
        <h3>Client</h3>
        <table>
            <tr>
                <th>Nom</th>
                <td>{{ $reservation->user->name }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $reservation->user->email }}</td>
            </tr>
            @if($reservation->association)
            <tr>
                <th>Association</th>
                <td>{{ $reservation->association->name }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="footer">
        <p>Merci pour votre réservation! Conservez ce reçu et présentez-le lors de la récupération.</p>
        <p>Date d'émission: {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>