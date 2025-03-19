<div class="step active" id="step-1">
    <h3 class="mb-4">Étape 1: Choisissez un jour</h3>
    <div class="row row-cols-1 row-cols-md-3 g-4">
        @foreach ($slotCounts as $item)
            
        <div class="col">
            <div class="card creneaux-jour" onclick="selectDay('{{$item->date}}')">
                <div class="card-body text-center">
                    <h5 class="card-title">{{ \Carbon\Carbon::parse($item->date)->translatedFormat('l d F Y') }}</h5>
                    <p class="card-text"><span class="badge bg-success">{{$item->total}} créneaux disponibles</span></p>
                </div>
            </div>
        </div>
        @endforeach
     
    </div>
</div>