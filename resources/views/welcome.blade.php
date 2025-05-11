@extends('layouts.app')

@section('content')
<div class="container-fluid vh-100 d-flex align-items-center" style="background-color: #f8f9fa;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8 offset-md-2 text-center">
                {{-- <img src="{{ asset('img/logo.jpeg') }}" alt="VarAïd Logo" class="img-fluid mb-4" style="max-height: 100px;"> --}}
                
                <h2 class="display-4 text-dark mb-4" style="color: #2c3e50;">
                    Réservez votre agneau pour l’Aïd Al Adha 2025 sur le site d’abattage temporaire de Hyères
                </h2>
                
                <p class="lead text-muted mb-5">
                    L’association Varaïd, en partenariat avec le CDCM-Var, simplifie votre préparation en quelques clics !
                </p>
                
                <div class="d-flex justify-content-center">
                    <a href="{{ route('login') }}" class="btn btn-primary me-3" style="background-color: #2c3e50; border-color: #2c3e50;">
                        Se Connecter
                    </a>
                    <!-- <a href="{{ route('register') }}" class="btn btn-outline-primary" style="color: #2c3e50; border-color: #2c3e50;">
                        S'inscrire
                    </a> -->
                </div>
            </div>
            
            {{-- <div class="col-md-6 text-center">
                <img src="https://www.pngitem.com/pimgs/m/84-840834_sheep-png-transparent-png.png" alt="Agneau" class="img-fluid" style="max-height: 500px;">
            </div> --}}
        </div>
    </div>
</div>
@endsection