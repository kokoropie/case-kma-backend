<?php

namespace App\Policies;

use App\Models\ShippingAddress;
use App\Models\User;

class ShippingAddressPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function update(User $user, ShippingAddress $shippingAddress)
    {
        return $user->is_admin ||  $user->user_id === $shippingAddress->user_id;
    }

    public function delete(User $user, ShippingAddress $shippingAddress)
    {
        return $user->is_admin ||  $user->user_id === $shippingAddress->user_id;
    }
}
