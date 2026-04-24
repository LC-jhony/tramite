<?php

declare(strict_types=1);

namespace App\Policies;

use FinityLabs\FinMail\Models\SentEmail;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class SentEmailPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SentEmail');
    }

    public function view(AuthUser $authUser, SentEmail $sentEmail): bool
    {
        return $authUser->can('View:SentEmail');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SentEmail');
    }

    public function update(AuthUser $authUser, SentEmail $sentEmail): bool
    {
        return $authUser->can('Update:SentEmail');
    }

    public function delete(AuthUser $authUser, SentEmail $sentEmail): bool
    {
        return $authUser->can('Delete:SentEmail');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SentEmail');
    }

    public function restore(AuthUser $authUser, SentEmail $sentEmail): bool
    {
        return $authUser->can('Restore:SentEmail');
    }

    public function forceDelete(AuthUser $authUser, SentEmail $sentEmail): bool
    {
        return $authUser->can('ForceDelete:SentEmail');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SentEmail');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SentEmail');
    }

    public function replicate(AuthUser $authUser, SentEmail $sentEmail): bool
    {
        return $authUser->can('Replicate:SentEmail');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SentEmail');
    }
}
