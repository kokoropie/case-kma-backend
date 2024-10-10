<?php

namespace App\Policies;

use App\Models\Configuration;
use App\Models\User;

class ConfigurationPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function update(User $user, Configuration $configuration)
    {
        return $user->is_admin || $user->user_id === $configuration->user_id;
    }

    public function delete(User $user, Configuration $configuration)
    {
        return $user->is_admin || $user->user_id === $configuration->user_id;
    }
}
