<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Usuario;
use App\Models\Granja;
use App\Models\Locacion;

class RegistroIngreso extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    protected $table = 'pay_bitacora_inicio_sesion';
    protected $primaryKey = 'cod_inicio_sesion';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cod_usuario',
        'fecha_ingreso',
        'fecha_egreso',
        'cod_farm_ci',
        'cod_location_ci',
        'cod_farm_co',
        'cod_location_co',
        'registro_modificado',
        'latitud',
        'longitud',
    ];


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    // protected function casts(): array
    // {
    //     return [
    //         'fecha_ingreso' => 'datetime',
    //         'fecha_egreso' => 'datetime',
    //     ];
    // }
    public function usuario(): HasOne{
        return $this->hasOne(Usuario::class, 'cod_usuario', 'cod_usuario');
    }

    public function granja(){
        return $this->hasOne(Granja::class, 'cod_farms', 'cod_farm_ci');
    }

    public function locacion(){
        return $this->hasOne(Locacion::class, 'cod_location', 'cod_location_ci');
    }

    public function nombre_empleado(){
        return $this->apellido_1 . "-" . $this->apellido_2 . ", " . $this->nombre_1 . "-" . $this->nombre_2;
    }
}
