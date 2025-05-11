<x-filament-panels::page>
    <div class="space-y-6">
        @if ($showResults)
            <div class="p-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <h2 class="text-xl font-semibold mb-4">Résultats de l'envoi</h2>
                
                <div class="mb-4 rounded-lg p-4 {{ $results['success'] ? 'bg-green-50 border border-green-200' : 'bg-yellow-50 border border-yellow-200' }}">
                    <div class="flex items-center">
                        @if($results['success'])
                            <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        @else
                            <svg class="h-5 w-5 text-yellow-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        @endif
                        <span class="font-medium">{{ $results['message'] }}</span>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Destinataires</p>
                        <p class="text-xl font-semibold">{{ $results['recipients_count'] }}</p>
                    </div>
                    
                    <div class="p-4 bg-green-50 dark:bg-green-900 rounded-lg">
                        <p class="text-sm text-green-600 dark:text-green-400">Succès</p>
                        <p class="text-xl font-semibold">{{ $results['success_count'] }}</p>
                    </div>
                    
                    <div class="p-4 bg-red-50 dark:bg-red-900 rounded-lg">
                        <p class="text-sm text-red-600 dark:text-red-400">Échecs</p>
                        <p class="text-xl font-semibold">{{ $results['error_count'] }}</p>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button wire:click="$set('showResults', false)" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-md text-gray-700">
                        Retour au formulaire
                    </button>
                </div>
            </div>
        @else
            <div class="p-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <h2 class="text-xl font-semibold mb-4">Envoi de SMS</h2>
                
                <form wire:submit="submit">
                    {{ $this->form }}
                    
                    <div class="flex justify-end mt-6">
                        <x-filament::button
                            type="submit"
                            color="primary"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove wire:target="submit">Envoyer</span>
                            <span wire:loading wire:target="submit">Envoi en cours...</span>
                        </x-filament::button>
                    </div>
                </form>
            </div>
            
            <div class="p-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <h3 class="text-lg font-semibold mb-2">Instructions</h3>
                <ul class="list-disc list-inside space-y-2 text-gray-600 dark:text-gray-400">
                    <li>Les SMS sont limités à 160 caractères. Au-delà, ils seront divisés en plusieurs messages.</li>
                    <li>Utilisez le mode test pour vérifier les destinataires sans envoyer de SMS réels.</li>
                    <li>Les coûts des SMS sont calculés selon les tarifs de Brevo.</li>
                </ul>
            </div>
        @endif
    </div>
</x-filament-panels::page>