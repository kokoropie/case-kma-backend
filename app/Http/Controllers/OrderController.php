<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\ThirdParty\Address;
use Gate;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth('sanctum')->user();

        $orders = collect($user->orders()->orderBy("created_at", "desc")->get()->append(['link']));
        
        $orders->transform(function ($order) {
            $order->shippingAddress->setHidden(['user_id']);
            $address = collect($order->shippingAddress->toArray());
            if ($address->get('country') === 'VN') {
                $address->put('postal_code', '');
                $address->put('district', Address::district($address->get('province'), $address->get('district')));
                $address->put('province', Address::province($address->get('province')));
            }
            $address->put('country', Address::country($address->get('country')));

            $order->configuration->setHidden(['user_id']);

            $order->configuration->load(['model', 'color']);

            $order = collect($order);
            $order->forget(['shipping_address', 'configuration_id', 'shipping_address_id', 'user_id']);
            $order->put('shipping_address', $address);
            return $order;
        });

        return response()->json($orders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        Gate::authorize('view', $order);

        $order->shippingAddress->setHidden(['user_id']);
        $address = collect($order->shippingAddress->toArray());
        if ($address->get('country') === 'VN') {
            $address->put('postal_code', '');
            $address->put('district', Address::district($address->get('province'), $address->get('district')));
            $address->put('province', Address::province($address->get('province')));
        }
        $address->put('country', Address::country($address->get('country')));

        $order->configuration->setHidden(['user_id']);

        $order->configuration->load(['model', 'color']);

        $order = collect($order);
        $order->forget(['shipping_address', 'configuration_id', 'shipping_address_id', 'user_id']);
        $order->put('shipping_address', $address);

        return response()->json($order);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }
}
