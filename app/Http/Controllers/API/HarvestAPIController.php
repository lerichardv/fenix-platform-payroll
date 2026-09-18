<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Harvest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HarvestAPIController extends Controller
{
    /** @var  userRepository */
    private $userRepository;

    /** @var  harvestsRepository */
    private $harvestsRepository;


    public function __construct(User $userRepo, Harvest $harvestRepo)
    {
        $this->userRepository = $userRepo;
        $this->harvestsRepository = $harvestRepo;
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
        $body = $request->all();

        $codigosCampos = json_decode($body['cods_fields'], true);
        $codigosBloques = json_decode($body['cods_blocks'], true);


        $harvest = new Harvest();
        // $harvest->cod_harvest = $body['cod_harvest'];
        $harvest->cod_farm = $body['cod_farm'];
        // $harvest->cod_field = $body['cod_field'];
        // $harvest->cod_bloque = $body['cod_bloque'];
        $harvest->crop_age = $body['crop_age'];
        $harvest->cod_tipo_pack = $body['cod_tipo_pack'];
        $harvest->cod_tipo_pago = $body['cod_tipo_pago'];
        $harvest->user_insert = $body['user_insert'];
        $harvest->save();
        $cod_harvest = $harvest->cod_harvest;


        try {
            foreach ($codigosCampos as $codigoCampo) {
                // Code to be executed for each element in the array
                DB::table('pay_harvests_fields')->insert([
                    'cod_harvest' => $cod_harvest,
                    'cod_field' => $codigoCampo
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to register assigned fields', 'cod_field' => $body['cod_field']], 404);
        }
        try {
            foreach ($codigosBloques as $codigoBloque) {
                DB::table('pay_harvests_blocks')->insert([
                    'cod_harvest' => $cod_harvest,
                    'cod_block' => $codigoBloque
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to register assigned blocks', 'cod_bloque' => $body['cod_bloque']], 404);
        }
        $harvest->cods_fields = $codigosCampos;
        $harvest->cods_blocks = $codigosBloques;
        return response()->json(['message' => 'Harvest stored successfully', 'data' => $harvest], 200);
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
}
