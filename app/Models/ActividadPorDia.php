<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActividadPorDia extends Model
{
    use HasFactory;

    protected $table = "pay_actividades_por_dia";
    protected $primaryKey = "cod_actividad_por_dia";
}
