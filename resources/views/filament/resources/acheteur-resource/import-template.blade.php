<div class="space-y-4">
    <p class="text-sm text-gray-500">
        Cette importation tentera d'identifier automatiquement les colonnes pertinentes dans votre fichier. Votre fichier doit obligatoirement contenir les colonnes suivantes:
    </p>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 border">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-4 py-2 text-xs font-medium text-gray-500 text-left">Nom (obligatoire)</th>
                    <th class="px-4 py-2 text-xs font-medium text-gray-500 text-left">Prénom</th>
                    <th class="px-4 py-2 text-xs font-medium text-gray-500 text-left">Email (obligatoire)</th>
                    <th class="px-4 py-2 text-xs font-medium text-gray-500 text-left">Téléphone (obligatoire)</th>
                    <th class="px-4 py-2 text-xs font-medium text-gray-500 text-left">Adresse</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr>
                    <td class="px-4 py-2 text-xs text-gray-500">Dupont</td>
                    <td class="px-4 py-2 text-xs text-gray-500">Jean</td>
                    <td class="px-4 py-2 text-xs text-gray-500">jean.dupont@example.com</td>
                    <td class="px-4 py-2 text-xs text-gray-500">06 01 02 03 04</td>
                    <td class="px-4 py-2 text-xs text-gray-500">123 Rue Exemple, 75000 Paris</td>
                </tr>
                <tr>
                    <td class="px-4 py-2 text-xs text-gray-500">Martin</td>
                    <td class="px-4 py-2 text-xs text-gray-500">Sophie</td>
                    <td class="px-4 py-2 text-xs text-gray-500">sophie.martin@example.com</td>
                    <td class="px-4 py-2 text-xs text-gray-500">+33 6 07 08 09 10</td>
                    <td class="px-4 py-2 text-xs text-gray-500">456 Avenue Exemple, 69000 Lyon</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="space-y-2">
        <p class="text-sm text-amber-600 font-medium">
            <span class="inline-flex items-center justify-center rounded-full bg-amber-100 h-5 w-5 text-amber-700 mr-1">
                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            </span>
            Notes importantes:
        </p>
        <ul class="list-disc pl-6 text-xs text-gray-600">
            <li>Le nom, l'email et le téléphone sont obligatoires pour chaque acheteur</li>
            <li>Les numéros de téléphone doivent être français et peuvent être au format:
                <ul class="pl-4 mt-1">
                    <li>06 XX XX XX XX ou 07 XX XX XX XX</li>
                    <li>+33 6 XX XX XX XX ou +33 7 XX XX XX XX</li>
                    <li>336XXXXXXXX ou 337XXXXXXXX</li>
                </ul>
            </li>
            <li>Les entêtes de colonnes peuvent avoir différents noms, le système tentera de les identifier</li>
            <li>Si un email existe déjà dans le système, l'utilisateur correspondant sera ignoré</li>
        </ul>
    </div>

    <div class="mt-4">
        <a href="{{ route('download.template') }}" class="text-primary-600 hover:text-primary-500 text-sm font-medium">
            Télécharger un modèle
        </a>
    </div>
</div>