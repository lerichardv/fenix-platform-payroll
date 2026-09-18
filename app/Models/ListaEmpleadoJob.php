<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class ListaEmpleadoJob extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = "pay_lista_empleados_jobs";

    protected $primaryKey = "cod_lista";
    public $timestamps = false;

    protected $fillable = [
        "cod_crew",
        "pieces",
        "cod_estado_job",
        "terminado",
        "user_insert"
    ];

}
