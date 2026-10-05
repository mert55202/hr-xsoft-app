<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $plain = (string) $request->bearerToken();
        $token = $plain === '' ? null : ApiToken::query()->where('token', hash('sha256', $plain))->first();

        if ($token === null) {
            abort(401);
        }

        $request->setUserResolver(fn () => $token->user);

        return $next($request);
    }
}
