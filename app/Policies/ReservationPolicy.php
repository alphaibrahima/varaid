<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReservationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Permettre aux administrateurs de voir toutes les réservations
        if ($user->role === 'admin') {
            return true;
        }

        // Les associations voient uniquement les réservations liées à elles
        if ($user->role === 'association') {
            return true; // Filtrage fait dans la requête
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Reservation $reservation): bool
    {
        // Les administrateurs peuvent voir toutes les réservations
        if ($user->role === 'admin') {
            return true;
        }

        // Les associations ne peuvent voir que leurs propres réservations
        if ($user->role === 'association') {
            return $reservation->association_id === $user->id;
        }

        // Les acheteurs ne peuvent voir que leurs propres réservations
        if ($user->role === 'buyer') {
            return $reservation->user_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Seul un administrateur peut créer une réservation manuellement
        // return $user->role === 'admin';
        return true; 
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Reservation $reservation): bool
    {
        // Seul un administrateur peut modifier une réservation
        if ($user->role === 'admin') {
            return true;
        }

        // Les associations peuvent modifier certains champs de leurs réservations
        if ($user->role === 'association' && $reservation->association_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Reservation $reservation): bool
    {
        // Seul un administrateur peut supprimer une réservation
        return $user->role === 'admin';
    }
}