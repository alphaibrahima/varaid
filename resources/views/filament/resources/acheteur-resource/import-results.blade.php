<div class="space-y-4">
    <p class="text-sm text-gray-500">
        Voici les identifiants des acheteurs importés. 
        <strong class="text-red-500">Veuillez les sauvegarder, car ils ne seront plus accessibles ultérieurement.</strong>
    </p>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 border">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-4 py-2 text-xs font-medium text-gray-500 text-left">Nom</th>
                    <th class="px-4 py-2 text-xs font-medium text-gray-500 text-left">Prénom</th>
                    <th class="px-4 py-2 text-xs font-medium text-gray-500 text-left">Email</th>
                    <th class="px-4 py-2 text-xs font-medium text-gray-500 text-left">Mot de passe</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($userCredentials as $credential)
                <tr>
                    <td class="px-4 py-2 text-xs text-gray-500">{{ $credential['Nom'] }}</td>
                    <td class="px-4 py-2 text-xs text-gray-500">{{ $credential['Prénom'] }}</td>
                    <td class="px-4 py-2 text-xs text-gray-500">{{ $credential['Email'] }}</td>
                    <td class="px-4 py-2 text-xs text-gray-500">{{ $credential['Mot de passe'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="flex justify-end">
        <button type="button" id="copyCredentialsBtn" class="text-primary-600 hover:text-primary-500 text-sm font-medium">
            Copier dans le presse-papiers
        </button>
    </div>

    <script>
        document.getElementById('copyCredentialsBtn').addEventListener('click', function() {
            const rows = @json($userCredentials);
            let text = 'Nom, Prénom, Email, Mot de passe\n';
            
            rows.forEach(row => {
                text += `${row.Nom}, ${row.Prénom}, ${row.Email}, ${row['Mot de passe']}\n`;
            });
            
            navigator.clipboard.writeText(text).then(() => {
                this.textContent = 'Copié!';
                setTimeout(() => {
                    this.textContent = 'Copier dans le presse-papiers';
                }, 2000);
            });
        });
    </script>
</div>