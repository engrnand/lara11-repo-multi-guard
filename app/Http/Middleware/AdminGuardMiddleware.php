<?php

namespace App\Http\Middleware;

use App\Enum\UserGaurdEnum;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\UnauthorizedException;
use Symfony\Component\HttpFoundation\Response;

class AdminGuardMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()->tokenCan("guard:" . UserGaurdEnum::ADMIN->value)) {
            return $next($request);
        }
        throw new UnauthorizedException(trans('auth.not_authorized'));
    }
}
