<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Affiche la page de contacts
     */
    public function index()
    {
        return view('contacts.index');
    }
}