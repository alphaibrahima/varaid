<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function verifyAffiliation(User $user, User $model)
    {
        // Seuls les admins ou l'association liée peuvent vérifier l'affiliation
        return $user->role === 'admin' || 
               ($user->role === 'association' && $user->id === $model->association_id);
    }
    
    public function viewAffiliationCode(User $user, User $model)
    {
        // Seuls les admins, l'utilisateur lui-même ou son association peuvent voir le code
        return $user->role === 'admin' || 
               $user->id === $model->id ||
               ($user->role === 'association' && $user->id === $model->association_id);
    }
}