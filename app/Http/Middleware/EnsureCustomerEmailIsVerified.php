<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerEmailIsVerified
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('customer')->user();

        // If the user exists, has an email, and that email is NOT verified
        if ($user && $user->email && ! $user->email_verified_at) {

            return $request->expectsJson()
                ? response()->json(['message' => 'Your email address is not verified.'], 403)
                : redirect()->route('store.otp.verify')->with('warning', 'Please verify your email address to continue.');
        }

        return $next($request);
    }
}
