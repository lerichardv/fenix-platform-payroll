<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActividadesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $actividades = DB::table('pay_activities')
            ->select('cod_activity', 'cod_farms', 'cod_location', 'codigo', 'activity', 'piece_rate')
            ->get();
        return $actividades;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return "ActividadesController@create";
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
        return "ActividadesController@show";
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


    public function listaActividadesPorGranjaLocacion(Request $request)
    {
        $body = $request->all();
        if (isset($body['cod_farm']) && isset($body['cod_location'])) {

            $cod_farms = $body['cod_farm'];
            $cod_location = $body['cod_location'];
            $actividades = DB::table('pay_activities')
                ->select('cod_activity', 'cod_farms', 'cod_location', 'codigo', 'activity', 'piece_rate')
                ->where('pay_activities.activo', 1)
                ->where('pay_activities.visible', 1)
                ->where('cod_farms', $cod_farms)
                ->where('cod_location', $cod_location)
                ->get();
            return $actividades;
        } else {
            return response()->json(['error' => 'Missing cod_farm and cod_location parameters'], 400);
        }
    }
}
