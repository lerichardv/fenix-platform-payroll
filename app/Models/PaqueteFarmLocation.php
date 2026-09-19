<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PaqueteFarmLocation extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    protected $table = 'pay_pack_for_farm_location_category';
    protected $primaryKey = 'id';

    public static function getPackData()
    {
        if (!Schema::hasTable('pay_tipo_paquetes_locaciones')) {
            return self::all()->toJson();
        }
        $packs = DB::table('pay_tipo_packs')->select('cod_tipo_pack', 'activo')->get();
        $locations = DB::table('pay_tipo_paquetes_locaciones')->select('cod_tipo_pack', 'cod_locacion as cod_location')->get()->all();
        $granjas = DB::table('pay_tipo_paquetes_granjas')->select('cod_tipo_pack', 'cod_farm')->get()->all();
        $categorias = DB::table('pay_tipo_paquetes_categorias')->select('cod_tipo_pack', 'cod_categoria')->get()->all();

        $result = [];
        $id = 1;
        foreach ($categorias as $categoria) {
            // Encuentra la locación que coincida con el cod_tipo_pack actual
            $location = current(array_filter($locations, function ($loc) use ($categoria) {
            return $loc->cod_tipo_pack == $categoria->cod_tipo_pack;
            }));
            // Encuentra la granja que coincida con el cod_tipo_pack actual
            $granja = current(array_filter($granjas, function ($granja) use ($categoria) {
            return $granja->cod_tipo_pack == $categoria->cod_tipo_pack;
            }));
            
            if (!$location || !$granja) {
            continue;
            }
            
            $result[] = [
            'id'             => $id++,
            'cod_tipo_pack'  => $categoria->cod_tipo_pack,
            'cod_farm'       => $granja->cod_farm,
            'cod_location'   => $location->cod_location,
            'cod_categoria'  => $categoria->cod_categoria,
            'activo'         => 1,
            'visible' => ($pack = current(array_filter($packs->all(), function ($p) use ($categoria) {
                return $p->cod_tipo_pack == $categoria->cod_tipo_pack;
            }))) ? $pack->activo : 1
            ];
        }
        
        return json_encode($result);
    }

}
