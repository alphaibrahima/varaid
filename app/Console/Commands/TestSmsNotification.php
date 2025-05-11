<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\BrevoSmsService;
use Illuminate\Support\Facades\Log;

class TestSmsNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'varaid:test-sms {phone?} {--user_id=} {--message=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the SMS notification functionality with Brevo';

    /**
     * The Brevo SMS service.
     *
     * @var BrevoSmsService
     */
    protected $smsService;

    /**
     * Create a new command instance.
     *
     * @param BrevoSmsService $smsService
     * @return void
     */
    public function __construct(BrevoSmsService $smsService)
    {
        parent::__construct();
        $this->smsService = $smsService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Test de notification SMS via Brevo');
        $this->line('-----------------------------------');

        // Déterminer le numéro de téléphone
        $phone = $this->getPhoneNumber();
        if (!$phone) {
            return 1;
        }

        // Déterminer le message
        $message = $this->getMessage();

        // Envoi du SMS de test
        $this->line("Envoi du SMS au numéro : {$phone}");
        $this->line("Message: {$message}");
        
        $this->newLine();
        $this->line('Envoi en cours...');
        
        try {
            $result = $this->smsService->sendSMS($phone, $message);
            
            if ($result) {
                $this->info('✓ SMS envoyé avec succès!');
                Log::info('Test SMS sent successfully', [
                    'phone' => $phone
                ]);
                return 0;
            } else {
                $this->error('✗ Échec de l\'envoi du SMS. Consultez les logs pour plus d\'informations.');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('✗ Exception lors de l\'envoi du SMS: ' . $e->getMessage());
            Log::error('Test SMS failed with exception', [
                'phone' => $phone,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Obtenir le numéro de téléphone à utiliser pour le test.
     *
     * @return string|null
     */
    protected function getPhoneNumber()
    {
        // Vérifier si un ID utilisateur est spécifié
        if ($this->option('user_id')) {
            $user = User::find($this->option('user_id'));
            if (!$user) {
                $this->error('Utilisateur non trouvé avec l\'ID ' . $this->option('user_id'));
                return null;
            }
            
            if (empty($user->phone)) {
                $this->error('L\'utilisateur sélectionné n\'a pas de numéro de téléphone');
                return null;
            }
            
            $this->info("Utilisation du numéro de téléphone de l'utilisateur: {$user->name}");
            return $user->phone;
        }
        
        // Vérifier si un numéro est fourni en argument
        if ($this->argument('phone')) {
            return $this->argument('phone');
        }
        
        // Demander un numéro de téléphone
        return $this->ask('Veuillez entrer un numéro de téléphone au format +33612345678 ou 0612345678');
    }

    /**
     * Obtenir le message à envoyer.
     *
     * @return string
     */
    protected function getMessage()
    {
        // Vérifier si un message est fourni en option
        if ($this->option('message')) {
            return $this->option('message');
        }
        
        // Message par défaut
        return "VARAID: Ceci est un SMS de test. Si vous recevez ce message, la configuration SMS fonctionne correctement. Heure: " . now()->format('H:i:s');
    }
}