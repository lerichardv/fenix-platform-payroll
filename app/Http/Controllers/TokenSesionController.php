<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\TokenSesion;

class TokenSesionController extends Controller
{
    public function manejarAccessToken(Request $request){
        session_start();
        if($request->input('php_access_token')){
            $token = TokenSesion::where('token_sesion', $request->input('php_access_token'))->first();
            $_SESSION['php_access_token'] = $token->token_sesion;
            $_SESSION['cod_usuario'] = $token->cod_usuario;
            // $_SESSION['access_token'] = $token->token_sesion;
            // $_SESSION['cod_usuario'] = $token->usuario->cod_usuario;
        }else{
            Log::info("No php_access_token");
        }
        return redirect()->route('home');
    }
}
