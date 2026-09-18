<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocacionAPController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $locaciones = DB::table('far_locations')
        ->select('cod_location', 'cod_farms', 'location', 'abreviacion')
        ->get();
        return $locaciones;
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

    public function listaLocacionesPorGranja(string $id)
    {
        $locaciones = DB::table('far_locations')
            ->select('cod_location', 'cod_farms', 'location', 'abreviacion')
            ->where('cod_farms', '=', $id)
            ->get();
            return $locaciones;
    }
}
