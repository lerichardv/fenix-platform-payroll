<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Crew;
use App\Models\Harvest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CrewJobAPIController extends Controller
{

    /** @var  userRepository */
    private $userRepository;

    /** @var  harvestsRepository */
    private $harvestsRepository;


    /** @var  crewRepository */
    private $crewRepository;


    public function __construct(User $userRepo, Harvest $harvestRepo, Crew $crewRepo)
    {
        $this->userRepository = $userRepo;
        $this->harvestsRepository = $harvestRepo;
        $this->crewRepository = $crewRepo;
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
    /**
     *
     */
    public function crearCrewJob(Request $request)
    {
        $body = $request->all();
        $cod_misceleaneo = null;
        $datosCrew = json_decode($body['datosCrew'], true);
        $datosHarvest = json_decode($body['datosHarvest'], true);
        $codsEmpleados = json_decode($datosCrew['cods_empleados'], true);

        $usuarios = DB::table('usu_usuarios')
            ->whereIn('cod_usuario', $codsEmpleados)
            ->where('disponible', 0)
            ->select('cod_usuario', 'disponible')
            ->get();
        //En caso de haber al menos un empleado no disponible se retorna un error
        if (!$usuarios->isEmpty()) {
            return response()->json(['code' => 1447, 'message' => 'At least one employee is not available', 'extra' => $codsEmpleados], 400);
        } else {
            // Variable $usuarios has data
            // Add your logic here

            $codigosCampos = json_decode($datosHarvest['cods_fields'], true);
            $codigosBloques = json_decode($datosHarvest['cods_blocks'], true);


            $harvest = new Harvest();
            $harvest->cod_farm = $datosHarvest['cod_farm'];
            $harvest->crop_age = $datosHarvest['crop_age'];
            $harvest->cod_tipo_pack = $datosHarvest['cod_tipo_pack'];
            $harvest->cod_tipo_pago = $datosHarvest['cod_tipo_pago'];
            $harvest->user_insert = $datosHarvest['user_insert'];
            $harvest->user_admin = $datosHarvest['user_insert'];
            $harvest->save();
            $cod_harvest = $harvest->cod_harvest;

            $estadoJob = DB::table('pay_estados_jobs')->select('cod_estado_job')->orderBy('cod_estado_job', 'asc')->limit(1)->get();
            $cod_estado_job = $estadoJob[0]->cod_estado_job;

            DB::table('pay_jobs_progresos')->insert([
                'cod_harvest' => $cod_harvest,
                'cod_miscellaneous' => $cod_misceleaneo,
                'cod_estado_job' => $cod_estado_job,
                'fecha_job' => DB::raw('CURRENT_DATE'),
                'hora_inicio' => DB::raw('CURRENT_TIME'),
                'hora_final' => DB::raw('CURRENT_TIME'),
                'user_insert' =>  $datosHarvest['user_insert']
            ]);
            try {
                foreach ($codigosCampos as $codigoCampo) {
                    // Code to be executed for each element in the array
                    DB::table('pay_harvests_fields')->insert([
                        'cod_harvest' => $cod_harvest,
                        'cod_field' => $codigoCampo
                    ]);
                }
            } catch (\Exception $e) {
                return response()->json(['code' => 1445, 'message' => 'Failed to register assigned fields'], 404);
            }
            try {
                foreach ($codigosBloques as $codigoBloque) {
                    DB::table('pay_harvests_blocks')->insert([
                        'cod_harvest' => $cod_harvest,
                        'cod_block' => $codigoBloque
                    ]);
                }
            } catch (\Exception $e) {
                return response()->json(['code' => 1446, 'message' => 'Failed to register assigned blocks'], 404);
            }
            $harvest->cods_fields = $codigosCampos;
            $harvest->cods_blocks = $codigosBloques;


            $usuarios = DB::table('usu_usuarios')->whereIn('cod_usuario', $codsEmpleados)->select('cod_usuario', 'pin', 'qcpin')->get();
            foreach ($usuarios as $usuario) {
                $crewId = DB::table('pay_crews')->insertGetId([
                    'cod_harvest' => $cod_harvest,
                    'cod_miscellaneous' => $cod_misceleaneo,
                    'cod_empleado' => $usuario->cod_usuario,
                    'pin' => $usuario->pin,
                    'cod_estado_job' => $cod_estado_job,
                    'qc_pin' => $usuario->qcpin,
                    'cod_supervisor' => $datosCrew['cod_supervisor'],
                    'user_insert' => $datosCrew['user_insert']
                ]);

                DB::table('pay_lista_empleados_jobs')->insert([
                    'cod_crew' => $crewId,
                    'pieces' => 0,
                    'terminado' => 0,
                    // 'hora_fuerza_terminado' => DB::raw('CURRENT_TIME'),
                    'GPS' => $datosCrew["latitud"] . ',' . $datosCrew["longitud"],
                    'user_insert' => $datosCrew['user_insert']
                ]);
                DB::table('usu_usuarios')
                    ->where('cod_usuario', $usuario->cod_usuario)
                    ->update(['disponible' => 0]);
            }
            if ($datosCrew['crear_nuevo_crew']) {

                $ultimoCrewUsado = DB::table('pay_crews_ultimo_usado')->where('cod_supervisor', $datosCrew['cod_supervisor'])->get();

                // Eliminar el anterior crew job usado
                DB::table('pay_crews_ultimo_usado')->whereIn('cod_crew_ultimo_usado', $ultimoCrewUsado->pluck('cod_crew_ultimo_usado'))->delete();
                // Crear el registro del ultimo crew usado
                foreach ($usuarios as $usuario) {
                    DB::table('pay_crews_ultimo_usado')->insert([
                        'cod_harvest' => $cod_harvest,
                        'cod_miscellaneous' => $cod_misceleaneo,
                        'cod_empleado' => $usuario->cod_usuario,
                        'pin' => $usuario->pin,
                        'qc_pin' => $usuario->qcpin,
                        'cod_supervisor' => $datosCrew['cod_supervisor'],
                        'user_insert' => $datosCrew['user_insert']
                    ]);
                }
                $ultimoCrewUsado = DB::table('pay_crews_ultimo_usado')->where('cod_supervisor', $datosCrew['cod_supervisor'])->get();
            }
            // return response()->json(['message' => 'CREW Job stored successfully', 'data' => $datosCrew], 200);


            return response()->json(['code' => 0, 'message' => 'Success', 'extra' => $cod_harvest], 200);
        }
    }
    public function crearTareaPendiente(Request $request)
    {
        $body = $request->all();
        $cod_misceleaneo = null;
        $datosCrew = json_decode($body['datosCrew'], true);
        $datosHarvest = json_decode($body['datosHarvest'], true);
        $cod_misceleaneo = json_decode($body['datosMiscelaneos'], true);
        $escaneo_pendientes = json_decode($body['escaneo_pendientes'], true);
        $empleados = json_decode($body['empleados'], true);
        $codsEmpleados = json_decode($datosCrew['cods_empleados'], true);

        // $usuarios = DB::table('usu_usuarios')
        //     ->whereIn('cod_usuario', $codsEmpleados)
        //     ->where('disponible', 0)
        //     ->select('cod_usuario', 'disponible')
        //     ->get();
        // //En caso de haber al menos un empleado no disponible se retorna un error
        // if (!$usuarios->isEmpty()) {
        //     return response()->json(['code' => 1447, 'message' => 'At least one employee is not available', 'extra' => $codsEmpleados], 400);
        // } else {
        //     // Variable $usuarios has data
        //     // Add your logic here

        //     $codigosCampos = json_decode($datosHarvest['cods_fields'], true);
        //     $codigosBloques = json_decode($datosHarvest['cods_blocks'], true);


        //     $harvest = new Harvest();
        //     $harvest->cod_farm = $datosHarvest['cod_farm'];
        //     $harvest->crop_age = $datosHarvest['crop_age'];
        //     $harvest->cod_tipo_pack = $datosHarvest['cod_tipo_pack'];
        //     $harvest->cod_tipo_pago = $datosHarvest['cod_tipo_pago'];
        //     $harvest->user_insert = $datosHarvest['user_insert'];
        //     $harvest->user_admin = $datosHarvest['user_insert'];
        //     $harvest->save();
        //     $cod_harvest = $harvest->cod_harvest;

        //     $estadoJob = DB::table('pay_estados_jobs')->select('cod_estado_job')->orderBy('cod_estado_job', 'asc')->limit(1)->get();
        //     $cod_estado_job = $estadoJob[0]->cod_estado_job;

        //     DB::table('pay_jobs_progresos')->insert([
        //         'cod_harvest' => $cod_harvest,
        //         'cod_miscellaneous' => $cod_misceleaneo,
        //         'cod_estado_job' => $cod_estado_job,
        //         'fecha_job' => DB::raw('CURRENT_DATE'),
        //         'hora_inicio' => DB::raw('CURRENT_TIME'),
        //         'hora_final' => DB::raw('CURRENT_TIME'),
        //         'user_insert' =>  $datosHarvest['user_insert']
        //     ]);
        //     try {
        //         foreach ($codigosCampos as $codigoCampo) {
        //             // Code to be executed for each element in the array
        //             DB::table('pay_harvests_fields')->insert([
        //                 'cod_harvest' => $cod_harvest,
        //                 'cod_field' => $codigoCampo
        //             ]);
        //         }
        //     } catch (\Exception $e) {
        //         return response()->json(['code' => 1445, 'message' => 'Failed to register assigned fields'], 404);
        //     }
        //     try {
        //         foreach ($codigosBloques as $codigoBloque) {
        //             DB::table('pay_harvests_blocks')->insert([
        //                 'cod_harvest' => $cod_harvest,
        //                 'cod_block' => $codigoBloque
        //             ]);
        //         }
        //     } catch (\Exception $e) {
        //         return response()->json(['code' => 1446, 'message' => 'Failed to register assigned blocks'], 404);
        //     }
        //     $harvest->cods_fields = $codigosCampos;
        //     $harvest->cods_blocks = $codigosBloques;


        //     $usuarios = DB::table('usu_usuarios')->whereIn('cod_usuario', $codsEmpleados)->select('cod_usuario', 'pin', 'qcpin')->get();
        //     foreach ($usuarios as $usuario) {
        //         $crewId = DB::table('pay_crews')->insertGetId([
        //             'cod_harvest' => $cod_harvest,
        //             'cod_miscellaneous' => $cod_misceleaneo,
        //             'cod_empleado' => $usuario->cod_usuario,
        //             'pin' => $usuario->pin,
        //             'cod_estado_job' => $cod_estado_job,
        //             'qc_pin' => $usuario->qcpin,
        //             'cod_supervisor' => $datosCrew['cod_supervisor'],
        //             'user_insert' => $datosCrew['user_insert']
        //         ]);

        //         DB::table('pay_lista_empleados_jobs')->insert([
        //             'cod_crew' => $crewId,
        //             'pieces' => 0,
        //             'terminado' => 0,
        //             // 'hora_fuerza_terminado' => DB::raw('CURRENT_TIME'),
        //             'GPS' => $datosCrew["latitud"] . ',' . $datosCrew["longitud"],
        //             'user_insert' => $datosCrew['user_insert']
        //         ]);
        //         DB::table('usu_usuarios')
        //             ->where('cod_usuario', $usuario->cod_usuario)
        //             ->update(['disponible' => 0]);
        //     }
        //     if ($datosCrew['crear_nuevo_crew']) {

        //         $ultimoCrewUsado = DB::table('pay_crews_ultimo_usado')->where('cod_supervisor', $datosCrew['cod_supervisor'])->get();

        //         // Eliminar el anterior crew job usado
        //         DB::table('pay_crews_ultimo_usado')->whereIn('cod_crew_ultimo_usado', $ultimoCrewUsado->pluck('cod_crew_ultimo_usado'))->delete();
        //         // Crear el registro del ultimo crew usado
        //         foreach ($usuarios as $usuario) {
        //             DB::table('pay_crews_ultimo_usado')->insert([
        //                 'cod_harvest' => $cod_harvest,
        //                 'cod_miscellaneous' => $cod_misceleaneo,
        //                 'cod_empleado' => $usuario->cod_usuario,
        //                 'pin' => $usuario->pin,
        //                 'qc_pin' => $usuario->qcpin,
        //                 'cod_supervisor' => $datosCrew['cod_supervisor'],
        //                 'user_insert' => $datosCrew['user_insert']
        //             ]);
        //         }
        //         $ultimoCrewUsado = DB::table('pay_crews_ultimo_usado')->where('cod_supervisor', $datosCrew['cod_supervisor'])->get();
        //     }
        //     // return response()->json(['message' => 'CREW Job stored successfully', 'data' => $datosCrew], 200);


        //     return response()->json(['code' => 0, 'message' => 'Success', 'extra' => $cod_harvest], 200);
        // }
        Log::emergency('Request body: ' . json_encode($body));
        return response()->json(['code' => 0, 'message' => 'Success', 'extra' => "destroy"], 200);
    }
}
