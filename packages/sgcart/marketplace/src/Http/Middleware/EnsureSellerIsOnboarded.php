<?php

namespace SGCart\Marketplace\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureSellerIsOnboarded
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('seller')->check()) {
            $seller = Auth::guard('seller')->user();

            if ($seller->status->value === 'pending_onboarding') {
                // If they are attempting to onboarding or logging out, allow it
                if ($request->routeIs('seller.onboarding') || $request->routeIs('seller.onboarding.submit') || $request->routeIs('seller.logout')) {
                    return $next($request);
                }
                return redirect()->route('seller.onboarding');
            }

            // If they are already onboarded, don't let them access onboarding page
            if ($request->routeIs('seller.onboarding') || $request->routeIs('seller.onboarding.submit')) {
                return redirect()->route('seller.dashboard');
            }
        } else {
            // Unauthenticated sellers should be redirected to login
            if (!$request->routeIs('seller.login') && !$request->routeIs('seller.register')) {
                return redirect()->route('seller.login')->with('error', 'Please log in to access the seller portal.');
            }
        }

        return $next($request);
    }
}
