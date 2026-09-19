<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\HelpController;
use App\Models\Empleado;
use App\Models\Miscelaneo;
use App\Models\TokenSesion;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ActividadPorDia;
use App\Models\RegistroIngreso;
use App\Models\Paquete;
use App\Models\Usuario;
use App\Models\Harvest;
use App\Models\Crew;
use App\Models\JobProgreso;
use App\Models\ListaEmpleadoJob;
use App\Models\HarvestBlock;
use App\Models\HarvestField;
use App\Models\MiscelaneoField;
use App\Models\MiscelaneoBlock;
use App\Models\PaqueteFarmLocation;
use App\Services\BitacoraInicioSesionService;
use App\Services\TareaHarvestService;
use App\Services\TareaMiscelaneaService;

class AdminRegistroAppController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $listaGranjas = DB::table('far_farms')
            ->select('cod_farms', 'farm')
            ->where('activo', 1)
            ->get();

        // $listaEmpleados = DB::table('usu_usuario_farm')
        //     ->join('usu_usuarios', 'usu_usuarios.cod_usuario', '=', 'usu_usuario_farm.cod_usuario')
        //     ->select('usu_usuario_farm.cod_usuario_farm', 'usu_usuario_farm.cod_granja', 'usu_usuario_farm.cod_usuario', 'usu_usuarios.nombre_1', 'usu_usuarios.apellido_1', DB::raw("CONCAT(usu_usuarios.nombre_1, ' ', usu_usuarios.apellido_1) AS nombre"))
        //     ->where('cod_granja', 0)
        //     ->get();
        $listaEmpleados = DB::table('usu_usuarios')
            ->select(
                'cod_usuario',
                'usuario',
                'pin',
                'qcpin',
                'es_veterano',
                'nombre_1',
                'apellido_1',
                DB::raw(
                    "CONCAT(nombre_1, ' ', apellido_1) AS nombre"
                )
            )
            ->whereNotIn('cod_tipo_usuario', [1, 2, 3])
            ->where('activo', 1)
            ->orderBy('nombre_1')
            ->get();

        foreach ($listaEmpleados as $empleado) {
            $empleado->al_menos_un_error = $this->comprobarSiHayAlgunError($empleado->cod_usuario);
        }

        $datosRegistro = [
            'name' => 'John Doe',
            'age' => 30,
        ];

        return view('admin.registro_tarea')
            ->with('datosRegistro', $datosRegistro)
            ->with('listaEmpleados', $listaEmpleados->toArray())
            ->with('listaGranjas', $listaGranjas->toArray())
            ->with('actividades', ActividadPorDia::where('activo', 1)->get());
    }

    public function listaEmpleadoPorGranja(Request $request)
    {

        $codGranja = $request->input('cod_granja');
        Log::emergency('Request body: ' . json_encode($request));
        $listaEmpleados = DB::table('usu_usuario_farm')
            ->join('usu_usuarios', 'usu_usuarios.cod_usuario', '=', 'usu_usuario_farm.cod_usuario')
            ->select(
                'usu_usuario_farm.cod_usuario_farm',
                'usu_usuario_farm.cod_granja',
                'usu_usuario_farm.cod_usuario',
                'usu_usuarios.nombre_1',
                'usu_usuarios.apellido_1',
                'usu_usuarios.pin',
                'usu_usuarios.qcpin',
                'usu_usuarios.es_veterano',
                DB::raw("CONCAT(usu_usuarios.nombre_1, ' ', usu_usuarios.apellido_1) AS nombre")
            )
            ->where('cod_granja', $codGranja)
            ->whereNotIn('usu_usuarios.cod_tipo_usuario', [1, 2, 3])
            ->orderBy('usu_usuarios.nombre_1', 'asc')
            ->get();

        return response()->json(['success' => true, 'message' => 'Cambio detectado', 'data' => $listaEmpleados]);
    }

    public function registrosIngresosYEgresos(Request $request, BitacoraInicioSesionService $bitacoraService)
    {
        $initial_date = $request->input('initial_date');
        $final_date = $request->input('final_date');
        $cod_usuario = (int) $request->input('cod_usuario');

        $initial_date = Carbon::createFromFormat('m-d-Y', $initial_date)->format('Y-m-d');
        $final_date = Carbon::createFromFormat('m-d-Y', $final_date)->format('Y-m-d');

        $bitacora = $bitacoraService->obtenerIniciosSesion($cod_usuario, $initial_date, $final_date);

        return response()->json([
            'success' => true,
            'message' => 'Fechas capturadas correctamente',
            'data' => [
                'fecha_ingreso' => $initial_date,
                'fecha_salida' => $final_date,
                'registros_fechas' => $bitacora,
                'pack_types' => Paquete::activos()->get()->toJson(),
                'packs_farms_category_assignment' => PaqueteFarmLocation::getPackData(),
            ]
        ]);
    }

    public function registroDeTareasPorUsuarioYFecha(Request $request)
    {
        $fecha = $request->input('fecha');
        $fecha = date('Y-m-d', strtotime($fecha));
        $cod_usuario = $request->input('cod_usuario');

        $registrosTareas = DB::table('pay_jobs_progresos')
            ->join('pay_crews', 'pay_crews.cod_harvest', '=', 'pay_jobs_progresos.cod_harvest')
            ->join('pay_harvests', 'pay_harvests.cod_harvest', '=', 'pay_jobs_progresos.cod_harvest')
            ->leftJoin('far_crop_semillas_bloques', 'far_crop_semillas_bloques.cod_semilla_bloque', '=', 'pay_harvests.crop_age')
            ->leftJoin('bw_inventario_semilla', 'bw_inventario_semilla.cod_inventario', '=', 'far_crop_semillas_bloques.cod_semilla')
            ->leftJoin('pay_harvests_blocks', 'pay_harvests_blocks.cod_harvest', '=', 'pay_harvests.cod_harvest')
            ->leftJoin('pay_harvests_fields', 'pay_harvests_fields.cod_harvest', '=', 'pay_harvests.cod_harvest')
            ->leftJoin('far_bloques', 'far_bloques.cod_bloque', '=', 'pay_harvests_blocks.cod_block')
            ->leftJoin('far_fields', 'far_fields.cod_field', '=', 'pay_harvests_fields.cod_field')
            ->leftJoin('pay_tipo_pagos', 'pay_tipo_pagos.cod_tipo_pago', '=', 'pay_harvests.cod_tipo_pago')
            ->select(
                'pay_jobs_progresos.cod_estado_job',
                'pay_jobs_progresos.cod_harvest',
                DB::raw("DATE_FORMAT(pay_jobs_progresos.hora_inicio, '%h:%i %p') AS hora_inicio"),
                DB::raw("DATE_FORMAT(pay_jobs_progresos.hora_final, '%h:%i %p') AS hora_final"),
                'pay_crews.cod_crew',
                'pay_crews.cod_empleado',
                DB::raw('SUM(pay_crews.cantidad_escaneos) AS cantidad_escaneo'),
                'pay_harvests.cod_harvest',
                'far_crop_semillas_bloques.cod_semilla_bloque',
                DB::raw("CONCAT(GROUP_CONCAT(far_bloques.bloque SEPARATOR ' - '), ' ', GROUP_CONCAT(far_fields.field SEPARATOR ' - '), ' ', bw_inventario_semilla.nombre_semilla, 'harvests') AS job"),
                'pay_tipo_pagos.tipo_pago',
                'pay_tipo_pagos.abreviatura AS abreviatura_tipo_pago'
            )
            ->where('pay_crews.cod_empleado', $cod_usuario)
            ->where('pay_crews.cod_estado_job', 3)
            ->where('pay_jobs_progresos.fecha_job', $fecha)
            ->groupBy('pay_jobs_progresos.cod_estado_job')
            ->get();

        return $registrosTareas;
    }

    public function actualizarHorasEntradaSalida(Request $request)
    {
        $codInicioSesion = $request->input('codInicioSesion'); //"86"
        $horaEntrada = $request->input('horaEntrada'); //"08:17"
        $horaSalida = $request->input('horaSalida'); //"21:00"

        DB::table('pay_bitacora_inicio_sesion')
            ->where('cod_inicio_sesion', $codInicioSesion)
            ->update([
                'fecha_ingreso' => DB::raw("CONCAT(DATE_FORMAT(fecha_ingreso, '%Y-%m-%d'), ' ', '$horaEntrada' )"),
                'fecha_egreso' => DB::raw("CONCAT(COALESCE(DATE_FORMAT(fecha_egreso, '%Y-%m-%d'), DATE_FORMAT(fecha_ingreso, '%Y-%m-%d')), ' ', '$horaSalida')")
            ]);
        DB::table('pay_bitacora_inicio_sesion')
            ->where('cod_inicio_sesion', $codInicioSesion)
            ->whereNull('cod_farm_co')
            ->update([
                'cod_farm_co' => DB::raw('cod_farm_ci'),
                'cod_location_co' => DB::raw('cod_location_ci')
            ]);

        // TODO: Agregar que se actualice la tabla usu_usuarios el campo disponible=0 si la fecha del cod_inicio_sesion es hoy
        // Se actualiza la tabla usu_usuarios el campo disponible=0 si la fecha del cod_inicio_sesion es hoy
        $registroInicioSesion = RegistroIngreso::find($codInicioSesion, ['cod_usuario', 'fecha_ingreso']);
        $fechaInicioSesion = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $registroInicioSesion->fecha_ingreso
        );
        if ($fechaInicioSesion->isToday()) {
            Usuario::asignarDisponibilidad($registroInicioSesion->cod_usuario, 0);
        }

        return $request->all();
    }

    public function actualizarHorasEntrada(Request $request)
    {
        $codInicioSesion = $request->input('codInicioSesion'); //"86"
        $horaEntrada = $request->input('horaEntrada'); //"08:17"

        DB::table('pay_bitacora_inicio_sesion')
            ->where('cod_inicio_sesion', $codInicioSesion)
            ->update([
                'fecha_ingreso' => DB::raw("CONCAT(DATE_FORMAT(fecha_ingreso, '%Y-%m-%d'), ' ', '$horaEntrada' )"),
            ]);

        return $request->all();
    }

    public function actualizarHorasInicioFinalTarea(Request $request)
    {
        $codCrew = $request->input('codCrew'); //"86"
        $horaInicio = $request->input('horaInicio'); //"08:17"
        $horaFinal = $request->input('horaFinal'); //"21:00"

        DB::table('pay_crews')
            ->where('cod_crew', $codCrew)
            ->update([
                'hora_inicio' => $horaInicio,
                'hora_final' => $horaFinal
            ]);
        return $request->all();
    }

    public function eliminarVinculoConTarea(Request $request)
    {
        $codCrew = $request->input('codCrew'); //"86"

        $deleted = DB::table('pay_crews')->where('cod_crew', $codCrew)->delete();
        if ($deleted == 0) {
            return response()->json(['estadoEliminacion' => false, 'message' => 'No se pudo eliminar el vinculo', 'data' => $deleted, 'codCrew' => $codCrew]);
        } else {
            return response()->json(['estadoEliminacion' => true, 'message' => 'Vinculo eliminado correctamente', 'data' => $deleted, 'codCrew' => $codCrew]);
        }
    }

    public function cambiarCantidadEscaneoTareaEmpleado(Request $request)
    {

        $cantidadIngresada = $request->input('cantidadIngresada');
        $codCrew = $request->input('codCrew');

        $listaEmpleadosJobs = DB::table('pay_lista_empleados_jobs')
            ->select('cod_lista', 'cod_crew', 'pieces', 'cod_estado_job', 'terminado', 'hora_fuerza_terminado', 'GPS', 'user_insert', 'date_insert')
            ->where('cod_crew', $codCrew)
            ->where('pieces', '>', 0)
            ->groupBy('cod_lista')
            ->orderBy('cod_lista', 'DESC')
            ->get();
        $registroSinPiezas = DB::table('pay_lista_empleados_jobs')
            ->select('cod_lista', 'cod_crew', 'pieces', 'cod_estado_job', 'terminado', 'hora_fuerza_terminado', 'GPS', 'user_insert', 'date_insert')
            ->where('cod_crew', $codCrew)
            ->where('pieces', '=', 0)
            ->groupBy('cod_lista')
            ->orderBy('cod_lista', 'DESC')
            ->get();
        $cantidadEscaneoExistentes = count($listaEmpleadosJobs);
        if ($cantidadEscaneoExistentes > $cantidadIngresada) { // 10 > 5
            //Eliminamos los registros que sobran
            $codElementosEliminados = [];
            $resultante = $cantidadEscaneoExistentes - $cantidadIngresada; //10-5 = 5
            for ($i = 0; $i < $resultante; $i++) {

                DB::table('pay_lista_empleados_jobs')->where('cod_lista', '=', $listaEmpleadosJobs[$i]->cod_lista)->delete();
                $codElementosEliminados[] = $listaEmpleadosJobs[$i]->cod_lista;
            }
            return $codElementosEliminados;
        } else {
            //Agregamos los nuevos registros
            $resultante = $cantidadIngresada - $cantidadEscaneoExistentes; //5 - 3 = 2
            for ($i = 0; $i < $resultante; $i++) {

                DB::table('pay_lista_empleados_jobs')->insert([
                    'cod_crew' => $registroSinPiezas[0]->cod_crew,
                    'pieces' => '1',
                    'cod_estado_job' => $registroSinPiezas[0]->cod_estado_job,
                    'registro_manual' => '1',
                    'terminado' => $registroSinPiezas[0]->terminado,
                    'hora_fuerza_terminado' => $registroSinPiezas[0]->hora_fuerza_terminado,
                    'GPS' => $registroSinPiezas[0]->GPS,
                    'user_insert' => $registroSinPiezas[0]->user_insert
                ]);
            }
            return $registroSinPiezas[0]->cod_lista;
            // return "cero";
        }
    }

    public function listaTipoPagos()
    {
        $listaTipoPagos = DB::table('pay_tipo_pagos')
            ->select('cod_tipo_pago', 'tipo_pago', 'abreviatura', DB::raw("CONCAT(tipo_pago, ' - ', abreviatura) AS tipo_de_pago"))
            ->get();

        return $listaTipoPagos;
    }

    public function listaHarvestsPorGranja(Request $request)
    {
        $cod_farm = $request->input('cod_farm');
        $fecha_job = $request->input('fecha_job');
        $listaHarvests = DB::table('pay_harvests')
            ->join('pay_jobs_progresos', 'pay_jobs_progresos.cod_harvest', '=', 'pay_harvests.cod_harvest')
            ->leftJoin('far_crop_semillas_bloques', 'far_crop_semillas_bloques.cod_semilla_bloque', '=', 'pay_harvests.crop_age')
            ->leftJoin('bw_inventario_semilla', 'bw_inventario_semilla.cod_inventario', '=', 'far_crop_semillas_bloques.cod_semilla')
            ->leftJoin('pay_harvests_blocks', 'pay_harvests_blocks.cod_harvest', '=', 'pay_harvests.cod_harvest')
            ->leftJoin('pay_harvests_fields', 'pay_harvests_fields.cod_harvest', '=', 'pay_harvests.cod_harvest')
            ->leftJoin('far_bloques', 'far_bloques.cod_bloque', '=', 'pay_harvests_blocks.cod_block')
            ->leftJoin('far_fields', 'far_fields.cod_field', '=', 'pay_harvests_fields.cod_field')
            ->leftJoin('pay_tipo_pagos', 'pay_tipo_pagos.cod_tipo_pago', '=', 'pay_harvests.cod_tipo_pago')
            ->select(
                'pay_harvests.cod_harvest',
                'pay_jobs_progresos.cod_job',
                'pay_tipo_pagos.abreviatura AS abreviatura_tipo_pago',
                DB::raw("GROUP_CONCAT(DATE_FORMAT(pay_jobs_progresos.hora_inicio, '%h:%i %p')) AS hora_inicio"),
                DB::raw("GROUP_CONCAT(DATE_FORMAT(pay_jobs_progresos.hora_final, '%h:%i %p')) AS hora_final"),
                DB::raw("COALESCE(CONCAT(GROUP_CONCAT(DISTINCT far_bloques.bloque SEPARATOR ' - '), ' ', GROUP_CONCAT(DISTINCT far_fields.field SEPARATOR ' - '), ' ', bw_inventario_semilla.nombre_semilla, ' H', ', ', pay_tipo_pagos.tipo_pago, ' ( ', pay_tipo_pagos.abreviatura, ' ) '), 'Missing data') AS job")
            )
            ->where('pay_harvests.cod_farm', $cod_farm)
            ->where('pay_jobs_progresos.cod_estado_job', 3)
            ->where('pay_jobs_progresos.fecha_job', date('Y-m-d', strtotime($fecha_job)))
            ->groupBy('pay_harvests.cod_harvest', 'pay_jobs_progresos.cod_job')
            ->get();

        return $listaHarvests;
    }

    public function listaMiscelaneosPorGranja(Request $request)
    {
        $cod_farm = $request->input('cod_farm');
        $fecha_job = $request->input('fecha_job');
        $listaMiscelaneos = DB::table('pay_miscellaneous')
            ->join('pay_jobs_progresos', 'pay_jobs_progresos.cod_miscellaneous', '=', 'pay_miscellaneous.cod_miscellaneous')
            ->leftJoin('far_crop_semillas_bloques', 'far_crop_semillas_bloques.cod_semilla_bloque', '=', 'pay_miscellaneous.crop_age')
            ->leftJoin('bw_inventario_semilla', 'bw_inventario_semilla.cod_inventario', '=', 'far_crop_semillas_bloques.cod_semilla')
            ->leftJoin('pay_miscelaneos_blocks', 'pay_miscelaneos_blocks.cod_miscelaneos', '=', 'pay_miscellaneous.cod_miscellaneous')
            ->leftJoin('pay_miscelaneos_fields', 'pay_miscelaneos_fields.cod_miscelaneos', '=', 'pay_miscellaneous.cod_miscellaneous')
            ->leftJoin('far_bloques', 'far_bloques.cod_bloque', '=', 'pay_miscelaneos_blocks.cod_block')
            ->leftJoin('far_fields', 'far_fields.cod_field', '=', 'pay_miscelaneos_fields.cod_field')
            ->leftJoin('far_locations', 'far_locations.cod_location', '=', 'pay_miscellaneous.cod_location')
            ->leftJoin('pay_activities', 'pay_activities.cod_activity', '=', 'pay_miscellaneous.cod_activity')
            ->leftJoin('pay_tipo_pagos', 'pay_tipo_pagos.cod_tipo_pago', '=', 'pay_miscellaneous.cod_tipo_pago')
            ->select(
                'pay_miscellaneous.cod_miscellaneous',
                'pay_jobs_progresos.cod_job',
                'pay_tipo_pagos.abreviatura AS abreviatura_tipo_pago',
                DB::raw("GROUP_CONCAT(DATE_FORMAT(pay_jobs_progresos.hora_inicio, '%h:%i %p')) AS hora_inicio"),
                DB::raw("GROUP_CONCAT(DATE_FORMAT(pay_jobs_progresos.hora_final, '%h:%i %p')) AS hora_final"),
                DB::raw("COALESCE(CONCAT(GROUP_CONCAT(DISTINCT far_bloques.bloque SEPARATOR ' - '), ' ', GROUP_CONCAT(DISTINCT far_fields.field SEPARATOR ' - '), ' ', bw_inventario_semilla.nombre_semilla, ' Miscellaneous', ', ', pay_tipo_pagos.tipo_pago, ' ( ', pay_tipo_pagos.abreviatura, ' ) '), CONCAT(far_locations.location, ' ', pay_activities.activity, 'M')) AS job")
            )
            ->where('pay_miscellaneous.cod_farm', $cod_farm)
            ->where('pay_jobs_progresos.cod_estado_job', 3)
            ->where('pay_jobs_progresos.fecha_job', date('Y-m-d', strtotime($fecha_job)))
            ->groupBy('pay_miscellaneous.cod_miscellaneous', 'pay_jobs_progresos.cod_job')
            ->get();

        return $listaMiscelaneos;
    }

    public function asignarTareaExistente(Request $request)
    {
        $cod_harvest = $request->input('cod_harvest');
        $cod_miscelaneos = $request->input('cod_miscelaneos');
        $cantidad_escaneos = $request->input('cantidad_escaneos');
        $cod_usuario = $request->input('cod_empleado');

        $respuestaFinal = [];

        $datosDeUsuario = DB::table('usu_usuarios')
            ->select('pin', 'qcpin', 'cod_usuario')
            ->where('cod_usuario', $cod_usuario)
            ->first();
        if ($datosDeUsuario == null) {
            return response()->json(['success' => false, 'message' => 'The user does not exist', 'data' => $request->all()]);
        }

        $datosDeUsuario->pin;
        if ($cod_harvest != '-b') {

            $crewExistente = DB::table('pay_crews')
                ->select('cod_crew')
                ->where('cod_empleado', $cod_usuario)
                ->where('cod_harvest', $cod_harvest)
                ->first();

            if ($crewExistente != null) {
                return response()->json(['success' => false, 'message' => 'The user already has a task assigned', 'data' => $request->all()]);
            }


            $job = DB::table('pay_jobs_progresos')
                ->select('cod_job', 'user_insert')
                ->where('cod_harvest', $cod_harvest)
                ->first();
            $respuestaFinal['cod_job'] = $job->cod_job;
            $cod_crew = DB::table('pay_crews')->insertGetId([
                'cod_harvest' => $cod_harvest,
                'cantidad_escaneos' => '0',
                'cod_empleado' => $datosDeUsuario->cod_usuario,
                'pin' => $datosDeUsuario->pin,
                'qc_pin' => $datosDeUsuario->qcpin,
                'registro_manual' => '1',
                'cod_supervisor' => $job->user_insert,
                'cod_estado_job' => '3',
                'user_insert' => $job->user_insert
            ]);
            $respuestaFinal['cod_crew'] = $cod_crew;
            $respuestaFinal['cod_job'] = $job->cod_job;

            DB::table('pay_lista_empleados_jobs')->insert([
                'cod_crew' => $cod_crew,
                'pieces' => '0',
                'cod_estado_job' => '3',
                'terminado' => '1',
                'hora_fuerza_terminado' => '00:00',
                'registro_manual' => '1',
                'GPS' => '0.0,0.0',
                'user_insert' => $job->user_insert
            ]);

            if ($cantidad_escaneos > 0) {

                for ($i = 0; $i < $cantidad_escaneos; $i++) {
                    DB::table('pay_lista_empleados_jobs')->insert([
                        'cod_crew' => $cod_crew,
                        'pieces' => '1',
                        'cod_estado_job' => '3',
                        'registro_manual' => '1',
                        'terminado' => '0',
                        'hora_fuerza_terminado' => '00:00',
                        'GPS' => '0',
                        'user_insert' => $job->user_insert
                    ]);
                }
            }
            return response()->json(['success' => true, 'message' => 'The Harvest task has been successfully assigned', 'data' => $request->all()]);
        } else if ($cod_miscelaneos != '-b') {
            $job = DB::table('pay_jobs_progresos')
                ->select('cod_job', 'user_insert')
                ->where('cod_miscellaneous', $cod_miscelaneos)
                ->first();

            $crewExistente = DB::table('pay_crews')
                ->select('cod_crew')
                ->where('cod_empleado', $cod_usuario)
                ->where('cod_miscellaneous', $cod_miscelaneos)
                ->first();

            if ($crewExistente != null) {
                return response()->json(['success' => false, 'message' => 'The user already has a task assigned', 'data' => $request->all()]);
            }
            $cod_crew = DB::table('pay_crews')->insertGetId([
                'cod_miscellaneous' => $cod_miscelaneos,
                'cantidad_escaneos' => '0',
                'cod_empleado' => $datosDeUsuario->cod_usuario,
                'pin' => $datosDeUsuario->pin,
                'qc_pin' => $datosDeUsuario->qcpin,
                'cod_supervisor' => $job->user_insert,
                'cod_estado_job' => '3',
                'registro_manual' => '1',
                'user_insert' => $job->user_insert
            ]);
            $respuestaFinal['cod_crew'] = $cod_crew;
            $respuestaFinal['cod_job'] = $job->cod_job;
            DB::table('pay_lista_empleados_jobs')->insert([
                'cod_crew' => $cod_crew,
                'pieces' => '0',
                'cod_estado_job' => '3',
                'registro_manual' => '1',
                'terminado' => '1',
                'hora_fuerza_terminado' => '00:00',
                'GPS' => '0',
                'user_insert' => $job->user_insert
            ]);

            if ($cantidad_escaneos > 0) {

                for ($i = 0; $i < $cantidad_escaneos; $i++) {
                    DB::table('pay_lista_empleados_jobs')->insert([
                        'cod_crew' => $cod_crew,
                        'pieces' => '1',
                        'cod_estado_job' => '3',
                        'terminado' => '0',
                        'registro_manual' => '1',
                        'hora_fuerza_terminado' => '00:00',
                        'GPS' => '0',
                        'user_insert' => $job->user_insert
                    ]);
                }
            }
            return response()->json(['success' => true, 'message' => 'The miscellaneous task has been successfully assigned', 'data' => $request->all()]);
        }

        return $request->all();
    }

    public function searchEmpleados(Request $request)
    {
        if ($request->ajax()) {
            $query = $request->get('query');
            $fecha_inicial = $request->get('fecha_inicial') ?? "";
            $fecha_final = $request->get('fecha_final') ?? "";
            $cod_granja = $request->get('cod_farm') ?? 0;
            $cod_location = $request->get('cod_location') ?? 0;
            if ($fecha_inicial != "" && $fecha_final != "") {
                $fecha_inicial = Carbon::createFromFormat('m-d-Y', $fecha_inicial)
                    ->startOfDay()
                    ->format('Y-m-d H:i:s');
                $fecha_final = Carbon::createFromFormat('m-d-Y', $fecha_final)
                    ->endOfDay()
                    ->format('Y-m-d H:i:s');
            }
            $employees = DB::table('usu_usuarios')
                ->distinct()
                ->select(
                    'usu_usuarios.cod_usuario',
                    'usuario',
                    'pin',
                    'qcpin',
                    'es_veterano',
                    'nombre_1',
                    'apellido_1',
                    'cod_tipo_usuario',
                    DB::raw("CONCAT(nombre_1, ' ', apellido_1) AS nombre")
                )
                ->leftJoin('pay_bitacora_inicio_sesion', 'usu_usuarios.cod_usuario', '=', 'pay_bitacora_inicio_sesion.cod_usuario');
            if ($cod_location > 0) {
                $employees
                    ->leftJoin('far_locations', 'pay_bitacora_inicio_sesion.cod_location_ci', '=', 'far_locations.cod_location')
                    ->addSelect('far_locations.location');
            }
            $employees->whereAny([
                "nombre_1",
                "nombre_2",
                "apellido_1",
                "apellido_2",
                "pin",
                "qcpin"
            ], 'LIKE', '%' . $query . '%');
            if ($cod_granja > 0) {
                $employees
                    ->leftJoin('far_farms', 'pay_bitacora_inicio_sesion.cod_farm_ci', '=', 'far_farms.cod_farms')
                    ->addSelect('far_farms.farm')
                    ->where('pay_bitacora_inicio_sesion.cod_farm_ci', $cod_granja);
            }
            if ($cod_location > 0) {
                $employees->where('pay_bitacora_inicio_sesion.cod_location_ci', $cod_location);
            }
            $employees->where('usu_usuarios.activo', 1)
                ->whereNotIn('cod_tipo_usuario', [1, 2, 3])
                ->whereBetween('pay_bitacora_inicio_sesion.fecha_ingreso', [$fecha_inicial, $fecha_final])
                ->orderBy('nombre_1');
            $employees = $employees->get();

            $output = '';
            foreach ($employees as $empleado) {
                $empleado->al_menos_un_error = $this->comprobarSiHayAlgunError(
                    $empleado->cod_usuario,
                    $fecha_final,
                    $fecha_inicial
                );
            }
            if (count($employees) > 0) {
                foreach ($employees as $employee) {
                    if ($employee->cod_tipo_usuario != 1 && $employee->cod_tipo_usuario != 2 && $employee->cod_tipo_usuario != 3) {
                        $output .= '<div onclick="clicEnTarjetaEmpleado(' . $employee->cod_usuario . ')" class="row selector_empleado">';
                        $output .= '<div class="col-md-10 pt-3">';
                        $output .= '<label class="elemento_seleccionable">';
                        $output .= '<span style="font-weight: bold;">' . $employee->nombre . '</span> <br>' . $employee->pin . ' - ' . $employee->qcpin . ($cod_granja > 0 ? (', Farm: ' . ($employee->farm == "" ? "Not defined" : $employee->farm)) : "") . ($cod_location > 0 ? ', Location: ' . ($employee->location == "" ? "Not defined" : $employee->location) : "");
                        $output .= '</span>';
                        $output .= '</div>';
                        $output .= '<div id="estado_alerta_usuario_' . $employee->cod_usuario . '" ' . ($employee->al_menos_un_error == 1 ? '' : 'hidden') . ' class="col-md-2 pt-3">';
                        $output .= '<label>';
                        $output .= '<i style="color: red" class="fa-solid fa-circle-exclamation"></i>';
                        $output .= '</label>';
                        $output .= '</div>';
                        $output .= '</div>';
                    }
                }
            } else {
                $output .= '<p>No results found</p>';
            }


            return $output;
        } else {
            return response()->json(['success' => false, 'message' => 'The request is not ajax', 'data' => $request->all()]);
        }
    }

    public function searchEmpleadosAsJson(Request $request)
    {

        $query = $request->input('query') ?? "";
        $fecha_inicial = $request->input('fecha_inicial') ?? "";
        $fecha_final = $request->input('fecha_final') ?? "";
        $cod_granja = $request->input('cod_farm') ?? "";
        $cod_location = $request->input('cod_location') ?? "";
        $cod_categories = json_decode($request->input('cod_category')) ?? [];

        if ($fecha_inicial != "" && $fecha_final != "") {
            $fecha_inicial = Carbon::createFromFormat('m-d-Y', $fecha_inicial)
                ->startOfDay()
                ->format('Y-m-d H:i:s');
            $fecha_final = Carbon::createFromFormat('m-d-Y', $fecha_final)
                ->endOfDay()
                ->format('Y-m-d H:i:s');
        }

        $employees = DB::table('usu_usuarios')
            ->distinct()
            ->select(
                'usu_usuarios.cod_usuario',
                'usuario',
                'pin',
                'qcpin',
                'es_veterano',
                'nombre_1',
                'apellido_1',
                'cod_tipo_usuario',
                DB::raw("CONCAT(nombre_1, ' ', apellido_1) AS nombre")
            )
            ->leftJoin('pay_bitacora_inicio_sesion', 'usu_usuarios.cod_usuario', '=', 'pay_bitacora_inicio_sesion.cod_usuario');

        $employees->whereAny([
            "nombre_1",
            "nombre_2",
            "apellido_1",
            "apellido_2",
            "pin",
            "qcpin"
        ], 'LIKE', '%' . $query . '%');

        if ($cod_granja > 0) {
            $employees
                ->leftJoin('far_farms', 'pay_bitacora_inicio_sesion.cod_farm_ci', '=', 'far_farms.cod_farms')
                ->addSelect('far_farms.farm')
                ->where('pay_bitacora_inicio_sesion.cod_farm_ci', $cod_granja);
        }

        if ($cod_location != "" && $cod_location > 0) {
            $employees->where('pay_bitacora_inicio_sesion.cod_location_ci', $cod_location);
        }

        $employees->where('usu_usuarios.activo', 1);
        if (count($cod_categories) > 0) {
            $employees->whereIn('es_veterano', $cod_categories);
        }
        $employees->whereNotIn('cod_tipo_usuario', [1, 2, 3])
            ->where('pay_bitacora_inicio_sesion.fecha_ingreso', '>=', $fecha_inicial)
            ->where('pay_bitacora_inicio_sesion.fecha_egreso', '<=', $fecha_final)
            ->orderBy('nombre_1');

        return response()->json([
            'success' => true,
            'employees' => $employees->get()
        ]);
    }

    public function searchEmpleadosAsJsonMultiple(Request $request)
    {

        $query = $request->input('query') ?? "";
        $fecha_inicial = $request->input('fecha_inicial') ?? "";
        $fecha_final = $request->input('fecha_final') ?? "";
        $cod_granja = json_decode($request->input('cod_farm')) ?? [];
        $cod_location = json_decode($request->input('cod_location')) ?? [];
        $cod_categories = json_decode($request->input('cod_category')) ?? [];

        Log::info($request->input('cod_category'));

        if ($fecha_inicial != "" && $fecha_final != "") {
            $fecha_inicial = Carbon::createFromFormat('m-d-Y', $fecha_inicial)
                ->startOfDay()
                ->format('Y-m-d H:i:s');
            $fecha_final = Carbon::createFromFormat('m-d-Y', $fecha_final)
                ->endOfDay()
                ->format('Y-m-d H:i:s');
        }

        $employees = DB::table('usu_usuarios')
            ->distinct()
            ->select(
                'usu_usuarios.cod_usuario',
                'usuario',
                'pin',
                'qcpin',
                'es_veterano',
                'nombre_1',
                'apellido_1',
                'cod_tipo_usuario',
                'far_farms.farm',
                DB::raw("CONCAT(nombre_1, ' ', apellido_1) AS nombre")
            )
            ->leftJoin('pay_bitacora_inicio_sesion', 'usu_usuarios.cod_usuario', '=', 'pay_bitacora_inicio_sesion.cod_usuario')
            ->leftJoin('far_farms', 'pay_bitacora_inicio_sesion.cod_farm_ci', '=', 'far_farms.cod_farms');

        $employees->whereAny([
            "nombre_1",
            "nombre_2",
            "apellido_1",
            "apellido_2",
            "pin",
            "qcpin"
        ], 'LIKE', '%' . $query . '%');

        if (count($cod_granja) > 0) {
            $employees->whereIn('pay_bitacora_inicio_sesion.cod_farm_ci', $cod_granja);
        }

        if (count($cod_location) > 0) {
            $employees->whereIn('pay_bitacora_inicio_sesion.cod_location_ci', $cod_location);
        }

        if (count($cod_categories) > 0) {
            $employees->whereIn('es_veterano', $cod_categories);
        }
        $employees->whereNotIn('cod_tipo_usuario', [1, 2, 3])
            ->where('pay_bitacora_inicio_sesion.fecha_ingreso', '>=', $fecha_inicial)
            ->where('pay_bitacora_inicio_sesion.fecha_egreso', '<=', $fecha_final)
            ->orderBy('nombre_1');
        $employees->where('usu_usuarios.activo', 1);

        return response()->json([
            'success' => true,
            'employees' => $employees->get()
        ]);
    }

    public function alternarActivacionAsingarAlmuerzo(Request $request)
    {
        $cod_inicio_sesion = $request->input('cod_inicio_sesion');
        $estado_activacion = $request->input('estado_activacion');
        $resultadoDeActualizacion = DB::table('pay_bitacora_inicio_sesion')
            ->where('cod_inicio_sesion', $cod_inicio_sesion)
            ->update(['lunch_acreditado' => $estado_activacion, 'lunch_automatico' => 0]);

        if ($resultadoDeActualizacion == 0) {

            return response()->json(['success' => false, 'message' => 'Failed to update', 'data' => $estado_activacion]);
        } else {
            return response()->json(['success' => true, 'message' => 'The lunch has been successfully accredited', 'data' => $estado_activacion]);
        }
    }

    public function listaLocacionesPorGranjas(Request $request)
    {
        $cod_farm = $request->input('cod_farm');
        $locations = DB::table('far_locations')
            ->select('cod_location', 'cod_farms', 'location', 'abreviacion')
            ->where('cod_farms', $cod_farm)
            ->orderBy('location')
            ->get();

        return $locations;
    }

    public function listaLocacionesPorGranjasMultiple(Request $request)
    {
        $cod_farms = json_decode($request->input('cod_farms'));
        $locations = DB::table('far_locations')
            ->select('cod_location', 'cod_farms', 'location', 'abreviacion')
            ->whereIn('cod_farms', $cod_farms)
            ->orderBy('location')
            ->get();

        return $locations;
    }

    public function nuevoRegistroIngresoYSalida(Request $request)
    {
        $resultadoOperacion = 0;
        $cod_usuario = $request->input('cod_usuario');
        $cod_farm = $request->input('cod_farm');
        $cod_locacion = $request->input('cod_locacion');
        $fecha_registro = $request->input('fecha_registro');
        $hora_entrada = $request->input('hora_entrada');
        $hora_salida = $request->input('hora_salida');

        $fecha_registro = Carbon::createFromFormat('m-d-Y', $fecha_registro)->format('Y-m-d');

        $resultadoOperacion = DB::table('pay_bitacora_inicio_sesion')->insertGetId([
            'cod_usuario' => $cod_usuario,
            'cod_farm_ci' => $cod_farm,
            'cod_location_ci' => $cod_locacion,
            'cod_farm_co' => $cod_farm,
            'cod_location_co' => $cod_locacion,
            'fecha_ingreso' => $fecha_registro . ' ' . $hora_entrada,
            'fecha_egreso' => $fecha_registro . ' ' . $hora_salida,
            'registro_modificado' => '0',
            'latitud' => '0',
            'longitud' => '0',
            'lunch_acreditado' => '0'
        ]);

        // TODO: Agregar que se actualice en la tabla usu_usuarios el campo disponible=0 de disponible a cero si la $fecha_registro es hoy
        //  Se actualiza en la tabla usu_usuarios el campo disponible=0 de disponible a cero si la $fecha_registro es hoy
        $fechaRegistroCarbon = Carbon::createFromFormat(
            'Y-m-d',
            $fecha_registro
        );
        if ($fechaRegistroCarbon->isToday()) {
            Usuario::asignarDisponibilidad($cod_usuario, 0);
        }

        if ($resultadoOperacion == 0) {
            return response()->json(['success' => false, 'message' => 'Failed to insert', 'data' => $request->all()]);
        } else {
            return response()->json(['success' => true, 'message' => 'Entry registered successfully', 'data' => $request->all()]);
        }
    }

    public function nuevoRegistroIngresoYSalidaBulkAdd(Request $request)
    {
        $resultadoOperacion = 0;
        $cod_usuario = $request->input('cod_usuario');
        $cod_farm = $request->input('cod_farm');
        $cod_locacion = $request->input('cod_locacion');
        $cod_actividad_por_dia = $request->input('cod_actividad_por_dia');
        $fecha_from = $request->input('fecha_from');
        $fecha_to = $request->input('fecha_to');
        $incluir_fines_de_semana = $request->input('incluir_fines_de_semana') == "yes" ? true : false;
        $hora_entrada = $request->input('hora_entrada');
        $hora_salida = $request->input('hora_salida');

        if ($incluir_fines_de_semana) {
            $fechas = collect(CarbonPeriod::create(Carbon::createFromFormat('m-d-Y', $fecha_from), '1 day', Carbon::createFromFormat('m-d-Y', $fecha_to)))
                ->map(fn($date) => $date->format('Y-m-d'))
                ->toArray();
        } else {
            $fechas = collect(CarbonPeriod::create(Carbon::createFromFormat('m-d-Y', $fecha_from), '1 day', Carbon::createFromFormat('m-d-Y', $fecha_to)))
                ->filter(fn($date) => !$date->isWeekend()) // Exclude weekends
                ->map(fn($date) => $date->format('Y-m-d')) // Format the date
                ->toArray();
        }

        $processedSuccesfully = true;
        DB::beginTransaction();
        foreach ($fechas as $fecha) {
            $resultadoOperacion = DB::table('pay_bitacora_inicio_sesion')->insertGetId([
                'cod_usuario' => $cod_usuario,
                'cod_farm_ci' => $cod_farm,
                'cod_location_ci' => $cod_locacion,
                'cod_farm_co' => $cod_farm,
                'cod_location_co' => $cod_locacion,
                'fecha_ingreso' => $fecha . ' ' . $hora_entrada,
                'fecha_egreso' => $fecha . ' ' . $hora_salida,
                'registro_modificado' => '0',
                'latitud' => '0',
                'longitud' => '0',
                'lunch_acreditado' => '0',
                'cod_actividad_por_dia' => $cod_actividad_por_dia
            ]);

            // TODO: Agregar en la tabla usu_usuarios que se actualice el campo disponible=0 de disponible a cero si la $fecha_registro es hoy
            $fechaRegistroCarbon = Carbon::createFromFormat(
                'Y-m-d',
                $fecha
            );
            if ($fechaRegistroCarbon->isToday()) {
                Usuario::asignarDisponibilidad($cod_usuario, 0);
            }

            if ($resultadoOperacion == 0) {
                DB::rollBack();
                $processedSuccesfully = false;
                break;
            }
        }
        if ($processedSuccesfully) {
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Bulk add processed succesfully', 'data' => $request->all()]);
        } else {
            return response()->json(['success' => false, 'message' => 'Failed to process the bulk add', 'data' => $request->all()]);
        }
    }


    public function nuevoRegistroActividadPorGranjaEmpleadosBulkAdd(Request $request)
    {

        $resultadoOperacion = 0;
        $cod_farm = $request->input('cod_farm');
        $cod_locacion = $request->input('cod_locacion');
        $cod_actividad_por_dia = $request->input('cod_actividad_por_dia');
        $fecha_from = $request->input('fecha_from');
        $fecha_to = $request->input('fecha_to');
        $incluir_fines_de_semana = $request->input('incluir_fines_de_semana') == "yes" ? true : false;
        $hora_entrada = $request->input('hora_entrada');
        $hora_salida = $request->input('hora_salida');
        $empleados = json_decode($request->input('empleados'));


        if ($incluir_fines_de_semana) {
            $fechas = collect(CarbonPeriod::create(Carbon::createFromFormat('m-d-Y', $fecha_from), '1 day', Carbon::createFromFormat('m-d-Y', $fecha_to)))
                ->map(fn($date) => $date->format('Y-m-d'))
                ->toArray();
        } else {
            $fechas = collect(CarbonPeriod::create(Carbon::createFromFormat('m-d-Y', $fecha_from), '1 day', Carbon::createFromFormat('m-d-Y', $fecha_to)))
                ->filter(fn($date) => !$date->isWeekend()) // Exclude weekends
                ->map(fn($date) => $date->format('Y-m-d')) // Format the date
                ->toArray();
        }

        $processedSuccesfully = true;
        DB::beginTransaction();
        foreach ($empleados as $empleado) {
            foreach ($fechas as $fecha) {
                $resultadoOperacion = DB::table('pay_bitacora_inicio_sesion')->insertGetId([
                    'cod_usuario' => $empleado->id,
                    'cod_farm_ci' => $cod_farm,
                    'cod_location_ci' => $cod_locacion,
                    'cod_farm_co' => $cod_farm,
                    'cod_location_co' => $cod_locacion,
                    'fecha_ingreso' => $fecha . ' ' . $hora_entrada,
                    'fecha_egreso' => $fecha . ' ' . $hora_salida,
                    'registro_modificado' => '0',
                    'latitud' => '0',
                    'longitud' => '0',
                    'lunch_acreditado' => '0',
                    'cod_actividad_por_dia' => $cod_actividad_por_dia
                ]);

                // TODO: Agregar que se actualice en la tabla usu_usuarios el campo disponible=0 de disponible a cero si la $fecha es hoy
                // Actualiza en la tabla usu_usuarios el campo disponible=0 si la $fecha es hoy
                $fechaRegistroCarbon = Carbon::createFromFormat(
                    'Y-m-d',
                    $fecha
                );
                if ($fechaRegistroCarbon->isToday()) {
                    Usuario::asignarDisponibilidad($empleado->id, 0);
                }

                if ($resultadoOperacion == 0) {
                    DB::rollBack();
                    $processedSuccesfully = false;
                    break;
                }
            }
        }
        if ($processedSuccesfully) {
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Bulk add processed succesfully', 'data' => $request->all()]);
        } else {
            return response()->json(['success' => false, 'message' => 'Failed to process the bulk add', 'data' => $request->all()]);
        }
    }

    private function comprobarSiHayAlgunError(
        $cod_usuario,
        $currentDate = 0,
        $sevenDaysAgo = 0
    ) {
        $currentDate = $currentDate == 0 ? date('Y-m-d') : $currentDate;
        $sevenDaysAgo = $sevenDaysAgo == 0 ? date('Y-m-d', strtotime('-7 days')) : $sevenDaysAgo;
        $bitacora = DB::table('pay_bitacora_inicio_sesion')
            ->select('cod_inicio_sesion', 'cod_usuario', 'fecha_ingreso', 'fecha_egreso', 'lunch_acreditado', 'lunch_automatico')
            ->where('cod_usuario', $cod_usuario)
            ->whereDate('fecha_ingreso', '>=', $sevenDaysAgo)
            ->whereDate('fecha_ingreso', '<=', $currentDate)
            ->get();
        if ($this->comprobarHoraRegistroFaltante($bitacora)) {
            return 1;
        }

        return 0;
    }

    private function comprobarHoraRegistroFaltante($bitacora)
    {
        foreach ($bitacora as $registro) {
            if ($registro->fecha_egreso == null) {
                return true;
            }
        }
    }


    public function cargarTareasPorFechaEmpleado(
        Request $request,
        TareaHarvestService $tareaHarvestService,
        TareaMiscelaneaService $tareaMiscelaneaService
    ) {

        HelpController::setDatabaseModeParaGrandesQuerys();

        $codUsuario = (int) $request->input('codUsuario');
        $fechaIngreso = $request->input('fechaIngreso');
        $fechaIngreso = date('Y-m-d', strtotime($fechaIngreso));

        $registrosTareasHarvest = $tareaHarvestService->obtenerTareasHarvestTerminadas($codUsuario, $fechaIngreso);
        $registrosTareasMiscelaneas = $tareaMiscelaneaService->obtenerTareasMiscelaneasTerminadas($codUsuario, $fechaIngreso);

        $registroTareasInificadas = [...$registrosTareasHarvest, ...$registrosTareasMiscelaneas];

        return response()->json([
            'success' => true,
            'message' => 'Finalización de proceso obtención de tareas',
            'data' => [
                'registroTareasInificadas' => $registroTareasInificadas,
                'pack_types' => Paquete::activos()->get()->toJson(),
                'packs_farms_category_assignment' => PaqueteFarmLocation::getPackData()
            ]
        ]);
    }
    public function listaDatosEscaneadosPorCodCrew(Request $request)
    {

        HelpController::setDatabaseModeParaGrandesQuerys();

        $codCrew = $request->input('cod_crew');

        $datosEscaneados = DB::table('pay_lista_empleados_jobs')
            ->select(
                'cod_lista',
                'pieces',
                DB::raw("DATE_FORMAT(hora_escaneo, '%H:%i') as hora_escaneo"),
                DB::raw("COALESCE(comentario, '') as comentario")
            )
            ->where('cod_crew', $codCrew)
            ->where('pieces', '>', 0)
            ->orderByRaw("DATE_FORMAT(hora_escaneo, '%H:%i') ASC")
            ->get();

        if ($datosEscaneados->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No data found',
                'algo' => $datosEscaneados,
                'codCrew' => $codCrew,
                'data' => [
                    'datosEscaneados' => $datosEscaneados,

                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data successfully retrieved',
            'data' => [
                'datosEscaneados' => $datosEscaneados,
            ]
        ]);
    }
    public function guardarRegistroDatosEscaneo(Request $request)
    {

        HelpController::setDatabaseModeParaGrandesQuerys();

        $codCrew = $request->input('cod_crew');

        $datosEscaneadosArray = $request->input('datosEscaneados');
        $queryResult = DB::table('pay_lista_empleados_jobs')
            ->select('cod_estado_job', 'terminado', 'hora_fuerza_terminado', 'GPS')
            ->where('cod_crew', $codCrew)
            ->where('pieces', 0)
            ->get();

        Log::info('Query result: ' . $queryResult);
        if ($queryResult->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No data found for the given crew',
                'data' => []
            ]);
        }
        if (is_array($datosEscaneadosArray)) {
            session_start();
            if (!isset($_SESSION['cod_usuario'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session expired or not started',
                    'data' => []
                ]);
            }
            // The DB::beginTransaction() method initiates a database transaction.
            // This means that any database operations that modify data will remain "pending"
            // until the transaction is either committed (with DB::commit()) or rolled back (with DB::rollBack()).
            // Using a transaction ensures data integrity, especially when multiple operations must either all succeed or all fail.
            DB::beginTransaction();
            try {
                $cantidadTotalPiezas = 0;
                foreach ($datosEscaneadosArray as $dato) {
                    $horaEscaneo = date('Y-m-d') . ' ' . $dato['hora'];
                    if ($dato['nuevo'] == 1 && $dato['vigencia'] == 1) {
                        $cantidadTotalPiezas += $dato['pieza'];

                        DB::table('pay_lista_empleados_jobs')->insert([
                            'cod_crew'              => $codCrew,
                            'pieces'                => $dato['pieza'],
                            'comentario'            => $dato['comentario'],
                            'cod_estado_job'        => $queryResult[0]->cod_estado_job,
                            'registro_manual'       => 1,
                            'hora_escaneo'          => $horaEscaneo,
                            'terminado'             => $queryResult[0]->terminado,
                            'hora_fuerza_terminado' => $queryResult[0]->hora_fuerza_terminado,
                            'GPS'                   => $queryResult[0]->GPS,
                            'user_insert'           => $_SESSION['cod_usuario'],
                        ]);
                    } else {
                        if ($dato['nuevo'] == 0) {

                            if ($dato['vigencia'] == 0) {
                                DB::table('pay_lista_empleados_jobs')
                                    ->where('cod_lista', $dato['cod_lista'])
                                    ->delete();
                            } else {
                                $cantidadTotalPiezas += $dato['pieza'];
                                DB::table('pay_lista_empleados_jobs')
                                    ->where('cod_lista', $dato['cod_lista'])
                                    ->update([
                                        'pieces'       => $dato['pieza'],
                                        'comentario'   => $dato['comentario'],
                                        'hora_escaneo' => $horaEscaneo,
                                    ]);
                            }
                        }
                    }
                }
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Data successfully saved',
                    'data' => [
                        'cantidad_total_piezas' => $cantidadTotalPiezas
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Error saving data: ' . $e->getMessage(),
                    'data' => []
                ]);
            }
        }
    }


    public function agregarComentarioRegistroIngreso(Request $request)
    {
        session_start();
        $comentario_registro_ingreso = $request->input('comentario_registro_ingreso');
        $id_registro_ingreso = $request->input('id_registro_ingreso');
        $fecha_comentario = $request->input('fecha_comentario');


        $registro = DB::table('pay_bitacora_inicio_sesion')
            ->select('cod_inicio_sesion')
            ->where('cod_inicio_sesion', $id_registro_ingreso)
            ->first();

        if (!$registro) {
            return response()->json(['success' => false, 'message' => 'Entry record not found', 'data' => $request->all()]);
        }

        $resultadoDeActualizacion = DB::table('pay_bitacora_inicio_sesion')
            ->where('cod_inicio_sesion', $id_registro_ingreso)
            ->update([
                'comentario' => $comentario_registro_ingreso,
                'cod_usuario_insert_coment' => $_SESSION['cod_usuario'],
                'fecha_comentario' => Carbon::now()->format('Y-m-d H:i:s')
            ]);
        if (!$resultadoDeActualizacion) {
            return response()->json(['success' => false, 'message' => 'Failed to update', 'data' => $request->all()]);
        } else {
            DB::table('pay_bitac_comentarios_inicios_sesiones')
                ->where('cod_inicio_sesion', $id_registro_ingreso)
                ->where('activo', 1)
                ->update([
                    'activo' => 0,
                ]);

            DB::table('pay_bitac_comentarios_inicios_sesiones')->insert([
                'cod_inicio_sesion' => $id_registro_ingreso,
                'comentario' => $comentario_registro_ingreso,
                'cod_usuario_insert_coment' => $_SESSION['cod_usuario'],
                'activo' => 1
            ]);

            $usuario = DB::table('usu_usuarios')
                ->select(DB::raw("CONCAT(nombre_1, ' ', apellido_1) AS nombre"))
                ->where('cod_usuario', $_SESSION['cod_usuario'])
                ->first();
            $nombre_admin_comentario = $usuario ? $usuario->nombre : null;

            return response()->json(['success' => true, 'message' => 'Comment added successfully', 'data' => [
                'comentario_registro_ingreso' => $comentario_registro_ingreso,
                'id_registro_ingreso' => $id_registro_ingreso,
                'fecha_comentario' => $fecha_comentario,
                'nombre_admin_comentario' => $nombre_admin_comentario
            ]]);
        }
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

    /**
     * Store a newly created resource in storage.
     */
    public function registrarIngresosDePINPendientes(Request $request)
    {
        //
        // return $request;
        HelpController::successResponse(0, 'Ingresos registrados correctamente', $request, 200);
    }

    public function eliminarTareaBitacoraInicioSesion(Request $request)
    {
        $cod_inicio_sesion = $request->input(key: 'cod_inicio_sesion');
        RegistroIngreso::find($cod_inicio_sesion)->delete();
        return response()->json(['success' => true, 'message' => 'Task deleted succesfully']);
    }

    public function actualizarFarmLocationTareaBitacoraInicioSesion(Request $request)
    {
        $cod_inicio_sesion = $request->input(key: 'cod_inicio_sesion');
        $cod_farm = $request->input(key: 'cod_farm');
        $cod_location = $request->input(key: 'cod_location');

        // Obtenemos el registro y lo actualizamos
        try {
            $registro = RegistroIngreso::find($cod_inicio_sesion);
            $registro->cod_farm_ci = $registro->cod_farm_co = $cod_farm;
            $registro->cod_location_ci = $registro->cod_location_co = $cod_location;
            $registro->save();
            return response()->json(['success' => true, 'message' => 'Farm and location updated succesfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Farm and location failed']);
        }
    }

    public function actualizarPackTypeHarvest(Request $request)
    {

        $new_pack_type = $request->input(key: 'new_pack_type');
        $cod_harvest = $request->input(key: 'cod_harvest');
        $current_pack_type_selected = $request->input(key: 'current_pack_type_selected');

        $harvest = Harvest::find($cod_harvest);
        $harvest->cod_tipo_pack = $new_pack_type;
        $harvest->save();

        return response()->json(['success' => true, 'message' => 'Harvest updated succesfully']);
    }

    public function registroActividadHarvestMiscelaneaEmpleadosBulkAdd(Request $request)
    {

        $date = $request->input("date");
        $time_start = $request->input("time_start");
        $time_end = $request->input("time_end");
        $activity_type = $request->input("activity_type");
        $cod_farm = $request->input("cod_farm");
        $crop_age = $request->input("crop_age") != "null" ? $request->input("crop_age") : 0;
        $cod_field = $request->input("cod_field") != "null" ? $request->input("cod_field") : 0;
        $cod_block = $request->input("cod_block") != "null" ? $request->input("cod_block") : 0;
        $pack_type = $request->input("pack_type");
        $cod_location = $request->input("cod_location");
        $cod_actividad = $request->input("cod_actividad");
        $cod_plantacion = $request->input("cod_plantacion");
        $employees = json_decode($request->input("employees"));

        $date = Carbon::createFromFormat('m-d-Y', $date)->format('Y-m-d');

        if ($activity_type == "harvest") {
            $harvest = Harvest::create([
                "cod_farm" => $cod_farm,
                "crop_age" => $crop_age,
                "cod_plantacion" => $cod_plantacion,
                "cod_tipo_pack" => $pack_type,
                "cod_tipo_pago" => 1,
                "user_admin" => 1,
                "user_insert" => 1
            ]);
            HarvestBlock::create([
                "cod_harvest" => $harvest->cod_harvest,
                "cod_block" => $cod_block,
                "cod_plantacion" => $cod_plantacion,
            ]);
            HarvestField::create([
                "cod_harvest" => $harvest->cod_harvest,
                "cod_field" => $cod_field,
            ]);
        } else {
            $misc = Miscelaneo::create([
                "cod_location" => $cod_location,
                "cod_activity" => $cod_actividad,
                "cod_farm" => $cod_farm,
                "cod_field" => $cod_field,
                "cod_bloque" => $cod_block,
                "crop_age" => $crop_age,
                "cod_tipo_pago" => 1
            ]);
            MiscelaneoField::create([
                "cod_miscelaneos" => $misc->cod_miscellaneous,
                "cod_field" => $cod_field,
            ]);
            MiscelaneoBlock::create([
                "cod_miscelaneos" => $misc->cod_miscellaneous,
                "cod_block" => $cod_block,
            ]);
        }

        JobProgreso::create([
            "cod_harvest" => isset($harvest) ? $harvest->cod_harvest : null,
            "cod_miscellaneous" => isset($misc) ? $misc->cod_miscellaneous : null,
            "cod_estado_job" => 3,
            "fecha_job" => $date,
            "hora_inicio" => $time_start,
            "hora_final" => $time_end,
            "user_insert" => 1
        ]);

        foreach ($employees as $employeeId) {
            $employee = Empleado::find($employeeId);
            if ($employee) {
                $crew = Crew::create([
                    "cod_harvest" => isset($harvest) ? $harvest->cod_harvest : null,
                    "cod_miscellaneous" => isset($misc) ? $misc->cod_miscellaneous : null,
                    "cod_empleado" => $employeeId,
                    "pin" => $employee->pin,
                    "qc_pin" => $employee->qcpin,
                    "cod_supervisor" => $employee->cod_jefe_inmediato,
                    "cantidad_escaneos" => 0,
                    "cod_estado_job" => 3,
                    "hora_inicio" => $time_start,
                    "hora_final" => $time_end,
                    "user_insert" => 1,
                ]);
                ListaEmpleadoJob::create([
                    "cod_crew" => $crew->cod_crew,
                    "pieces" => 0,
                    "cod_estado_job" => 3,
                    "terminado" => 1,
                    "user_insert" => 1
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Activity created succesfully.']);
    }
}
