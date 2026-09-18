<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Miscelaneo extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    protected $table = 'pay_miscellaneous';
    protected $primaryKey = 'cod_miscellaneous';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "cod_location",
        "cod_activity",
        "cod_farm",
        "cod_field",
        "cod_bloque",
        "crop_age",
        "cod_tipo_pago"
    ];
}
