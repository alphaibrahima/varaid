<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestBrevoSms extends Command
{
    protected $signature = 'brevo:test-sms {phone}';
    protected $description = 'Test Brevo SMS sending';

    public function handle()
    {
        $phone = $this->argument('phone');
        $apiKey = config('services.brevo.api_key');
        
        $this->info("Attempting to send SMS to $phone");
        $this->info("Using API key: " . substr($apiKey, 0, 5) . '...' . substr($apiKey, -5));
        
        try {
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'api-key' => $apiKey,
                'content-type' => 'application/json',
            ])->post('https://api.brevo.com/v3/transactionalSMS/sms', [
                'sender' => 'VARAID',
                'recipient' => $phone,
                'content' => 'Ceci est un test de SMS via Brevo',
            ]);
            
            $this->info('Response status: ' . $response->status());
            $this->info('Response body: ' . $response->body());
            
            Log::info('Brevo SMS test response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            if ($response->successful()) {
                $this->info('SMS sent successfully');
            } else {
                $this->error('Failed to send SMS');
            }
        } catch (\Exception $e) {
            $this->error('Exception: ' . $e->getMessage());
            Log::error('Exception while testing Brevo SMS', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        return 0;
    }
}