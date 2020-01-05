<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $success_status = 200;
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            if (
                $e instanceof TokenExpiredException || $e instanceof TokenBlacklistedException
            ) {
                try {
                    $new_token  = JWTAuth::refresh($request->header('Authorization'));
                    $response = $next($request);
                    $response->headers->set('Authorization', 'Bearer '. $new_token);
                    $original = $response->getOriginalContent();
                    $original['token_refreshed'] = true;
                    $response->setContent(json_encode($original));
                    return $response;
                } catch (JWTException $e) {
                    $status     = 401;
                    $message    = $e->getMessage();
                    return response()->json(compact('status','message'), $success_status);
                }
            } else if ($e instanceof TokenInvalidException) {
                $status     = 401;
                $message    = 'Por favor, inicia sesión de nuevo, el token es inválido';
                return response()->json(compact('status','message'), $success_status);
            } else {
                $status     = 404;
                $message    = $e->getMessage();
                return response()->json(compact('status','message'), $success_status);
            }
        }
        return $next($request);
    }
}
