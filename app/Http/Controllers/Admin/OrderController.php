<?php

namespace App\Http\Controllers\Admin;

use App\Enum\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('admin');

        // cache()->tags('orders')->flush();

        $page = request()->query('page', 1);
        $limit = request()->query('limit', 10);

        $orderBy = request()->query('orderBy', 'created_at');
        $orderDirection = request()->query('orderDirection', 'desc');
        if (!in_array($orderDirection, haystack: ['asc', 'desc'])) {
            $orderDirection = 'desc';
        }
        $columns = [
            'created_at',
            'updated_at',
        ];
        if (!in_array($orderBy, $columns)) {
            $orderBy = 'created_at';
        }

        $status = request()->query('status', null);
        $orderStatus = collect(OrderStatus::cases())->map(fn($status) => $status->value);

        if ($orderStatus->doesntContain($status)) {
            $status = null;
        }

        $orders = cache()->tags('orders', $page, $limit, $orderBy, $orderDirection, $status)->rememberForever('orders', function () use ($orderBy, $orderDirection, $page, $limit, $status) {
            $query = Order::query();
            if ($status) {
                $query->where('status', $status);
            }
            return $query->orderBy($orderBy, $orderDirection)->with(["configuration.color", "configuration.model"])->paginate($limit)->withQueryString();
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        Gate::authorize('admin');

        $validated = $request->validate([
            'action' => 'required|in:status',
            'status' => [
                'required_if:action,status',
                Rule::enum(OrderStatus::class),
            ]
        ]);

        $order->update([
            'status' => $validated['status'],
        ]);

        return response()->json($order);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }
}
