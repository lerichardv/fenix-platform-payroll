<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\HelpController;
use App\Models\Campo;
use App\Models\Granja;
use App\Models\User;
use App\Models\UsuarioGranjas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TareaProgramadaAPIController extends Controller
{

    /** @var  userRepository */
    private $userRepository;
    /** @var  granjaRepository */
    private $granjaRepository;
    /** @var  granjaRepository */
    private $usuariGranjaRepository;
    /** @var  campoRepoRepository */
    private $campoRepoRepository;
    public function __construct(User $userRepo, Granja $granjaRepo, UsuarioGranjas $usuariGranjaRepo, Campo $campoRepo)
    {
        $this->userRepository = $userRepo;
        $this->granjaRepository = $granjaRepo;
        $this->usuariGranjaRepository = $usuariGranjaRepo;
        $this->campoRepoRepository = $campoRepo;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        //
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

    public function CulminarTareasCaducadas(Request $request)
    {
        DB::statement("SET sql_mode = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION';");

        $miscelaneasTerminadas = $this->terminarTareasMiscelaneas();
        $harvestTerminadas = $this->terminarTareasHarvest();
        return response()->json(['message' => 'Successful state change', "code" => 0, 'extra' => ['harvestTerminadas' => $harvestTerminadas, 'miscelaneasTerminadas' => $miscelaneasTerminadas]], 200);
    }
    private function terminarTareasHarvest()
    {
        $harvestTerminadas = [];
        //Tareas del HARVEST
        $tareasPendientes = DB::table('pay_harvests')
            ->select(
                'pay_harvests.cod_harvest',
                'pay_harvests.user_admin',
                'pay_harvests.cod_farm',
                'pay_harvests.crop_age',
                'pay_harvests.cod_tipo_pack',
                'pay_harvests.cod_tipo_pago',
                'pay_harvests.date_insert AS fecha_registro_tarea',
                'pay_jobs_progresos.cod_job',
                'pay_jobs_progresos.cod_harvest',
                'pay_jobs_progresos.cod_miscellaneous',
                'pay_jobs_progresos.cod_estado_job',
                'pay_jobs_progresos.fecha_job',
                'pay_jobs_progresos.hora_inicio',
                'pay_jobs_progresos.hora_final'
            )
            ->join('pay_jobs_progresos', 'pay_jobs_progresos.cod_harvest', '=', 'pay_harvests.cod_harvest')
            ->join('pay_estados_jobs', 'pay_estados_jobs.cod_estado_job', '=', 'pay_jobs_progresos.cod_estado_job')
            ->where('pay_estados_jobs.cod_estado_job', '<>', 3)
            ->get();
        foreach ($tareasPendientes as $tarea) {
            // Your code here
            $tarea->fecha_registro_tarea;
            $currentDate = date('Y-m-d');
            $tareaFecha = date('Y-m-d', strtotime($tarea->fecha_registro_tarea));
            $tarea->diasDiferentes = $tareaFecha != $currentDate;
            $tarea->currentDate = $currentDate;
            $tarea->tareaFecha = $tareaFecha;
            if ($tareaFecha != $currentDate) {
                $harvestTerminadas[]   = $tarea;
                // The fecha_registro_tarea is on a different day than the current date
                $crewData = DB::table('pay_crews')
                    ->select('cod_crew', 'cantidad_escaneos', 'cod_empleado')
                    ->where('cod_harvest', $tarea->cod_harvest)
                    ->get();

                foreach ($crewData as $crew) {
                    DB::table('pay_crews')
                        ->where('cod_crew', $crew->cod_crew)
                        ->update(['cod_estado_job' => '3']);

                    DB::table('usu_usuarios')
                        ->where('cod_usuario', $crew->cod_empleado)
                        ->update(['disponible' => '1']);
                }
                DB::table('pay_jobs_progresos')
                    ->where('cod_job', $tarea->cod_job)
                    ->update(['cod_estado_job' => '3',  'hora_final' => DB::raw('CURRENT_TIME')]);
            }
        }

        return $harvestTerminadas;
    }
    private function terminarTareasMiscelaneas()
    {
        $harvestTerminadas = [];
        //Tareas del HARVEST
        $tareasPendientes = DB::table('pay_miscellaneous')
            ->select(
                'pay_miscellaneous.cod_miscellaneous',
                'pay_miscellaneous.date_insert AS fecha_registro_tarea',

                'pay_jobs_progresos.cod_job',
                'pay_jobs_progresos.cod_estado_job'
            )
            ->join('pay_jobs_progresos', 'pay_jobs_progresos.cod_miscellaneous', '=', 'pay_miscellaneous.cod_miscellaneous')
            ->join('pay_estados_jobs', 'pay_estados_jobs.cod_estado_job', '=', 'pay_jobs_progresos.cod_estado_job')
            ->where('pay_estados_jobs.cod_estado_job', '<>', 3)
            ->get();
        foreach ($tareasPendientes as $tarea) {
            // Your code here
            $tarea->fecha_registro_tarea;
            $currentDate = date('Y-m-d');
            $tareaFecha = date('Y-m-d', strtotime($tarea->fecha_registro_tarea));
            $tarea->diasDiferentes = $tareaFecha != $currentDate;
            $tarea->currentDate = $currentDate;
            $tarea->tareaFecha = $tareaFecha;
            if ($tareaFecha != $currentDate) {
                $harvestTerminadas[]   = $tarea;
                // The fecha_registro_tarea is on a different day than the current date
                $crewData = DB::table('pay_crews')
                    ->select('cod_crew', 'cantidad_escaneos', 'cod_empleado')
                    ->where('cod_miscellaneous', $tarea->cod_miscellaneous)
                    ->get();

                foreach ($crewData as $crew) {
                    DB::table('pay_crews')
                        ->where('cod_crew', $crew->cod_crew)
                        ->update(['cod_estado_job' => '3']);

                    DB::table('usu_usuarios')
                        ->where('cod_usuario', $crew->cod_empleado)
                        ->update(['disponible' => '1']);
                }
                DB::table('pay_jobs_progresos')
                    ->where('cod_job', $tarea->cod_job)
                    ->update(['cod_estado_job' => '3',  'hora_final' => DB::raw('CURRENT_TIME')]);
            }
        }

        return $harvestTerminadas;
    }

    function cargarTodaLaInformacionNecesaria(int $id_user)
    {
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");

        $datosRecopilados = [];
        $cropsAgesVinculadosAGranja = [];
        $locacionesVinculadosAGranja = [];
        $actividadesVinculadosAGranja = [];
        try {
            //code...

            $usuario = $this->userRepository->find($id_user);
            if ($usuario == null) {
                return response()->json(['message' => 'User not found', 'data' => $id_user], 404);
            }
            $codigosGranjasAsociadas = $this->usuariGranjaRepository->where('cod_usuario', $usuario->cod_usuario)->get();
            if ($codigosGranjasAsociadas == null || count($codigosGranjasAsociadas) == 0) {
                return response()->json(['message' => 'The user has no associated farms', 'data' => $id_user], 404);
            }
            $codigosGranjas = [];
            foreach ($codigosGranjasAsociadas as $granja) {
                $codigosGranjas[] = $granja->cod_granja;
            }
            $granjasAsociadas = $this->granjaRepository
                ->whereIn('cod_farms', $codigosGranjas)
                ->where('far_farms.cod_estado', $usuario->cod_estado)
                ->join('bw_inventario_estados_plantaciones', 'far_farms.cod_estado', '=', 'bw_inventario_estados_plantaciones.cod_estado')
                ->orderBy('farm')
                ->get();
            // ASIGNADO LAS GRANJAS OBTENIDAS A UN ARRAY
            $datosRecopilados['granjas'] = $granjasAsociadas;

            foreach ($granjasAsociadas as $granja) {
                $cropsAgesVinculadosAGranja[] =  DB::table('far_crop_bloques_implementados')
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
                        'bw_inventario_semilla.nombre_semilla',
                        'bw_inventario_semilla.cod_categoria',
                        'bw_inventario_plantaciones.edad',
                        DB::raw('concat(bw_inventario_plantaciones.edad," - ",bw_inventario_semilla.nombre_semilla) as datos_semillas'),
                    )
                    ->join('far_crop_semillas_bloques', 'far_crop_semillas_bloques.cod_bloque_implementado', '=', 'far_crop_bloques_implementados.cod_bloque_implementado')
                    ->join('bw_inventario_semilla', 'bw_inventario_semilla.cod_inventario', '=', 'far_crop_semillas_bloques.cod_semilla')
                    ->join('bw_inventario_plantaciones', 'bw_inventario_plantaciones.cod_plantacion', '=', 'far_crop_semillas_bloques.cod_plantacion')
                    ->where('far_crop_bloques_implementados.cod_farm', $granja->cod_farms)
                    ->groupBy('far_crop_semillas_bloques.cod_semilla')
                    ->groupBy('bw_inventario_plantaciones.edad')
                    ->orderBy('bw_inventario_plantaciones.cod_plantacion')
                    ->get();

                // Your code here
                $locacionesVinculadosAGranja[] = DB::table('far_locations')
                    ->select('cod_location', 'cod_farms', 'location', 'abreviacion')
                    ->where('cod_farms', '=', $granja->cod_farms)
                    ->get();
            }
            // ASIGNADO LOS CROPS AGES VINCULADOS A LA GRANJA A UN ARRAY
            $cropsAgesVinculadosAGranja = $this->flattenArray($cropsAgesVinculadosAGranja);
            $datosRecopilados['cropsAges'] = $cropsAgesVinculadosAGranja;


            // ASIGNADO LOS LOCACIONES VINCULADOS A LA GRANJA A UN ARRAY
            $locacionesVinculadosAGranja = $this->flattenArray($locacionesVinculadosAGranja);
            $datosRecopilados['locaciones'] = $locacionesVinculadosAGranja;


            foreach ($locacionesVinculadosAGranja as $locacion) {
                $cod_farms = $locacion->cod_farms;
                $cod_location = $locacion->cod_location;
                $actividadesVinculadosAGranja[] = DB::table('pay_activities')
                    ->select('cod_activity', 'cod_farms', 'cod_location', 'codigo', 'activity', 'piece_rate')
                    ->where('cod_farms', $cod_farms)
                    ->where('cod_location', $cod_location)
                    ->get();
            }

            // ASIGNADO LAS ACTIVIDADES VINCULADAS A LA GRANJA A UN ARRAY
            $actividadesVinculadosAGranja = $this->flattenArray($actividadesVinculadosAGranja);
            $datosRecopilados['actividades'] = $actividadesVinculadosAGranja;


            $fieldsFiltrado = [];
            foreach ($cropsAgesVinculadosAGranja as $cropAge) {

                $codsCampos = explode(',', $cropAge->cods_fields);
                $cod_semilla = $cropAge->cod_semilla ?? 0;
                $cod_plantacion = $cropAge->cod_plantacion ?? 0;
                $fields = $this->campoRepoRepository->whereIn('cod_field', $codsCampos)->get(['cod_field', 'cod_farm', 'field', 'activo']);
                $alMenosUnBloque =  false;

                foreach ($fields as $field) {
                    // Access each field object using $field variable
                    // Add your code here
                    $codHarvestFarms = DB::table('pay_harvests_fields')
                        ->join('pay_jobs_progresos', 'pay_jobs_progresos.cod_harvest', '=', 'pay_harvests_fields.cod_harvest')
                        ->join('pay_estados_jobs', 'pay_estados_jobs.cod_estado_job', '=', 'pay_jobs_progresos.cod_estado_job')
                        ->select(
                            'pay_harvests_fields.cod_harvest_fields',
                            'pay_harvests_fields.cod_field',
                            'pay_harvests_fields.cod_harvest'
                        )
                        ->where('pay_harvests_fields.cod_field', $field->cod_field)
                        ->whereIn('pay_estados_jobs.estado_job', ['lista', 'iniciada'])
                        ->get();
                    if ($codHarvestFarms->isEmpty()) {
                        // Variable $codHarvestFarms is empty
                        $field->cod_semilla = (int)$cod_semilla;
                        $field->cod_plantacion = (int)$cod_plantacion;
                        $fieldsFiltrado[] = $field;
                    } else {
                        $alMenosUnBloque =  false;
                        foreach ($codHarvestFarms as $codHarvestField) {
                            // Variable $codHarvestFarms is not empty
                            $harvests = DB::table('pay_harvests')
                                ->join('far_crop_semillas_bloques', 'far_crop_semillas_bloques.cod_semilla_bloque', '=', 'pay_harvests.crop_age')
                                ->select('pay_harvests.cod_harvest', 'pay_harvests.crop_age', 'far_crop_semillas_bloques.cod_semilla', 'far_crop_semillas_bloques.cod_bloque_implementado')
                                // ->where('pay_harvests.cod_harvest', $codHarvestField->cod_harvest)
                                ->where('pay_harvests.cod_plantacion', $cod_plantacion)
                                ->get();
                            if (!$harvests->isEmpty()) {
                                foreach ($harvests as $harvest) {
                                    $datosSemillas = DB::table('far_crop_semillas_bloques')
                                        ->join('far_crop_bloques_implementados', 'far_crop_bloques_implementados.cod_bloque_implementado', '=', 'far_crop_semillas_bloques.cod_bloque_implementado')
                                        ->select(
                                            DB::raw('GROUP_CONCAT(DISTINCT far_crop_semillas_bloques.cod_semilla_bloque) AS cod_semilla_bloque'),
                                            DB::raw('GROUP_CONCAT(DISTINCT far_crop_semillas_bloques.cod_bloque_implementado) AS cod_bloque_implementado'),
                                            DB::raw('GROUP_CONCAT(DISTINCT far_crop_semillas_bloques.cod_semilla) AS cod_semilla'),
                                            DB::raw('GROUP_CONCAT(DISTINCT far_crop_bloques_implementados.cod_bloque) AS cod_bloque'),
                                            // 'far_crop_semillas_bloques.cod_semilla_bloque',
                                            // 'far_crop_semillas_bloques.cod_bloque_implementado',
                                            // 'far_crop_semillas_bloques.cod_semilla',
                                            // 'far_crop_bloques_implementados.cod_bloque'
                                        )
                                        ->where('far_crop_semillas_bloques.cod_semilla', $harvest->cod_semilla)
                                        ->groupBy('far_crop_bloques_implementados.cod_bloque')
                                        ->distinct()
                                        ->get();

                                    if (!$datosSemillas->isEmpty()) {
                                        foreach ($datosSemillas as $semilla) {
                                            $bloques = DB::table('far_crop_bloques_implementados')
                                                ->select('far_crop_bloques_implementados.cod_bloque', 'far_bloques.bloque', 'far_crop_bloques_implementados.cod_field')
                                                ->join('far_crop_semillas_bloques', 'far_crop_semillas_bloques.cod_bloque_implementado', '=', 'far_crop_bloques_implementados.cod_bloque_implementado')
                                                ->join('far_bloques', 'far_bloques.cod_bloque', '=', 'far_crop_bloques_implementados.cod_bloque')
                                                ->where('far_crop_bloques_implementados.cod_farm', $field->cod_farm)
                                                ->where('far_crop_bloques_implementados.cod_field', $field->cod_field)
                                                ->where('far_crop_semillas_bloques.cod_semilla', $semilla->cod_semilla)
                                                ->where('far_crop_semillas_bloques.cod_plantacion', $cod_plantacion)
                                                ->groupBy('far_crop_bloques_implementados.cod_bloque')
                                                ->distinct()
                                                ->get();
                                            foreach ($bloques as $bloque) {
                                                $harvestsBlocks = DB::table('pay_harvests_blocks')
                                                    ->join('pay_jobs_progresos', 'pay_jobs_progresos.cod_harvest', '=', 'pay_harvests_blocks.cod_harvest')
                                                    ->join('pay_estados_jobs', 'pay_estados_jobs.cod_estado_job', '=', 'pay_jobs_progresos.cod_estado_job')
                                                    ->select(
                                                        'pay_harvests_blocks.cod_harvests_blocks',
                                                        'pay_harvests_blocks.cod_block',
                                                        'pay_harvests_blocks.cod_harvest',
                                                        'pay_jobs_progresos.cod_job',
                                                        'pay_jobs_progresos.cod_job_local',
                                                        'pay_jobs_progresos.cod_estado_job',
                                                        'pay_estados_jobs.estado_job'
                                                    )
                                                    ->where('pay_harvests_blocks.cod_block', $bloque->cod_bloque)
                                                    ->where('pay_harvests_blocks.cod_plantacion', $cod_plantacion)
                                                    ->whereIn('pay_estados_jobs.estado_job', ['lista', 'iniciada'])
                                                    ->get();
                                                if ($harvestsBlocks->isEmpty()) {
                                                    $alMenosUnBloque = true;
                                                }
                                            }
                                        }
                                    } else {
                                        $alMenosUnBloque = true;
                                    }
                                }
                            } else {
                                $alMenosUnBloque = true;
                            }
                        }
                        if ($alMenosUnBloque) {
                            $field->cod_semilla = (int)$cod_semilla;
                            $field->cod_plantacion = (int)$cod_plantacion;
                            $fieldsFiltrado[] = $field;
                        }
                    }
                }
            }
            // $fieldsFiltrado = $this->flattenArray($fieldsFiltrado);
            // ASIGNADO LOS FIELDS FILTRADOS A UN ARRAY
            $datosRecopilados['campos'] = $fieldsFiltrado;

            $bloqueFiltrado = [];
            $cods_fields = [];

            foreach ($fieldsFiltrado as $field) {

                $cods_fields = $field['cod_field'];
                $cod_farm = $field['cod_farm'];
                $cod_semilla = $field['cod_semilla'];
                $cod_plantacion = $field['cod_plantacion'];
                // $cods_fields = explode(',', $cods_fields);


                $bloques = DB::table('far_crop_bloques_implementados')
                    ->select(
                        'far_crop_bloques_implementados.cod_bloque',
                        'far_bloques.bloque',
                        'far_crop_bloques_implementados.cod_field',
                        'far_crop_bloques_implementados.cod_farm',
                        'far_crop_semillas_bloques.cod_semilla',
                        'far_crop_semillas_bloques.cod_plantacion',
                    )
                    ->join('far_crop_semillas_bloques', 'far_crop_semillas_bloques.cod_bloque_implementado', '=', 'far_crop_bloques_implementados.cod_bloque_implementado')
                    ->join('far_bloques', 'far_bloques.cod_bloque', '=', 'far_crop_bloques_implementados.cod_bloque')
                    ->where('far_crop_bloques_implementados.cod_farm', $cod_farm)
                    ->where('far_crop_bloques_implementados.cod_field', $cods_fields)
                    ->where('far_crop_semillas_bloques.cod_semilla', $cod_semilla)
                    ->where('far_crop_semillas_bloques.cod_plantacion', $cod_plantacion)
                    ->groupBy('far_crop_bloques_implementados.cod_bloque')
                    ->distinct()
                    ->get();
                foreach ($bloques as $bloque) {
                    $harvestsBlocks = DB::table('pay_harvests_blocks')
                        ->join('pay_jobs_progresos', 'pay_jobs_progresos.cod_harvest', '=', 'pay_harvests_blocks.cod_harvest')
                        ->join('pay_estados_jobs', 'pay_estados_jobs.cod_estado_job', '=', 'pay_jobs_progresos.cod_estado_job')
                        ->select(
                            'pay_harvests_blocks.cod_harvests_blocks',
                            'pay_harvests_blocks.cod_block',
                            'pay_harvests_blocks.cod_harvest',
                            'pay_jobs_progresos.cod_job',
                            'pay_jobs_progresos.cod_job_local',
                            'pay_jobs_progresos.cod_estado_job',
                            'pay_estados_jobs.estado_job'
                        )
                        ->where('pay_harvests_blocks.cod_block', $bloque->cod_bloque)
                        ->whereIn('pay_estados_jobs.estado_job', ['lista', 'iniciada'])
                        ->get();

                    if ($harvestsBlocks->isEmpty()) {
                        $bloqueFiltrado[] = $bloque;
                    }
                }
            }
            // ASIGNADO LOS BLOQUES FILTRADOS A UN ARRAY
            $datosRecopilados['bloques'] = $bloqueFiltrado;
            $datosRecopilados['tipo_paquetes'] = $this->listaTiposPaquetesAsociados();

            return HelpController::successResponse(0, 'Registro obetnido exitosamente', $datosRecopilados, 200);
        } catch (\Throwable $th) {
            return HelpController::failureResponse(1555, 'TrayCath ejecutado', $th->getMessage(), 500);

            //throw $th;
        }
    }
    function flattenArray($array)
    {
        $flattenedArray = [];
        foreach ($array as $subArray) {
            foreach ($subArray as $item) {
                $flattenedArray[] = $item;
            }
        }
        return $flattenedArray;
    }

    public function listaTiposPaquetesAsociados()
    {


        $cods_tipos_packs = [];

        $cods_tipos_packs = DB::table('pay_pack_for_farm_location_category')
            ->select(DB::raw('GROUP_CONCAT(DISTINCT cod_tipo_pack) AS cod_tipo_pack'))
            ->get()
            ->pluck('cod_tipo_pack')
            ->toArray();


        $cods_tipos_packs = explode(',', $cods_tipos_packs[0]);
        $tipoPacks = DB::table('pay_tipo_packs')
            ->whereIn('pay_tipo_packs.cod_tipo_pack', $cods_tipos_packs)
            // ->where('pay_tipo_packs.activo', 1)
            ->join(
                'pay_pack_for_farm_location_category',
                'pay_pack_for_farm_location_category.cod_tipo_pack',
                '=',
                'pay_tipo_packs.cod_tipo_pack'
            )
            ->select(
                'pay_tipo_packs.cod_tipo_pack',
                'pay_tipo_packs.tipo_pack',
                'pay_tipo_packs.cantidad',
                'pay_tipo_packs.activo',
                'pay_pack_for_farm_location_category.cod_farm',
                'pay_pack_for_farm_location_category.cod_location',
                'pay_pack_for_farm_location_category.cod_categoria'
            )
            ->get();
        return $tipoPacks;
    }

    public function eliminarDatos()
    {
       return $this->resetearAutoIncrementAll();
        // $ultimoIndice = DB::table('bw_plantaciones_bitacora')->max('cod_bitacora');

        // $indiceActual = 1;
        // do {
        //     DB::table('bw_plantaciones_bitacora')
        //         ->where('cod_bitacora', $indiceActual)
        //         ->delete();
        //     $indiceActual++;
        // } while ($indiceActual <= $ultimoIndice);

        // return response()->json(['message' => 'Datos eliminados exitosamente', 'cantidad_eliminada' => $ultimoIndice], 200);
    }

    public function resetearAutoIncrementAll()
    {
        $dbName = config('database.connections.mysql.database');
        $tables = DB::select('SHOW TABLES');
        $resetCount = 0;

        foreach ($tables as $table) {
            $tableName = array_values((array)$table)[0];
            DB::statement("ALTER TABLE `{$dbName}`.`{$tableName}` AUTO_INCREMENT = 1;");
            $resetCount++;
        }

        return response()->json([
            'message' => 'Se ha reiniciado el AUTO_INCREMENT de todas las tablas',
            'tables_reset' => $resetCount
        ], 200);
    }
}
