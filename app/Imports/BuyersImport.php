<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class BuyersImport implements ToCollection, WithHeadingRow, WithValidation
{
    protected $associationId;
    protected $sendEmails;
    protected $passwords = [];

    public function __construct($associationId, $sendEmails = false)
    {
        $this->associationId = $associationId;
        $this->sendEmails = $sendEmails;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Générer un mot de passe aléatoire
            $password = Str::random(10);
            
            // Créer l'utilisateur
            $user = User::create([
                'name' => $row['nom'],
                'firstname' => $row['prenom'],
                'email' => $row['email'],
                'phone' => $row['telephone'],
                'full_address' => $row['adresse_complete'],
                'password' => Hash::make($password),
                'role' => 'buyer',
                'association_id' => $this->associationId,
                'is_active' => true,
            ]);

            // Stocker le mot de passe pour l'envoyer par email si nécessaire
            $this->passwords[$user->id] = $password;
            
            // Envoyer un email avec les identifiants si demandé
            if ($this->sendEmails && $user) {
                $user->notify(new \App\Notifications\AcheteurCredentialsNotification($password));
            }
        }
    }

    public function rules(): array
    {
        return [
            '*.nom' => ['required', 'string', 'max:255'],
            '*.prenom' => ['required', 'string', 'max:255'],
            '*.email' => ['required', 'email', 'unique:users,email'],
            '*.telephone' => ['required', 'string'],
            '*.adresse_complete' => ['required', 'string'],
        ];
    }

    public function getPasswords()
    {
        return $this->passwords;
    }
}