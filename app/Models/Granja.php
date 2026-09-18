<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\BwInventarioEstadoPlantacion;

class Granja extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    protected $table = 'far_farms';
    protected $primaryKey = 'cod_farms';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cod_farms',
        'farm',
        'activo'
    ];

    public static function todasLasActivas(){
        return Granja::where('activo', '1')->get();
    }

    public function estado(){
        return $this->hasOne(BwInventarioEstadoPlantacion::class, 'cod_estado', 'cod_estado');
    }
}
