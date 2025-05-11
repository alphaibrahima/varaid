<div class="p-4 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-700 dark:border-gray-600">
    <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Format du fichier CSV attendu :</h3>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 border">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-4 py-2 text-xs font-medium text-gray-500 text-left">prenom</th>
                    <th class="px-4 py-2 text-xs font-medium text-gray-500 text-left">nom</th>
                    <th class="px-4 py-2 text-xs font-medium text-gray-500 text-left">email</th>
                    <th class="px-4 py-2 text-xs font-medium text-gray-500 text-left">telephone</th>
                    <th class="px-4 py-2 text-xs font-medium text-gray-500 text-left">adresse</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr>
                    <td class="px-4 py-2 text-xs text-gray-500">Jean</td>
                    <td class="px-4 py-2 text-xs text-gray-500">Dupont</td>
                    <td class="px-4 py-2 text-xs text-gray-500">jean.dupont@example.com</td>
                    <td class="px-4 py-2 text-xs text-gray-500">0612345678</td>
                    <td class="px-4 py-2 text-xs text-gray-500">123 Rue de Paris, 75001 Paris</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="mt-3">
        <a href="{{ route('download.admin-reservation-template') }}" class="text-sm text-primary-600 hover:text-primary-500">
            <x-heroicon-o-arrow-down-tray class="w-4 h-4 inline-block" />
            Télécharger un modèle
        </a>
    </div>
</div>