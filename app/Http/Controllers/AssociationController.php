<?php

namespace App\Http\Controllers;

use App\Models\User;


use Illuminate\Http\Request;

class AssociationController extends Controller
{
    //
    public function index()
        {
            // Récupérer les associations actives
            $associations = User::where('role', 'association')->where('is_active', true)->get();

            return view('associations.index', compact('associations'));
        }
}
