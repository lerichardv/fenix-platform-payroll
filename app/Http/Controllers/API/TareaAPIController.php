<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Crew;
use App\Models\Harvest;
use App\Models\JobProgreso;
use App\Models\User;
use App\Models\UsuarioGranjas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TareaAPIController extends Controller
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
        DB::statement("SET sql_mode = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION';");

        $tareas = DB::table('pay_harvests')
            ->select(
                'pay_harvests.cod_harvest',
                'pay_harvests.user_admin',
                'pay_harvests.cod_farm',
                'pay_harvests.crop_age',
                'pay_harvests.cod_tipo_pack',
                'pay_harvests.cod_tipo_pago',
                DB::raw("DATE_FORMAT(pay_harvests.date_insert, '%Y-%m-%d %H:%i') as date_insert"),
                'far_crop_semillas_bloques.cod_semilla_bloque',
                'far_crop_semillas_bloques.cod_semilla',
                'bw_inventario_semilla.nombre_semilla',
                'bw_inventario_semilla.abreviatura_semilla',
                'pay_tipo_packs.tipo_pack',
                'pay_tipo_packs.cantidad AS cantidad_pack',
                'pay_tipo_pagos.tipo_pago',
                'pay_tipo_pagos.abreviatura AS abreviatura_pago',
                DB::raw("GROUP_CONCAT(DISTINCT pay_crews.cod_crew) as cod_crew"),
                'pay_crews.cod_miscellaneous',
                DB::raw("GROUP_CONCAT(DISTINCT pay_crews.cod_empleado) as cods_empleados"),
                DB::raw("GROUP_CONCAT(DISTINCT pay_crews.pin) as pins"),
                DB::raw("GROUP_CONCAT(DISTINCT pay_crews.qc_pin) as qc_pins"),
                'pay_crews.cod_supervisor',
                'usu_usuarios.usuario',
                'usu_usuarios.nombre_1 AS primer_nombre',
                'usu_usuarios.nombre_2 AS segundo_nombre',
                'usu_usuarios.apellido_1 AS primer_apellido',
                'usu_usuarios.apellido_2 AS segundo_apellido',
                'usu_usuarios.email',
                'usu_usuarios.pin AS pin_actual',
                'usu_usuarios.qcpin AS qcpin_actual',
                'usu_usuarios.activo',
                'far_farms.farm',
                'far_farms.cod_estado',
                'bw_inventario_estados_plantaciones.nombre AS nombre_estado',
                'bw_inventario_estados_plantaciones.abreviatura AS abreviatura_estado',
                'pay_harvests_blocks.cod_harvests_blocks',
                DB::raw("GROUP_CONCAT(DISTINCT pay_harvests_blocks.cod_block) AS cod_blocks"),
                'pay_harvests_fields.cod_harvest_fields',
                DB::raw("GROUP_CONCAT(DISTINCT pay_harvests_fields.cod_field) AS cod_fields"),
                'pay_jobs_progresos.cod_job',
                'pay_jobs_progresos.cod_estado_job',
                'pay_jobs_progresos.fecha_job',
                'pay_jobs_progresos.hora_inicio',
                'pay_jobs_progresos.hora_final',
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
            ->join('usu_usuarios', 'usu_usuarios.cod_usuario', '=', 'pay_crews.cod_empleado')
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
        foreach ($tareas as $tarea) {
            // Code to process each task
            $tarea->cods_empleados = explode(',', $tarea->cods_empleados);
            $tarea->pins = explode(',', $tarea->pins);
            $tarea->qc_pins = explode(',', $tarea->qc_pins);
            $tarea->cod_crew = explode(',', $tarea->cod_crew);
            $tarea->cod_blocks = explode(',', $tarea->cod_blocks);
            $tarea->cod_fields = explode(',', $tarea->cod_fields);
            $empleados = DB::table('usu_usuarios')
                ->select(
                    'usu_usuarios.cod_usuario',
                    'usu_usuarios.usuario',
                    'usu_usuarios.nombre_1',
                    'usu_usuarios.nombre_2',
                    'usu_usuarios.apellido_1',
                    'usu_usuarios.apellido_2',
                    'usu_usuarios.identidad',
                    'usu_usuarios.email',
                    'usu_usuarios.pin',
                    'usu_usuarios.qcpin',
                    'usu_usuarios.cod_gerencia',
                    'usu_usuarios.cod_cargo',
                    'usu_usuarios.cod_perfil',
                    'usu_usuarios.cod_jefe_inmediato',
                    'usu_usuarios.cod_pais',
                    'usu_usuarios.cod_departamento',
                    'usu_usuarios.cod_municipio',
                    'usu_usuarios.cod_info_empresa',
                    'usu_usuarios.cod_tipo_usuario',
                    'pay_crews.cod_estado_job',
                    'pay_estados_jobs.estado_job',
                    DB::raw('COALESCE(pay_crews.cantidad_escaneos, 0) AS cantidad_escaneos')
                )
                ->leftJoin('pay_crews', 'usu_usuarios.cod_usuario', '=', 'pay_crews.cod_empleado')
                ->leftJoin('pay_estados_jobs', 'pay_estados_jobs.cod_estado_job', '=', 'pay_crews.cod_estado_job')
                ->where('pay_crews.cod_harvest', $tarea->cod_harvest)
                // ->orWhere('pay_crews.cod_miscellaneous', $tarea->cod_miscellaneous)
                ->get();
            foreach ($empleados as &$empleado) {
                $empleado->cod_empleado = $empleado->cod_usuario;
                $empleado->cod_harvest = $tarea->cod_harvest;
                $crew = DB::table('pay_crews')
                    ->select('pay_crews.cod_crew', 'pay_crews.pin', 'pay_crews.qc_pin', DB::raw("COALESCE(pay_estados_jobs.estado_job, 'lista') AS estado_empleado"))
                    ->join('pay_lista_empleados_jobs', 'pay_lista_empleados_jobs.cod_crew', '=', 'pay_crews.cod_crew')
                    ->leftJoin('pay_estados_jobs', 'pay_estados_jobs.cod_estado_job', '=', 'pay_lista_empleados_jobs.cod_estado_job')
                    ->where('pay_crews.cod_harvest', $tarea->cod_harvest)
                    ->where('pay_crews.cod_empleado', $empleado->cod_empleado)
                    ->get();
                $empleado->cod_crew = $crew[0]->cod_crew;
                $empleado->pin = $crew[0]->pin;
                $empleado->qc_pin = $crew[0]->qc_pin;
                $empleado->estado_empleado = $crew[0]->estado_empleado;
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
        }


        // return $empleados;

        return $tareas;
    }
    public function cambiarEstadoTarea(Request $request)
    {
        $body = $request->all();
        $id_harvest = $body['id_harvest'];
        $id_miscelano = $body['id_miscelano'];
        $estado = $body['estado'];
        $latitud = $body['latitud'];
        $longitud = $body['longitud'];
        $resultadoActualizar = false;

        if ($id_harvest != "0") {
            $resultadoActualizar = $this->cambiarEstadoHarvest(
                $id_harvest,
                $estado,
                $latitud,
                $longitud,
            );
        } else if ($id_miscelano != "0") {
        }
        if ($resultadoActualizar) {
            return response()->json(['message' => 'Successful state change', "code" => 0, 'extra' => $estado], 200);
        } else {
            return response()->json(['message' => 'Failed change', "code" => 1554, 'extra' => $estado], 400);
        }
    }
    private function cambiarEstadoHarvest(
        string $id_harvest,
        string $estado,
        string $latitud,
        string $longitud,
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
        }


        $updateResult = $this->jobEnProgresoRepository->where('cod_harvest', $id_harvest)->update(['cod_estado_job' => $codEstadoJob]);
        if ($estadoJob == "culminada") {
            // $updateResultCrew = $this->crewRepository->where('cod_harvest', $id_harvest)->update(['cod_estado_job' => $codEstadoJob,'terminado' => 1, 'terminado' => DB::raw('CURRENT_TIME')]);
            $updateResultCrew = $this->crewRepository->where('cod_harvest', $id_harvest)->update(['cod_estado_job' => $codEstadoJob]);
            $cod_crews = DB::table('pay_crews')
                ->select('cod_crew', 'cod_empleado')
                ->where('cod_harvest', $id_harvest)
                ->get();

            foreach ($cod_crews as $cod_crew) {

                DB::table('pay_lista_empleados_jobs')
                    ->where('cod_crew', $cod_crew->cod_crew)
                    ->where('terminado', '0')
                    ->update(['terminado' => '1', 'hora_fuerza_terminado' => DB::raw('CURRENT_TIME')]);

                DB::table('usu_usuarios')
                    ->where('cod_usuario', $cod_crew->cod_empleado)
                    ->update(['disponible' => '1']);
            }
        } else {
            $updateResultCrew = $this->crewRepository->where('cod_harvest', $id_harvest)->update(['cod_estado_job' => $codEstadoJob]);
        }
        if ($updateResult) {
            // Update was successful
            return true;
        } else {
            // Update failed
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
        $cod_crew = $body['cod_crew'];
        $user_insert = $body['user_insert'];
        $resultadoActualizar = false;

        if ($id_harvest != "0") {
            $resultadoActualizar = $this->actualizarCantidadEscaneoEnHarvest(
                $id_harvest,
                $cantidad,
                $latitud,
                $longitud,
                $cod_crew,
                $user_insert,
            );
        } else if ($id_miscelano != "0") {
        }
        if ($resultadoActualizar == 0) {
            return response()->json(['message' => 'Successful state change', "code" => 0, 'extra' => $cantidad], 200);
        } else if ($resultadoActualizar == 1) {
            return response()->json(['message' => 'Failed change', "code" => 1555, 'extra' => $cantidad], 400);
        } else  if ($resultadoActualizar == 2) {
            return response()->json(['message' => 'You must wait 5 minutes for your next scan', "code" => 1556, 'extra' => $cantidad], 400);
        }
    }


    private function actualizarCantidadEscaneoEnHarvest(
        string $id_harvest,
        string $cantidad,
        string $latitud,
        string $longitud,
        string $cod_crew,
        string $user_insert,
    ) {

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
            ->orderBy('cod_lista', 'desc')
            ->limit(1)
            ->get();
        $currentDate = date('Y-m-d H:i:s');
        $diff = strtotime($currentDate) - strtotime($datosRegistroHora[0]->date_insert);
        $diff = round($diff / 60); // Convert to minutes
        if ($diff >= 5) {
            // There is 5 minutes or more difference

            // Add your code here
            $resultadoActualizarCantidad = DB::table('pay_lista_empleados_jobs')->insert([
                'cod_crew' => $cod_crew,
                'GPS' => $latitud . ',' . $longitud,
                'user_insert' => $user_insert
            ]);
            if ($resultadoActualizarCantidad) {
                $resultadoActualizarCantidad = 0;
            } else {
                $resultadoActualizarCantidad = 1;
            }
        } else {
            $resultadoActualizarCantidad = 2;
        }

        return $resultadoActualizarCantidad;
    }


    public function actualizarEstadoEmpleado(Request $request)
    {
        $body = $request->all();
        $fozar_terminacion = $body['fozar_terminacion'];
        $cod_usuario = $body['cod_usuario'];
        $cod_harvest = $body['cod_harvest'];
        $cod_crew = $body['cod_crew'];
        $estado_tarea_empleado = $body['estado_tarea_empleado'];
        $user_insert = $body['user_insert'];

        $resultadoActualizar = false;
        $CrewsEnTarea = DB::table('pay_crews')
            ->select('cod_crew', 'cantidad_escaneos', 'cod_empleado')
            ->where('cod_harvest', $cod_harvest)
            ->where('cod_empleado', $cod_usuario)
            ->get();
        foreach ($CrewsEnTarea as $crew) {
            // Add your code here
            if ($estado_tarea_empleado == "iniciado") {
                $resultadoActualizar = DB::table('pay_lista_empleados_jobs')
                    ->where('cod_crew', $crew->cod_crew)
                    ->update(['cod_estado_job' => 2]);
            }

            if ($estado_tarea_empleado == "completada" || $estado_tarea_empleado == "terminada" || $estado_tarea_empleado == "suspendido") {
                $resultadoActualizar  = DB::table('pay_lista_empleados_jobs')
                    ->where('cod_crew', $crew->cod_crew)
                    ->update(['cod_estado_job' => 3]);
            }

            if ($fozar_terminacion) {
                $resultadoActualizar = DB::table('pay_lista_empleados_jobs')
                    ->where('cod_crew', $crew->cod_crew)
                    ->where('terminado', '0')
                    ->update(['terminado' => '1', 'hora_fuerza_terminado' => DB::raw('CURRENT_TIME')]);


                DB::table('usu_usuarios')
                    ->where('cod_usuario', $cod_usuario)
                    ->update(['disponible' => '1']);
            }
        }
        if ($resultadoActualizar == 0) {
            return response()->json(['message' => 'Successful state change', "code" => 0, 'extra' => $cod_crew], 200);
        } else if ($resultadoActualizar == 1) {
            return response()->json(['message' => 'Failed change', "code" => 1555, 'extra' => $cod_crew], 400);
        } else  if ($resultadoActualizar == 2) {
            return response()->json(['message' => 'You must wait 5 minutes for your next scan', "code" => 1556, 'extra' => $cod_crew], 400);
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
}
