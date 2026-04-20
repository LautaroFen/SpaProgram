<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRoleAccess
{
    /**
     * @param  Closure(Request):Response  $next
     */
    public function handle(Request $request, Closure $next, string $allowedLevels = ''): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(403);
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        $allowed = array_values(array_filter(array_map('trim', explode(',', strtolower($allowedLevels)))));
        if ($allowed === []) {
            return $this->deny($request);
        }

        $level = $user->accessLevel();

        // Support some friendly aliases.
        $allowed = array_map(function (string $value) {
            return match ($value) {
                'recepcionista' => 'reception',
                'receptionist' => 'reception',
                'recepcion' => 'reception',
                default => $value,
            };
        }, $allowed);

        if (in_array($level, $allowed, true)) {
            return $next($request);
        }

        return $this->deny($request);
    }

    private function deny(Request $request): Response
    {
        if ($request->expectsJson()) {
            abort(403);
        }

        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            abort(403);
        }

        return redirect()->route('dashboard');
    }
}
