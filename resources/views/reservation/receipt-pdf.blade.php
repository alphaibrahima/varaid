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
        .admin-created {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 20px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <h3> {{ config('app.name') }} - Reçu de Réservation</h3>
    </div>

    @if(isset($isAdminCreated) && $isAdminCreated)
        <div class="admin-created">
            <p>Cette réservation a été créée par l'administration de la plateforme.</p>
        </div>
    @endif

    <div class="details">
        <h4>Détails de la réservation</h4>
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
                <td>
                    @if($reservation->slot && $reservation->slot->start_time)
                        @if(is_string($reservation->slot->start_time))
                            {{ substr($reservation->slot->start_time, 0, 5) }}
                        @else
                            {{ $reservation->slot->start_time->format('H:i') }}
                        @endif
                    @else
                        Non spécifié
                    @endif
                </td>
            </tr>
            <tr>
                <th>Jour du sacrifice :</th>
                <td>
                    {{ $reservation->eid_day }}
                </td>
            </tr>
            <tr>
                <th>Taille</th>
                <td>{{ ucfirst($reservation->size) }}</td>
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
            @if(!isset($isAdminCreated) || !$isAdminCreated)
                <tr>
                    <th>Acompte payé</th>
                    <td>{{ $reservation->quantity * 50 }} €</td>
                </tr>
                <tr>
                    <th>Solde à payer</th>
                    <td class="important">{{ $reservation->quantity * 100 }}€ (à régler sur place)</td>
                </tr>
            @endif
        </table>
    </div>

    <div class="details">
        <h4>Informations propriétaires</h4>
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
        <h4>Client</h4>
        <table>
            <tr>
                <th>Nom</th>
                <td>{{ $reservation->user->name }}</td>
            </tr>
            @if(isset($reservation->user->firstname))
            <tr>
                <th>Prénom</th>
                <td>{{ $reservation->user->firstname }}</td>
            </tr>
            @endif
            <tr>
                <th>Email</th>
                <td>{{ $reservation->user->email }}</td>
            </tr>
            <tr>
                <th>Téléphone</th>
                <td>{{ $reservation->user->phone }}</td>
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