<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\Models\RegistroIngreso;
use App\Models\Estado;
use Illuminate\Database\Eloquent\Collection;

class Usuario extends Model
{
    use HasFactory;

    protected $table = "usu_usuarios";
    protected $primaryKey = "cod_usuario";

    protected $hidden = [
        "pass"
    ];

    /**
     * Asigna la disponibilidad del usuario dado
     * @param mixed $cod_usuario
     * @param mixed $disponible default 1
     * @return void
     */
    public static function asignarDisponibilidad($cod_usuario, $disponible = 1){
        $usuario = Usuario::find($cod_usuario);
        if($usuario->disponible != $disponible){
            $usuario->disponible = $disponible;
            $usuario->save();
        }
    }

    public function estado(){
        return $this->hasOne(Estado::class, 'cod_departamento', 'cod_departamento');
    }

}
