<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Movement;
use Illuminate\Auth\Access\HandlesAuthorization;

class MovementPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Movement');
    }

    public function view(AuthUser $authUser, Movement $movement): bool
    {
        return $authUser->can('View:Movement');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Movement');
    }

    public function update(AuthUser $authUser, Movement $movement): bool
    {
        return $authUser->can('Update:Movement');
    }

    public function delete(AuthUser $authUser, Movement $movement): bool
    {
        return $authUser->can('Delete:Movement');
    }

    public function restore(AuthUser $authUser, Movement $movement): bool
    {
        return $authUser->can('Restore:Movement');
    }

    public function forceDelete(AuthUser $authUser, Movement $movement): bool
    {
        return $authUser->can('ForceDelete:Movement');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Movement');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Movement');
    }

    public function replicate(AuthUser $authUser, Movement $movement): bool
    {
        return $authUser->can('Replicate:Movement');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Movement');
    }

}