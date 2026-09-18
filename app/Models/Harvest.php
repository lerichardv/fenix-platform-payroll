<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Harvest extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    protected $table = 'pay_harvests';
    protected $primaryKey = 'cod_harvest';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cod_farm',
        'crop_age',
        'cod_plantacion',
        'cod_tipo_pack',
        'cod_tipo_pago',
        'user_admin',
        'user_insert',
    ];
}
