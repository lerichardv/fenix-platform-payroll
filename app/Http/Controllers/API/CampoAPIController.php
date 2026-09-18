<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Campo;
use App\Models\Granja;
use App\Models\User;
use App\Models\UsuarioGranjas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CampoAPIController extends Controller
{

    /** @var  userRepository */
    private $userRepository;
    /** @var  granjaRepository */
    private $granjaRepository;

    /** @var  usuariGranjaRepository */
    private $usuariGranjaRepository;

    /** @var  campoRepoRepository */
    private $campoRepoRepository;

    public function __construct(Campo $campoRepo, User $userRepo, Granja $granjaRepo, UsuarioGranjas $usuariGranjaRepo)
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
        return $this->campoRepoRepository->all();
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
    public function campoUsadoEnCropAge(Request $request)
    {


        $body = $request->all();
        $codsCampos = explode(',', $body['codsCampos']);
        $fieldsFiltrado = [];
        $fields = $this->campoRepoRepository->whereIn('cod_field', $codsCampos)->get(['cod_field', 'cod_farm', 'field', 'activo']);
        $alMenosUnBloque =  false;

        foreach ($fields as $field) {
            // Access each field object using $field variable
            // Add your code here
            $codHarvestFarms = DB::table('pay_harvests_fields')
                ->join('pay_jobs_progresos', 'pay_jobs_progresos.cod_harvest', '=', 'pay_harvests_fields.cod_harvest')
                ->join('pay_estados_jobs', 'pay_estados_jobs.cod_estado_job', '=', 'pay_jobs_progresos.cod_estado_job')
                ->select('pay_harvests_fields.cod_harvest_fields', 'pay_harvests_fields.cod_harvest')
                ->where('pay_harvests_fields.cod_field', $field->cod_field)
                ->whereIn('pay_estados_jobs.estado_job', ['lista', 'iniciada'])
                ->get();
            if ($codHarvestFarms->isEmpty()) {
                // Variable $codHarvestFarms is empty
                // Add your code here
                $fieldsFiltrado[] = $field;
            } else {
                foreach ($codHarvestFarms as $codHarvestField) {
                    $alMenosUnBloque =  false;
                    // Variable $codHarvestFarms is not empty
                    $harvests = DB::table('pay_harvests')
                        ->join('far_crop_semillas_bloques', 'far_crop_semillas_bloques.cod_semilla_bloque', '=', 'pay_harvests.crop_age')
                        ->select('pay_harvests.cod_harvest', 'pay_harvests.crop_age', 'far_crop_semillas_bloques.cod_semilla', 'far_crop_semillas_bloques.cod_bloque_implementado')
                        ->where('pay_harvests.cod_harvest', $codHarvestField->cod_harvest)
                        ->get();
                    if (!$harvests->isEmpty()) {
                        foreach ($harvests as $harvest) {
                            $datosSemillas = DB::table('far_crop_semillas_bloques')
                                ->join('far_crop_bloques_implementados', 'far_crop_bloques_implementados.cod_bloque_implementado', '=', 'far_crop_semillas_bloques.cod_bloque_implementado')
                                ->select('far_crop_semillas_bloques.cod_semilla_bloque', 'far_crop_semillas_bloques.cod_bloque_implementado', 'far_crop_semillas_bloques.cod_semilla', 'far_crop_bloques_implementados.cod_bloque')
                                ->where('far_crop_semillas_bloques.cod_semilla', $harvest->cod_semilla)
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
                                        ->get();
                                    foreach ($bloques as $bloque) {
                                        $harvestsBlocks = DB::table('pay_harvests_blocks')
                                            ->join('pay_jobs_progresos', 'pay_jobs_progresos.cod_harvest', '=', 'pay_harvests_blocks.cod_harvest')
                                            ->join('pay_estados_jobs', 'pay_estados_jobs.cod_estado_job', '=', 'pay_jobs_progresos.cod_estado_job')
                                            ->select('pay_harvests_blocks.cod_harvests_blocks', 'pay_harvests_blocks.cod_block', 'pay_harvests_blocks.cod_harvest', 'pay_jobs_progresos.cod_job', 'pay_jobs_progresos.cod_estado_job', 'pay_estados_jobs.estado_job')
                                            ->where('pay_harvests_blocks.cod_block', $bloque->cod_bloque)
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
                $alMenosUnBloque ? $fieldsFiltrado[] = $field : null;
            }
        }

        return $fieldsFiltrado;
    }
}
