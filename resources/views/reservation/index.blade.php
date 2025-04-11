<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Réservation d'Agneau pour l'Eid</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://js.stripe.com/v3/"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <link rel="stylesheet" href="{{asset('css/template/style.css')}}">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="#"> Varaid </a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    @auth
                        {{-- <li class="nav-item">
                            <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
                        </li> --}}
                        <li class="nav-item">
                            <!-- Formulaire de déconnexion -->
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-link nav-link" style="display: inline; padding: 0;">
                                    Déconnexion
                                </button>
                            </form>
                        </li>
                    @endauth

                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Connexion</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">Inscription</a>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>


    <div class="container py-5">
        <div class="page-container">
            <h2 class="text-center mb-5">
                Réservez votre agneau pour l’Aïd Al Adha 2025
            </h2>
            
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


            <!-- Alertes spécifiques à chaque étape -->
            <div id="alert-step-1" class="alert alert-info mb-4 step-alert">
                <i class="bi bi-info-circle"></i> 
                En confirmant votre choix, vous recevrez une notification par sms et par mail détaillant vos rendez-vous.
            </div>
            
            <div id="alert-step-2" class="alert alert-info mb-4 step-alert" style="display: none;">
                <i class="bi bi-info-circle"></i>
                Votre horaire d’abattage sera identique au créneau choisi. 
            </div>
            
            <div id="alert-step-3" class="alert alert-info mb-4 step-alert" style="display: none;">
                <i class="bi bi-info-circle"></i>
                Un acompte de 100€ par réservataire est obligatoire. Acompte non remboursable.
            </div>
            
            <div id="alert-step-4" class="alert alert-info mb-4 step-alert" style="display: none;">
                <i class="bi bi-info-circle"></i>
                En confirmant cette réservation, vous acceptez que l'heure choisie soit la même pour le jour de votre choix et pour l'abattement de l'agneau. Une notification vous sera envoyée par mail et par SMS avec les détails.
            </div>
            
            <!-- Étape 1: Sélection du jour -->
            @include('reservation.partials.step1')
            
            <!-- Étape 2: Sélection de l'heure -->
            @include('reservation.partials.step2')
            
            <!-- Étape 3: Configuration de la commande -->
            @include('reservation.partials.step3')
            
            <!-- Étape 4: Paiement et confirmation -->
            @include('reservation.partials.step4')
            <!-- fin etape 4 -->
            
            <!-- Modal de confirmation -->
            @include('reservation.partials.modal')
            {{-- modal code --}}
            @include('reservation.partials.affiliation-modal')

        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/reservation.js') }}?v={{ time() }}"></script>
    <script>console.log("URL JS:", "{{ asset('js/reservation.js') }}?v={{ time() }}");</script>


    <script>
        // Make CSRF token and user info globally available
        window.csrf_token = '{{ csrf_token() }}';
        window.userInfo = {
            // name: '{{ $user->name }}',
            // firstName: '{{ $user->firstname}}',
            // Si vous avez les champs séparés de prénom et nom dans votre base de données, utilisez-les
            full_address: '{{ $user->full_address }}',
            firstName: '{{ $user->firstname}}',
            lastName: '{{ $user->name }}',
            email: '{{ $user->email }}',
            phone: '{{ $user->phone }}'
        };
        window.stripeConfig = {
            publicKey: "{{ config('services.stripe.key') }}"
        };


        // script pour gérer l'affichage de la modal et la vérification du code
        document.addEventListener('DOMContentLoaded', function() {
            // Variables globales
            const affiliationVerified = {{ $affiliationVerified ? 'true' : 'false' }};
            const affiliationModal = new bootstrap.Modal(document.getElementById('affiliationModal'));
            const affiliationForm = document.getElementById('affiliation-form');
            const affiliationAlert = document.getElementById('affiliation-alert');
            const resendCodeBtn = document.getElementById('resend-code-btn');
            
            // Afficher la modal si l'affiliation n'est pas vérifiée
            if (!affiliationVerified) {
                affiliationModal.show();
            }
            
            // Gérer la soumission du formulaire
            affiliationForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const code = document.getElementById('affiliation_code').value;
                const verifyBtn = document.getElementById('verify-code-btn');
                
                // Désactiver le bouton pendant la vérification
                verifyBtn.disabled = true;
                verifyBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Vérification...';
                
                // Envoyer la requête AJAX
                fetch('{{ route("affiliation.verify.ajax") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ affiliation_code: code })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Afficher un message de succès
                        affiliationAlert.classList.remove('d-none', 'alert-danger');
                        affiliationAlert.classList.add('alert-success');
                        affiliationAlert.textContent = data.message;
                        
                        // Fermer la modal après 2 secondes
                        setTimeout(() => {
                            affiliationModal.hide();
                            // Rafraîchir la page pour mettre à jour l'interface
                            window.location.reload();
                        }, 2000);
                    } else {
                        throw new Error(data.message);
                    }
                })
                .catch(error => {
                    // Afficher un message d'erreur
                    affiliationAlert.classList.remove('d-none', 'alert-success');
                    affiliationAlert.classList.add('alert-danger');
                    affiliationAlert.textContent = error.message || 'Une erreur est survenue. Veuillez réessayer.';
                    
                    // Réactiver le bouton
                    verifyBtn.disabled = false;
                    verifyBtn.textContent = 'Vérifier le code';
                });
            });
            
            // Gérer la demande d'un nouveau code
            resendCodeBtn.addEventListener('click', function() {
                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Envoi en cours...';
                
                fetch('{{ route("affiliation.resend.ajax") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Afficher un message de succès
                        affiliationAlert.classList.remove('d-none', 'alert-danger');
                        affiliationAlert.classList.add('alert-success');
                        affiliationAlert.textContent = data.message;
                    } else {
                        throw new Error(data.message);
                    }
                })
                .catch(error => {
                    // Afficher un message d'erreur
                    affiliationAlert.classList.remove('d-none', 'alert-success');
                    affiliationAlert.classList.add('alert-danger');
                    affiliationAlert.textContent = error.message || 'Une erreur est survenue. Veuillez réessayer.';
                })
                .finally(() => {
                    // Réactiver le bouton
                    this.disabled = false;
                    this.textContent = 'Recevoir un nouveau code';
                });
            });
        });
    </script>
</body>
</html>