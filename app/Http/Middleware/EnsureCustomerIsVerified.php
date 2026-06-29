<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerIsVerified
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('customer')->user();

        if ($user) {
            $isEmailUnverified = $user->email && ! $user->email_verified_at;
            $isPhoneUnverified = $user->phone_no && ! $user->phone_verified_at;

            if ($isEmailUnverified || $isPhoneUnverified) {
                return $request->expectsJson()
                    ? response()->json(['message' => 'Your contact information is not verified.'], 403)
                    : redirect()->route('store.otp.verify')->with('warning', 'Please verify your contact information to continue.');
            }
        }

        return $next($request);
    }
}
