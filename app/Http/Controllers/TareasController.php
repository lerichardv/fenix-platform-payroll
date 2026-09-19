<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\HelpController;
use App\Models\Crew;
use App\Models\Harvest;
use App\Models\JobProgreso;
use App\Models\User;
use App\Models\UsuarioGranjas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TareasController extends Controller
{
    /** @var  userRepository */
    private $userRepository;

    /** @var  harvestsRepository */
    private $harvestsRepository;

    /** @var  usuariGranjaRepository */
    private $usuariGranjaRepository;

    /** @var  jobEnProgresoRepository */
    private $jobEnProgresoRepository;

    /** @var  crewRepository */
    private $crewRepository;

    public function __construct(
        User $userRepo,
        Harvest $harvestRepo,
        UsuarioGranjas $usuariGranjaRepo,
        Crew $crewGranjaRepo,
        JobProgreso $jobEnProgresoRepo
    ) {
        $this->userRepository = $userRepo;
        $this->harvestsRepository = $harvestRepo;
        $this->usuariGranjaRepository = $usuariGranjaRepo;
        $this->jobEnProgresoRepository = $jobEnProgresoRepo;
        $this->crewRepository = $crewGranjaRepo;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return $this->harvestsRepository->all();
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
        // ->whereDate('pay_harvests.date_insert', '=', date('Y-m-d'))

        //
    }

    public function tareasAsignadasPorJefe(string $id)
    {

        try {
            HelpController::setDatabaseModeParaGrandesQuerys();

            $cantidadMiscelaneos = DB::table('pay_jobs_progresos')
                ->select(DB::raw('count(pay_jobs_progresos.cod_job) as cantidad_miscelaneos'))
                ->where('user_insert', $id)
                ->where('cod_miscellaneous', '>', 0)
                ->whereDate('date_insert', '=', date('Y-m-d'))
                ->first();


            if ($cantidadMiscelaneos->cantidad_miscelaneos > 0) {

                //TODO: Mejorar la consulta
                $tareasCompletasMiscenlaneas = DB::table('pay_jobs_progresos')
                    ->select(
                        'pay_jobs_progresos.cod_job',
                        'pay_jobs_progresos.cod_miscellaneous',
                        'pay_jobs_progresos.cod_harvest',
                        'pay_jobs_progresos.cod_job_local',
                        'pay_jobs_progresos.cod_estado_job',
                        'pay_jobs_progresos.fecha_job',
                        'pay_jobs_progresos.hora_inicio',
                        'pay_jobs_progresos.hora_final',
                        'pay_jobs_progresos.identificador_unico_local',
                        DB::raw('GROUP_CONCAT(DISTINCT pay_miscelaneos_blocks.cod_block) AS cod_blocks'),
                        DB::raw("GROUP_CONCAT(DISTINCT pay_miscelaneos_fields.cod_field) AS cod_fields"),
                        'pay_crews.cod_crew',
                        'pay_crews.cod_empleado',
                        DB::raw('GROUP_CONCAT(DISTINCT pay_crews.cod_empleado) AS cods_empleados'),
                        'pay_miscellaneous.crop_age as crop_age_miscelaneo',
                        'far_crop_semillas_bloques.cod_semilla_bloque',
                        'far_crop_semillas_bloques.cod_semilla',
                        'bw_inventario_semilla.nombre_semilla',
                        'bw_inventario_semilla.abreviatura_semilla',
                        'bw_inventario_plantaciones.edad AS edad_semilla',
                        'pay_jobs_progresos.cod_miscellaneous',
                        'far_farms.cod_farms',
                        'far_farms.farm',
                        'bw_inventario_estados_plantaciones.cod_estado',
                        'bw_inventario_estados_plantaciones.abreviatura',
                        'pay_tipo_pagos.tipo_pago',
                        'pay_tipo_pagos.abreviatura AS abreviatura_pago',
                        'far_locations.cod_location',
                        'far_locations.location',
                        'far_locations.abreviacion AS abreviacion_locacion',
                        'pay_activities.cod_activity',
                        'pay_activities.activity',
                        'pay_activities.codigo AS codigo_actividad',
                        DB::raw("COALESCE(pay_estados_jobs.estado_job, 'lista') AS estado_tarea")
                    )
                    ->join('pay_miscellaneous', 'pay_miscellaneous.cod_miscellaneous', '=', 'pay_jobs_progresos.cod_miscellaneous')
                    ->leftJoin('far_crop_semillas_bloques', function ($join) {
                        $join->on('far_crop_semillas_bloques.cod_semilla_bloque', '=', 'pay_miscellaneous.crop_age');
                    })
                    ->leftJoin('bw_inventario_plantaciones', 'bw_inventario_plantaciones.cod_plantacion', '=', 'far_crop_semillas_bloques.cod_plantacion')
                    ->leftJoin('bw_inventario_semilla', 'bw_inventario_semilla.cod_inventario', '=', 'far_crop_semillas_bloques.cod_semilla')
                    ->leftJoin('pay_tipo_pagos', function ($join) {
                        $join->on('pay_tipo_pagos.cod_tipo_pago', '=', 'pay_miscellaneous.cod_tipo_pago');
                    })
                    ->leftJoin('far_farms', function ($join) {
                        $join->on('far_farms.cod_farms', '=', 'pay_miscellaneous.cod_farm');
                    })
                    ->leftJoin('bw_inventario_estados_plantaciones', 'bw_inventario_estados_plantaciones.cod_estado', '=', 'far_farms.cod_estado')
                    ->leftJoin('far_locations', 'far_locations.cod_location', '=', 'pay_miscellaneous.cod_location')
                    ->leftJoin('pay_activities', 'pay_activities.cod_activity', '=', 'pay_miscellaneous.cod_activity')
                    ->leftJoin('pay_crews', function ($join) {
                        $join->on('pay_crews.cod_miscellaneous', '=', 'pay_miscellaneous.cod_miscellaneous');
                    })
                    ->leftJoin('pay_miscelaneos_blocks', 'pay_miscelaneos_blocks.cod_miscelaneos', '=', 'pay_miscellaneous.cod_miscellaneous')
                    ->leftJoin('pay_miscelaneos_fields', 'pay_miscelaneos_fields.cod_miscelaneos', '=', 'pay_miscellaneous.cod_miscellaneous')
                    ->leftJoin('pay_estados_jobs', 'pay_estados_jobs.cod_estado_job', '=', 'pay_jobs_progresos.cod_estado_job')
                    ->where('pay_jobs_progresos.user_insert', '=', $id)
                    ->groupBy('pay_jobs_progresos.cod_job')
                    ->whereDate('pay_miscellaneous.date_insert', '=', date('Y-m-d'))
                    ->orderBy('pay_miscellaneous.cod_miscellaneous', 'desc')
                    ->get();

                foreach ($tareasCompletasMiscenlaneas as $tarea) {
                    $tarea->cods_empleados = explode(',', $tarea->cods_empleados);
                    $tarea->cod_crew = explode(',', $tarea->cod_crew);
                    $tarea->cod_blocks = explode(',', $tarea->cod_blocks);
                    $tarea->cod_fields = explode(',', $tarea->cod_fields);
                    $empleados = DB::table('usu_usuarios')
                        ->select(
                            'usu_usuarios.cod_usuario',
                            'usu_usuarios.usuario',
                            'usu_usuarios.nombre_1',
                            'usu_usuarios.apellido_1',
                            'usu_usuarios.pin',
                            'usu_usuarios.qcpin',
                            'pay_crews.cod_estado_job',
                            'pay_estados_jobs.estado_job',
                            DB::raw('COALESCE(pay_crews.cantidad_escaneos, 0) AS cantidad_escaneos_desfazdo'),
                            'pay_crews.cod_crew',
                            'pay_crews.pin AS crew_pin',
                            'pay_crews.qc_pin AS crew_qc_pin',
                            DB::raw("COALESCE(pay_estados_jobs.estado_job, 'lista') AS estado_empleado"),
                            DB::raw('SUM(pay_lista_empleados_jobs.pieces) AS cantidad_escaneos')
                        )
                        ->leftJoin('pay_crews', 'usu_usuarios.cod_usuario', '=', 'pay_crews.cod_empleado')
                        ->leftJoin('pay_estados_jobs', 'pay_estados_jobs.cod_estado_job', '=', 'pay_crews.cod_estado_job')
                        ->leftJoin('pay_lista_empleados_jobs', 'pay_lista_empleados_jobs.cod_crew', '=', 'pay_crews.cod_crew')
                        ->where('pay_crews.cod_miscellaneous', $tarea->cod_miscellaneous)
                        ->groupBy('usu_usuarios.cod_usuario', 'pay_crews.cod_crew', 'pay_crews.pin', 'pay_crews.qc_pin', 'pay_estados_jobs.estado_job')
                        ->get();

                    foreach ($empleados as &$empleado) {
                        $empleado->cod_empleado = $empleado->cod_usuario;
                        $empleado->cod_harvest = $tarea->cod_harvest;
                        $empleado->cod_crew = $empleado->cod_crew;
                        $empleado->pin = $empleado->crew_pin;
                        $empleado->qc_pin = $empleado->crew_qc_pin;
                        $empleado->estado_empleado = $empleado->estado_empleado;
                        $empleado->cantidad_escaneos = $empleado->cantidad_escaneos;
                    }
                    $tarea->empleados = $empleados;

                    $bloques = DB::table('far_bloques')
                        ->select(DB::raw("GROUP_CONCAT(bloque SEPARATOR ' - ') as bloques"))
                        ->whereIn('cod_bloque', $tarea->cod_blocks)
                        ->get();

                    $campos = DB::table('far_fields')
                        ->select(DB::raw("GROUP_CONCAT(field SEPARATOR ' - ') as campos"))
                        ->whereIn('cod_field', $tarea->cod_fields)
                        ->get();

                    $tarea->campos = $campos[0]->campos;
                    $tarea->bloques = $bloques[0]->bloques;
                    if (isset($tarea->nombre_semilla)) {
                        $tarea->texto_principal = $campos[0]->campos . ' / ' . $bloques[0]->bloques . ' / ' . $tarea->activity . ' / ' . $tarea->nombre_semilla;
                    } else {
                        if (isset($campos[0]->campos) && isset($bloques[0]->bloques)) {
                            $tarea->texto_principal = $campos[0]->campos . ' / ' . $bloques[0]->bloques . ' / ' . $tarea->activity;
                        } else {
                            $tarea->texto_principal = $tarea->activity;
                        }
                    }
                    $tarea->texto_secundario = "M " . $tarea->location . ' - ' . $tarea->abreviatura_pago . ' - ' . $tarea->farm;
                }
            } else {
                $tareasCompletasMiscenlaneas = [];
            }

            $cantidadHarvest = DB::table('pay_jobs_progresos')
                ->select(DB::raw('count(pay_jobs_progresos.cod_job) as cantidad_harvest'))
                ->where('user_insert', $id)
                ->where('cod_harvest', '>', 0)
                ->whereDate('date_insert', '=', date('Y-m-d'))
                ->first();
            if ($cantidadHarvest->cantidad_harvest > 0) {
                //TODO: Mejorar la consulta
                $tareasHarvest = DB::table('pay_harvests')
                    ->select(
                        'pay_harvests.cod_harvest',
                        'pay_harvests.user_admin',
                        'pay_harvests.cod_farm',
                        'pay_harvests.crop_age',
                        'pay_harvests.cod_tipo_pack',
                        'pay_harvests.cod_tipo_pago',
                        'far_crop_semillas_bloques.cod_semilla_bloque',
                        'far_crop_semillas_bloques.cod_semilla',
                        'bw_inventario_semilla.nombre_semilla',
                        'bw_inventario_semilla.abreviatura_semilla',
                        'pay_tipo_packs.tipo_pack',
                        'pay_tipo_packs.cantidad AS cantidad_pack',
                        'pay_tipo_pagos.tipo_pago',
                        'pay_tipo_pagos.abreviatura AS abreviatura_pago',
                        DB::raw("GROUP_CONCAT(DISTINCT pay_crews.cod_crew) as cod_crew"),
                        DB::raw("GROUP_CONCAT(DISTINCT pay_crews.cod_empleado) as cods_empleados"),
                        'pay_crews.cod_supervisor',
                        'far_farms.farm',
                        'far_farms.cod_estado',
                        'bw_inventario_estados_plantaciones.abreviatura AS abreviatura_estado',
                        DB::raw("GROUP_CONCAT(DISTINCT pay_harvests_blocks.cod_block) AS cod_blocks"),
                        DB::raw("GROUP_CONCAT(DISTINCT pay_harvests_fields.cod_field) AS cod_fields"),
                        'pay_jobs_progresos.cod_job',
                        'pay_jobs_progresos.cod_job_local',
                        'pay_jobs_progresos.cod_estado_job',
                        'pay_jobs_progresos.fecha_job',
                        'pay_jobs_progresos.hora_inicio',
                        'pay_jobs_progresos.hora_final',
                        'pay_jobs_progresos.identificador_unico_local',
                        'bw_inventario_plantaciones.edad as edad_semilla',
                        DB::raw("COALESCE(pay_estados_jobs.estado_job, 'lista') AS estado_tarea")
                    )
                    ->join('far_farms', 'far_farms.cod_farms', '=', 'pay_harvests.cod_farm')
                    ->join('far_crop_semillas_bloques', 'far_crop_semillas_bloques.cod_semilla_bloque', '=', 'pay_harvests.crop_age')
                    ->join('bw_inventario_plantaciones', 'bw_inventario_plantaciones.cod_plantacion', '=', 'far_crop_semillas_bloques.cod_plantacion')
                    ->join('bw_inventario_semilla', 'bw_inventario_semilla.cod_inventario', '=', 'far_crop_semillas_bloques.cod_semilla')
                    ->join('pay_tipo_packs', 'pay_tipo_packs.cod_tipo_pack', '=', 'pay_harvests.cod_tipo_pack')
                    ->join('pay_tipo_pagos', 'pay_tipo_pagos.cod_tipo_pago', '=', 'pay_harvests.cod_tipo_pago')
                    ->join('pay_crews', 'pay_crews.cod_harvest', '=', 'pay_harvests.cod_harvest')
                    ->join('bw_inventario_estados_plantaciones', 'bw_inventario_estados_plantaciones.cod_estado', '=', 'far_farms.cod_estado')
                    ->join('pay_harvests_blocks', 'pay_harvests_blocks.cod_harvest', '=', 'pay_harvests.cod_harvest')
                    ->join('pay_harvests_fields', 'pay_harvests_fields.cod_harvest', '=', 'pay_harvests.cod_harvest')
                    ->join('pay_jobs_progresos', 'pay_jobs_progresos.cod_harvest', '=', 'pay_harvests.cod_harvest')
                    ->leftJoin('pay_estados_jobs', 'pay_estados_jobs.cod_estado_job', '=', 'pay_jobs_progresos.cod_estado_job')
                    ->where('pay_harvests.user_admin', $id)
                    ->whereDate('pay_harvests.date_insert', '=', date('Y-m-d'))
                    ->groupBy('pay_harvests.cod_harvest')
                    ->orderBy('pay_harvests.cod_harvest', 'desc')
                    ->get();

                foreach ($tareasHarvest as $tarea) {
                    $tarea->cods_empleados = explode(',', $tarea->cods_empleados);
                    $tarea->cod_crew = explode(',', $tarea->cod_crew);
                    $tarea->cod_blocks = explode(',', $tarea->cod_blocks);
                    $tarea->cod_fields = explode(',', $tarea->cod_fields);

                    $empleados = DB::table('usu_usuarios')
                        ->select(
                            'usu_usuarios.cod_usuario',
                            'usu_usuarios.usuario',
                            'usu_usuarios.nombre_1',
                            'usu_usuarios.apellido_1',
                            'usu_usuarios.pin',
                            'usu_usuarios.qcpin',
                            'pay_crews.cod_estado_job',
                            'pay_estados_jobs.estado_job',
                            DB::raw('COALESCE(pay_crews.cantidad_escaneos, 0) AS cantidad_escaneos_desfazdo'),
                            'pay_crews.cod_crew',
                            'pay_crews.pin AS crew_pin',
                            'pay_crews.qc_pin AS crew_qc_pin',
                            DB::raw("COALESCE(pay_estados_jobs.estado_job, 'lista') AS estado_empleado"),
                            DB::raw('SUM(pay_lista_empleados_jobs.pieces) AS cantidad_escaneos')
                        )
                        ->leftJoin('pay_crews', 'usu_usuarios.cod_usuario', '=', 'pay_crews.cod_empleado')
                        ->leftJoin('pay_estados_jobs', 'pay_estados_jobs.cod_estado_job', '=', 'pay_crews.cod_estado_job')
                        ->leftJoin('pay_lista_empleados_jobs', 'pay_lista_empleados_jobs.cod_crew', '=', 'pay_crews.cod_crew')
                        ->where('pay_crews.cod_harvest', $tarea->cod_harvest)
                        ->groupBy('usu_usuarios.cod_usuario', 'pay_crews.cod_crew', 'pay_crews.pin', 'pay_crews.qc_pin', 'pay_estados_jobs.estado_job')
                        ->get();

                    foreach ($empleados as &$empleado) {
                        $empleado->cod_empleado = $empleado->cod_usuario;
                        $empleado->cod_harvest = $tarea->cod_harvest;
                        $empleado->cod_crew = $empleado->cod_crew;
                        $empleado->pin = $empleado->crew_pin;
                        $empleado->qc_pin = $empleado->crew_qc_pin;
                        $empleado->estado_empleado = $empleado->estado_empleado;
                        $empleado->cantidad_escaneos = $empleado->cantidad_escaneos;
                    }
                    $tarea->empleados = $empleados;

                    $bloques = DB::table('far_bloques')
                        ->select(DB::raw("GROUP_CONCAT(bloque SEPARATOR ' - ') as bloques"))
                        ->whereIn('cod_bloque', $tarea->cod_blocks)
                        ->get();

                    $campos = DB::table('far_fields')
                        ->select(DB::raw("GROUP_CONCAT(field SEPARATOR ' - ') as campos"))
                        ->whereIn('cod_field', $tarea->cod_fields)
                        ->get();

                    $tarea->campos = $campos[0]->campos;
                    $tarea->bloques = $bloques[0]->bloques;

                    $tarea->texto_principal = $campos[0]->campos . ' / ' . $bloques[0]->bloques . ' / ' . $tarea->nombre_semilla;
                    $tarea->texto_secundario = "H " . $tarea->abreviatura_pago . ' - ' . $tarea->tipo_pack . ' - ' . $tarea->abreviatura_estado . ' - ' . $tarea->farm;
                }
            } else {
                $tareasHarvest = [];
            }

            $unificacionTareas = [...$tareasCompletasMiscenlaneas, ...$tareasHarvest];
            usort($unificacionTareas, function ($a, $b) {
                return $b->cod_job - $a->cod_job;
            });
            HelpController::desconectarBaseDatos();
        } catch (\Exception $e) {
            HelpController::desconectarBaseDatos();

            // throw $e;
            return response()->json(['message' => 'Failed to retrieve the list of assigned tasks', "code" => 1454, 'extra' => $e], 400);
        }

        return $unificacionTareas;
    }

    public function cambiarEstadoTarea(Request $request)
    {
        $body = $request->all();
        $id_harvest = $body['id_harvest'];
        $id_miscelano = $body['id_miscelaneo'];
        $estado = $body['estado'];
        $latitud = $body['latitud'];
        $longitud = $body['longitud'];
        $hora_dispositivo_movil = $body['hora_dispositivo_movil'] ?? date('Y-m-d H:i:s');
        $resultadoActualizar = false;

        if ($id_harvest != "0" && $id_harvest != null && $id_harvest != "null") {
            $resultadoActualizar = $this->cambiarEstadoHarvest(
                $id_harvest,
                $estado,
                $latitud,
                $longitud,
                $hora_dispositivo_movil
            );
        } else if ($id_miscelano != "0" && $id_miscelano != null && $id_miscelano != "null") {
            $resultadoActualizar = $this->cambiarEstadoMiscelaneos(
                $id_miscelano,
                $estado,
                $latitud,
                $longitud,
                $hora_dispositivo_movil
            );
        }
        if ($resultadoActualizar) {
            return response()->json(['message' => 'Successful state change', "code" => 0, 'extra' => $estado, "datosRastreo" => $resultadoActualizar], 200);
        } else {
            return response()->json(['message' => 'Failed change', "code" => 1554, 'extra' => $estado, "datosRastreo" => $resultadoActualizar], 400);
        }
    }

    private function cambiarEstadoHarvest(
        string $id_harvest,
        string $estado,
        string $latitud,
        string $longitud,
        string $hora_dispositivo_movil
    ) {
        $coordenadas = $latitud . ',' . $longitud;
        $estadosJobs = DB::table('pay_estados_jobs')
            ->select('estado_job', 'cod_estado_job')
            ->where('estado_job', $estado)
            ->first();
        $estadoJob = "";
        $codEstadoJob = "";
        if ($estadosJobs) {
            $estadoJob = $estadosJobs->estado_job;
            $codEstadoJob = $estadosJobs->cod_estado_job;
            // Continue with the rest of the code
        } else {
            // Handle the case when no estado_job is found
            return false;
        }


        if ($estadoJob == "culminada") {
            $updateResultCrew = $this->crewRepository->where('cod_harvest', $id_harvest)->update(['cod_estado_job' => $codEstadoJob]);
            $crews = DB::table('pay_crews')
                ->select('cod_crew', 'cod_empleado')
                ->where('cod_harvest', $id_harvest)
                ->get();


            //Marcar la hora de inicio como la hora actual en caso de ser null
            $updateResultFinal = $this->jobEnProgresoRepository
                ->where('cod_harvest', $id_harvest)
                ->where('hora_inicio', '!=', null)
                ->where('hora_final', '!=', null)
                ->update(['cod_estado_job' => $codEstadoJob, 'hora_final' => $hora_dispositivo_movil]);

            $updateResultInicio = $this->jobEnProgresoRepository
                ->where('cod_harvest', $id_harvest)
                ->where('hora_inicio', null)
                ->update(['cod_estado_job' => $codEstadoJob, 'hora_inicio' => $hora_dispositivo_movil]);

            $updateResultFinal = $this->jobEnProgresoRepository
                ->where('cod_harvest', $id_harvest)
                ->where('hora_final', null)
                ->update(['cod_estado_job' => $codEstadoJob, 'hora_final' => $hora_dispositivo_movil]);





            $updateResultListaEmpleados = DB::table('pay_lista_empleados_jobs')
                ->whereIn('cod_crew', $crews->pluck('cod_crew'))
                ->where('terminado', '0')
                ->where('cod_estado_job', '2')
                ->update(['terminado' => '1', 'cod_estado_job' => $codEstadoJob, 'hora_fuerza_terminado' => $hora_dispositivo_movil]);

            $updateResultCrewsFinal = DB::table('pay_crews')
                ->whereIn('cod_crew', $crews->pluck('cod_crew'))
                ->where('hora_final', null)
                ->update(['cod_estado_job' =>  $codEstadoJob, 'hora_final' => $hora_dispositivo_movil]);

            $updateResultCrewsInicio = DB::table('pay_crews')
                ->whereIn('cod_crew', $crews->pluck('cod_crew'))
                ->where('hora_inicio', null)
                ->update(['cod_estado_job' =>  $codEstadoJob, 'hora_inicio' => $hora_dispositivo_movil]);

            $updateResultUsuarios = DB::table('usu_usuarios')
                ->whereIn('cod_usuario', $crews->pluck('cod_empleado'))
                ->update(['disponible' => '1']);

            return [
                'updateResultInicio' => $updateResultInicio,
                'updateResultFinal' => $updateResultFinal,
                'updateResultListaEmpleados' => $updateResultListaEmpleados,
                'updateResultCrewsFinal' => $updateResultCrewsFinal,
                'updateResultCrewsInicio' => $updateResultCrewsInicio,
                'updateResultUsuarios' => $updateResultUsuarios
            ];
        } else  if ($estadoJob == "iniciada") {
            $updateResultCrew = $this->crewRepository->where('cod_harvest', $id_harvest)->update(['cod_estado_job' => $codEstadoJob]);
            $crews = DB::table('pay_crews')
                ->select('cod_crew', 'cod_empleado')
                ->where('cod_harvest', $id_harvest)
                ->get();

            $updateResult = $this->jobEnProgresoRepository
                ->where('cod_harvest', $id_harvest)
                ->where('cod_estado_job', 1)
                ->update(['cod_estado_job' => $codEstadoJob, 'hora_inicio' => $hora_dispositivo_movil]);

            foreach ($crews as $crew) {

                DB::table('pay_lista_empleados_jobs')
                    ->where('cod_crew', $crew->cod_crew)
                    ->where('terminado', '0')
                    ->where('cod_estado_job', '1')
                    ->update(['cod_estado_job' =>  $codEstadoJob]);


                DB::table('pay_crews')
                    ->where('cod_crew', $crew->cod_crew)
                    ->update(['cod_estado_job' =>  $codEstadoJob, 'hora_inicio' => $hora_dispositivo_movil]);
            }
        } else {
            $updateResultCrew = $this->crewRepository->where('cod_harvest', $id_harvest)->update(['cod_estado_job' => $codEstadoJob]);
        }
        if ($updateResult) {
            // Update was successful
            return $updateResult;
        } else {
            // Update failed
            return $updateResult;
        }

        HelpController::desconectarBaseDatos();
    }

    private function cambiarEstadoMiscelaneos(
        string $id_miscelano,
        string $estado,
        string $latitud,
        string $longitud,
        string $hora_dispositivo_movil
    ) {
        try {
            $coordenadas = $latitud . ',' . $longitud;
            $estadosJobs = DB::table('pay_estados_jobs')
                ->select('estado_job', 'cod_estado_job')
                ->where('estado_job', $estado)
                ->first();
            $estadoJob = "";
            $codEstadoJob = "";
            if ($estadosJobs) {
                $estadoJob = $estadosJobs->estado_job;
                $codEstadoJob = $estadosJobs->cod_estado_job;
                // Continue with the rest of the code
            } else {
                // Handle the case when no estado_job is found
            }


            // $updateResult = $this->jobEnProgresoRepository->where('cod_miscellaneous', $id_miscelano)->update(['cod_estado_job' => $codEstadoJob]);
            if ($estadoJob == "culminada") {
                $updateResultCrew = $this->crewRepository->where('cod_miscellaneous', $id_miscelano)->update(['cod_estado_job' => $codEstadoJob]);
                $crews = DB::table('pay_crews')
                    ->select('cod_crew', 'cod_empleado')
                    ->where('cod_miscellaneous', $id_miscelano)
                    ->get();

                //Marcar la hora de inicio como la hora actual en caso de ser null
                $updateResult = $this->jobEnProgresoRepository
                    ->where('cod_miscellaneous', $id_miscelano)
                    ->where('hora_inicio', null)
                    ->update(['cod_estado_job' => $codEstadoJob, 'hora_inicio' => $hora_dispositivo_movil]);

                // $updateResult = $this->jobEnProgresoRepository->where('cod_miscellaneous', $id_miscelano)->update(['cod_estado_job' => $codEstadoJob, 'hora_final' => $hora_dispositivo_movil]);

                $updateResultFinal = $this->jobEnProgresoRepository
                    ->where('cod_miscellaneous', $id_miscelano)
                    ->where('hora_inicio', '!=', null)
                    ->where('hora_final', '!=', null)
                    ->update(['cod_estado_job' => $codEstadoJob, 'hora_final' => $hora_dispositivo_movil]);

                $updateResultInicio = $this->jobEnProgresoRepository
                    ->where('cod_miscellaneous', $id_miscelano)
                    ->where('hora_inicio', null)
                    ->update(['cod_estado_job' => $codEstadoJob, 'hora_inicio' => $hora_dispositivo_movil]);

                $updateResultFinal = $this->jobEnProgresoRepository
                    ->where('cod_miscellaneous', $id_miscelano)
                    ->where('hora_final', null)
                    ->update(['cod_estado_job' => $codEstadoJob, 'hora_final' => $hora_dispositivo_movil]);


                $updateResultListaEmpleados = DB::table('pay_lista_empleados_jobs')
                    ->whereIn('cod_crew', $crews->pluck('cod_crew'))
                    ->where('terminado', '0')
                    ->update(['terminado' => '1', 'cod_estado_job' => $codEstadoJob, 'hora_fuerza_terminado' => $hora_dispositivo_movil]);

                $updateResultCrewsFinal = DB::table('pay_crews')
                    ->whereIn('cod_crew', $crews->pluck('cod_crew'))
                    ->where('hora_final', null)
                    ->update(['cod_estado_job' =>  $codEstadoJob, 'hora_final' => $hora_dispositivo_movil]);

                $updateResultUsuarios = DB::table('usu_usuarios')
                    ->whereIn('cod_usuario', $crews->pluck('cod_empleado'))
                    ->update(['disponible' => '1']);
                // foreach ($crews as $crew) {

                //     DB::table('pay_lista_empleados_jobs')
                //         ->where('cod_crew', $crew->cod_crew)
                //         ->where('terminado', '0')
                //         ->update(['terminado' => '1', 'cod_estado_job' => '3', 'hora_fuerza_terminado' => $hora_dispositivo_movil]);
                //     DB::table('pay_crews')
                //         ->where('cod_crew', $crew->cod_crew)
                //         ->where('hora_final', null)
                //         ->update(['cod_estado_job' =>  $codEstadoJob, 'hora_final' => $hora_dispositivo_movil]);
                //     DB::table('usu_usuarios')
                //         ->where('cod_usuario', $crew->cod_empleado)
                //         ->update(['disponible' => '1']);
                // }
            } else  if ($estadoJob == "iniciada") {
                // $updateResultCrew = $this->crewRepository->where('cod_miscellaneous', $id_miscelano)->update(['cod_estado_job' => $codEstadoJob,'terminado' => 1, 'terminado' => $hora_dispositivo_movil]);
                // $updateResultCrew = $this->crewRepository->where('cod_miscellaneous', $id_miscelano)->update(['cod_estado_job' => $codEstadoJob]);
                $updateResult = $this->crewRepository
                    ->where('cod_miscellaneous', $id_miscelano)

                    ->update(['cod_estado_job' => $codEstadoJob]);
                $crews = DB::table('pay_crews')
                    ->select('cod_crew', 'cod_empleado')
                    ->where('cod_miscellaneous', $id_miscelano)
                    ->get();
                $updateResult = $this->jobEnProgresoRepository
                    ->where('cod_miscellaneous', $id_miscelano)
                    ->where('cod_estado_job', 1)
                    ->update(['cod_estado_job' => $codEstadoJob, 'hora_inicio' => $hora_dispositivo_movil]);

                $updateResultCrewsInicio = DB::table('pay_crews')
                    ->whereIn('cod_crew', $crews->pluck('cod_crew'))
                    ->where('hora_inicio', null)
                    ->update(['cod_estado_job' =>  $codEstadoJob, 'hora_inicio' => $hora_dispositivo_movil]);
            } else {
                $updateResultCrew = $this->crewRepository->where('cod_miscellaneous', $id_miscelano)->update(['cod_estado_job' => $codEstadoJob]);
            }
            HelpController::desconectarBaseDatos();

            $updateResult = true;
            if ($updateResult) {
                // Update was successful
                return true;
            } else {
                // Update failed
                return false;
            }
        } catch (\Exception $e) {
            // Handle the exception
            return false;
        }
    }

    public function agregarCantidadEscaneo(Request $request)
    {
        $body = $request->all();
        $id_harvest = $body['id_harvest'];
        $id_miscelano = $body['id_miscelano'];
        $cantidad = $body['cantidad'];
        $latitud = $body['latitud'];
        $longitud = $body['longitud'];
        $cod_crew = $body['cod_crew'] ?? 0;
        $user_insert = $body['user_insert'];
        $cod_empleado = $body['cod_empleado'] ?? 0;
        $hora_escaneo = $body['hora_escaneo'] ?? date('Y-m-d H:i:s');

        $resultadoActualizar = false;

        if ($id_harvest != "0") {

            $crewData = DB::table('pay_crews')
                ->select('cod_crew', 'cod_harvest', 'cod_empleado', 'cod_estado_job')
                ->where('cod_harvest', $id_harvest)
                ->where('cod_empleado', $cod_empleado)
                ->limit(1)
                ->get();

            $cod_crew = $crewData[0]->cod_crew;
            $cod_estado_job = $crewData[0]->cod_estado_job;
            $resultadoActualizar = $this->actualizarCantidadEscaneoEnHarvest(
                $id_harvest,
                $cod_estado_job,
                $latitud,
                $longitud,
                $cod_crew,
                $user_insert,
                1,
                $hora_escaneo
            );
        } else if ($id_miscelano != "0") {
            $crewData = DB::table('pay_crews')
                ->select('cod_miscellaneous', 'cod_crew', 'cod_harvest', 'cod_empleado', 'cod_estado_job')
                ->where('cod_miscellaneous', $id_miscelano)
                ->where('cod_empleado', $cod_empleado)
                ->limit(1)
                ->get();

            $cod_crew = $crewData[0]->cod_crew;
            $cod_estado_job = $crewData[0]->cod_estado_job;
            $resultadoActualizar = $this->actualizarCantidadEscaneoEnMiscelaneos(
                $id_miscelano,
                $cod_estado_job,
                $latitud,
                $longitud,
                $cod_crew,
                $user_insert,
                1,
                $hora_escaneo
            );
        }
        if ($resultadoActualizar == 0) {
            $resultadoActualizarCantidad = DB::table('pay_lista_empleados_jobs')
                ->select(DB::raw('SUM(pieces) AS cantidad_escaneada'))
                ->where('cod_crew',  $cod_crew)
                ->groupBy('cod_crew')
                ->first();

            $cantidad_escaneada = $resultadoActualizarCantidad->cantidad_escaneada;
            return response()->json(['message' => 'Successful state change', "code" => 0, 'extra' => $cantidad_escaneada], 200);
        } else if ($resultadoActualizar == 1) {
            return response()->json(['message' => 'Failed to register the quantity', "code" => 1555, 'extra' => $resultadoActualizar], 400);
        } else  if ($resultadoActualizar == 2) {
            return response()->json(['message' => 'You must wait 30 seconds for your next scan', "code" => 1556, 'extra' => $resultadoActualizar], 400);
        } else  if ($resultadoActualizar == 3) {
            return response()->json(['message' => 'The employee assigned to this qcpin is not active in the current task', "code" => 1557, 'extra' => $resultadoActualizar], 400);
        }
    }

    public function agregarCantidadEscaneoPendientes(Request $request)
    {
        $body = $request->all();
        $id_harvest = $body['id_harvest'];
        $id_miscelano = $body['id_miscelano'];
        $cantidad = $body['cantidad'];
        $latitud = $body['latitud'];
        $longitud = $body['longitud'];
        $cod_crew = $body['cod_crew'] ?? 0;
        $user_insert = $body['user_insert'];
        $cod_empleado = $body['cod_empleado'] ?? 0;
        $cantidad_pendiente = $body['cantidad_pendiente'] ?? 1;
        $hora_escaneo = $body['hora_escaneo'] ?? date('Y-m-d H:i:s');
        $resultadoActualizar = false;

        if ($id_harvest != "0") {

            $crewData = DB::table('pay_crews')
                ->select('cod_crew', 'cod_harvest', 'cod_empleado', 'cod_estado_job')
                ->where('cod_harvest', $id_harvest)
                ->where('cod_empleado', $cod_empleado)
                ->limit(1)
                ->get();

            $cod_crew = $crewData[0]->cod_crew;
            $cod_estado_job = $crewData[0]->cod_estado_job;
            $resultadoActualizar = $this->actualizarCantidadEscaneoEnHarvest(
                $id_harvest,
                $cod_estado_job,
                $latitud,
                $longitud,
                $cod_crew,
                $user_insert,
                $cantidad_pendiente,
                $hora_escaneo,
            );
        } else if ($id_miscelano != "0") {
            $crewData = DB::table('pay_crews')
                ->select('cod_miscellaneous', 'cod_crew', 'cod_harvest', 'cod_empleado', 'cod_estado_job')
                ->where('cod_miscellaneous', $id_miscelano)
                ->where('cod_empleado', $cod_empleado)
                ->limit(1)
                ->get();

            $cod_crew = $crewData[0]->cod_crew;
            $cod_estado_job = $crewData[0]->cod_estado_job;
            $resultadoActualizar = $this->actualizarCantidadEscaneoEnMiscelaneos(
                $id_miscelano,
                $cod_estado_job,
                $latitud,
                $longitud,
                $cod_crew,
                $user_insert,
                $cantidad_pendiente,
                $hora_escaneo,
            );
        }
        if ($resultadoActualizar == 0) {
            $resultadoActualizarCantidad = DB::table('pay_lista_empleados_jobs')
                ->select(DB::raw('SUM(pieces) AS cantidad_escaneada'))
                ->where('cod_crew',  $cod_crew)
                ->groupBy('cod_crew')
                ->first();

            $cantidad_escaneada = $resultadoActualizarCantidad->cantidad_escaneada;
            return response()->json(['message' => 'Scan successfully registered', "id_harvest" => $id_harvest, "cod_miscellaneous" => $id_miscelano, 'cod_empleado' => $cod_empleado, "code" => 0, 'extra' => $cantidad_escaneada], 200);
        } else if ($resultadoActualizar == 1) {
            return response()->json(['message' => 'Failed to register the quantity', "code" => 1555, 'extra' => $resultadoActualizar], 400);
        } else  if ($resultadoActualizar == 2) {
            return response()->json(['message' => 'You must wait 30 seconds for your next scan', "code" => 1556, 'extra' => $resultadoActualizar], 400);
        } else  if ($resultadoActualizar == 3) {
            return response()->json(['message' => 'The employee assigned to this qcpin is not active in the current task, id_harvest: ' . $id_harvest . ', cod_miscellaneous: ' . $id_miscelano . ', cod_empleado: ' . $cod_empleado, "id_harvest" => $id_harvest, "cod_miscellaneous" => $id_miscelano, 'cod_empleado' => $cod_empleado, "code" => 1557, 'extra' => $resultadoActualizar], 400);
        }
    }

    private function actualizarCantidadEscaneoEnHarvest(
        string $id_harvest,
        string $cod_estado_job,
        string $latitud,
        string $longitud,
        string $cod_crew,
        string $user_insert,
        int $cantidad_pendiente,
        string $hora_escaneo,
    ) {
        $resultadoActualizarCantidad  = -1;
        $estadoJob = DB::table('pay_lista_empleados_jobs')
            ->select('cod_estado_job')
            ->where('cod_crew', $cod_crew)
            ->orderBy('cod_lista', 'desc')
            ->limit(1)
            ->value('cod_estado_job');
        // if ($estadoJob == 2) {

        $datosRegistroHora = DB::table('pay_lista_empleados_jobs')
            ->select(
                'cod_lista',
                'cod_crew',
                'pieces',
                'terminado',
                'GPS',
                'date_insert'
            )
            ->where('cod_crew', $cod_crew)
            // ->where('cod_estado_job', "2")
            ->orderBy('cod_lista', 'desc')
            ->limit(1)
            ->get();
        $currentDate = date('Y-m-d H:i:s');
        $diff = strtotime($currentDate) - strtotime($datosRegistroHora[0]->date_insert);
        $diff = round($diff);
        if ($datosRegistroHora[0]->pieces == 0) {
            $diff = 30;
        }
        if ($cantidad_pendiente > 1) {
            // Le aumentamos la diferencia para que se pueda asignar la cantidad pendiente
            $diff = 100;
        }
        // Convert to seconds
        if ($diff >= 30) {
            // There is 30 seconds or more difference
            $resultadoActualizarCantidad = false;
            for ($i = 0; $i < $cantidad_pendiente; $i++) {
                $resultadoActualizarCantidad = DB::table('pay_lista_empleados_jobs')->insert([
                    'cod_estado_job' => $cod_estado_job,
                    'cod_crew' => $cod_crew,
                    'GPS' => $latitud . ',' . $longitud,
                    'user_insert' => $user_insert,
                    'hora_escaneo' => $hora_escaneo,
                    'date_insert' => $currentDate
                ]) && DB::table('pay_bitacora_escaneos_realizados')->insert([
                    'cod_estado_job' => $cod_estado_job,
                    'cod_crew' => $cod_crew,
                    'GPS' => $latitud . ',' . $longitud,
                    'user_insert' => $user_insert,
                    'date_insert' => $currentDate
                ]);
            }

            if ($resultadoActualizarCantidad) {
                $resultadoActualizarCantidad = 0;
            } else {
                $resultadoActualizarCantidad = 1;
            }
        } else {
            $resultadoActualizarCantidad = 2;
        }
        // } else {

        //     $resultadoActualizarCantidad = 3;
        // }

        return $resultadoActualizarCantidad;
    }

    private function actualizarCantidadEscaneoEnMiscelaneos(
        string $id_miscelano,
        string $cod_estado_job,
        string $latitud,
        string $longitud,
        string $cod_crew,
        string $user_insert,
        int $cantidad_pendiente,
        string $hora_escaneo,
    ) {
        $resultadoActualizarCantidad  = -1;
        $estadoJob = DB::table('pay_lista_empleados_jobs')
            ->select('cod_estado_job')
            ->where('cod_crew', $cod_crew)
            ->orderBy('cod_lista', 'desc')
            ->limit(1)
            ->value('cod_estado_job');
        // if ($estadoJob == 2) {

        $datosRegistroHora = DB::table('pay_lista_empleados_jobs')
            ->select(
                'cod_lista',
                'cod_crew',
                'pieces',
                'terminado',
                'GPS',
                'date_insert'
            )
            ->where('cod_crew', $cod_crew)
            // ->where('cod_estado_job', "2")
            ->orderBy('cod_lista', 'desc')
            ->limit(1)
            ->get();
        $currentDate = date('Y-m-d H:i:s');
        $diff = strtotime($currentDate) - strtotime($datosRegistroHora[0]->date_insert);
        $diff = round($diff);
        if ($datosRegistroHora[0]->pieces == 0) {
            $diff = 30;
        }
        if ($cantidad_pendiente > 1) {
            // Le aumentamos la diferencia para que se pueda asignar la cantidad pendiente
            $diff = 100;
        }
        // Convert to seconds
        if ($diff >= 30) {
            // There is 30 seconds or more difference

            $resultadoActualizarCantidad = false;
            for ($i = 0; $i < $cantidad_pendiente; $i++) {
                $resultadoActualizarCantidad = DB::table('pay_lista_empleados_jobs')->insert([
                    'cod_estado_job' => $cod_estado_job,
                    'cod_crew' => $cod_crew,
                    'GPS' => $latitud . ',' . $longitud,
                    'user_insert' => $user_insert,
                    'hora_escaneo' => $hora_escaneo,
                    'date_insert' => $currentDate
                ]) &&
                    DB::table('pay_bitacora_escaneos_realizados')->insert([
                        'cod_estado_job' => $cod_estado_job,
                        'cod_crew' => $cod_crew,
                        'GPS' => $latitud . ',' . $longitud,
                        'user_insert' => $user_insert,
                        'date_insert' => $currentDate
                    ]);
            }

            if ($resultadoActualizarCantidad) {
                $resultadoActualizarCantidad = 0;
            } else {
                $resultadoActualizarCantidad = 1;
            }
        } else {
            $resultadoActualizarCantidad = 2;
        }
        // } else {

        //     $resultadoActualizarCantidad = 3;
        // }

        return $resultadoActualizarCantidad;
    }

    public function actualizarEstadoEmpleado(Request $request)
    {
        $body = $request->all();
        $fozar_terminacion = (bool) $body['fozar_terminacion'];
        $cod_usuario = $body['cod_usuario'];
        $cod_harvest = $body['cod_harvest'];
        $cod_miscellaneous = $body['cod_miscellaneous'];
        $estado_tarea_empleado = $body['estado_tarea_empleado'];
        $user_insert = $body['user_insert'];
        $hora_dispositivo_movil = $body['hora_dispositivo_movil'] ?? DB::raw('CURRENT_TIME');

        $resultadoActualizar = false;

        //Buscar el cod_crew del empleado en la tarea
        if ($cod_harvest != "0") {

            $CrewsEnTarea = DB::table('pay_crews')
                ->select('cod_crew', 'cantidad_escaneos', 'cod_empleado')
                ->where('cod_harvest', $cod_harvest)
                ->where('cod_empleado', $cod_usuario)
                ->get();
        } else if ($cod_miscellaneous != "0") {
            $CrewsEnTarea = DB::table('pay_crews')
                ->select('cod_crew', 'cantidad_escaneos', 'cod_empleado')
                ->where('cod_miscellaneous', $cod_miscellaneous)
                ->where('cod_empleado', $cod_usuario)
                ->get();
        }

        //Registramo la hora de inicio o final, para cada empleado por separado
        if ($estado_tarea_empleado == "iniciado") {
            DB::table('pay_crews')
                ->where('cod_crew', $CrewsEnTarea[0]->cod_crew)
                ->where('hora_inicio', null)
                ->update(['cod_estado_job' => 2, 'hora_inicio' => $hora_dispositivo_movil]);
        } else  if ($estado_tarea_empleado == "terminado") {
            DB::table('pay_crews')
                ->where('cod_crew', $CrewsEnTarea[0]->cod_crew)
                ->where('hora_final', null)
                ->update(['cod_estado_job' => 3, 'hora_final' => $hora_dispositivo_movil]);
        }

        foreach ($CrewsEnTarea as $crew) {
            // Add your code here
            if ($estado_tarea_empleado == "iniciado") {
                $resultadoActualizar = DB::table('pay_lista_empleados_jobs')
                    ->where('cod_crew', $crew->cod_crew)
                    ->update(['cod_estado_job' => 2]);
            }

            if ($estado_tarea_empleado == "terminado") {
                $resultadoActualizar  = DB::table('pay_lista_empleados_jobs')
                    ->where('cod_crew', $crew->cod_crew)
                    ->update(['cod_estado_job' => 3]);

                $resultadoActualizar = DB::table('pay_lista_empleados_jobs')
                    ->where('cod_crew', $crew->cod_crew)
                    ->where('terminado', '0')
                    ->update(['terminado' => '1', 'hora_fuerza_terminado' =>  $hora_dispositivo_movil]);


                DB::table('usu_usuarios')
                    ->where('cod_usuario', $cod_usuario)
                    ->update(['disponible' => '1']);
            }

            // if ($fozar_terminacion == true) {

            // }
        }
        if ($resultadoActualizar == 1) {
            return response()->json(['message' => 'Successful state change', "code" => 0, 'extra' => $fozar_terminacion], 200);
        } else if ($resultadoActualizar == 0) {
            return response()->json(['message' => 'Failed change', "code" => 1555, 'extra' => $fozar_terminacion], 400);
        }
    }

    public function CulminarTareasCaducadas(Request $request)
    {
        $harvestTerminadas = $this->terminarTareasHarvest();
        return response()->json(['message' => 'Successful state change', "code" => 0, 'extra' => ['harvestTerminadas' => $harvestTerminadas]], 200);
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
                'pay_jobs_progresos.cod_job_local',
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

                    DB::table('pay_crews')
                        ->where('cod_crew', $crew->cod_crew)
                        ->where('hora_final', null)
                        ->update(['hora_final' => DB::raw('CURRENT_TIME')]);

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

    public function normalizarCantidadCajasEscaneadas(Request $request)
    {
        $body = $request->all();

        try {


            $cantidadNormalizada = $body['cantidad_normalizada'] ?? 1;
            $listaCodigosEmpleados = $body['listado_codigos_empleados'] ?? "";
            // response()->json(['message' => 'Scan successfully registered', "id_harvest" => $id_harvest, "cod_miscellaneous" => $id_miscelano, 'cod_empleado' => $cod_empleado, "code" => 0, 'extra' => $cantidad_escaneada], 200);
            // $codigosEmpleados = explode(',', $listaCodigosEmpleados);
            $codHarvest = $body['cod_harvest'] ?? 0;
            $codMiscelaneo = $body['cod_miscelaneo'] ?? 0;
            if ($codHarvest != 0) {
                $codigosEmpleados = DB::table('pay_crews')
                    ->select('cod_crew')
                    ->where('cod_harvest', $codHarvest)
                    ->get();
            } else if ($codMiscelaneo != 0) {
                $codigosEmpleados = DB::table('pay_crews')
                    ->select('cod_crew')
                    ->where('cod_miscellaneous', $codMiscelaneo)
                    ->get();
            } else {
                return response()->json(['message' => 'Task code not specified', "code" => 1454, 'extra' => 'Task code not specified'], 400);
            }
            // return $codigosEmpleados;
            // die();
            $codigosEmpleadosArray = [];
            foreach ($codigosEmpleados as $empleado) {
                $codigosEmpleadosArray[] = $empleado->cod_crew;
            }
            $datosEscaneosActuales = DB::table('pay_lista_empleados_jobs')
                ->select('cod_crew', DB::raw('SUM(pieces) AS cantidad_escaneada'))
                ->whereIn('cod_crew', $codigosEmpleadosArray)
                ->groupBy('cod_crew')
                ->get();

            foreach ($datosEscaneosActuales as $escaneo) {
                if ($escaneo->cantidad_escaneada < $cantidadNormalizada) {
                    $diferencia = $cantidadNormalizada - $escaneo->cantidad_escaneada;
                    $registroActual = DB::table('pay_lista_empleados_jobs')
                        ->select('cod_estado_job', 'terminado', 'hora_fuerza_terminado', 'GPS', 'registro_manual', 'user_insert')
                        ->where('cod_crew', $escaneo->cod_crew)
                        ->orderBy('cod_lista', 'desc')
                        ->limit(1)
                        ->first();


                    // Crea los registros faltantes para normalizar la cantidad de cajas escaneadas
                    for ($i = 0; $i < floor($diferencia); $i++) {
                        DB::table('pay_lista_empleados_jobs')->insert([
                            'cod_estado_job' => $registroActual->cod_estado_job,
                            'cod_crew' => $escaneo->cod_crew,
                            'GPS' => $registroActual->GPS,
                            'user_insert' => $registroActual->user_insert,
                            'pieces' => 1
                        ]);
                    }

                    // Crea un registro con la parte decimal de la cantidad de cajas escaneadas
                    $decimalPart = $diferencia - floor($diferencia);
                    if ($decimalPart > 0) {
                        DB::table('pay_lista_empleados_jobs')->insert([
                            'cod_estado_job' => $registroActual->cod_estado_job,
                            'cod_crew' => $escaneo->cod_crew,
                            'GPS' => $registroActual->GPS,
                            'user_insert' => $registroActual->user_insert,
                            'pieces' => $decimalPart
                        ]);
                    }
                } else if ($escaneo->cantidad_escaneada > $cantidadNormalizada) {

                    $diferencia = $escaneo->cantidad_escaneada - $cantidadNormalizada;
                    $registrosActuales = DB::table('pay_lista_empleados_jobs')
                        ->where('cod_crew', $escaneo->cod_crew)
                        ->orderBy('cod_lista', 'desc')
                        ->get();

                    foreach ($registrosActuales as $registroActual) {
                        if ($diferencia <= 0) {
                            break;
                        }

                        // Si la cantidad de cajas escaneadas en el registro actual es menor o igual a la diferencia se elimina las cajas extras
                        if ($registroActual->pieces <= $diferencia) {
                            $diferencia -= $registroActual->pieces;
                            DB::table('pay_lista_empleados_jobs')
                                ->where('cod_lista', $registroActual->cod_lista)
                                ->delete();
                        } else {
                            // Se actualiza el ultimo registro con la cantidad de cajas escaneadas restantes con los deciamles faltantes
                            DB::table('pay_lista_empleados_jobs')
                                ->where('cod_lista', $registroActual->cod_lista)
                                ->update(['pieces' => $registroActual->pieces - $diferencia]);
                            $diferencia = 0;
                        }
                    }
                }
            }
            HelpController::desconectarBaseDatos();

            return response()->json(['message' => 'Quantities successfully normalized', "code" => 0, 'extra' => $datosEscaneosActuales], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error', "code" => 1454, 'extra' => $th->getMessage()], 400);
        }
    }

    public function actualizarCantidadEspecificaDeCajas(Request $request)
    {
        $body = $request->all();

        try {


            $codigoEmpleado = $body['codigo_empleado'] ?? 1;
            $cantidadActual = $body['cantidad_actual'] ?? 1;
            $cantidadNueva = $body['cantidad_nueva'] ?? $cantidadActual;
            $codHarvest = $body['cod_harvest'] ?? 0;
            $codMiscelaneo = $body['cod_miscelaneo'] ?? 0;
            if ($codHarvest != 0) {
                $codigoEmpleado = DB::table('pay_crews')
                    ->select('cod_crew')
                    ->where('cod_empleado', $codigoEmpleado)
                    ->where('cod_harvest', $codHarvest)
                    ->first();
            } else if ($codMiscelaneo != 0) {
                $codigoEmpleado = DB::table('pay_crews')
                    ->select('cod_crew')
                    ->where('cod_empleado', $codigoEmpleado)
                    ->where('cod_miscellaneous', $codMiscelaneo)
                    ->first();
            } else {
                return response()->json(['message' => 'Task code not specified', "code" => 1454, 'extra' => 'Task code not specified'], 400);
            }
            if (!$codigoEmpleado) {
                return response()->json(['message' => 'Employee code not found', "code" => 1454, 'extra' => 'Employee code not found'], 400);
            }
            $codigoEmpleado = $codigoEmpleado->cod_crew;
            // return $codigoEmpleado;
            // die();
            $registroActual = DB::table('pay_lista_empleados_jobs')
                ->select('cod_crew', 'cod_estado_job', 'terminado', 'hora_fuerza_terminado', 'GPS', 'registro_manual', 'user_insert')
                ->where('cod_crew', $codigoEmpleado)
                ->orderBy('cod_lista', 'desc')
                ->limit(1)
                ->first();
            $datosEscaneosActuales = DB::table('pay_lista_empleados_jobs')
                ->select('cod_crew', DB::raw('SUM(pieces) AS cantidad_escaneada'))
                ->where('cod_crew', $codigoEmpleado)
                ->groupBy('cod_crew')
                ->first();

            $cantidadActual = $datosEscaneosActuales->cantidad_escaneada;

            if ($cantidadActual < $cantidadNueva) {
                $diferencia = $cantidadNueva - $cantidadActual; //5 - 3 = 2



                // Crea los registros faltantes para normalizar la cantidad de cajas escaneadas
                for ($i = 0; $i < floor($diferencia); $i++) {
                    DB::table('pay_lista_empleados_jobs')->insert([
                        'cod_estado_job' => $registroActual->cod_estado_job,
                        'cod_crew' => $registroActual->cod_crew,
                        'GPS' => $registroActual->GPS,
                        'user_insert' => $registroActual->user_insert,
                        'pieces' => 1
                    ]);
                }

                // Crea un registro con la parte decimal de la cantidad de cajas escaneadas
                $decimalPart = $diferencia - floor($diferencia);
                if ($decimalPart > 0) {
                    $registroConDecimal = DB::table('pay_lista_empleados_jobs')
                        ->where('cod_crew', $registroActual->cod_crew)
                        ->where('pieces', '>', 0)
                        ->where('pieces', '<', 1)
                        ->orderBy('cod_lista', 'desc')
                        ->first();

                    if ($registroConDecimal) {
                        DB::table('pay_lista_empleados_jobs')
                            ->where('cod_lista', $registroConDecimal->cod_lista)
                            ->update(['pieces' => 1]);
                    } else {
                        // DB::table('pay_lista_empleados_jobs')->insert([
                        //     'cod_estado_job' => $registroActual->cod_estado_job,
                        //     'cod_crew' => $registroActual->cod_crew,
                        //     'GPS' => $registroActual->GPS,
                        //     'user_insert' => $registroActual->user_insert,
                        //     'pieces' => 1
                        // ]);
                    }
                }
            } else if ($cantidadActual > $cantidadNueva) {

                $diferencia = $cantidadActual - $cantidadNueva;
                $registrosActuales = DB::table('pay_lista_empleados_jobs')
                    ->where('cod_crew', $registroActual->cod_crew)
                    ->orderBy('cod_lista', 'desc')
                    ->get();

                foreach ($registrosActuales as $registroActual) {
                    if ($diferencia <= 0) {
                        break;
                    }

                    // Si la cantidad de cajas escaneadas en el registro actual es menor o igual a la diferencia se elimina las cajas extras
                    if ($registroActual->pieces <= $diferencia) {
                        $diferencia -= $registroActual->pieces;
                        DB::table('pay_lista_empleados_jobs')
                            ->where('cod_lista', $registroActual->cod_lista)
                            ->delete();
                    } else {
                        // Se actualiza el ultimo registro con la cantidad de cajas escaneadas restantes con los deciamles faltantes
                        DB::table('pay_lista_empleados_jobs')
                            ->where('cod_lista', $registroActual->cod_lista)
                            ->update(['pieces' => $registroActual->pieces - $diferencia]);
                        $diferencia = 0;
                    }
                }
            }
            HelpController::desconectarBaseDatos();

            return response()->json(['message' => 'Quantities successfully normalized', "code" => 0, 'extra' => $registroActual], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage(), "code" => 1454, 'extra' => $th->getMessage()], 400);
        }
    }

    public function eliminarTarea(Request $request)
    {
        $body = $request->all();

        try {


            $id_harvest = $body['id_harvest'] ?? 0;
            $id_miscelaneo = $body['id_miscelaneo'] ?? 0;
            $identificardor_unico_local = $body['identificardor_unico_local'] ?? 0;
            $id_usuario = $body['id_usuario'] ?? 0;
            if ($id_harvest != '0') {

                $harvestBlocks = DB::table('pay_harvests_blocks')
                    ->where('cod_harvest', $id_harvest)
                    ->get();
                $harvestFields = DB::table('pay_harvests_fields')
                    ->where('cod_harvest', $id_harvest)
                    ->get();
                $harvest = DB::table('pay_harvests')
                    ->where('cod_harvest', $id_harvest)
                    ->get();
                $crews = DB::table('pay_crews')
                    ->select('cod_crew')
                    ->where('cod_harvest', $id_harvest)
                    ->get();
                $employeeJobs = DB::table('pay_lista_empleados_jobs')
                    ->whereIn('cod_crew', $crews->pluck('cod_crew'))
                    ->get();
                $crewData = DB::table('pay_crews')
                    ->where('cod_harvest', $id_harvest)
                    ->get();
                $jobProgress = DB::table('pay_jobs_progresos')
                    ->where('cod_harvest', $id_harvest)
                    ->get();


                DB::table('pay_harvests_blocks')
                    ->where('cod_harvest', $id_harvest)
                    ->delete();
                DB::table('pay_harvests_fields')
                    ->where('cod_harvest', $id_harvest)
                    ->delete();
                $resultadoEliminar = DB::table('pay_harvests')
                    ->where('cod_harvest', $id_harvest)
                    ->delete();
                DB::table('pay_lista_empleados_jobs')
                    ->whereIn('cod_crew', $crews->pluck('cod_crew'))
                    ->delete();
                DB::table('pay_crews')
                    ->where('cod_harvest', $id_harvest)
                    ->delete();
                DB::table('pay_jobs_progresos')
                    ->where('cod_harvest', $id_harvest)
                    ->delete();

                $codigosUsuarios = $crewData->pluck('cod_empleado')->toArray();
                DB::table('usu_usuarios')
                    ->whereIn('cod_usuario', $codigosUsuarios)
                    ->update(['disponible' => '1']);

                if ($resultadoEliminar == 1) {
                    $logData = json_encode([
                        'date' => date('d-m-Y H:i:s'),
                        'action' => 'delete_harvest',
                        'user_id' => $id_usuario,
                        'details' => [
                            'harvestBlocks' => $harvestBlocks,
                            'harvestFields' => $harvestFields,
                            'harvest' => $harvest,
                            'employeeJobs' => $employeeJobs,
                            'crewData' => $crewData,
                            'jobProgress' => $jobProgress,
                            'resultadoEliminar' => $resultadoEliminar
                        ]
                    ], JSON_PRETTY_PRINT);

                    $currentDate = date('d-m-Y');
                    file_put_contents(storage_path("logs/harvest_task_deletion_{$currentDate}.log"), $logData . PHP_EOL, FILE_APPEND);
                    return response()->json([
                        'message' => 'Task deletion',
                        'code' => 0,
                        'extra' => [
                            'harvestBlocks' => $harvestBlocks,
                            'harvestFields' => $harvestFields,
                            'harvest' => $harvest,
                            'employeeJobs' => $employeeJobs,
                            'crewData' => $crewData,
                            'jobProgress' => $jobProgress,
                            'resultadoEliminar' => $resultadoEliminar
                        ]
                    ], 200);
                } else {
                    return response()->json(['message' => 'Failed to delete harvest task', "code" => 1454, 'extra' => $resultadoEliminar], 400);
                }
            } else if ($id_miscelaneo != '0') {

                $miscelaneosBlocks = DB::table('pay_miscelaneos_blocks')
                    ->where('cod_miscelaneos', $id_miscelaneo)
                    ->get();
                $miscelaneosFields = DB::table('pay_miscelaneos_fields')
                    ->where('cod_miscelaneos', $id_miscelaneo)
                    ->get();
                $miscelaneos = DB::table('pay_miscellaneous')
                    ->where('cod_miscellaneous', $id_miscelaneo)
                    ->get();
                $crews = DB::table('pay_crews')
                    ->select('cod_crew')
                    ->where('cod_miscellaneous', $id_miscelaneo)
                    ->get();
                $employeeJobs = DB::table('pay_lista_empleados_jobs')
                    ->whereIn('cod_crew', $crews->pluck('cod_crew'))
                    ->get();
                $crewData = DB::table('pay_crews')
                    ->where('cod_miscellaneous', $id_miscelaneo)
                    ->get();
                $jobProgress = DB::table('pay_jobs_progresos')
                    ->where('cod_miscellaneous', $id_miscelaneo)
                    ->get();


                DB::table('pay_miscelaneos_blocks')
                    ->where('cod_miscelaneos', $id_miscelaneo)
                    ->delete();
                DB::table('pay_miscelaneos_fields')
                    ->where('cod_miscelaneos', $id_miscelaneo)
                    ->delete();
                $resultadoEliminar = DB::table('pay_miscellaneous')
                    ->where('cod_miscellaneous', $id_miscelaneo)
                    ->delete();
                DB::table('pay_lista_empleados_jobs')
                    ->whereIn('cod_crew', $crews->pluck('cod_crew'))
                    ->delete();
                DB::table('pay_crews')
                    ->where('cod_miscellaneous', $id_miscelaneo)
                    ->delete();
                DB::table('pay_jobs_progresos')
                    ->where('cod_miscellaneous', $id_miscelaneo)
                    ->delete();

                $codigosUsuarios = $crewData->pluck('cod_empleado')->toArray();
                DB::table('usu_usuarios')
                    ->whereIn('cod_usuario', $codigosUsuarios)
                    ->update(['disponible' => '1']);
                if ($resultadoEliminar == 1) {
                    $logData = json_encode([
                        'date' => date('d-m-Y H:i:s'),
                        'action' => 'delete_miscellaneous',
                        'user_id' => $id_usuario,
                        'details' => [
                            'miscelaneosBlocks' => $miscelaneosBlocks,
                            'miscelaneosFields' => $miscelaneosFields,
                            'miscelaneos' => $miscelaneos,
                            'employeeJobs' => $employeeJobs,
                            'crewData' => $crewData,
                            'jobProgress' => $jobProgress,
                            'resultadoEliminar' => $resultadoEliminar
                        ]
                    ], JSON_PRETTY_PRINT);
                    $currentDate = date('d-m-Y');
                    file_put_contents(storage_path("logs/harvest_task_deletion_{$currentDate}.log"), $logData . PHP_EOL, FILE_APPEND);
                    return response()->json([
                        'message' => 'Task deletion',
                        'code' => 0,
                        'extra' => $logData,
                    ], 200);
                } else {
                    return response()->json(['message' => 'Failed to delete miscellaneous task', "code" => 1454, 'extra' => $resultadoEliminar], 400);
                }
            } else {
                return response()->json(['message' => 'Task code not specified', "code" => 1454, 'extra' => 'Task code not specified'], 400);
            }
            HelpController::desconectarBaseDatos();

            return response()->json(['message' => 'Eliminación de la tarea', "code" => 0, 'extra' => ""], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage(), "code" => 1454, 'extra' => $th->getMessage()], 400);
        }
    }
}
