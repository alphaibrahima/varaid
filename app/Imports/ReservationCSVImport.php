<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ReservationCSVImport implements ToCollection, WithHeadingRow, WithValidation
{
    protected $associationId;
    protected $passwords = [];

    public function __construct($associationId)
    {
        $this->associationId = $associationId;
    }

    public function collection(Collection $rows)
    {
        $users = [];

        foreach ($rows as $row) {
            // Vérifier si l'utilisateur existe déjà
            $existingUser = User::where('email', $row['email'])->first();
            
            if ($existingUser) {
                $users[] = $existingUser;
                continue;
            }
            
            // Générer un mot de passe aléatoire
            $password = Str::random(10);
            
            // Créer l'utilisateur
            $user = User::create([
                'name' => $row['nom'],
                'firstname' => $row['prenom'],
                'email' => $row['email'],
                'phone' => $row['telephone'],
                'full_address' => $row['adresse'],
                'password' => Hash::make($password),
                'role' => 'buyer',
                'association_id' => $this->associationId,
                'is_active' => true,
            ]);

            // Stocker le mot de passe pour l'envoyer par email si nécessaire
            $this->passwords[$user->id] = $password;
            $users[] = $user;
        }

        return $users;
    }

    public function rules(): array
    {
        return [
            '*.prenom' => ['required', 'string', 'max:255'],
            '*.nom' => ['required', 'string', 'max:255'],
            '*.email' => ['required', 'email'],
            '*.telephone' => ['required', 'string'],
            '*.adresse' => ['required', 'string'],
        ];
    }

    public function getPasswords()
    {
        return $this->passwords;
    }
}