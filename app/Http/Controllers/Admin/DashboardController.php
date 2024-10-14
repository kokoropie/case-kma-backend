<?php

namespace App\Http\Controllers\Admin;

use App\Enum\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Configuration;
use App\Models\Order;
use App\Models\User;
use Gate;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        Gate::authorize('admin');

        // cache()->tags('dashboard', 'orders')->flush();
        // cache()->store('file')->flush();

        $year = (int) request()->query('year', now()->year);
        $month = (int) request()->query('month', now()->month);
        
        $dashboard = collect([
            'year' => $year,
            'month' => $month,
            'configurations' => collect([
                'count' => cache()->tags('dashboard', 'orders')->rememberForever('configurations-count', fn() => Configuration::count()),
            ]),
            'orders' => collect([
                'count' => cache()->tags('dashboard', 'orders', $year, $month)->rememberForever('orders-count', function () use ($year, $month) {
                    $orders = cache()->tags('dashboard', 'orders')->rememberForever('orders', fn() => Order::all());
                    $startOfMonth = now()->setMonth($month)->setYear($year)->startOfMonth()->format('Y-m-d H:i:s');
                    $endOfMonth = now()->setMonth($month)->setYear($year)->endOfMonth()->format('Y-m-d H:i:s');
                    $startOfYear = now()->setYear($year)->startOfYear()->format('Y-m-d H:i:s');
                    $endOfYear = now()->setYear($year)->endOfYear()->format('Y-m-d H:i:s');

                    return collect([
                        'total' => collect([
                            "total" => $orders->count(),
                            OrderStatus::PENDING->value => $orders->where('status', OrderStatus::PENDING)->count(),
                            OrderStatus::PROCESSING->value => $orders->where('status', OrderStatus::PROCESSING)->count(),
                            OrderStatus::SHIPPED->value => $orders->where('status', OrderStatus::SHIPPED)->count(),
                            OrderStatus::COMPLETED->value => $orders->where('status', OrderStatus::COMPLETED)->count(),
                            OrderStatus::CANCELLED->value => $orders->where('status', OrderStatus::CANCELLED)->count(),
                            'paid' => $orders->whereNotIn('status', [OrderStatus::PENDING, OrderStatus::CANCELLED])->count(),
                        ]),
                        'month' => collect([
                            "total" => $orders->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
                            OrderStatus::PENDING->value => $orders->where('status', OrderStatus::PENDING)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
                            OrderStatus::PROCESSING->value => $orders->where('status', OrderStatus::PROCESSING)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
                            OrderStatus::SHIPPED->value => $orders->where('status', OrderStatus::SHIPPED)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
                            OrderStatus::COMPLETED->value => $orders->where('status', OrderStatus::COMPLETED)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
                            OrderStatus::CANCELLED->value => $orders->where('status', OrderStatus::CANCELLED)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
                            'paid' => $orders->whereNotIn('status', [OrderStatus::PENDING, OrderStatus::CANCELLED])->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
                        ]),
                        'year' => collect([
                            "total" => $orders->whereBetween('created_at', [$startOfYear, $endOfYear])->count(),
                            OrderStatus::PENDING->value => $orders->where('status', OrderStatus::PENDING)->whereBetween('created_at', [$startOfYear, $endOfYear])->count(),
                            OrderStatus::PROCESSING->value => $orders->where('status', OrderStatus::PROCESSING)->whereBetween('created_at', [$startOfYear, $endOfYear])->count(),
                            OrderStatus::SHIPPED->value => $orders->where('status', OrderStatus::SHIPPED)->whereBetween('created_at', [$startOfYear, $endOfYear])->count(),
                            OrderStatus::COMPLETED->value => $orders->where('status', OrderStatus::COMPLETED)->whereBetween('created_at', [$startOfYear, $endOfYear])->count(),
                            OrderStatus::CANCELLED->value => $orders->where('status', OrderStatus::CANCELLED)->whereBetween('created_at', [$startOfYear, $endOfYear])->count(),
                            'paid' => $orders->whereNotIn('status', [OrderStatus::PENDING, OrderStatus::CANCELLED])->whereBetween('created_at', [$startOfYear, $endOfYear])->count(),
                        ]),
                    ]);
                }),
                'amount' => cache()->tags('dashboard', 'orders', $year, $month)->rememberForever('orders-amount', function () use ($year, $month) {
                    $orders = cache()->tags('dashboard', 'orders')->rememberForever('orders', fn() => Order::all());
                    $startOfMonth = now()->setMonth($month)->setYear($year)->startOfMonth()->format('Y-m-d H:i:s');
                    $endOfMonth = now()->setMonth($month)->setYear($year)->endOfMonth()->format('Y-m-d H:i:s');
                    $startOfYear = now()->setYear($year)->startOfYear()->format('Y-m-d H:i:s');
                    $endOfYear = now()->setYear($year)->endOfYear()->format('Y-m-d H:i:s');

                    return collect([
                        'total' => collect([
                            "total" => $orders->sum("total_amount"),
                            OrderStatus::PENDING->value => $orders->where('status', OrderStatus::PENDING)->sum("total_amount"),
                            OrderStatus::PROCESSING->value => $orders->where('status', OrderStatus::PROCESSING)->sum("total_amount"),
                            OrderStatus::SHIPPED->value => $orders->where('status', OrderStatus::SHIPPED)->sum("total_amount"),
                            OrderStatus::COMPLETED->value => $orders->where('status', OrderStatus::COMPLETED)->sum("total_amount"),
                            OrderStatus::CANCELLED->value => $orders->where('status', OrderStatus::CANCELLED)->sum("total_amount"),
                            'paid' => $orders->whereNotIn('status', [OrderStatus::PENDING, OrderStatus::CANCELLED])->sum("total_amount"),
                        ]),
                        'month' => collect([
                            "total" => $orders->whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum("total_amount"),
                            OrderStatus::PENDING->value => $orders->where('status', OrderStatus::PENDING)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum("total_amount"),
                            OrderStatus::PROCESSING->value => $orders->where('status', OrderStatus::PROCESSING)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum("total_amount"),
                            OrderStatus::SHIPPED->value => $orders->where('status', OrderStatus::SHIPPED)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum("total_amount"),
                            OrderStatus::COMPLETED->value => $orders->where('status', OrderStatus::COMPLETED)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum("total_amount"),
                            OrderStatus::CANCELLED->value => $orders->where('status', OrderStatus::CANCELLED)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum("total_amount"),
                            'paid' => $orders->whereNotIn('status', [OrderStatus::PENDING, OrderStatus::CANCELLED])->whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum("total_amount"),
                        ]),
                        'year' => collect([
                            "total" => $orders->whereBetween('created_at', [$startOfYear, $endOfYear])->sum("total_amount"),
                            OrderStatus::PENDING->value => $orders->where('status', OrderStatus::PENDING)->whereBetween('created_at', [$startOfYear, $endOfYear])->sum("total_amount"),
                            OrderStatus::PROCESSING->value => $orders->where('status', OrderStatus::PROCESSING)->whereBetween('created_at', [$startOfYear, $endOfYear])->sum("total_amount"),
                            OrderStatus::SHIPPED->value => $orders->where('status', OrderStatus::SHIPPED)->whereBetween('created_at', [$startOfYear, $endOfYear])->sum("total_amount"),
                            OrderStatus::COMPLETED->value => $orders->where('status', OrderStatus::COMPLETED)->whereBetween('created_at', [$startOfYear, $endOfYear])->sum("total_amount"),
                            OrderStatus::CANCELLED->value => $orders->where('status', OrderStatus::CANCELLED)->whereBetween('created_at', [$startOfYear, $endOfYear])->sum("total_amount"),
                            'paid' => $orders->whereNotIn('status', [OrderStatus::PENDING, OrderStatus::CANCELLED])->whereBetween('created_at', [$startOfYear, $endOfYear])->sum("total_amount"),
                        ]),
                    ]);
                }),
                'chart' => cache()->tags('dashboard', 'orders', $year)->rememberForever('orders-chart', function () use ($year) {
                    $startOfYear = now()->setYear($year)->startOfYear()->format('Y-m-d H:i:s');
                    $endOfYear = now()->setYear($year)->endOfYear()->format('Y-m-d H:i:s');

                    $orders = cache()->tags('dashboard', 'orders')->rememberForever('orders', fn() => Order::all());
                    $listMonthsOfYear = cache()->store('file')->rememberForever("listMonthsOfYear_{$year}", fn() => collect(range(1, 12))->map(fn($month) => now()->setYear($year)->setMonth($month)->format('F')));
                    $zeroData = cache()->store('file')->rememberForever("zeroData_{$year}", fn() => collect($listMonthsOfYear)->mapWithKeys(fn($month) => [$month => 0]));
                    $filledCountData = $orders->whereBetween('created_at', [$startOfYear, $endOfYear])->groupBy(fn($order) => $order->created_at->format('F'))->map(fn($orders) => $orders->count());
                    $filledCountData = $zeroData->collect()->merge($filledCountData);
                    $filledAmountData = $orders->whereBetween('created_at', [$startOfYear, $endOfYear])->groupBy(fn($order) => $order->created_at->format('F'))->map(fn($orders) => $orders->sum('total_amount'));
                    $filledAmountData = $zeroData->collect()->merge($filledAmountData);
                    return collect([
                        'count' => $filledCountData->map(fn($value, $key) => ['month' => $key, 'value' => $value])->values(),
                        'amount' => $filledAmountData->map(fn($value, $key) => ['month' => $key, 'value' => $value])->values()
                    ]);
                })
            ]),
            'users' => collect([
                'count' => cache()->tags('dashboard', 'users')->rememberForever('users-count', function () {
                    $users = cache()->tags('dashboard', 'users')->rememberForever('users', fn() => User::all());
                    return collect([
                        'total' => $users->count(),
                        'active' => $users->whereNotNull('email_verified_at')->count(),
                        'inactive' => $users->whereNull('email_verified_at')->count(),
                        'admin' => $users->where('role', 'admin')->count(),
                        'user' => $users->where('role', 'user')->count()
                    ]);
                })
            ])
        ]);

        return response()->json($dashboard);
    }
}
