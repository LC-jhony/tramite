<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Administration;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdministrationPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Administration');
    }

    public function view(AuthUser $authUser, Administration $administration): bool
    {
        return $authUser->can('View:Administration');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Administration');
    }

    public function update(AuthUser $authUser, Administration $administration): bool
    {
        return $authUser->can('Update:Administration');
    }

    public function delete(AuthUser $authUser, Administration $administration): bool
    {
        return $authUser->can('Delete:Administration');
    }

    public function restore(AuthUser $authUser, Administration $administration): bool
    {
        return $authUser->can('Restore:Administration');
    }

    public function forceDelete(AuthUser $authUser, Administration $administration): bool
    {
        return $authUser->can('ForceDelete:Administration');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Administration');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Administration');
    }

    public function replicate(AuthUser $authUser, Administration $administration): bool
    {
        return $authUser->can('Replicate:Administration');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Administration');
    }

}