@extends('layouts.buyer')

@section('header', 'Aide & FAQ')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                Foire aux questions
            </h3>
        </div>
        
        <div class="p-6 space-y-6">
            <!-- Section FAQ -->
            <div class="space-y-4">
                <div x-data="{ open: false }" class="border border-gray-200 dark:border-gray-700 rounded-lg">
                    <button @click="open = !open" class="w-full px-4 py-3 text-left font-medium text-gray-800 dark:text-gray-200 flex justify-between items-center focus:outline-none">
                        <span>Comment fonctionne le système de réservation ?</span>
                        <svg :class="{'rotate-180': open}" class="h-5 w-5 transform transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open" class="px-4 py-3 text-gray-600 dark:text-gray-400 text-sm border-t border-gray-200 dark:border-gray-700">
                        <p>Notre système de réservation vous permet de choisir un jour, un créneau horaire et le nombre d'agneaux que vous souhaitez réserver. Un acompte de 50€ par agneau est requis lors de la réservation. Le reste du paiement sera effectué sur place.</p>
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
                        <span>L'acompte est-il remboursable en cas d'annulation ?</span>
                        <svg :class="{'rotate-180': open}" class="h-5 w-5 transform transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open" class="px-4 py-3 text-gray-600 dark:text-gray-400 text-sm border-t border-gray-200 dark:border-gray-700">
                        <p>Non, l'acompte de 50€ par agneau n'est pas remboursable en cas d'annulation. Il sert à garantir votre réservation et à couvrir les frais administratifs.</p>
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
    
    <!-- Contact assistance -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                Besoin d'aide supplémentaire ?
            </h3>
        </div>
        <div class="p-6">
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
@endsection