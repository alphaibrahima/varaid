<?php

// app/Http/Livewire/GenerateTimeSlots.php
namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Slot;
use Carbon\Carbon;

class GenerateTimeSlots extends Component
{
    public $date;
    public $start_time;
    public $end_time;

    // Dans la classe Livewire (GenerateTimeSlots.php)
    protected $listeners = ['generateTimeSlots' => 'generateTimeSlots'];

    public function generateTimeSlots()
    {
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);

        if ($start->diffInHours($end) < 1) {
            $this->notify('danger', 'La plage doit couvrir au moins 1 heure.');
            return;
        }

        $current = $start->copy();
        while ($current->addHour() <= $end) {
            Slot::create([
                'date' => $this->date,
                'start_time' => $current->copy()->subHour(),
                'end_time' => $current,
                'max_reservations' => 50 // Valeur par défaut
            ]);
        }

        $this->notify('success', 'Créneaux générés avec succès !');
    }

    public function render()
    {
        return view('livewire.generate-time-slots');
    }
}