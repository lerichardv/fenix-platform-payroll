<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Usuario;

class TokenSesion extends Model
{
    use HasFactory;
    protected $table = "usu_tokens_sesion";
    protected $primaryKey = "cod_token_sesion";

    public function usuario(){
        return $this->hasOne(Usuario::class, 'cod_usuario', 'cod_usuario');
    }
}
