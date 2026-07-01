<?php

namespace SGCart\DashboardAnalytics\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use SGCart\DashboardAnalytics\Models\SearchLog;

class LogSearchMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only log successful GET requests
        if ($request->isMethod('GET') && $response->isSuccessful()) {
            $term = null;
            if ($request->routeIs('store.shop') && $request->filled('search')) {
                $term = $request->input('search');
            } elseif ($request->routeIs('store.search-live') && $request->filled('q')) {
                $term = $request->input('q');
            }

            if ($term && is_string($term)) {
                $term = trim(strtolower($term));
                if (!empty($term)) {
                    SearchLog::create([
                        'term' => $term,
                        'ip_address' => $request->ip(),
                    ]);
                }
            }
        }

        return $response;
    }
}
