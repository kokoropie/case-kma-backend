<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LockUser;
use App\Models\User;
use Gate;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('admin');

        $page = request()->query('page', 1);
        $limit = request()->query('limit', 10);

        $orderBy = strtolower(request()->query('orderBy', default: 'created_at'));
        $orderDirection = strtolower(request()->query('orderDirection', 'desc'));
        if (!in_array($orderDirection, ['asc', 'desc'])) {
            $orderDirection = 'desc';
        }
        $columns = [
            'name',
            'email',
            'created_at',
            'updated_at',
            'email_verified_at',
        ];
        if (!in_array($orderBy, $columns)) {
            $orderBy = 'created_at';
        }

        $search = request()->query('search', default: null);
        $active = request()->query('active', null);
        if ($active !== null) {
            $active = !in_array($active, ['true', 'false']) ? null : $active === 'true';
        }
        $role = request()->query('role', null);
        if ($role && !in_array($role, ['admin', 'user'])) {
            $role = null;
        }

        $users = cache()->tags('users', $page, $limit, $orderBy, $orderDirection, $search, $active, $role)->rememberForever('users', function () use ($page, $limit, $orderBy, $orderDirection, $search, $active, $role) {
            $query = User::query();

            if ($search) {
                $query->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            }

            if ($active !== null) {
                $query->where('email_verified_at', $active ? '!=' : '=', null);
            }

            if ($role !== null) {
                $query->where('role', $role);
            }

            return $query->withCount(['orders'])->orderBy($orderBy, $orderDirection)->paginate($limit)->withQueryString();
        });

        return response()->json($users);
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
    public function show(User $user)
    {
        Gate::authorize('admin');

        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        Gate::authorize('admin');

        $validated = $request->validate([
            "name" => "required|string",
            "role" => "required|in:admin,user",
            "is_lock" => "required|boolean",
            "reason" => "required_if_accepted:is_lock|string",
            "time" => "nullable|numeric"
        ]);

        $user->name = $validated["name"];
        $user->role = $validated["role"];
        if ($user->is_lock != $validated["is_lock"]) {
            if ($user->is_lock) {
                $user->lock()->delete();
            } else {
                $lock = new LockUser;
                $lock->reason = $validated["reason"];
                $time = $validated["time"];
                if ($time) {
                    $lock->end_at = now()->addDays($time);
                }
                $lock->user_id = $user->user_id;
                $lock->save();
            }
        }
        if ($user->isDirty())
        {
            $user->save();
        }

        return response()->json($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}
