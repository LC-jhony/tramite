<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! setting('maintenance.enabled', false)) {
            return $next($request);
        }

        // El panel vive en /admin, pero Livewire habla por /livewire/*
        // (updates del formulario, uploads, notificaciones) y el health
        // check de Laravel vive en /up. Si NO se excluyen, cada click del
        // panel devuelve 503 y el admin se queda bloqueado dentro del
        // mantenimiento que acaba de activar.
        if ($request->is('admin*') || $request->is('livewire/*') || $request->is('up')) {
            return $next($request);
        }

        $allowedIps = setting('maintenance.allowed_ips', []);

        if (in_array($request->ip(), $allowedIps, true)) {
            return $next($request);
        }

        return response()->view('errors.maintenance', [
            'message' => setting('maintenance.message', 'Estamos en mantenimiento. Vuelve pronto.'),
        ], 503);
    }
}
