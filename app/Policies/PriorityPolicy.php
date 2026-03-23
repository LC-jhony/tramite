<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Priority;
use Illuminate\Auth\Access\HandlesAuthorization;

class PriorityPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Priority');
    }

    public function view(AuthUser $authUser, Priority $priority): bool
    {
        return $authUser->can('View:Priority');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Priority');
    }

    public function update(AuthUser $authUser, Priority $priority): bool
    {
        return $authUser->can('Update:Priority');
    }

    public function delete(AuthUser $authUser, Priority $priority): bool
    {
        return $authUser->can('Delete:Priority');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Priority');
    }

    public function restore(AuthUser $authUser, Priority $priority): bool
    {
        return $authUser->can('Restore:Priority');
    }

    public function forceDelete(AuthUser $authUser, Priority $priority): bool
    {
        return $authUser->can('ForceDelete:Priority');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Priority');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Priority');
    }

    public function replicate(AuthUser $authUser, Priority $priority): bool
    {
        return $authUser->can('Replicate:Priority');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Priority');
    }

}