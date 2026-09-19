<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\HelpController;
use App\Models\Campo;
use App\Models\Granja;
use App\Models\User;
use App\Models\UsuarioGranjas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CamposController extends Controller
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
     * Envia los campos asociados a un cropage especifico y una plantación
     */
    public function campoUsadoEnCropAge(Request $request)
    {


        $body = $request->all();
        $codsCampos = explode(',', $body['codsCampos']); // 16 / [1,5]
        $cod_plantacion = $body['cod_plantacion'] ?? 0; // 6539

        $por_granja = $body['por_granja'] ?? 0; // 1: true, 0: false
        $id_granja = $body['id_granja'] ?? 0; // 2

        $fieldsFiltrado = [];
        $fields = $this->campoRepoRepository->whereIn('cod_field', $codsCampos)->where('activo', 1)->get(['cod_field', 'cod_farm', 'field', 'activo']);

        foreach ($fields as $field) {
            $field->cod_plantacion = (int)$cod_plantacion;
        }

        $fieldsFiltrado = $fields;

        HelpController::desconectarBaseDatos();

        return $fieldsFiltrado;
    }
    /**
     * Store a newly created resource in storage.
     */
    public function obtenerCampos(Request $request)
    {


        $body = $request->all();
        $codsCampos = explode(',', $body['codsCampos']); // 16 / [1,5]
        $cod_plantacion = $body['cod_plantacion'] ?? 0; // 6539

        $por_granja = $body['por_granja'] ?? 0; // 1: true, 0: false
        $id_granja = $body['id_granja'] ?? 0; // 2
        $fieldsFiltrado = [];
        if ($por_granja == 1) {
            $fields = $this->campoRepoRepository->where('cod_farm', $id_granja)->where('activo', 1)->get(['cod_field', 'cod_farm', 'field', 'activo']);
        } else {
            $fields = $this->campoRepoRepository->whereIn('cod_field', $codsCampos)->where('activo', 1)->get(['cod_field', 'cod_farm', 'field', 'activo']);
        }

        foreach ($fields as $field) {
            $field->cod_plantacion = (int)$cod_plantacion;
        }

        $fieldsFiltrado = $fields->sortBy('field')->values();

        HelpController::desconectarBaseDatos();

        return $fieldsFiltrado;
    }
}
