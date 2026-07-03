<?php

namespace SGCart\Marketplace\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureSellerIsApproved
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('seller')->check()) {
            $seller = Auth::guard('seller')->user();

            if ($seller->status->value !== 'approved') {
                return redirect()->route('seller.dashboard')->with('error', 'Access restricted. Please wait for admin approval.');
            }
        } else {
            // If they are not logged in as a seller, redirect them to the seller login
            return redirect()->route('seller.login')->with('error', 'Please log in to access the seller dashboard.');
        }

        return $next($request);
    }
}
