<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Illuminate\Support\Facades\Log;

class BuyersImport implements ToCollection, WithHeadingRow, WithValidation, WithChunkReading, WithBatchInserts
{
    protected $associationId;
    protected $sendEmails;
    protected $passwords = [];
    protected $stats = [
        'total' => 0,
        'created' => 0,
        'skipped' => 0,
        'errors' => 0,
    ];

    public function __construct($associationId, $sendEmails = false)
    {
        $this->associationId = $associationId;
        $this->sendEmails = $sendEmails;
    }

    public function collection(Collection $rows)
    {
        // Augmenter la limite de temps d'exécution à 10 minutes
        set_time_limit(600);
        
        Log::info('Starting import with ' . count($rows) . ' rows');
        $this->stats['total'] = count($rows);
        
        // Déboguer les données reçues
        if ($rows->count() > 0) {
            Log::info('Headers in first row:', [
                'headers' => array_keys($rows->first()->toArray())
            ]);
        } else {
            Log::error('No rows found in the imported file');
            return;
        }
        
        // Récupération préalable de tous les emails existants pour éviter les requêtes multiples
        $existingEmails = User::pluck('email')->flip()->toArray();
        
        // Utilisation de transactions pour améliorer les performances
        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                try {
                    Log::info('Processing row ' . ($index + 1));
                    
                    // Récupérer et nettoyer les données
                    $name = $this->getValueFromVariations($row, ['nom', 'name', 'lastname', 'last_name']);
                    $firstname = $this->getValueFromVariations($row, ['prenom', 'firstname', 'first_name', 'prénom']);
                    $email = $this->getValueFromVariations($row, ['email', 'e-mail', 'courriel', 'mail']);
                    $phone = $this->getValueFromVariations($row, ['telephone', 'phone', 'tel', 'téléphone']);
                    $address = $this->getValueFromVariations($row, ['adresse_complete', 'adresse', 'address', 'full_address']);
                    
                    // Vérifier si les données minimales sont présentes
                    if (empty($name) || empty($email)) {
                        Log::warning('Row ' . ($index + 1) . ' skipped: Missing required fields (nom/email)');
                        $this->stats['skipped']++;
                        continue;
                    }
                    
                    // Nettoyer le numéro de téléphone
                    $cleanPhone = preg_replace('/\D/', '', $phone);
                    
                    // Vérifier si l'email existe déjà - utilise le cache en mémoire
                    if (isset($existingEmails[$email])) {
                        Log::warning('Row ' . ($index + 1) . ' skipped: Email already exists: ' . $email);
                        $this->stats['skipped']++;
                        continue;
                    }
                    
                    // Générer un mot de passe aléatoire
                    $password = Str::random(10);
                    
                    // Créer l'utilisateur
                    $user = User::create([
                        'name' => $name,
                        'firstname' => $firstname,
                        'email' => $email,
                        'phone' => $cleanPhone,
                        'full_address' => $address,
                        'password' => Hash::make($password),
                        'role' => 'buyer',
                        'association_id' => $this->associationId,
                        'is_active' => true,
                    ]);

                    // Ajouter l'email au cache pour éviter les doublons dans le même lot
                    $existingEmails[$email] = true;
                    
                    // Stocker le mot de passe
                    $this->passwords[$user->id] = $password;
                    $this->stats['created']++;
                    
                    // Noter les emails à traiter pour envoi groupé plutôt que d'envoyer un par un
                    if ($this->sendEmails && $user) {
                        try {
                            // Nous ne voulons pas ralentir l'importation avec des envois d'emails
                            // Les emails seront envoyés par lots après la transaction
                            if (($index + 1) % 50 == 0) {
                                DB::commit();
                                DB::beginTransaction();
                            }
                        } catch (\Exception $e) {
                            Log::error('Failed to queue email to ' . $email . ': ' . $e->getMessage());
                        }
                    }
                    
                    if (($index + 1) % 100 == 0) {
                        Log::info('Processed ' . ($index + 1) . ' rows so far. Created ' . $this->stats['created'] . ' users');
                    }
                    
                } catch (\Exception $e) {
                    Log::error('Error processing row ' . ($index + 1), [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    $this->stats['errors']++;
                }
            }
            
            DB::commit();
            
            // Si l'envoi d'emails est activé, traiter les emails en arrière-plan
            if ($this->sendEmails && count($this->passwords) > 0) {
                // Envoyer les emails après l'importation
                foreach ($this->passwords as $userId => $password) {
                    try {
                        $user = User::find($userId);
                        if ($user) {
                            $user->notify(new \App\Notifications\AcheteurCredentialsNotification($password));
                        }
                    } catch (\Exception $e) {
                        Log::error('Failed to send email to user #' . $userId . ': ' . $e->getMessage());
                    }
                }
            }
            
            Log::info('Import completed. Created ' . $this->stats['created'] . ' users');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transaction failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Définit la taille des lots pour la lecture du fichier
     * Cela permet de lire le fichier par morceaux pour éviter les problèmes de mémoire
     */
    public function chunkSize(): int
    {
        return 100; // Lire 100 lignes à la fois
    }
    
    /**
     * Définit la taille des lots pour l'insertion en base de données
     */
    public function batchSize(): int
    {
        return 50; // Insérer 50 enregistrements à la fois
    }

    /**
     * Récupère les statistiques d'importation
     */
    public function getStats()
    {
        return $this->stats;
    }

    /**
     * Récupère une valeur en cherchant parmi différentes variations de clés
     */
    private function getValueFromVariations($row, $variations)
    {
        foreach ($variations as $key) {
            if (isset($row[$key]) && !empty($row[$key])) {
                return trim($row[$key]);
            }
        }
        return '';
    }

    public function rules(): array
    {
        // Règles minimalistes pour éviter la validation qui pourrait ralentir l'importation
        return [];
    }

    /**
     * Personnaliser les messages de validation
     */
    public function customValidationMessages()
    {
        return [
            'email.unique' => 'L\'email :input est déjà utilisé par un autre utilisateur.',
        ];
    }

    /**
     * Retourne tous les mots de passe générés
     */
    public function getPasswords()
    {
        return $this->passwords;
    }
}