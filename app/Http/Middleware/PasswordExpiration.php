<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class PasswordExpiration
{
    /**
     * Días hasta que expire la contraseña (90 días)
     */
    protected int $passwordExpirationDays = 90;

    /**
     * Rutas que deben excluirse de la verificación
     */
    protected array $exceptRoutes = [
        'profile.password-expired',
        'logout',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // Si el usuario acaba de cambiar la contraseña, no verificar
        if ($request->routeIs(...$this->exceptRoutes)) {
            return $next($request);
        }

        // Verificar si la contraseña ha expirado
        if ($this->passwordExpired($user)) {
            if (! $request->expectsJson()) {
                return redirect()->route('profile.password-expired');
            }

            return response()->json([
                'message' => 'Su contraseña ha expirado. Por favor, cámbiela.',
                'password_expired' => true,
            ], 403);
        }

        return $next($request);
    }

    /**
     * Verificar si la contraseña ha expirado
     */
    protected function passwordExpired($user): bool
    {
        $passwordChangedAt = $user->password_changed_at;

        // Si nunca se ha registrado un cambio, verificar desde created_at
        if (! $passwordChangedAt) {
            $passwordChangedAt = $user->created_at;
        }

        return $passwordChangedAt->diffInDays(Carbon::now()) >= $this->passwordExpirationDays;
    }
}
