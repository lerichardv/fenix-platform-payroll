<?php

namespace App\Http\Controllers;

use App\Models\Granja;
use App\Models\User;
use App\Models\UsuarioGranjas;
use Illuminate\Http\Request;

class GranjasController extends Controller
{
    /** @var  userRepository */
    private $userRepository;
    /** @var  granjaRepository */
    private $granjaRepository;
    /** @var  granjaRepository */
    private $usuariGranjaRepository;

    public function __construct(User $userRepo, Granja $granjaRepo, UsuarioGranjas $usuariGranjaRepo)
    {
        $this->userRepository = $userRepo;
        $this->granjaRepository = $granjaRepo;
        $this->usuariGranjaRepository = $usuariGranjaRepo;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        // return $this->granjaRepository->all();
        return $this->granjaRepository
            ->join('bw_inventario_estados_plantaciones', 'far_farms.cod_estado', '=', 'bw_inventario_estados_plantaciones.cod_estado')
            ->where('far_farms.activo', 1)
            ->orderBy('farm')
            ->get();;
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
    public function show(string $id_user)
    {
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
        return $this->granjaRepository
            ->whereIn('cod_farms', $codigosGranjas)
            ->where('far_farms.cod_estado', $usuario->cod_estado)
            ->join('bw_inventario_estados_plantaciones', 'far_farms.cod_estado', '=', 'bw_inventario_estados_plantaciones.cod_estado')
            ->orderBy('farm')
            ->get();
        // return $codigosGranjasAsociadas;
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
}
