@extends('layouts.app')

@section('content')
<div class="container py-5 ">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h3 class="mb-0 text-center text-md-center text-lg-center">Confirmation de Réservation</h3>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h2><strong>Détails de la réservation</strong></h2><br/>
                            <p><strong>Numéro de réservation:</strong> {{ $reservation->code }}</p>
                            <p><strong>Date:</strong> {{ Carbon\Carbon::parse($reservation->date)->format('d/m/Y') }}</p>
                            <p><strong>Créneau:</strong> {{ $reservation->slot->start_time }}</p>
                            <p><strong>Quantité:</strong> {{ $reservation->quantity }}</p>
                            <p><strong>Taille:</strong> {{ $reservation->size }}</p>
                            <p><strong>Montant de l'acompte:</strong> {{ $reservation->quantity * 100 }}€</p>
                        </div>
                        <div class="col-md-6">
                            <h2><strong>Informations client</strong></h2><br/>
                            <p><strong>Nom:</strong> {{ $reservation->user->name }}</p>
                            <p><strong>Email:</strong> {{ $reservation->user->email }}</p>
                            @if($reservation->association)
                                <p><strong>Association:</strong> {{ $reservation->association->name }}</p>
                            @endif
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <a href="{{ route('reservation.receipt.download', $reservation->code) }}" 
                           class="btn btn-primary">
                            <i class="fas fa-download"></i> Télécharger le reçu (PDF)
                        </a>
                        <a href="{{ route('dashboard') }}" 
                           class="btn btn-secondary ms-2">
                            Retour au tableau de bord
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection