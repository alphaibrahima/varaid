<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TutorialController extends Controller
{
    /**
     * Affiche la page des tutoriels vidéo
     */
    public function index()
    {
        return view('buyer.tutorials');
    }
}