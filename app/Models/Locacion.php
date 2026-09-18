<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class Locacion extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = "far_locations";

    protected $primaryKey = "cod_location";

    public static function todasLasActivas(){
        return Locacion::where('activo', '1')->get();
    }
}
