<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\HelpController;
use App\Models\CropAgesImplementadas;
use App\Models\Granja;
use App\Models\User;
use App\Models\UsuarioGranjas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Helper\Helper;

class CropAgeController extends Controller
{
    /** @var  userRepository */
    private $userRepository;

    /** @var  granjaRepository */
    private $granjaRepository;

    /** @var  usuariGranjaRepository */
    private $usuariGranjaRepository;

    /** @var  cropAgeImplementadoGranjaRepository */
    private $cropAgeImplementadoGranjaRepository;

    public function __construct(User $userRepo, Granja $granjaRepo, UsuarioGranjas $usuariGranjaRepo, CropAgesImplementadas $cropAgeRepo)
    {
        $this->userRepository = $userRepo;
        $this->granjaRepository = $granjaRepo;
        $this->usuariGranjaRepository = $usuariGranjaRepo;
        $this->cropAgeImplementadoGranjaRepository = $cropAgeRepo;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return $this->cropAgeImplementadoGranjaRepository->all();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {


        // Disable 'sql_mode=only_full_group_by'
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");


        $resultDatosTipoUsuario = DB::select("SELECT
                                                far_crop_semillas_bloques.cod_semilla_bloque,
                                                CONCAT(bw_inventario_semilla.nombre_semilla) AS datos_semilla,
                                                far_crop_semillas_bloques.cod_trasplante,
                                                SUM(far_crop_semillas_bloques.acres_usados) AS suma_acres_usados,
                                                far_crop_semillas_bloques.cod_semilla,
                                                far_crop_semillas_bloques.cod_semilla AS cod_inventario,
                                                far_crop_semillas_bloques.cod_unificacion,
                                                far_crop_semillas_bloques.cod_bloque_implementado as cod_bloque,
                                                GROUP_CONCAT(
                                                    far_crop_semillas_bloques.cod_bloque_implementado
                                                ) AS codigos_bloque_implementado,
                                                far_crop_semillas_bloques.cod_plantacion,
                                                far_crop_semillas_bloques.acres_usados,
                                                far_crop_semillas_bloques.porcentaje_acre_usado,
                                                bw_inventario_plantaciones.edad AS edad_semilla_en_plantacion,
                                                bw_inventario_trasplantes.numero_orden,
                                                bw_inventario_trasplantes.numero_ticket,
                                                bw_inventario_plantaciones.fecha_inicial,
                                                bw_inventario_semilla.nombre_semilla,
                                                bw_inventario_semilla.abreviatura_semilla,
                                                bw_inventario_semilla.codigo_semilla,
                                                COALESCE(far_bloques.bloque, 'Block removed') AS nombre_bloque_usado,
                                                CONCAT(
                                                    bw_inventario_estados_plantaciones.abreviatura,
                                                    ' - ',
                                                    far_farms.farm
                                                ) AS granja_asociada,
                                                bw_inventario_estados_plantaciones.nombre AS nombre_estado,
                                                far_fields.field,
                                                far_fields.cod_field,
                                                far_fields.cod_field AS cod_fields,
                                                far_farms.cod_farms AS cod_farm
                                            FROM
                                                far_crop_semillas_bloques
                                                INNER JOIN far_crop_bloques_implementados ON (
                                                    far_crop_bloques_implementados.cod_bloque_implementado = far_crop_semillas_bloques.cod_bloque_implementado
                                                )
                                                INNER JOIN bw_inventario_plantaciones ON (
                                                    bw_inventario_plantaciones.cod_plantacion = far_crop_semillas_bloques.cod_plantacion
                                                )
                                                INNER JOIN bw_inventario_trasplantes ON (
                                                    bw_inventario_trasplantes.cod_trasplante = far_crop_semillas_bloques.cod_trasplante
                                                )
                                                INNER JOIN bw_inventario_semilla ON (
                                                    bw_inventario_semilla.cod_inventario = far_crop_semillas_bloques.cod_semilla
                                                )
                                                LEFT JOIN far_bloques ON (
                                                    far_bloques.cod_bloque = far_crop_bloques_implementados.cod_bloque
                                                )
                                                INNER JOIN far_farms ON (
                                                    far_farms.cod_farms = far_crop_bloques_implementados.cod_farm
                                                )
                                                INNER JOIN bw_inventario_estados_plantaciones ON (
                                                    bw_inventario_estados_plantaciones.cod_estado = far_farms.cod_estado
                                                )
                                                INNER JOIN far_fields ON (
                                                    far_fields.cod_field = far_crop_bloques_implementados.cod_field
                                                )
                                            WHERE
                                                far_crop_bloques_implementados.cod_farm = $id
                                                AND far_crop_semillas_bloques.completada_en_app = 0
                                                AND far_crop_semillas_bloques.en_proceso = 0
                                            GROUP BY
                                                bw_inventario_plantaciones.edad,
                                                far_farms.farm,
                                                bw_inventario_semilla.nombre_semilla
                                            ORDER BY
                                                far_farms.farm,
                                                bw_inventario_semilla.nombre_semilla,
                                                bw_inventario_plantaciones.edad ASC;");

        HelpController::desconectarBaseDatos();

        if ($resultDatosTipoUsuario == null) {
            return response()->json(['message' => 'No crops available for the farm', 'data' => $id], 404);
        }

        return $resultDatosTipoUsuario;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function cropAsociadasAGranja(string $id)
    {
        HelpController::setDatabaseModeParaAgrupacionesGrandes();
        // DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");

        //
        $query = DB::table('far_crop_bloques_implementados')
            ->select(
                'far_crop_bloques_implementados.cod_farm',
                'far_crop_bloques_implementados.cod_bloque_implementado',
                DB::raw('GROUP_CONCAT(DISTINCT far_crop_bloques_implementados.cod_bloque_implementado) AS bloques_implementados_agrupado'),
                DB::raw('GROUP_CONCAT(DISTINCT far_crop_semillas_bloques.cod_semilla_bloque) AS cod_crop_age_agrupado'),
                'far_crop_bloques_implementados.cod_bloque',
                DB::raw('GROUP_CONCAT(DISTINCT far_crop_bloques_implementados.cod_bloque) AS cods_bloques'),
                'far_crop_bloques_implementados.cod_field',
                DB::raw('GROUP_CONCAT(DISTINCT far_crop_bloques_implementados.cod_field) AS cods_fields'),
                'far_crop_semillas_bloques.cod_semilla_bloque AS cod_crop_age',
                'far_crop_semillas_bloques.cod_plantacion',
                'far_crop_semillas_bloques.cod_semilla',
                'far_crop_semillas_bloques.fecha_plantacion',
                'bw_inventario_semilla.nombre_semilla',
                'bw_inventario_semilla.cod_categoria',
                'bw_inventario_plantaciones.edad',
                'bw_inventario_categorias_semillas.nombre AS nombre_categoria',
                'bw_inventario_categorias_semillas.cantidadDiasEspera',
                DB::raw('concat(bw_inventario_plantaciones.edad," - ",bw_inventario_semilla.nombre_semilla) as datos_semillas'),
                DB::raw('DATEDIFF(CURDATE(), far_crop_semillas_bloques.fecha_plantacion) as cantidad_dias'),
            )
            ->join('far_crop_semillas_bloques', 'far_crop_semillas_bloques.cod_bloque_implementado', '=', 'far_crop_bloques_implementados.cod_bloque_implementado')
            ->join('bw_inventario_semilla', 'bw_inventario_semilla.cod_inventario', '=', 'far_crop_semillas_bloques.cod_semilla')
            ->join('bw_inventario_plantaciones', 'bw_inventario_plantaciones.cod_plantacion', '=', 'far_crop_semillas_bloques.cod_plantacion') // Numeros de dias de plantacion en granja
            ->join('bw_inventario_categorias_semillas', 'bw_inventario_categorias_semillas.cod_categoria', '=', 'bw_inventario_semilla.cod_categoria')
            ->where('far_crop_bloques_implementados.cod_farm', $id)
            ->where('far_crop_semillas_bloques.completada', 0)
            ->whereRaw('DATEDIFF(CURDATE(), far_crop_semillas_bloques.fecha_plantacion) >= bw_inventario_categorias_semillas.cantidadDiasEspera')
            ->groupBy('far_crop_semillas_bloques.cod_semilla')
            ->groupBy('bw_inventario_plantaciones.edad')
            ->orderBy('bw_inventario_semilla.nombre_semilla')
            ->orderBy('bw_inventario_plantaciones.edad')
            ->get();
        // HelpController::setDatabaseModeOnlyFullGroupBy();

        // $query = DB::table('far_crop_bloques_implementados')
        //     ->select(
        //         'far_crop_bloques_implementados.cod_farm',
        //         'far_crop_bloques_implementados.cod_bloque_implementado',
        //         DB::raw('GROUP_CONCAT(DISTINCT far_crop_bloques_implementados.cod_bloque_implementado) AS bloques_implementados_agrupado'),
        //         DB::raw('GROUP_CONCAT(DISTINCT far_crop_semillas_bloques.cod_semilla_bloque) AS cod_crop_age_agrupado'),
        //         'far_crop_bloques_implementados.cod_bloque',
        //         DB::raw('GROUP_CONCAT(DISTINCT far_crop_bloques_implementados.cod_bloque) AS cods_bloques'),
        //         'far_crop_bloques_implementados.cod_field',
        //         DB::raw('GROUP_CONCAT(DISTINCT far_crop_bloques_implementados.cod_field) AS cods_fields'),
        //         'far_crop_semillas_bloques.cod_semilla_bloque AS cod_crop_age',
        //         'far_crop_semillas_bloques.cod_plantacion',
        //         'far_crop_semillas_bloques.cod_semilla',
        //         'bw_inventario_semilla.nombre_semilla',
        //         'bw_inventario_semilla.cod_categoria',
        //         'bw_inventario_plantaciones.edad',
        //         'bw_inventario_categorias_semillas.nombre AS nombre_categoria',
        //         'bw_inventario_categorias_semillas.cantidadDiasEspera',
        //         DB::raw('CONCAT(bw_inventario_plantaciones.edad, " - ", bw_inventario_semilla.nombre_semilla) AS datos_semillas'),
        //         DB::raw('DATEDIFF(CURDATE(), far_crop_semillas_bloques.fecha_plantacion) as dias_diferencias_actuales')
        //     )
        //     ->join('far_crop_semillas_bloques', 'far_crop_semillas_bloques.cod_bloque_implementado', '=', 'far_crop_bloques_implementados.cod_bloque_implementado')
        //     ->join('bw_inventario_semilla', 'bw_inventario_semilla.cod_inventario', '=', 'far_crop_semillas_bloques.cod_semilla')
        //     ->join('bw_inventario_plantaciones', 'bw_inventario_plantaciones.cod_plantacion', '=', 'far_crop_semillas_bloques.cod_plantacion')
        //     ->join('bw_inventario_categorias_semillas', 'bw_inventario_categorias_semillas.cod_categoria', '=', 'bw_inventario_semilla.cod_categoria')
        //     ->where('far_crop_bloques_implementados.cod_farm', $id)
        //     ->where('far_crop_semillas_bloques.completada', 0)
        //     ->whereRaw('DATEDIFF(CURDATE(), far_crop_semillas_bloques.fecha_plantacion) >= bw_inventario_categorias_semillas.cantidadDiasEspera')
        //     ->groupBy('far_crop_semillas_bloques.cod_semilla', 'bw_inventario_plantaciones.edad')
        //     ->orderBy('bw_inventario_semilla.nombre_semilla')
        //     ->orderBy('bw_inventario_plantaciones.edad')
        //     ->get();

        HelpController::desconectarBaseDatos();

        return $query;
    }
    public function cropAsociadasAGranjaSinVerificarDiasPlantados(string $id)
    {
        HelpController::setDatabaseModeParaAgrupacionesGrandes();
        // DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");

        //
        $query = DB::table('far_crop_bloques_implementados')
            ->select(
                'far_crop_bloques_implementados.cod_farm',
                'far_crop_bloques_implementados.cod_bloque_implementado',
                DB::raw('GROUP_CONCAT(DISTINCT far_crop_bloques_implementados.cod_bloque_implementado) AS bloques_implementados_agrupado'),
                DB::raw('GROUP_CONCAT(DISTINCT far_crop_semillas_bloques.cod_semilla_bloque) AS cod_crop_age_agrupado'),
                'far_crop_bloques_implementados.cod_bloque',
                DB::raw('GROUP_CONCAT(DISTINCT far_crop_bloques_implementados.cod_bloque) AS cods_bloques'),
                'far_crop_bloques_implementados.cod_field',
                DB::raw('GROUP_CONCAT(DISTINCT far_crop_bloques_implementados.cod_field) AS cods_fields'),
                'far_crop_semillas_bloques.cod_semilla_bloque AS cod_crop_age',
                'far_crop_semillas_bloques.cod_plantacion',
                'far_crop_semillas_bloques.cod_semilla',
                'far_crop_semillas_bloques.fecha_plantacion',
                'bw_inventario_semilla.nombre_semilla',
                'bw_inventario_semilla.cod_categoria',
                'bw_inventario_plantaciones.edad',
                'bw_inventario_categorias_semillas.nombre AS nombre_categoria',
                'bw_inventario_categorias_semillas.cantidadDiasEspera',
                DB::raw('concat(bw_inventario_plantaciones.edad," - ",bw_inventario_semilla.nombre_semilla) as datos_semillas'),
                DB::raw('DATEDIFF(CURDATE(), far_crop_semillas_bloques.fecha_plantacion) as cantidad_dias'),
            )
            ->join('far_crop_semillas_bloques', 'far_crop_semillas_bloques.cod_bloque_implementado', '=', 'far_crop_bloques_implementados.cod_bloque_implementado')
            ->join('bw_inventario_semilla', 'bw_inventario_semilla.cod_inventario', '=', 'far_crop_semillas_bloques.cod_semilla')
            ->join('bw_inventario_plantaciones', 'bw_inventario_plantaciones.cod_plantacion', '=', 'far_crop_semillas_bloques.cod_plantacion') // Numeros de dias de plantacion en granja
            ->join('bw_inventario_categorias_semillas', 'bw_inventario_categorias_semillas.cod_categoria', '=', 'bw_inventario_semilla.cod_categoria')
            ->where('far_crop_bloques_implementados.cod_farm', $id)
            ->where('far_crop_semillas_bloques.completada', 0)
            // ->whereRaw('DATEDIFF(CURDATE(), far_crop_semillas_bloques.fecha_plantacion) >= bw_inventario_categorias_semillas.cantidadDiasEspera')
            ->groupBy('far_crop_semillas_bloques.cod_semilla')
            ->groupBy('bw_inventario_plantaciones.edad')
            ->orderBy('bw_inventario_semilla.nombre_semilla')
            ->orderBy('bw_inventario_plantaciones.edad')
            ->get();
        // HelpController::setDatabaseModeOnlyFullGroupBy();

        // $query = DB::table('far_crop_bloques_implementados')
        //     ->select(
        //         'far_crop_bloques_implementados.cod_farm',
        //         'far_crop_bloques_implementados.cod_bloque_implementado',
        //         DB::raw('GROUP_CONCAT(DISTINCT far_crop_bloques_implementados.cod_bloque_implementado) AS bloques_implementados_agrupado'),
        //         DB::raw('GROUP_CONCAT(DISTINCT far_crop_semillas_bloques.cod_semilla_bloque) AS cod_crop_age_agrupado'),
        //         'far_crop_bloques_implementados.cod_bloque',
        //         DB::raw('GROUP_CONCAT(DISTINCT far_crop_bloques_implementados.cod_bloque) AS cods_bloques'),
        //         'far_crop_bloques_implementados.cod_field',
        //         DB::raw('GROUP_CONCAT(DISTINCT far_crop_bloques_implementados.cod_field) AS cods_fields'),
        //         'far_crop_semillas_bloques.cod_semilla_bloque AS cod_crop_age',
        //         'far_crop_semillas_bloques.cod_plantacion',
        //         'far_crop_semillas_bloques.cod_semilla',
        //         'bw_inventario_semilla.nombre_semilla',
        //         'bw_inventario_semilla.cod_categoria',
        //         'bw_inventario_plantaciones.edad',
        //         'bw_inventario_categorias_semillas.nombre AS nombre_categoria',
        //         'bw_inventario_categorias_semillas.cantidadDiasEspera',
        //         DB::raw('CONCAT(bw_inventario_plantaciones.edad, " - ", bw_inventario_semilla.nombre_semilla) AS datos_semillas'),
        //         DB::raw('DATEDIFF(CURDATE(), far_crop_semillas_bloques.fecha_plantacion) as dias_diferencias_actuales')
        //     )
        //     ->join('far_crop_semillas_bloques', 'far_crop_semillas_bloques.cod_bloque_implementado', '=', 'far_crop_bloques_implementados.cod_bloque_implementado')
        //     ->join('bw_inventario_semilla', 'bw_inventario_semilla.cod_inventario', '=', 'far_crop_semillas_bloques.cod_semilla')
        //     ->join('bw_inventario_plantaciones', 'bw_inventario_plantaciones.cod_plantacion', '=', 'far_crop_semillas_bloques.cod_plantacion')
        //     ->join('bw_inventario_categorias_semillas', 'bw_inventario_categorias_semillas.cod_categoria', '=', 'bw_inventario_semilla.cod_categoria')
        //     ->where('far_crop_bloques_implementados.cod_farm', $id)
        //     ->where('far_crop_semillas_bloques.completada', 0)
        //     ->whereRaw('DATEDIFF(CURDATE(), far_crop_semillas_bloques.fecha_plantacion) >= bw_inventario_categorias_semillas.cantidadDiasEspera')
        //     ->groupBy('far_crop_semillas_bloques.cod_semilla', 'bw_inventario_plantaciones.edad')
        //     ->orderBy('bw_inventario_semilla.nombre_semilla')
        //     ->orderBy('bw_inventario_plantaciones.edad')
        //     ->get();

        HelpController::desconectarBaseDatos();

        return $query;
    }
}
