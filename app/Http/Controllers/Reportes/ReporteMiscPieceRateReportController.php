<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Http\Exports\MiscPieceRateReportExcel;
use App\Http\Exports\PieceRateReportCsv;
use Illuminate\Http\Request;
use App\Models\Granja;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ReporteMiscPieceRateReportController extends Controller
{
    public function index()
    {

        return view('admin.reportes.reporte_misc_piece_rate_report')
            ->with('listaGranjas', Granja::todasLasActivas());
    }

    public function obtenerMiscRateReportData(Request $request)
    {

        $codEmpleados = $request->input('employees', []);
        $initial_date = $request->input('start_date');
        $final_date = $request->input('end_date');
        $farmIds = $request->input('farm_ids', []); // Farm IDs

        $results = $this->generarMiscPieceRateReportData(
            $codEmpleados,
            $initial_date,
            $final_date,
            $farmIds
        );

        return response()->json([
            "success" => true,
            "data" => $results
        ]);
    }

    public function generarMiscPieceRateReportData(
        $codEmpleados,
        $initial_date,
        $final_date,
        $farmIds
    ) {

        if ($initial_date != "" && $final_date != "") {
            $initial_date = Carbon::createFromFormat('m-d-Y', $initial_date)->format('Y-m-d');
            $final_date = Carbon::createFromFormat('m-d-Y', $final_date)->format('Y-m-d');
        }

        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");
        DB::statement('SET SQL_BIG_SELECTS=1');

        $results = DB::table('pay_jobs_progresos')
            ->join('pay_crews', 'pay_crews.cod_miscellaneous', '=', 'pay_jobs_progresos.cod_miscellaneous')
            ->join('pay_lista_empleados_jobs', 'pay_lista_empleados_jobs.cod_crew', '=', 'pay_crews.cod_crew')
            ->join('pay_miscellaneous', 'pay_miscellaneous.cod_miscellaneous', '=', 'pay_jobs_progresos.cod_miscellaneous')
            ->join('far_farms', 'far_farms.cod_farms', '=', 'pay_miscellaneous.cod_farm')
            ->join('usu_usuarios', 'pay_crews.cod_empleado', '=', 'usu_usuarios.cod_usuario')
            ->join('usu_usuarios as supervisor', 'pay_crews.cod_supervisor', '=', 'supervisor.cod_usuario')
            ->leftJoin('far_crop_semillas_bloques', 'far_crop_semillas_bloques.cod_semilla_bloque', '=', 'pay_miscellaneous.crop_age')
            ->leftJoin('bw_inventario_semilla', 'bw_inventario_semilla.cod_inventario', '=', 'far_crop_semillas_bloques.cod_semilla')
            ->leftJoin('far_locations', 'far_locations.cod_location', '=', 'pay_miscellaneous.cod_location')
            ->leftJoin('pay_miscelaneos_blocks', 'pay_miscelaneos_blocks.cod_miscelaneos', '=', 'pay_miscellaneous.cod_miscellaneous')
            ->leftJoin('pay_miscelaneos_fields', 'pay_miscelaneos_fields.cod_miscelaneos', '=', 'pay_miscellaneous.cod_miscellaneous')
            ->leftJoin('far_bloques', 'far_bloques.cod_bloque', '=', 'pay_miscelaneos_blocks.cod_block')
            ->leftJoin('far_fields', 'far_fields.cod_field', '=', 'pay_miscelaneos_fields.cod_field')
            ->leftJoin('pay_activities', 'pay_activities.cod_activity', '=', 'pay_miscellaneous.cod_activity')
            ->leftJoin('pay_tipo_pagos', 'pay_tipo_pagos.cod_tipo_pago', '=', 'pay_miscellaneous.cod_tipo_pago')
            ->leftJoin('bw_inventario_plantaciones', 'far_crop_semillas_bloques.cod_plantacion', '=', 'bw_inventario_plantaciones.cod_plantacion')
            ->leftJoin('bw_inventario_categorias_semillas', 'bw_inventario_semilla.cod_categoria', '=', 'bw_inventario_categorias_semillas.cod_categoria')
            ->select(
                'pay_miscellaneous.cod_farm',
                'far_farms.cod_farms',
                'far_farms.farm',
                'far_locations.location',
                'pay_jobs_progresos.cod_job',
                'pay_lista_empleados_jobs.cod_crew',
                'pay_lista_empleados_jobs.hora_fuerza_terminado',
                'pay_jobs_progresos.fecha_job',
                'pay_jobs_progresos.cod_estado_job',
                'pay_jobs_progresos.cod_miscellaneous',
                'pay_crews.hora_inicio',
                'pay_crews.hora_final',
                'pay_activities.activity',
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
                'bw_inventario_semilla.codigo_semilla',
                DB::raw('SUM(pay_lista_empleados_jobs.pieces) AS cantidad_escaneo'),
                'pay_miscellaneous.cod_miscellaneous',
                DB::raw("COALESCE(
                    CONCAT(GROUP_CONCAT(DISTINCT far_bloques.bloque SEPARATOR ' - '), ' ', GROUP_CONCAT(DISTINCT far_fields.field SEPARATOR ' - '), ' M.', ', ',DATE_FORMAT(pay_jobs_progresos.hora_inicio, '%h:%i %p'), ' - ',DATE_FORMAT(pay_jobs_progresos.hora_final, '%h:%i %p')),
                    CONCAT(far_locations.location,' ',pay_activities.activity, ' ', ' M.', ', ',DATE_FORMAT(pay_jobs_progresos.hora_inicio, '%h:%i %p'), ' - ',DATE_FORMAT(pay_jobs_progresos.hora_final, '%h:%i %p'))) AS job"),
                'pay_tipo_pagos.tipo_pago',
                'far_fields.field',
                'far_bloques.bloque',
                'far_bloques.acres',
                'supervisor.nombre_1 as usuario_supervisor',
                'pay_activities.piece_rate as pay_rate',
                'bw_inventario_plantaciones.edad',
                'bw_inventario_categorias_semillas.nombre AS nombre_categoria',
                DB::raw('GROUP_CONCAT(DISTINCT IF(far_fields.field IS NULL, "n/a", far_fields.field) SEPARATOR ", ") as fields'),
                DB::raw('GROUP_CONCAT(DISTINCT IF(far_bloques.bloque IS NULL, "n/a", far_bloques.bloque) SEPARATOR ", ") as bloques'),
                DB::raw('SUM(IF(far_bloques.acres IS NULL, 0, far_bloques.acres)) as acreage_back'),
                DB::raw('SUM(far_crop_semillas_bloques.acres_usados) as acreage')
            )
            ->when(count($codEmpleados) > 0, function ($query) use ($codEmpleados) {
                $query->whereIn('pay_crews.cod_empleado', $codEmpleados);
            })
            ->when(count($farmIds) > 0, function ($query) use ($farmIds) {
                $query->whereIn('pay_miscellaneous.cod_farm', $farmIds);
            })
            ->where('pay_crews.cod_estado_job', 3)
            ->whereBetween('pay_jobs_progresos.fecha_job', [$initial_date, $final_date])
            ->groupBy('pay_jobs_progresos.cod_job', 'pay_crews.cod_crew')
            ->orderBy('pay_jobs_progresos.fecha_job')
            ->orderBy('nombre_empleado')
            ->get();

        $cod_crews = "";
        $cod_crews = implode(", ", $results->pluck('cod_crew')->toArray());

        $escaneoTotales = DB::table('pay_lista_empleados_jobs')
            ->select('cod_crew', DB::raw('SUM(pieces) AS units'))
            ->whereIn('cod_crew', $results->pluck('cod_crew')->toArray())
            ->groupBy('cod_crew')
            ->get()
            ->keyBy('cod_crew');

        $escaneosOriginales = DB::table('pay_bitacora_escaneos_realizados')
            ->select('cod_crew', DB::raw('COALESCE(SUM(pieces),0) AS units_original'))
            ->whereIn('cod_crew', $results->pluck('cod_crew')->toArray())
            ->groupBy('cod_crew')
            ->get()
            ->keyBy('cod_crew');




        $acresResults = DB::table('far_crop_semillas_bloques')
            ->select('far_crop_semillas_bloques.cod_semilla', 'bw_inventario_plantaciones.edad', 'far_crop_bloques_implementados.cod_farm', DB::raw('sum(far_crop_semillas_bloques.acres_usados) as suma_acres_usados'))
            ->join('far_crop_bloques_implementados', 'far_crop_bloques_implementados.cod_bloque_implementado', '=', 'far_crop_semillas_bloques.cod_bloque_implementado')
            ->join('bw_inventario_plantaciones', 'bw_inventario_plantaciones.cod_plantacion', '=', 'far_crop_semillas_bloques.cod_plantacion')
            ->whereIn('far_crop_semillas_bloques.cod_semilla', $results->pluck('codigo_semilla')->toArray())
            ->whereIn('far_crop_bloques_implementados.cod_farm', $results->pluck('cod_farms')->toArray())
            ->where('far_crop_semillas_bloques.completada', 0)
            ->groupBy('far_crop_semillas_bloques.cod_semilla', 'bw_inventario_plantaciones.edad', 'far_crop_bloques_implementados.cod_farm')
            ->get()
            ->keyBy(function ($item) {
                return $item->cod_semilla . '-' . $item->edad . '-' . $item->cod_farm;
            });

        $miscellaneousAcres = DB::table('pay_miscelaneos_blocks AS mis_block')
            ->select(
                'mis_block.cod_miscelaneos',
                DB::raw('SUM(far_block.acres) AS suma_acres_usados')
            )
            ->join('far_bloques AS far_block', 'far_block.cod_bloque', '=', 'mis_block.cod_block')
            ->whereIn('mis_block.cod_miscelaneos', $results->pluck('cod_miscellaneous')->toArray())
            ->groupBy('mis_block.cod_miscelaneos')
            ->get()
            ->keyBy('cod_miscelaneos');


        foreach ($results as $registroMisc) {
            $registroMisc->units = $escaneoTotales[$registroMisc->cod_crew]->units ?? 0;
            $registroMisc->units_original = $escaneosOriginales[$registroMisc->cod_crew]->units_original ?? 0;
            $key = $registroMisc->codigo_semilla . '-' . $registroMisc->edad . '-' . $registroMisc->cod_farms;
            $registroMisc->acreage = $acresResults[$key]->suma_acres_usados ?? $miscellaneousAcres[$registroMisc->cod_miscellaneous]->suma_acres_usados ?? 0;
        }

        foreach ($results as $result) {
            $result->pwhr = $result->diff_in_milliseconds >= 0
                ? number_format($this->milisegundosAHoras($result->diff_in_milliseconds), 2)
                : 0;
            $result->pwhr_horas = $result->diff_in_milliseconds >= 0
                ? $this->convertirMilisegundosAFormato($result->diff_in_milliseconds)
                : 0;
            $result->pwhr_rate = $result->pwhr > 0 ? (number_format($result->units / ($result->pwhr > 0 ? $result->pwhr : 1), 2)) : 0;
            $result->total = number_format($result->units * $result->pay_rate, 2);
        }

        return $results;
    }

    public function searchEmpleadosPorGranjaMisc(Request $request)
    {

        // $query = $request->input('query') ?? "";
        $fecha_inicial = $request->input('fecha_inicial') ?? "";
        $fecha_final = $request->input('fecha_final') ?? "";
        $cod_granja = json_decode($request->input('cod_farm'));
        // $cod_crop = json_decode($request->input('cod_crop'));

        if ($fecha_inicial != "" && $fecha_final != "") {
            $fecha_inicial = Carbon::createFromFormat('m-d-Y', $fecha_inicial)
                ->startOfDay()
                ->format('Y-m-d H:i:s');
            $fecha_final = Carbon::createFromFormat('m-d-Y', $fecha_final)
                ->endOfDay()
                ->format('Y-m-d H:i:s');
        }

        $empleados = DB::table('pay_jobs_progresos')
            ->join('pay_crews', 'pay_crews.cod_miscellaneous', '=', 'pay_jobs_progresos.cod_miscellaneous')
            ->join('pay_lista_empleados_jobs', 'pay_lista_empleados_jobs.cod_crew', '=', 'pay_crews.cod_crew')
            ->join('pay_miscellaneous', 'pay_miscellaneous.cod_miscellaneous', '=', 'pay_jobs_progresos.cod_miscellaneous')
            ->join('far_farms', 'far_farms.cod_farms', '=', 'pay_miscellaneous.cod_farm')
            ->join('usu_usuarios', 'pay_crews.cod_empleado', '=', 'usu_usuarios.cod_usuario')
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

    public function exportMiscPieceRateFile(Request $request)
    {

        // ini_set('max_execution_time', '300');
        // ini_set('max_input_vars', '5000');
        // ini_set('post_max_size', '20M');

        $employees = json_decode($request->input('employees'));
        $initial_date = $request->input('start_date');
        $final_date = $request->input('end_date');
        // $crops = json_decode($request->input('crop_ids')) ?? [];
        $farms = json_decode($request->input('farm_ids')) ?? [];
        $format = $request->input('format');

        $filename = "piece_rate_report_"
            . Carbon::createFromFormat('m-d-Y', $initial_date)->format('Y-m-d') . "_"
            . Carbon::createFromFormat('m-d-Y', $final_date)->format('Y-m-d');

        switch ($format) {
            case 'xlsx':
                return Excel::download(
                    new MiscPieceRateReportExcel(
                        $employees,
                        $initial_date,
                        $final_date,
                        $farms
                        // $crops
                    ),
                    $filename . '.xlsx',
                    null,
                    [
                        'Content-Type' => 'application/octet-stream',
                        'Content-Disposition' => 'attachment; filename="' . $filename . '.xlsx"',
                    ]
                );
                // case 'csv':
                //     // Establecer las cabeceras para la descarga del archivo
                //     return Excel::download(
                //         new PieceRateReportCsv(
                //             $employees,
                //             $initial_date,
                //             $final_date,
                //             $farms,
                //             $crops
                //         ),
                //         $filename.'.csv',
                //         \Maatwebsite\Excel\Excel::CSV,
                //         [
                //             'Content-Type' => 'application/octet-stream',
                //             'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
                //         ]
                //     );
            default:
                return response('Invalid format');
        }
    }

    function milisegundosAHoras($milisegundos): float
    {
        // Convertir milisegundos a horas
        $horas = $milisegundos / 3600000; // 3600000 milisegundos = 1 hora

        return $horas;
    }

    function convertirMilisegundosAFormato($milisegundos)
    {
        // Convertir milisegundos a segundos
        $segundos_totales = $milisegundos / 1000;

        // Calcular horas y minutos
        $horas = floor($segundos_totales / 3600); // 1 hora = 3600 segundos
        $minutos = floor(($segundos_totales % 3600) / 60); // Obtener el residuo en minutos

        // Formatear en H:i
        return sprintf('%02d:%02d', $horas, $minutos);
    }
}
