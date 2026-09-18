<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use App\Models\TokenSesion;
use Illuminate\Support\Facades\DB;

class VerificarInicioSesion
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        // if(env('APP_ENV') == 'local'){
        //     return $next($request);
        // }
        session_start();
        // Determinamos si están en la sesión las variables de autenticación
        if (!isset($_SESSION['php_access_token']) && !isset($_SESSION['cod_usuario'])) {
            Log::info("php_access_token: not found");
            return redirect(env('PHP_APP_URL'));
        }
        // El token está presente, ahora solo tenemos que determinar si el token no ha vencido
        $token = TokenSesion::where('cod_usuario', $_SESSION['cod_usuario'])
            ->where('fecha_expiracion', '>=', DB::raw('NOW()'))
            ->first();
        if(!$token){
            Log::info("php_access_token: expired token");
            return redirect(env('PHP_APP_URL'));
        }

        Log::info("php_access_token: " . $_SESSION['php_access_token']);
        Log::info("cod_usuario: " . $_SESSION['cod_usuario']);
        return $next($request);
    }
}
