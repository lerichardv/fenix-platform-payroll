<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Http\Exports\PieceRateReportExcel;
use App\Http\Exports\PieceRateReportCsv;
use Illuminate\Http\Request;
use App\Models\Granja;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ReportePieceRateReportController extends Controller
{
    public function index(){

        return view('admin.reportes.reporte_piece_rate_report')
            ->with('listaGranjas', Granja::todasLasActivas());
    }

    public function obtenerRateReportData(Request $request){

        $codEmpleados = $request->input('employees', []);
        $initial_date = $request->input('start_date');
        $final_date = $request->input('end_date');
        $cropIds = $request->input('crop_ids', []); // Crop IDs
        $farmIds = $request->input('farm_ids', []); // Farm IDs

        $results = $this->generarPieceRateReportData(
            $codEmpleados,
            $initial_date,
            $final_date,
            $farmIds,
            $cropIds
        );

        return response()->json([
            "success" => true,
            "data" => $results
        ]);

    }

    public function generarPieceRateReportData(
        $codEmpleados,
        $initial_date,
        $final_date,
        $farmIds,
        $cropIds
    ){

        if ($initial_date != "" && $final_date != "") {
            $initial_date = Carbon::createFromFormat('m-d-Y', $initial_date)->format('Y-m-d');
            $final_date = Carbon::createFromFormat('m-d-Y', $final_date)->format('Y-m-d');
        }

        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");
 

        $results = DB::table('pay_jobs_progresos')

            ->join('pay_crews', 'pay_crews.cod_harvest', '=', 'pay_jobs_progresos.cod_harvest')
            ->join('pay_lista_empleados_jobs', 'pay_lista_empleados_jobs.cod_crew', '=', 'pay_crews.cod_crew')
            ->join('pay_harvests', 'pay_harvests.cod_harvest', '=', 'pay_jobs_progresos.cod_harvest')
            ->join('far_farms', 'far_farms.cod_farms', '=', 'pay_harvests.cod_farm')
            ->join('usu_usuarios', 'pay_crews.cod_empleado', '=', 'usu_usuarios.cod_usuario')
            ->leftJoin('far_crop_semillas_bloques', 'far_crop_semillas_bloques.cod_semilla_bloque', '=', 'pay_harvests.crop_age')
            ->leftJoin('bw_inventario_semilla', 'bw_inventario_semilla.cod_inventario', '=', 'far_crop_semillas_bloques.cod_semilla')
            ->leftJoin('pay_harvests_blocks', 'pay_harvests_blocks.cod_harvest', '=', 'pay_harvests.cod_harvest')
            ->leftJoin('pay_harvests_fields', 'pay_harvests_fields.cod_harvest', '=', 'pay_harvests.cod_harvest')
            ->leftJoin('far_bloques', 'far_bloques.cod_bloque', '=', 'pay_harvests_blocks.cod_block')
            ->leftJoin('far_fields', 'far_fields.cod_field', '=', 'pay_harvests_fields.cod_field')
            ->leftJoin('pay_tipo_pagos', 'pay_tipo_pagos.cod_tipo_pago', '=', 'pay_harvests.cod_tipo_pago')
            ->leftJoin('pay_tipo_packs', 'pay_harvests.cod_tipo_pack', '=', 'pay_tipo_packs.cod_tipo_pack')
            ->leftJoin('bw_inventario_plantaciones', 'pay_harvests.cod_plantacion', '=', 'bw_inventario_plantaciones.cod_plantacion')
            ->leftJoin('bw_inventario_categorias_semillas', 'bw_inventario_semilla.cod_categoria', '=', 'bw_inventario_categorias_semillas.cod_categoria')
            ->select(
                'pay_harvests.cod_farm',
                'far_farms.farm',
                'pay_jobs_progresos.cod_job',
                'pay_lista_empleados_jobs.cod_crew',
                'pay_lista_empleados_jobs.hora_fuerza_terminado',
                'pay_jobs_progresos.fecha_job',
                'pay_jobs_progresos.cod_estado_job',
                'pay_jobs_progresos.cod_harvest',
                'pay_crews.hora_inicio',
                'pay_crews.hora_final',
                DB::raw("
                    (TIME_TO_SEC(DATE_FORMAT(pay_crews.hora_final, '%H:%i:00')) - TIME_TO_SEC(DATE_FORMAT(pay_crews.hora_inicio, '%H:%i:00'))) * 1000
                    AS diff_in_milliseconds
                "),
                'usu_usuarios.pin',
                DB::raw("CONCAT(usu_usuarios.nombre_1, ' ', usu_usuarios.apellido_1) AS nombre_empleado"),
                DB::raw("COALESCE(DATE_FORMAT(pay_crews.hora_inicio, '%h:%i %p'),'00:00') AS hora_inicio"),
                DB::raw("COALESCE(DATE_FORMAT(pay_crews.hora_final, '%h:%i %p'),'00:00') AS hora_final"),
                DB::raw("COALESCE(DATE_FORMAT(pay_crews.hora_inicio, '%H:%i'),'00:00') AS hora_inicio_sin_formato"),
                DB::raw("COALESCE(DATE_FORMAT(pay_crews.hora_final, '%H:%i'),'00:00') AS hora_final_sin_formato"),
                'pay_crews.cod_crew',
                'pay_crews.cod_empleado',
                'bw_inventario_semilla.nombre_semilla',
                DB::raw('SUM(pay_lista_empleados_jobs.pieces) AS cantidad_escaneo'),
                'pay_harvests.cod_harvest',
                'far_crop_semillas_bloques.cod_semilla_bloque',
                DB::raw("COALESCE(CONCAT(GROUP_CONCAT( DISTINCT far_bloques.bloque SEPARATOR ' - '), ' ', GROUP_CONCAT(DISTINCT far_fields.field SEPARATOR ' - '), ' ', bw_inventario_semilla.nombre_semilla, ' H.',
                    ', ',DATE_FORMAT(pay_jobs_progresos.hora_inicio, '%h:%i %p'), ' - ',DATE_FORMAT(pay_jobs_progresos.hora_final, '%h:%i %p')
                    ),'Missing data') AS job"),
                'pay_tipo_pagos.tipo_pago',
                'pay_tipo_pagos.abreviatura',
                'pay_tipo_packs.cod_tipo_pack',
                'pay_tipo_packs.tipo_pack',
                'pay_tipo_packs.piece_rate',
                'bw_inventario_plantaciones.edad',
                'bw_inventario_categorias_semillas.nombre AS nombre_categoria'
            )
            ->when(count($codEmpleados) > 0, function ($query) use ($codEmpleados) {
                $query->whereIn('pay_crews.cod_empleado', $codEmpleados);
            })
            ->when(count($cropIds) > 0, function ($query) use ($cropIds) {
                $query->whereIn('bw_inventario_semilla.cod_inventario', $cropIds);
            })
            ->when(count($farmIds) > 0, function ($query) use ($farmIds) {
                $query->whereIn('pay_harvests.cod_farm', $farmIds);
            })
            ->where('pay_crews.cod_estado_job', 3)
            ->whereBetween('pay_jobs_progresos.fecha_job', [$initial_date, $final_date])
            ->groupBy('pay_jobs_progresos.cod_job', 'pay_crews.cod_crew')
            ->orderBy('pay_jobs_progresos.fecha_job')
            ->orderBy('nombre_empleado')
            ->get();

        // foreach ($results as $registroHarvest) {
        //     $escaneoTotal = DB::table('pay_lista_empleados_jobs')
        //         ->select(DB::raw('SUM(pieces) AS units'))
        //         ->where('cod_crew', $registroHarvest->cod_crew)
        //         ->first();
        //     $registroHarvest->units = $escaneoTotal->units;
        // }

        $crewIds = $results->pluck('cod_crew')->unique();

        $escaneoTotales = DB::table('pay_lista_empleados_jobs')
            ->select('cod_crew', DB::raw('SUM(pieces) AS units'))
            ->whereIn('cod_crew', $crewIds)
            ->groupBy('cod_crew')
            ->get()
            ->keyBy('cod_crew');

        foreach ($results as $registroHarvest) {
            $registroHarvest->units = $escaneoTotales->get($registroHarvest->cod_crew)->units ?? 0;
            // $registroHarvest->units = 100;
        }


        foreach($results as $result){
            $result->pwhr = $result->diff_in_milliseconds >= 0
                ? number_format($this->milisegundosAHoras($result->diff_in_milliseconds), 2)
                : 0;
            $result->pwhr_horas = $result->diff_in_milliseconds >= 0
                ? $this->convertirMilisegundosAFormato($result->diff_in_milliseconds)
                : 0;
            $result->pwhr_rate = $result->pwhr > 0 ? (number_format($result->units / ($result->pwhr > 0 ? $result->pwhr : 1), 2)) : 0;
            $result->total = number_format($result->units * $result->piece_rate, 2);
        }

        return $results;

    }

    public function obtenerCropsEntreFechas(Request $request){

        $fecha_inicial = $request->input('fecha_inicial') ?? "";
        $fecha_final = $request->input('fecha_final') ?? "";
        $granjas = json_decode($request->input('granjas'));

        if ($fecha_inicial != "" && $fecha_final != "") {
            $fecha_inicial = Carbon::createFromFormat('m-d-Y', $fecha_inicial)
                ->format('Y-m-d');
            $fecha_final = Carbon::createFromFormat('m-d-Y', $fecha_final)
                ->format('Y-m-d');
        }

        $query = DB::table('pay_jobs_progresos as a')
            ->join('pay_harvests as b', 'a.cod_harvest', '=', 'b.cod_harvest')
            ->join('far_crop_semillas_bloques as c', 'b.crop_age', '=', 'c.cod_semilla_bloque')
            ->join('bw_inventario_semilla as d', 'c.cod_semilla', '=', 'd.cod_inventario')
            ->leftJoin('far_farms', 'far_farms.cod_farms', '=', 'b.cod_farm')
            ->where('a.cod_estado_job', 3)
            ->whereBetween('a.fecha_job', [$fecha_inicial, $fecha_final])
            ->groupBy('far_farms.farm', 'b.cod_farm', 'd.nombre_semilla', 'd.cod_inventario')
            ->select('far_farms.farm', 'b.cod_farm', 'd.nombre_semilla', 'd.cod_inventario');

        if(count($granjas) > 0){
            $query->whereIn('b.cod_farm', $granjas);
        }

        $crops = $query->get();

        return response()->json([
            'success' => 'true',
            'crops' => $crops
        ]);
    }

    public function obtenerTodosLosCrops(Request $request){

        $crops = DB::table('bw_inventario_semilla as a')
            ->where('activo', 1)
            ->get();

        return response()->json([
            'success' => 'true',
            'crops' => $crops
        ]);

    }

    public function searchEmpleadosPorGranjaCrop(Request $request){

        $query = $request->input('query') ?? "";
        $fecha_inicial = $request->input('fecha_inicial') ?? "";
        $fecha_final = $request->input('fecha_final') ?? "";
        $cod_granja = json_decode($request->input('cod_farm'));
        $cod_crop = json_decode($request->input('cod_crop'));

        if ($fecha_inicial != "" && $fecha_final != "") {
            $fecha_inicial = Carbon::createFromFormat('m-d-Y', $fecha_inicial)
                ->startOfDay()
                ->format('Y-m-d H:i:s');
            $fecha_final = Carbon::createFromFormat('m-d-Y', $fecha_final)
                ->endOfDay()
                ->format('Y-m-d H:i:s');
        }

        $empleados = DB::table('pay_jobs_progresos')
            ->join('pay_crews', 'pay_crews.cod_harvest', '=', 'pay_jobs_progresos.cod_harvest')
            ->join('pay_lista_empleados_jobs', 'pay_lista_empleados_jobs.cod_crew', '=', 'pay_crews.cod_crew')
            ->join('pay_harvests', 'pay_harvests.cod_harvest', '=', 'pay_jobs_progresos.cod_harvest')
            ->join('far_farms', 'far_farms.cod_farms', '=', 'pay_harvests.cod_farm')
            ->join('usu_usuarios', 'pay_crews.cod_empleado', '=', 'usu_usuarios.cod_usuario')
            ->leftJoin('far_crop_semillas_bloques', 'far_crop_semillas_bloques.cod_semilla_bloque', '=', 'pay_harvests.crop_age')
            ->leftJoin('bw_inventario_semilla', 'bw_inventario_semilla.cod_inventario', '=', 'far_crop_semillas_bloques.cod_semilla')
            ->select([
                'usu_usuarios.cod_usuario',
                'usu_usuarios.pin',
                'usu_usuarios.qcpin',
                'usu_usuarios.nombre_1',
                'usu_usuarios.apellido_1',
                'usu_usuarios.es_veterano',
                DB::raw("CONCAT(usu_usuarios.nombre_1, ' ', usu_usuarios.apellido_1) AS nombre_empleado")
            ]);

        // Filtrar por fecha si se proporciona
        if ($fecha_inicial && $fecha_final) {
            $empleados->whereBetween('pay_jobs_progresos.fecha_job', [$fecha_inicial, $fecha_final]);
        }

        // Filtrar por granjas si hay valores en la lista
        if (!empty($cod_granja)) {
            $empleados->whereIn('far_farms.cod_farms', $cod_granja);
        }

        // Filtrar por cultivos si hay valores en la lista
        if (!empty($cod_crop)) {
            $empleados->whereIn('bw_inventario_semilla.cod_inventario', $cod_crop);
        }

        // Aplicar un filtro de búsqueda general si se proporciona
        // if (!empty($query)) {
        //     $empleados = $empleados->where(function ($q) use ($query) {
        //         $q->where('usu_usuarios.nombre_1', 'like', "%{$query}%")
        //         ->orWhere('usu_usuarios.apellido_1', 'like', "%{$query}%")
        //         ->orWhere('far_farms.farm', 'like', "%{$query}%")
        //         ->orWhere('bw_inventario_semilla.nombre_semilla', 'like', "%{$query}%");
        //     });
        // }

        $result = $empleados->groupBy([
            'usu_usuarios.cod_usuario',
            'usu_usuarios.pin',
            'usu_usuarios.qcpin',
            'usu_usuarios.nombre_1',
            'usu_usuarios.apellido_1',
            'usu_usuarios.es_veterano',
            'nombre_empleado'
        ])
            ->orderBy('nombre_empleado', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'employees' => $result,
        ]);
    }

    public function exportPieceRateFile(Request $request){

        // ini_set('max_execution_time', '300');
        // ini_set('max_input_vars', '5000');
        // ini_set('post_max_size', '20M');

        $employees = json_decode($request->input('employees'));
        $initial_date = $request->input('start_date');
        $final_date = $request->input('end_date');
        $crops = json_decode($request->input('crop_ids')) ?? [];
        $farms = json_decode($request->input('farm_ids')) ?? [];
        $format = $request->input('format');

        $filename = "piece_rate_report_"
            . Carbon::createFromFormat('m-d-Y', $initial_date)->format('Y-m-d')."_"
            . Carbon::createFromFormat('m-d-Y', $final_date)->format('Y-m-d');

        switch($format){
            case 'xlsx':
                return Excel::download(
                    new PieceRateReportExcel(
                        $employees,
                        $initial_date,
                        $final_date,
                        $farms,
                        $crops
                    ),
                    $filename.'.xlsx',
                    null,
                    [
                        'Content-Type' => 'application/octet-stream',
                        'Content-Disposition' => 'attachment; filename="' . $filename . '.xlsx"',
                    ]
                );
            case 'csv':
                // Establecer las cabeceras para la descarga del archivo
                return Excel::download(
                    new PieceRateReportCsv(
                        $employees,
                        $initial_date,
                        $final_date,
                        $farms,
                        $crops
                    ),
                    $filename.'.csv',
                    \Maatwebsite\Excel\Excel::CSV,
                    [
                        'Content-Type' => 'application/octet-stream',
                        'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
                    ]
                );
            default:
                return response('Invalid format');
        }
    }

    function milisegundosAHoras($milisegundos): float {
        // Convertir milisegundos a horas
        $horas = $milisegundos / 3600000; // 3600000 milisegundos = 1 hora

        return $horas;
    }

    function convertirMilisegundosAFormato($milisegundos) {
        // Convertir milisegundos a segundos
        $segundos_totales = $milisegundos / 1000;

        // Calcular horas y minutos
        $horas = floor($segundos_totales / 3600); // 1 hora = 3600 segundos
        $minutos = floor(($segundos_totales % 3600) / 60); // Obtener el residuo en minutos

        // Formatear en H:i
        return sprintf('%02d:%02d', $horas, $minutos);
    }
}
