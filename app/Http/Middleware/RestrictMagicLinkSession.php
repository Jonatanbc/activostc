<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Confines a session that was authenticated through a passwordless acceptance "magic link"
 * (see GuestAcceptanceController) to ONLY the accept/decline pages. If such a session tries to
 * reach any other part of the app, it is bounced back to the acceptance list. This ensures a
 * leaked/forwarded magic link can be used to accept or decline the assigned item — and nothing
 * else (no inventory, no users, no settings).
 */
class RestrictMagicLinkSession
{
    /** Routes a magic-link session is permitted to reach. */
    private const ALLOWED_ROUTES = [
        'account.accept',
        'account.accept.item',
        'account.store-acceptance',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->hasSession() || ! $request->session()->has('magic_link_acceptance')) {
            return $next($request);
        }

        // Visiting the login page means the user wants a normal session — release the restriction.
        if ($request->routeIs('login') || $request->is('login')) {
            $request->session()->forget('magic_link_acceptance');

            return $next($request);
        }

        foreach (self::ALLOWED_ROUTES as $allowed) {
            if ($request->routeIs($allowed)) {
                return $next($request);
            }
        }

        return redirect()->route('account.accept')
            ->with('error', trans('general.magic_link_session_restricted'));
    }
}
