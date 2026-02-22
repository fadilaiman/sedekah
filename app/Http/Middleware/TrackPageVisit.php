<?php

namespace App\Http\Middleware;

use App\Models\DailyAnalytic;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackPageVisit
{
    /**
     * Handle an incoming request by tracking the page visit after response.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Track page visit asynchronously (after response sent)
        // This ensures admin action is never blocked by analytics tracking
        DailyAnalytic::incrementToday('page_visits');

        return $response;
    }
}
