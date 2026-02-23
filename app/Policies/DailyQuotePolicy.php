<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\DailyQuote;
use Illuminate\Auth\Access\HandlesAuthorization;

class DailyQuotePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DailyQuote');
    }

    public function view(AuthUser $authUser, DailyQuote $dailyQuote): bool
    {
        return $authUser->can('View:DailyQuote');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:DailyQuote');
    }

    public function update(AuthUser $authUser, DailyQuote $dailyQuote): bool
    {
        return $authUser->can('Update:DailyQuote');
    }

    public function delete(AuthUser $authUser, DailyQuote $dailyQuote): bool
    {
        return $authUser->can('Delete:DailyQuote');
    }

    public function restore(AuthUser $authUser, DailyQuote $dailyQuote): bool
    {
        return $authUser->can('Restore:DailyQuote');
    }

    public function forceDelete(AuthUser $authUser, DailyQuote $dailyQuote): bool
    {
        return $authUser->can('ForceDelete:DailyQuote');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:DailyQuote');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:DailyQuote');
    }

    public function replicate(AuthUser $authUser, DailyQuote $dailyQuote): bool
    {
        return $authUser->can('Replicate:DailyQuote');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:DailyQuote');
    }

}
