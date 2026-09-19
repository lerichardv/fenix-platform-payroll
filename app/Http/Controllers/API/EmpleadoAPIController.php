<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\HelpController;
use App\Models\RegistroEntradaYSalida;
use App\Models\RegistroIngreso;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmpleadoAPIController extends Controller
{
    /** @var  userRepository */
    private $userRepository;
    /** @var  registroIngresoRepository */
    private $registroIngresoRepository;

    /** @var  registroIngresoRepository */
    private $registroEntradaYSalidaRepository;

    public function __construct(User $userRepo, RegistroIngreso $registroIngresoRepo, RegistroEntradaYSalida $registroEntradaYSalidaRepo)
    {
        $this->userRepository = $userRepo;
        $this->registroIngresoRepository = $registroIngresoRepo;
        $this->registroEntradaYSalidaRepository = $registroEntradaYSalidaRepo;
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



    public function ingresoEmpleado($datosUsuarios, $latitud, $longitud, $metodo_ingreso = "escaneo")
    {
        $fechaDelServidor = date('Y-m-d H:i:s');
        $horaDelServidor = date('H:i:s');

        $resultRegistroIngreso = $this->registroEntradaYSalidaRepository->select('cod_clock', 'cod_estado', 'cod_empleado', 'fecha', 'fecha_clock_out', 'hora', 'gps')
            ->where('cod_empleado', $datosUsuarios->cod_usuario)
            ->orderBy('date_insert', 'desc')
            ->limit(1)
            ->get();
        if ($resultRegistroIngreso->isEmpty()) {
            return $this->registrarClockInEmpleado($datosUsuarios, $latitud, $longitud, $fechaDelServidor,  $metodo_ingreso);
        } else {
            $resultRegistroIngreso = $resultRegistroIngreso[0];
            $fechaDiaIngreso = $resultRegistroIngreso->fecha;
            $horaIngreso = $resultRegistroIngreso->hora;

            $diaDiferente =  $this->comprobarQueHayUnaDiaDeDiferencia($fechaDelServidor, $fechaDiaIngreso);

            if ($diaDiferente) {
                return $this->registrarClockInEmpleado($datosUsuarios, $latitud, $longitud, $fechaDelServidor,  $metodo_ingreso);
            }

            $minutos =  $this->calcularMinutosDiferencias($horaDelServidor, $horaIngreso);
            $resultRegistroIngreso->minutosDiferenciaRegistro = $minutos;
            $resultRegistroIngreso->fechaDelServidor = $fechaDelServidor;

            if ($minutos < 30) {
                return HelpController::successResponse(1007, 'You already had the entry marked', $resultRegistroIngreso, 201);
            } else if ($minutos > 30) {
                return $this->registrarClockOutEmpleado($resultRegistroIngreso, $fechaDelServidor);
            }

            return HelpController::successResponse(1009, 'Operation undetermined', $resultRegistroIngreso, 404);
        }
    }

    private function registrarClockInEmpleado($datosUsuarios, $latitud, $longitud, $horaDelServidor,  $metodo_ingreso)
    {
        $registroIngreso = new RegistroEntradaYSalida();
        $registroIngreso->cod_empleado = $datosUsuarios->cod_usuario;
        $registroIngreso->cod_estado = $datosUsuarios->cod_estado;
        $registroIngreso->gps = $latitud . ',' . $longitud;
        $registroIngreso->fecha = date('Y-m-d', strtotime($horaDelServidor));
        $registroIngreso->hora = date('H:i:s', strtotime($horaDelServidor));
        $registroIngreso->save();
        return HelpController::successResponse(1005, 'Entry successfully registered', $datosUsuarios->cod_usuario, 200);
    }

    private function registrarClockOutEmpleado($resultRegistroIngreso, $fechaDelServidor)
    {
        $this->registroEntradaYSalidaRepository->find($resultRegistroIngreso->cod_clock)->update(['fecha_clock_out' => $fechaDelServidor]);
        return HelpController::successResponse(1008, 'Egress has been registered', $resultRegistroIngreso, 201);
    }

    private function calcularMinutosDiferencias($horaDelServidor, $horaIngreso)
    {

        $horaDelServidor = strtotime($horaDelServidor);
        $horaIngreso = strtotime($horaIngreso);
        $minutos = round(abs($horaDelServidor - $horaIngreso) / 60);
        return $minutos;
    }

    private function comprobarQueHayUnaDiaDeDiferencia($horaActual, $horaSecundaria)
    {
        $horaInicio = strtotime('04:00:00');
        $horaFin = strtotime('21:00:00');
        $horaSecundaria = strtotime($horaSecundaria);

        if ($horaSecundaria >= $horaInicio && $horaSecundaria <= $horaFin) {
            $diferencia = abs(strtotime($horaActual) - $horaSecundaria);
            $dias = floor($diferencia / (60 * 60 * 24)); // Calculate the difference in days
            return $dias >= 1; // Check if the difference is at least 1 day
        } else {
            return false;
        }
    }

    public function listaTodosLosEmpleados()
    {

        $empleados = DB::table('usu_usuarios')
            ->select('cod_usuario', 'usuario', 'nombre_1', 'nombre_2', 'apellido_1', 'apellido_2', 'pin', 'qcpin', 'cod_estado', 'activo', 'cod_jefe_inmediato', 'disponible')
            ->whereNotIn('cod_tipo_usuario', [1, 2, 3])
            ->get();

        return $empleados;
    }
    public function listaDeEmpleadosPermitidos()
    {
        helpController::setDatabaseModeParaGrandesQuerys();
        $empleados = DB::table('usu_usuarios')
            ->select(
                'usu_usuarios.cod_usuario',
                'usu_usuarios.usuario',
                'usu_usuarios.nombre_1',
                DB::raw("COALESCE(usu_usuarios.nombre_2, '') AS nombre_2"),
                'usu_usuarios.apellido_1',
                DB::raw("COALESCE(usu_usuarios.apellido_2,'') AS apellido_2"),
                'usu_usuarios.pin',
                'usu_usuarios.qcpin',
                'usu_usuarios.cod_estado',
                'usu_usuarios.activo',
                'usu_usuarios.cod_jefe_inmediato',
                DB::raw("'-' AS fecha_ingreso"),
                DB::raw("'-' AS fecha_egreso"),
                DB::raw("0 AS registro_modificado"),
                DB::raw("'manual' AS metodo_ingreso"),
                DB::raw("0 as latitud"),
                DB::raw("0 as longitud"),
            )

            ->whereNotIn('usu_usuarios.cod_tipo_usuario', [1, 2, 3])
            ->where('usu_usuarios.activo', 1)
            ->get();
        helpController::desconectarBaseDatos();

        return $empleados;
    }
    public function agregarEmpleadosATareaExistente(Request $request)
    {
        $body = $request->all();
        $id_harvest = $body['id_harvest'] ?? "0";
        $id_miscelaneo = $body['id_miscelaneo'] ?? "0";
        $cod_usuarios = $body['cod_usuarios'];
        $cod_supervisor = $body['cod_supervisor'];
        $latitud = $body['latitud'];
        $longitud = $body['longitud'];
        $hora_actual = $body['hora_actual']?? date('H:i:s');
        if (!is_array($cod_usuarios)) {
            $cod_usuarios = explode(',', $cod_usuarios);
        }
        helpController::setDatabaseModeParaGrandesQuerys();

        $empleadosExistentesEnTarea = DB::table('pay_crews')
            ->select('cod_empleado')
            ->where('cod_harvest', $id_harvest)
            ->orWhere('cod_miscellaneous', $id_miscelaneo)
            ->pluck('cod_empleado');

        $empleadosNuevos = array_diff($cod_usuarios, $empleadosExistentesEnTarea->toArray());
        if (!empty($empleadosNuevos)) {


            $cod_estado_job = DB::table('pay_crews')
                ->where('cod_harvest', $id_harvest)
                ->orWhere('cod_miscellaneous', $id_miscelaneo)
                ->pluck('cod_estado_job')
                ->first();

            $empleadosNuevosData = array_map(function ($cod_usuario) use ($id_harvest, $id_miscelaneo, $cod_supervisor, $cod_estado_job,  $hora_actual) {
                return [
                    'cod_empleado' => $cod_usuario,
                    'cod_harvest' => $id_harvest == "0" ? null : $id_harvest,
                    'cod_miscellaneous' => $id_miscelaneo == "0" ? null : $id_miscelaneo,
                    'registro_manual' => 1,
                    'pin' => 0,
                    'qc_pin' => 0,
                    'hora_inicio' =>  $cod_estado_job == 2 ? $hora_actual : null,
                    'hora_final' => null,
                    'cod_estado_job' => $cod_estado_job,
                    'cod_supervisor' => $cod_supervisor,
                    'user_insert' => $cod_supervisor,
                    'date_insert' => now(),
                ];
            }, $empleadosNuevos);

            DB::table('pay_crews')->insert($empleadosNuevosData);

            $codCrews = DB::table('pay_crews')
                ->whereIn('cod_empleado', $empleadosNuevos)
                ->where('cod_harvest', $id_harvest)
                ->orWhere('cod_miscellaneous', $id_miscelaneo)
                ->pluck('cod_crew');


            $empleadosConPinsFaltantes = DB::table('pay_crews')
                ->select('pay_crews.cod_crew', 'usu.cod_usuario', 'usu.pin', 'usu.qcpin')
                ->join('usu_usuarios as usu', 'usu.cod_usuario', '=', 'pay_crews.cod_empleado')
                ->where(function ($query) use ($id_harvest, $id_miscelaneo) {
                    $query->where('cod_harvest', $id_harvest)
                        ->orWhere('cod_miscellaneous', $id_miscelaneo);
                })
                ->where(function ($query) {
                    $query->where('pay_crews.pin', 0)
                        ->orWhere('pay_crews.qc_pin', 0);
                })
                ->orderBy('cod_crew', 'DESC')
                ->get();

            $updates = $empleadosConPinsFaltantes->map(function ($empleado) {
                return [
                    'cod_crew' => $empleado->cod_crew,
                    'pin' => $empleado->pin,
                    'qc_pin' => $empleado->qcpin,
                ];
            })->toArray();

            foreach ($updates as $update) {
                DB::table('pay_crews')
                    ->where('cod_crew', $update['cod_crew'])
                    ->update(['pin' => $update['pin'], 'qc_pin' => $update['qc_pin']]);
            }

            $listaEmpleadosJobsData = $codCrews->map(function ($cod_crew) use ($cod_estado_job, $cod_supervisor, $latitud, $longitud) {
                return [
                    'cod_crew' => $cod_crew,
                    'pieces' => '0',
                    'cod_estado_job' => $cod_estado_job,
                    'registro_manual' => '1',
                    'terminado' => '0',
                    'GPS' => $latitud . ',' . $longitud,
                    'user_insert' => $cod_supervisor,
                ];
            })->toArray();

            DB::table('pay_lista_empleados_jobs')->insert($listaEmpleadosJobsData);
        }
        helpController::desconectarBaseDatos();
        if (empty($empleadosNuevos)) {
            return HelpController::failureResponse(1477, 'No new employees to add', $empleadosExistentesEnTarea, 200);
        }
        return HelpController::successResponse(0, 'Employees added successfully', $empleadosNuevos, 200);
    }
}
