<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation d'Agneau pour l'Eid</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://js.stripe.com/v3/"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <link rel="stylesheet" href="{{asset('css/template/style.css')}}">
</head>
<body>
    <div class="container py-5">
        <div class="page-container">
            <h1 class="text-center mb-5">Réservation d'Agneau pour l'Eid</h1>
            
            <!-- Indicateur de progression -->
            <div class="step-indicator mb-5">
                <div class="step-progress" id="step-progress"></div>
                <div class="step-dots">
                    <div class="step-dot active" data-step="1"></div>
                    <div class="step-dot" data-step="2"></div>
                    <div class="step-dot" data-step="3"></div>
                    <div class="step-dot" data-step="4"></div>
                </div>
            </div>
            <!-- fin indicateur de progression -->
            
            <!-- Étape 1: Sélection du jour -->
            @include('reservation.partials.step1')
            <!-- fin etape 1 -->
            
            <!-- Étape 2: Sélection de l'heure -->
            @include('reservation.partials.step2')
            <!-- fin etape 2 -->
            
            <!-- Étape 3: Configuration de la commande -->
            @include('reservation.partials.step3')
            <!-- fin etape3 -->
            
            <!-- Étape 4: Paiement et confirmation -->
            @include('reservation.partials.step4')
            <!-- fin etape 4 -->
            
            <!-- Modal de confirmation -->
            @include('reservation.partials.modal')
            <!-- fin modal de confirmation -->

        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/reservation.js') }}?v={{ time() }}"></script>
    <script>console.log("URL JS:", "{{ asset('js/reservation.js') }}?v={{ time() }}");</script>
    
</body>
</html>