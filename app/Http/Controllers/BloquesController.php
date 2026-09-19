<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\HelpController;
use App\Models\Bloque;
use App\Models\Campo;
use App\Models\Granja;
use App\Models\User;
use App\Models\UsuarioGranjas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BloquesController extends Controller
{


    /** @var  userRepository */
    private $userRepository;
    /** @var  granjaRepository */
    private $granjaRepository;

    /** @var  usuariGranjaRepository */
    private $usuariGranjaRepository;

    /** @var  campoRepoRepository */
    private $campoRepoRepository;

    /** @var  bloqueRepoRepository */
    private $bloqueRepoRepository;

    public function __construct(
        Campo $campoRepo,
        User $userRepo,
        Granja $granjaRepo,
        UsuarioGranjas $usuariGranjaRepo,
        Bloque $bloqueGranjaRepo,
    ) {
        $this->userRepository = $userRepo;
        $this->granjaRepository = $granjaRepo;
        $this->usuariGranjaRepository = $usuariGranjaRepo;
        $this->campoRepoRepository = $campoRepo;
        $this->bloqueRepoRepository = $bloqueGranjaRepo;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return $this->bloqueRepoRepository->all();
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
    public function bloqueUsadoEnCropAge(Request $request)
    {
        $body = $request->all();
        return [$this->bloqueRepoRepository->find($body['cod_bloque'])];
    }

    /**
     * 
     */
    public function bloqueUsadoEnCampoCropAge(Request $request)
    {
        $body = $request->all();
        $codigosCampos = $body['idsCampos'];
        $codigosCampos = json_decode($body['idsCampos']);
        $resultado = $this->bloqueRepoRepository->whereIn('cod_bloque', $codigosCampos)->get();
        return $resultado;
    }

    public function bloquesEnCropAgeAsociadosCampo(Request $request)
    {
        $body = $request->all();
        $cod_farm = $body['cod_farm'] ?? 0;
        $cods_fields = $body['cods_fields'] ?? 0;
        $cod_semilla = $body['cod_semilla'] ?? 0;
        $cod_plantacion = $body['cod_plantacion'] ?? 0;
        $cods_fields = explode(',', $cods_fields);

        HelpController::setDatabaseModeParaAgrupacionesGrandes();
        if ($cod_semilla != 0 && $cod_plantacion != 0) {
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
                ->whereIn('far_crop_bloques_implementados.cod_field', $cods_fields)
                ->where('far_crop_semillas_bloques.cod_semilla', $cod_semilla)
                ->where('far_crop_semillas_bloques.cod_plantacion', $cod_plantacion)
                ->where('far_bloques.activo', 1)
                ->groupBy('far_crop_bloques_implementados.cod_bloque')
                ->distinct()
                ->get();
        } else {
            $bloques = DB::table('far_bloques')
                ->select(
                    'cod_bloque',
                    'cod_farm',
                    'cod_field',
                    'bloque',
                    DB::raw('activo AS disponible')
                )
                ->where('cod_farm', $cod_farm)
                ->whereIn('cod_field', $cods_fields)
                ->where('activo', 1)
                ->orderByRaw('CAST(bloque AS UNSIGNED)')
                ->get();
            // $bloques = DB::table('far_crop_bloques_implementados')
            // ->select(
            //     'far_crop_bloques_implementados.cod_bloque',
            //     'far_bloques.bloque',
            //     'far_crop_bloques_implementados.cod_field',
            //     'far_crop_bloques_implementados.cod_farm',
            //     'far_crop_semillas_bloques.cod_semilla',
            //     'far_crop_semillas_bloques.cod_plantacion',
            // )
            // ->join('far_crop_semillas_bloques', 'far_crop_semillas_bloques.cod_bloque_implementado', '=', 'far_crop_bloques_implementados.cod_bloque_implementado')
            // ->join('far_bloques', 'far_bloques.cod_bloque', '=', 'far_crop_bloques_implementados.cod_bloque')
            // ->where('far_crop_bloques_implementados.cod_farm', $cod_farm)
            // ->whereIn('far_crop_bloques_implementados.cod_field', $cods_fields)
            // ->where('far_bloques.activo', 1)
            // ->groupBy('far_crop_bloques_implementados.cod_bloque')
            // ->distinct()
            // ->get();
        }


        return $bloques;
    }
}
