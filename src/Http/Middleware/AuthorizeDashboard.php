<?php

namespace Reno\Dashboard\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Reno\Dashboard\Models\Dashboard;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeDashboard
{
    /**
     * @param  Closure(Request):Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var string|null $slug */
        $slug = $request->route('slug') !== null ? (string) $request->route('slug') : null;

        if ($slug) {
            $dashboard = Dashboard::where('slug', $slug)->first();

            if (! $dashboard) {
                abort(404);
            }

            $user = $request->user();

            if ($user && ! $user->can('view', $dashboard)) {
                abort(403);
            }
        }

        return $next($request);
    }
}
