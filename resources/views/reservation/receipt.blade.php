@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow border-0 overflow-hidden">
                <!-- Header section with beautiful gradient -->
                <div class="card-header bg-success text-white p-4">
                    <h3 class="mb-3 text-center fw-bold">Confirmation de Réservation</h3>
                    
                    <!-- Jour du sacrifice mis en évidence -->
                    <div class="alert alert-warning py-2 mb-0 text-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-calendar-alt me-2"></i>
                            Jour du sacrifice : {{ $reservation->eid_day }}
                        </h5>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <!-- Details section -->
                    <div class="row g-0">
                        <!-- Left column - Reservation details -->
                        <div class="col-md-6 p-4">
                            <div class="mb-4">
                                <h5 class="bg-primary text-white p-2 rounded">Détails de la réservation</h5>
                                <div class="mt-3">
                                    <p class="mb-2"><strong>Numéro de réservation:</strong> {{ $reservation->code }}</p>
                                    <p class="mb-2"><strong>Date:</strong> {{ Carbon\Carbon::parse($reservation->date)->format('d/m/Y') }}</p>
                                    <p class="mb-2">
                                        <strong>Créneau:</strong>
                                        @if($reservation->slot && $reservation->slot->start_time)
                                            @if(is_string($reservation->slot->start_time))
                                                {{ substr($reservation->slot->start_time, 0, 5) }}
                                            @else
                                                {{ $reservation->slot->start_time->format('H:i') }}
                                            @endif
                                        @else
                                            Non spécifié
                                        @endif
                                    </p>
                                    <p class="mb-2"><strong>Quantité:</strong> {{ $reservation->quantity }}</p>
                                    <p class="mb-2">
                                        <strong>Sélection sur place:</strong> 
                                        @if($reservation->skip_selection)
                                            Non (l'agneau sera attribué par l'association)
                                        @else
                                            Oui (viendra choisir l'agneau)
                                        @endif
                                    </p>
                                    <p class="mb-0"><strong>Montant de l'acompte:</strong> {{ $reservation->quantity * 50 }}€</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right column - Client information -->
                        <div class="col-md-6 p-4 bg-light">
                            <div class="mb-4">
                                <h5 class="bg-primary text-white p-2 rounded">Informations client</h5>
                                <div class="mt-3">
                                    <p class="mb-2"><strong>Nom:</strong> {{ $reservation->user->name }}</p>
                                    <p class="mb-2"><strong>Email:</strong> {{ $reservation->user->email }}</p>
                                    @if($reservation->association)
                                        <p class="mb-2"><strong>Association:</strong> {{ $reservation->association->name }}</p>
                                    @endif
                                    <p class="mb-2">
                                        <strong>Statut:</strong> 
                                        <span class="badge bg-success rounded-pill px-3">Acompte payé</span>
                                    </p>
                                    <p class="mb-0">
                                        <strong>Référence de paiement:</strong> 
                                        <span class="text-muted">{{ $reservation->payment_intent_id }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Rappel du jour du sacrifice - doublement important -->
                    <div class="bg-warning text-dark p-3 text-center">
                        <h6 class="mb-0 fw-bold">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            IMPORTANT : Votre rendez-vous est prévu pour le {{ $reservation->eid_day }}
                        </h6>
                    </div>
                    
                    <!-- Action buttons -->
                    <div class="p-4 border-top text-center">
                        <a href="{{ route('reservation.receipt.download', $reservation->code) }}" 
                           class="btn btn-primary rounded-pill px-4 py-2 me-2">
                            <i class="fas fa-download me-1"></i> Télécharger le reçu (PDF)
                        </a>
                        <a href="{{ route('dashboard') }}" 
                           class="btn btn-secondary rounded-pill px-4 py-2">
                            <i class="fas fa-home me-1"></i> Retour au tableau de bord
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection