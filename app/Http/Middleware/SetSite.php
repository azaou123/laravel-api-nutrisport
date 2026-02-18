<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Site;

class SetSite
{
    public function handle(Request $request, Closure $next)
    {
        $siteId = $request->header('X-Site-ID');

        if ($siteId) {
            $site = Site::find($siteId);
            if ($site) {
                // attach to request safely
                $request->attributes->set('site', $site);
            }
        }

        return $next($request);
    }
}
