<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\HelpController;
use App\Models\Paquete;
use App\Models\User;
use App\Models\UsuarioGranjas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaquetesController extends Controller
{

    /** @var  userRepository */
    private $userRepository;

    /** @var  usuariGranjaRepository */
    private $usuariGranjaRepository;

    /** @var  tipoPaqueteGranjaRepository */
    private $tipoPaqueteGranjaRepository;


    public function __construct(
        User $userRepo,
        UsuarioGranjas $usuariGranjaRepo,
        Paquete $tipoPaqueteGranjaRepo,

    ) {
        $this->userRepository = $userRepo;
        $this->usuariGranjaRepository = $usuariGranjaRepo;
        $this->tipoPaqueteGranjaRepository = $tipoPaqueteGranjaRepo;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return $this->tipoPaqueteGranjaRepository->all();
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
    public function listaPaquetesAsociadosGranjasLocacionInventario(Request $request)
    {
        $request->validate(
            [
                'cod_farm' => 'required|numeric',
                'cod_categoria' => 'required|numeric',
                'cod_locacion' => 'required|numeric',
            ],
            [
                'required' => 'El campo :attribute es obligatorio.',
                'numeric' => 'El campo :attribute debe ser numérico.'
            ]
        );

        $body = $request->all();
        $cod_farm = $body['cod_farm'];
        $cod_locacion = $body['cod_locacion'];
        $cod_categoria = $body['cod_categoria'] ?? 0;

        $tipoPacks = DB::table('pay_tipo_packs as pack')
            ->join('pay_tipo_paquetes_granjas as pack_granja', 'pack_granja.cod_tipo_pack', '=', 'pack.cod_tipo_pack')
            ->join('pay_tipo_paquetes_locaciones as pack_locacion', 'pack_locacion.cod_tipo_pack', '=', 'pack.cod_tipo_pack')
            ->join('pay_tipo_paquetes_categorias as pack_categoria', 'pack_categoria.cod_tipo_pack', '=', 'pack.cod_tipo_pack')
            ->select(
                'pack.cod_tipo_pack',
                'pack.tipo_pack',
                'pack.cantidad',
                'pack.activo',
                'pack.piece_rate',
                'pack_granja.cod_farm',
                'pack_locacion.cod_locacion',
                'pack_categoria.cod_categoria'
            )
            ->where('pack.activo', 1)
            ->where('pack_granja.activo', 1)
            ->where('pack_categoria.activo', 1)

            ->when($cod_farm != 0, function ($query) use ($cod_farm) {
                return $query->where('pack_granja.cod_farm', $cod_farm);
            })
            ->when($cod_categoria != 0, function ($query) use ($cod_categoria) {
                return $query->where('pack_categoria.cod_categoria', $cod_categoria);
            })
            ->when($cod_locacion != 0, function ($query) use ($cod_locacion) {
                return $query->where('pack_locacion.cod_locacion', $cod_locacion);
            })
            ->get();

        HelpController::desconectarBaseDatos();

        return response()->json($tipoPacks, 200);
    }


    public function crearRegistrosTiposPaquetesAsociados()
    {
        $todosLosRegistrosExitosos = true;
        $cantidadCreados = 0;
        $tipoPacks = DB::table('pay_tipo_packs')->select('cod_tipo_pack')->get();

        foreach ($tipoPacks as $tipoPack) {
            // Do something with $tipoPack->cod_tipo_pack
            $inventarioCategoriasSemillas = DB::table('bw_inventario_categorias_semillas')
                ->select('cod_categoria')
                ->get();

            foreach ($inventarioCategoriasSemillas as $categoria) {
                // Do something with $categoria->cod_categoria
                // Your code here
                $cod_farms = DB::table('far_farms')->select('cod_farms')->get();

                foreach ($cod_farms as $cod_farm) {
                    // Do something with $cod_farm->cod_farms
                    // Your code here
                    $cod_locations = DB::table('far_locations')->select('cod_location')->get();

                    foreach ($cod_locations as $cod_location) {
                        // Do something with $cod_location->cod_location
                        // Your code here
                        DB::table('pay_pack_for_farm_location_category')->insert([
                            'cod_tipo_pack' =>  $tipoPack->cod_tipo_pack,
                            'cod_farm' =>  $cod_farm->cod_farms,
                            'cod_location' =>  $cod_location->cod_location,
                            'cod_categoria' =>  $categoria->cod_categoria
                        ]);
                        $cantidadCreados++;
                    }
                }
            }
        }
        HelpController::desconectarBaseDatos();
        if ($todosLosRegistrosExitosos) {
            return response()->json(['message' => 'Todos los registros se crearon exitosamente.', 'total_creados' => $cantidadCreados], 200);
        } else {
            return response()->json(['message' => 'Algunos registros no se crearon exitosamente.'], 400);
        }
    }
}
