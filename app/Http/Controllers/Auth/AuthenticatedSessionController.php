<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): Response | JsonResponse
    {
        $request->authenticate();

        // $request->session()->regenerate();
        
        $user = $request->user();

        if ($user->is_lock) {
            $end_at = null;
            if (!is_null($user->lock->end_at)) {
                $end_at = Carbon::parse($user->lock->end_at);
            }
            if ($end_at && $end_at->lessThanOrEqualTo(now())) {
                $user->lock()->delete();
            } else {
                $message = "This user is locked. Reason: " . $user->lock->reason . ". ";
                if ($end_at) {
                    $message .= "Unlock at " . $end_at->format("Y-m-d H:i:s");
                }
                return response()->json([
                    "message" => $message
                ], 403);
            }
        }

        return response()->json([
            'user' => $user,
            'token' => str($user->createToken('api')->plainTextToken)->explode('|')[1],
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();

        // Auth::guard('api')->logout();

        // $request->session()->invalidate();

        // $request->session()->regenerateToken();

        return response()->noContent();
    }
}
