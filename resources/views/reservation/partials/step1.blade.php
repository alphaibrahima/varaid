<div class="step active" id="step-1">
    <h3 class="mb-4">Étape 1: Sélectionnez le jour pour venir choisir votre agneau sur le site de Hyères</h3>
    <div class="row row-cols-1 row-cols-md-3 g-4">
        @foreach ($slotCounts as $item)
        @php
            // Déterminer la classe CSS du badge et de la bordure
            $badgeClass = $item->total === 0 ? 'bg-danger' : ($item->total <= 10 ? 'bg-warning' : 'bg-success');
            $borderClass = $item->total === 0 ? 'border-danger' : ($item->total <= 10 ? 'border-warning' : 'border-success');
        @endphp
        <div class="col">
            <div class="card creneaux-jour {{ $borderClass }}" onclick="selectDay('{{$item->date}}')">
                <div class="card-body text-center">
                    {{-- <h5 class="card-title">{{ \Carbon\Carbon::parse($item->date)->translatedFormat('l d F Y') }}</h5> --}}
                    <h5 class="card-title">
                        {{ ucfirst(\Carbon\Carbon::parse($item->date)->translatedFormat('l d F Y')) }}
                    </h5>
                    
                    <p class="card-text">
                        <span class="badge {{ $badgeClass }}">
                            {{ $item->total }} créneaux disponibles
                        </span>
                    </p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

