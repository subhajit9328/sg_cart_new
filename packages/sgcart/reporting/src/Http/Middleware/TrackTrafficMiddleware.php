<?php

namespace SGCart\Reporting\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TrackTrafficMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Track only GET page requests that do not target the admin panel, API, or static assets
        if ($request->isMethod('GET') 
            && !$request->ajax() 
            && !$request->is('admin*') 
            && !$request->is('api*')
            && !$request->is('_debugbar*')
        ) {
            try {
                // Ensure session is started and get ID
                $sessionId = $request->session()->getId();
                $customerId = auth('customer')->id();
                $path = $request->getPathInfo();
                $referrer = $request->headers->get('referer');
                $ipAddress = $request->ip();
                $userAgent = $request->userAgent();

                // Save asynchronously or directly to DB (keep it simple and direct)
                DB::table('traffic_logs')->insert([
                    'session_id'  => $sessionId,
                    'customer_id' => $customerId,
                    'path'        => $path,
                    'referrer'    => $referrer ? parse_url($referrer, PHP_URL_HOST) : null,
                    'ip_address'  => $ipAddress,
                    'user_agent'  => substr($userAgent, 0, 500),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            } catch (\Exception $e) {
                // Prevent database errors from breaking user page loads
            }
        }

        return $response;
    }
}
