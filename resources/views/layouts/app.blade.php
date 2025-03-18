<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Réservations Eid') }}</title>
    
    <!-- Intégration de Vite pour les assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Styles Bootstrap et personnalisés -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* ... (vos styles CSS existants) ... */
    </style>
</head>
<body>
    <!-- Contenu principal -->
    <div class="container py-5">
        @yield('content')
    </div>

  

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
    @push('scripts')
    @vite(['resources/js/reservation.js']) <!-- Ajoutez cette ligne -->
    <script>
        // Initialisation Stripe
        window.stripeKey = '{{ config('services.stripe.key') }}';
    </script>
@endpush
</body>
</html>