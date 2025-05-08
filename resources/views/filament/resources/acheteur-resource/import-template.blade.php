<div class="space-y-4">
    <p class="text-sm text-gray-500">
        Votre fichier doit contenir les colonnes suivantes (la première ligne doit être l'en-tête):
    </p>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 border">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-4 py-2 text-xs font-medium text-gray-500 text-left">nom</th>
                    <th class="px-4 py-2 text-xs font-medium text-gray-500 text-left">prenom</th>
                    <th class="px-4 py-2 text-xs font-medium text-gray-500 text-left">email</th>
                    <th class="px-4 py-2 text-xs font-medium text-gray-500 text-left">telephone</th>
                    <th class="px-4 py-2 text-xs font-medium text-gray-500 text-left">adresse_complete</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr>
                    <td class="px-4 py-2 text-xs text-gray-500">Dupont</td>
                    <td class="px-4 py-2 text-xs text-gray-500">Jean</td>
                    <td class="px-4 py-2 text-xs text-gray-500">jean.dupont@example.com</td>
                    <td class="px-4 py-2 text-xs text-gray-500">0601020304</td>
                    <td class="px-4 py-2 text-xs text-gray-500">123 Rue Exemple, 75000 Paris</td>
                </tr>
                <tr>
                    <td class="px-4 py-2 text-xs text-gray-500">Martin</td>
                    <td class="px-4 py-2 text-xs text-gray-500">Sophie</td>
                    <td class="px-4 py-2 text-xs text-gray-500">sophie.martin@example.com</td>
                    <td class="px-4 py-2 text-xs text-gray-500">0607080910</td>
                    <td class="px-4 py-2 text-xs text-gray-500">456 Avenue Exemple, 69000 Lyon</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <a href="{{ route('download.template') }}" class="text-primary-600 hover:text-primary-500 text-sm font-medium">
            Télécharger un modèle
        </a>
    </div>
</div>