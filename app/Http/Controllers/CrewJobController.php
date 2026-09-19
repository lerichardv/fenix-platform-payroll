<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\HelpController;
use App\Models\Crew;
use App\Models\Harvest;
use App\Models\Miscelaneo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CrewJobController extends Controller
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
        $user_insert = 1;
        $cod_misceleaneo = null;
        $cod_harvest = null;
        $cod_tarea_creada = null;
        $datosCrew = json_decode($body['datosCrew'], true);
        $datosHarvest = json_decode($body['datosHarvest'], true);
        $datosMiscelaneos = json_decode($body['datosMiscelaneos'], true);
        $codsEmpleados = json_decode($datosCrew['cods_empleados'], true);
        $cod_job_local = $datosCrew['cod_tarea'];
        $identificador_unico_local = $datosCrew['identificador_unico_local'];

        // if ($datosHarvest != -1) {
        //     return response()->json(['code' => 1444, 'message' => 'Harvest Tiene datos', 'extra'=>$datosHarvest], 200);
        // } else if ($datosMiscelaneos != -1) {
        //     return response()->json(['code' => 1443, 'message' => 'Miscellaneous Tiene datos', 'extra'=>$datosMiscelaneos], 200);
        // }

        //TODO: VERIFICAR QUE EL COMPORTAMIENTO ES EL ESPERADO (fecha de anotación 13-05-2025)
        $usuarios = DB::table('usu_usuarios')
            ->whereIn('cod_usuario', $codsEmpleados)
            ->where('disponible', 5)
            ->select('cod_usuario', 'disponible')
            ->get();
        HelpController::desconectarBaseDatos();

        //En caso de haber al menos un empleado no disponible se retorna un error
        if (!$usuarios->isEmpty()) {
            // return response()->json(['code' => 1447, 'message' => 'At least one employee is not available', 'extra' => $codsEmpleados], 400);
        } else {
            if ($datosHarvest != -1) {
                $codigosCampos = json_decode($datosHarvest['cods_fields'], true);
                $codigosBloques = json_decode($datosHarvest['cods_blocks'], true);


                $harvest = new Harvest();
                $harvest->cod_farm = $datosHarvest['cod_farm'];
                $harvest->crop_age = $datosHarvest['crop_age'];
                $harvest->cod_plantacion = $datosHarvest['cod_plantacion'] ?? 0;
                $harvest->cod_tipo_pack = $datosHarvest['cod_tipo_pack'];
                $harvest->cod_tipo_pago = $datosHarvest['cod_tipo_pago'];
                $harvest->user_insert = $datosHarvest['user_insert'];
                $harvest->user_admin = $datosHarvest['user_insert'];
                $harvest->save();
                $cod_harvest = $harvest->cod_harvest;
                $cod_tarea_creada = $harvest->cod_harvest;
                $user_insert = $datosHarvest['user_insert'];
                try {
                    $harvestFields = [];
                    foreach ($codigosCampos as $codigoCampo) {
                        $harvestFields[] = [
                            'cod_harvest' => $cod_harvest,
                            'cod_field' => $codigoCampo
                        ];
                    }
                    DB::table('pay_harvests_fields')->insert($harvestFields);
                    HelpController::desconectarBaseDatos();
                } catch (\Exception $e) {
                    return response()->json(['code' => 1445, 'message' => 'Failed to register assigned fields'], 404);
                }
                try {
                    $harvestBlocks = [];
                    foreach ($codigosBloques as $codigoBloque) {
                        $harvestBlocks[] = [
                            'cod_harvest' => $cod_harvest,
                            'cod_block' => $codigoBloque,
                            'cod_plantacion' => $datosHarvest['cod_plantacion'] ?? 0
                        ];
                    }
                    DB::table('pay_harvests_blocks')->insert($harvestBlocks);
                    HelpController::desconectarBaseDatos();
                } catch (\Exception $e) {
                    return response()->json(['code' => 1446, 'message' => 'Failed to register assigned blocks'], 404);
                }
                // $harvest->cods_fields = $codigosCampos;
                // $harvest->cods_blocks = $codigosBloques;
            } else if ($datosMiscelaneos != -1) {
                try {
                    $miscelanos = new Miscelaneo();
                    $miscelanos->cod_location =  $datosMiscelaneos["cod_locacion"];
                    $miscelanos->cod_activity =  $datosMiscelaneos["cod_activdiad"] ?? $datosMiscelaneos["cod_actividad"];
                    $miscelanos->cod_farm =  $datosMiscelaneos["cod_farm"];
                    $miscelanos->crop_age = $datosMiscelaneos['cod_crop_age'];

                    $miscelanos->cod_field =  0;
                    $miscelanos->cod_bloque =  0;
                    $miscelanos->cod_tipo_pago =  $datosMiscelaneos["cod_tipo_pago"];

                    $miscelanos->save();
                    $user_insert = $datosMiscelaneos['user_insert'];

                    $cod_misceleaneo = $miscelanos->cod_miscellaneous;
                    $cod_tarea_creada = $miscelanos->cod_miscellaneous;
                    $codigosCampos = json_decode($datosMiscelaneos['cods_fields'], true);
                    $codigosBloques = json_decode($datosMiscelaneos['cods_blocks'], true);

                    try {
                        $miscelaneosFields = [];
                        foreach ($codigosCampos as $codigoCampo) {
                            $miscelaneosFields[] = [
                                'cod_miscelaneos' => $cod_misceleaneo,
                                'cod_field' => $codigoCampo
                            ];
                        }
                        DB::table('pay_miscelaneos_fields')->insert($miscelaneosFields);
                        HelpController::desconectarBaseDatos();
                    } catch (\Exception $e) {
                        return response()->json(['code' => 1445, 'message' => 'Failed to register assigned fields'], 404);
                    }
                    try {
                        $miscelaneosBlocks = [];
                        foreach ($codigosBloques as $codigoBloque) {
                            $miscelaneosBlocks[] = [
                                'cod_miscelaneos' => $cod_misceleaneo,
                                'cod_block' => $codigoBloque
                            ];
                        }
                        DB::table('pay_miscelaneos_blocks')->insert($miscelaneosBlocks);
                        HelpController::desconectarBaseDatos();
                    } catch (\Exception $e) {
                        return response()->json(['code' => 1446, 'message' => 'Failed to register assigned blocks'], 404);
                    }
                } catch (\Exception $error) {
                    return response()->json(['code' => 1448, 'message' => 'Failed to register miscellaneous', 'error_capturado:' => $error->getMessage()], 404);
                }
            }





            $estadoJob = DB::table('pay_estados_jobs')->select('cod_estado_job')->orderBy('cod_estado_job', 'asc')->limit(1)->get();
            $cod_estado_job = $estadoJob[0]->cod_estado_job;

            DB::table('pay_jobs_progresos')->insert([
                'cod_harvest' => $cod_harvest,
                'cod_miscellaneous' => $cod_misceleaneo,
                'cod_estado_job' => $cod_estado_job,
                'cod_job_local' => $cod_job_local,
                'identificador_unico_local' => $identificador_unico_local,
                'fecha_job' => DB::raw('CURRENT_DATE'),
                'user_insert' =>  $user_insert
            ]);

            HelpController::desconectarBaseDatos();


            $usuarios = DB::table('usu_usuarios')->whereIn('cod_usuario', $codsEmpleados)->select('cod_usuario', 'pin', 'qcpin')->get();
            $crewsData = [];
            $empleadosJobsData = [];
            $usuariosUpdateData = [];

            foreach ($usuarios as $usuario) {
                $crewsData[] = [
                    'cod_harvest' => $cod_harvest,
                    'cod_miscellaneous' => $cod_misceleaneo,
                    'cod_empleado' => $usuario->cod_usuario,
                    'pin' => $usuario->pin,
                    'cod_estado_job' => $cod_estado_job,
                    'qc_pin' => $usuario->qcpin,
                    'cod_supervisor' => $datosCrew['cod_supervisor'],
                    'user_insert' => $datosCrew['user_insert']
                ];
            }

            DB::table('pay_crews')->insert($crewsData);
            HelpController::desconectarBaseDatos();

            $crewIds = DB::table('pay_crews')
                ->where('cod_harvest', $cod_harvest)
                ->where('cod_miscellaneous', $cod_misceleaneo)
                ->pluck('cod_crew');
            HelpController::desconectarBaseDatos();

            foreach ($crewIds as $crewId) {
                $empleadosJobsData[] = [
                    'cod_crew' => $crewId,
                    'pieces' => 0,
                    'terminado' => 0,
                    'GPS' => $datosCrew["latitud"] . ',' . $datosCrew["longitud"],
                    'user_insert' => $datosCrew['user_insert']
                ];
            }

            DB::table('pay_lista_empleados_jobs')->insert($empleadosJobsData);
            HelpController::desconectarBaseDatos();

            foreach ($usuarios as $usuario) {
                $usuariosUpdateData[] = [
                    'cod_usuario' => $usuario->cod_usuario,
                ];
            }

            DB::table('usu_usuarios')
                ->whereIn('cod_usuario', array_column($usuariosUpdateData, 'cod_usuario'))
                ->update(['disponible' => 0]);
            HelpController::desconectarBaseDatos();

            if ($datosCrew['crear_nuevo_crew']) {

                $ultimoCrewUsado = DB::table('pay_crews_ultimo_usado')->where('cod_supervisor', $datosCrew['cod_supervisor'])->get();

                // Eliminar el anterior crew job usado
                DB::table('pay_crews_ultimo_usado')->whereIn('cod_crew_ultimo_usado', $ultimoCrewUsado->pluck('cod_crew_ultimo_usado'))->delete();
                // Crear el registro del ultimo crew usado
                $ultimoCrewUsadoData = [];
                foreach ($usuarios as $usuario) {
                    $ultimoCrewUsadoData[] = [
                        'cod_harvest' => $cod_harvest,
                        'cod_miscellaneous' => $cod_misceleaneo,
                        'cod_empleado' => $usuario->cod_usuario,
                        'pin' => $usuario->pin,
                        'qc_pin' => $usuario->qcpin,
                        'cod_supervisor' => $datosCrew['cod_supervisor'],
                        'user_insert' => $datosCrew['user_insert']
                    ];
                }
                DB::table('pay_crews_ultimo_usado')->insert($ultimoCrewUsadoData);
                //$ultimoCrewUsado = DB::table('pay_crews_ultimo_usado')->where('cod_supervisor', $datosCrew['cod_supervisor'])->get();
            }
            // return response()->json(['message' => 'CREW Job stored successfully', 'data' => $datosCrew], 200);


            return response()->json(['code' => 0, 'message' => 'Success', 'extra' => $cod_tarea_creada], 200);
        }
    }
}
