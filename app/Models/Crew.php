<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Crew extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    protected $table = 'pay_crews';
    protected $primaryKey = 'cod_crew';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "cod_harvest",
        "cod_miscellaneous",
        "cod_empleado",
        "pin",
        "qc_pin",
        "cod_supervisor",
        "cantidad_escaneos",
        "cod_estado_job",
        "hora_inicio",
        "hora_final",
        "user_insert"
    ];
}
