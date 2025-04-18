@extends('layouts.buyer')

@section('header', 'Tutoriels')

@section('content')
<div class="space-y-6">
    <!-- Introduction -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
        <div class="p-6">
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200 mb-4">
                Tutoriels vidéo
            </h2>
            <p class="text-gray-600 dark:text-gray-400">
                Découvrez nos tutoriels pour vous guider dans le processus de réservation d'agneaux pour l'Aïd. Ces vidéos vous aideront à comprendre facilement chaque étape du processus.
            </p>
        </div>
    </div>

    <!-- Tutoriels -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Tutoriel 1 -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
            <div class="aspect-w-16 aspect-h-9">
                <iframe class="w-full h-72" src="https://www.youtube.com/embed/VIDEO_ID_1" title="Comment créer un compte" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">Comment créer un compte</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-3">Apprenez à créer votre compte et à vous inscrire sur la plateforme.</p>
                <div class="flex items-center text-sm text-gray-500">
                    <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                    </svg>
                    <span>Durée: 3:45</span>
                </div>
            </div>
        </div>

        <!-- Tutoriel 2 -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
            <div class="aspect-w-16 aspect-h-9">
                <iframe class="w-full h-72" src="https://www.youtube.com/embed/VIDEO_ID_2" title="Comment vérifier son affiliation" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">Comment vérifier son affiliation</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-3">Ce tutoriel vous explique comment valider votre code d'affiliation.</p>
                <div class="flex items-center text-sm text-gray-500">
                    <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                    </svg>
                    <span>Durée: 2:30</span>
                </div>
            </div>
        </div>

        <!-- Tutoriel 3 -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
            <div class="aspect-w-16 aspect-h-9">
                <iframe class="w-full h-72" src="https://www.youtube.com/embed/VIDEO_ID_3" title="Comment faire une réservation" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">Comment faire une réservation</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-3">Guide complet pour effectuer votre réservation d'agneau étape par étape.</p>
                <div class="flex items-center text-sm text-gray-500">
                    <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                    </svg>
                    <span>Durée: 5:20</span>
                </div>
            </div>
        </div>

        <!-- Tutoriel 4 -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
            <div class="aspect-w-16 aspect-h-9">
                <iframe class="w-full h-72" src="https://www.youtube.com/embed/VIDEO_ID_4" title="Comment effectuer le paiement" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">Comment effectuer le paiement</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-3">Apprenez à effectuer le paiement en toute sécurité sur notre plateforme.</p>
                <div class="flex items-center text-sm text-gray-500">
                    <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                    </svg>
                    <span>Durée: 4:15</span>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                Questions fréquentes
            </h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                <div x-data="{ open: false }" class="border border-gray-200 dark:border-gray-700 rounded-lg">
                    <button @click="open = !open" class="w-full px-4 py-3 text-left font-medium text-gray-800 dark:text-gray-200 flex justify-between items-center focus:outline-none">
                        <span>Comment fonctionne le système de réservation ?</span>
                        <svg :class="{'rotate-180': open}" class="h-5 w-5 transform transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open" class="px-4 py-3 text-gray-600 dark:text-gray-400 text-sm border-t border-gray-200 dark:border-gray-700">
                        <p>Notre système de réservation vous permet de choisir un jour, un créneau horaire et le nombre d'agneaux que vous souhaitez réserver. Un acompte de 100€ par agneau est requis lors de la réservation. Le reste du paiement sera effectué sur place.</p>
                    </div>
                </div>

                <div x-data="{ open: false }" class="border border-gray-200 dark:border-gray-700 rounded-lg">
                    <button @click="open = !open" class="w-full px-4 py-3 text-left font-medium text-gray-800 dark:text-gray-200 flex justify-between items-center focus:outline-none">
                        <span>Que se passe-t-il après ma réservation ?</span>
                        <svg :class="{'rotate-180': open}" class="h-5 w-5 transform transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open" class="px-4 py-3 text-gray-600 dark:text-gray-400 text-sm border-t border-gray-200 dark:border-gray-700">
                        <p>Après votre réservation, vous recevrez une confirmation par email et SMS avec toutes les informations nécessaires. Le jour de votre rendez-vous, présentez-vous au site d'abattage à l'heure indiquée avec votre reçu de réservation.</p>
                    </div>
                </div>

                <div x-data="{ open: false }" class="border border-gray-200 dark:border-gray-700 rounded-lg">
                    <button @click="open = !open" class="w-full px-4 py-3 text-left font-medium text-gray-800 dark:text-gray-200 flex justify-between items-center focus:outline-none">
                        <span>Comment puis-je vérifier mon affiliation ?</span>
                        <svg :class="{'rotate-180': open}" class="h-5 w-5 transform transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open" class="px-4 py-3 text-gray-600 dark:text-gray-400 text-sm border-t border-gray-200 dark:border-gray-700">
                        <p>Pour vérifier votre affiliation, vous devez saisir le code d'affiliation qui vous a été envoyé par email et SMS lors de votre inscription. Vous pouvez le faire dans la section "Mon profil" ou lors de votre première tentative de réservation.</p>
                    </div>
                </div>

                <div x-data="{ open: false }" class="border border-gray-200 dark:border-gray-700 rounded-lg">
                    <button @click="open = !open" class="w-full px-4 py-3 text-left font-medium text-gray-800 dark:text-gray-200 flex justify-between items-center focus:outline-none">
                        <span>L'acompte est-il remboursable en cas d'annulation ?</span>
                        <svg :class="{'rotate-180': open}" class="h-5 w-5 transform transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open" class="px-4 py-3 text-gray-600 dark:text-gray-400 text-sm border-t border-gray-200 dark:border-gray-700">
                        <p>Non, l'acompte de 100€ par agneau n'est pas remboursable en cas d'annulation. Il sert à garantir votre réservation et à couvrir les frais administratifs.</p>
                    </div>
                </div>

                <div x-data="{ open: false }" class="border border-gray-200 dark:border-gray-700 rounded-lg">
                    <button @click="open = !open" class="w-full px-4 py-3 text-left font-medium text-gray-800 dark:text-gray-200 flex justify-between items-center focus:outline-none">
                        <span>Puis-je choisir mon agneau sur place ?</span>
                        <svg :class="{'rotate-180': open}" class="h-5 w-5 transform transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open" class="px-4 py-3 text-gray-600 dark:text-gray-400 text-sm border-t border-gray-200 dark:border-gray-700">
                        <p>Oui, vous avez la possibilité de choisir votre agneau sur place ou de laisser l'association l'attribuer pour vous. Cette option est disponible lors de votre réservation dans l'étape de configuration.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Support -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                Besoin d'aide supplémentaire ?
            </h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                Si vous avez d'autres questions ou si vous rencontrez des difficultés, n'hésitez pas à contacter notre équipe de support.
            </p>
            <div class="flex flex-col sm:flex-row gap-4">
                <a href="mailto:support@varaid.fr" class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Nous contacter par email
                </a>
                <a href="tel:+33612345678" class="inline-flex items-center justify-center px-5 py-3 border border-gray-300 text-base font-medium rounded-md text-indigo-600 bg-white hover:bg-gray-50">
                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    Nous appeler directement
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialisation des scripts spécifiques à la page tutoriels si nécessaire
    });
</script>
@endsection