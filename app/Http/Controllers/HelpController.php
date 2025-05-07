<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HelpController extends Controller
{
    /**
     * Affiche la page d'aide et FAQ
     */
    public function index()
    {
        return view('help.index');
    }
}