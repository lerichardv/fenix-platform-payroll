<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class UsuarioGranjas extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
/**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    protected $table = 'usu_usuario_farm';
    protected $primaryKey = 'cod_usuario_farm';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cod_usuario_farm',
        'cod_usuario',
        'cod_granja'
    ];
}
