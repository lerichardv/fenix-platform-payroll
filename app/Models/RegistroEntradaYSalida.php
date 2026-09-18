<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class RegistroEntradaYSalida extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    protected $table = 'pay_clocks';
    protected $primaryKey = 'cod_clock';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cod_estado',
        'cod_empleado',
        'clock_in',
        'clock_out',
        'fecha',
        'fecha_clock_out',
        'hora',
        'GPS',
        'date_insert',
    ];


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_insert' => 'date:d-m-Y H:i:s',
            'fecha' => 'date:d-m-Y',
            'fecha_clock_out' => 'date:d-m-Y H:i:s',
            'hora' => 'date:H:i:s',
        ];
    }
}
