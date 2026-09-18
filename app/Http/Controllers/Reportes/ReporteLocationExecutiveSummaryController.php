<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Granja;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Exports\LocationExecutiveSummaryExcel;

class ReporteLocationExecutiveSummaryController extends Controller
{
    public function index(){

        $weeks = [];
        $today = Carbon::today();

        for ($i = 0; $i < 20; $i++) {
            $startOfWeek = $today->copy()->subWeeks($i)->startOfWeek(Carbon::MONDAY);
            $endOfWeek = $startOfWeek->copy()->endOfWeek(Carbon::SUNDAY);

            // Obtener el número de la semana del año
            $weekNumber = $startOfWeek->weekOfYear;

            $weeks[] = (object)[
                'dates' => (object)[
                    'start' => $startOfWeek->format('Y-m-d'),
                    'end' => $endOfWeek->format('Y-m-d'),
                ],
                'identifier' => "Week $weekNumber: " . $startOfWeek->format('Y-m-d') . " - " . $endOfWeek->format('Y-m-d')
            ];
        }

        return view('admin.reportes.reporte_location_executive_summary')
            ->with('listaGranjas', Granja::todasLasActivas())
            ->with('weeks', $weeks);
    }

    public function obtenerLocationExecutiveData(Request $request){

        $week = $request->input('week');
        $farms = $request->input('farms', []);
        $locations = $request->input('locations', []);

        $week = json_decode($week);
        $days = $this->getDaysArray($week);

        $results = $this->generarLocationExecutiveReporteData(
            $days,
            $farms,
            $locations
        );

        return response()->json([
            "success" => true,
            "data" => $results
        ]);

    }

    public function generarLocationExecutiveReporteData($days, $farms, $locations){

        $initial_date = Carbon::createFromFormat('Y-m-d', $days[0])->startOfDay()->format('Y-m-d H:i:s');
        $final_date = Carbon::createFromFormat('Y-m-d', end($days))->endOfDay()->format('Y-m-d H:i:s');

        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");

        // $data = DB::table('pay_jobs_progresos')
        //     ->selectRaw('
        //         IF(pay_jobs_progresos.cod_harvest IS NULL, far_locations.cod_location, harvest_location.cod_location) as cod_location,
        //         IF(pay_jobs_progresos.cod_harvest IS NULL, far_locations.location, harvest_location.location) as location,
        //         far_farms.farm,
        //         CONCAT(IF(pay_jobs_progresos.cod_harvest IS NULL, far_locations.location, harvest_location.location), " - ", far_farms.farm) AS name,
        //         SUM(CASE
        //             WHEN DATE(pay_jobs_progresos.fecha_job) = ? THEN
        //                 (TIME_TO_SEC(DATE_FORMAT(pay_crews.hora_final, "%H:%i:00")) - TIME_TO_SEC(DATE_FORMAT(pay_crews.hora_inicio, "%H:%i:00"))) / 3600
        //             ELSE 0
        //         END) AS dia1,
        //         SUM(CASE
        //             WHEN DATE(pay_jobs_progresos.fecha_job) = ? THEN
        //                 (TIME_TO_SEC(DATE_FORMAT(pay_crews.hora_final, "%H:%i:00")) - TIME_TO_SEC(DATE_FORMAT(pay_crews.hora_inicio, "%H:%i:00"))) / 3600
        //             ELSE 0
        //         END) AS dia2,
        //         SUM(CASE
        //             WHEN DATE(pay_jobs_progresos.fecha_job) = ? THEN
        //                 (TIME_TO_SEC(DATE_FORMAT(pay_crews.hora_final, "%H:%i:00")) - TIME_TO_SEC(DATE_FORMAT(pay_crews.hora_inicio, "%H:%i:00"))) / 3600
        //             ELSE 0
        //         END) AS dia3,
        //         SUM(CASE
        //             WHEN DATE(pay_jobs_progresos.fecha_job) = ? THEN
        //                 (TIME_TO_SEC(DATE_FORMAT(pay_crews.hora_final, "%H:%i:00")) - TIME_TO_SEC(DATE_FORMAT(pay_crews.hora_inicio, "%H:%i:00"))) / 3600
        //             ELSE 0
        //         END) AS dia4,
        //         SUM(CASE
        //             WHEN DATE(pay_jobs_progresos.fecha_job) = ? THEN
        //                 (TIME_TO_SEC(DATE_FORMAT(pay_crews.hora_final, "%H:%i:00")) - TIME_TO_SEC(DATE_FORMAT(pay_crews.hora_inicio, "%H:%i:00"))) / 3600
        //             ELSE 0
        //         END) AS dia5,
        //         SUM(CASE
        //             WHEN DATE(pay_jobs_progresos.fecha_job) = ? THEN
        //                 (TIME_TO_SEC(DATE_FORMAT(pay_crews.hora_final, "%H:%i:00")) - TIME_TO_SEC(DATE_FORMAT(pay_crews.hora_inicio, "%H:%i:00"))) / 3600
        //             ELSE 0
        //         END) AS dia6,
        //         SUM(CASE
        //             WHEN DATE(pay_jobs_progresos.fecha_job) = ? THEN
        //                 (TIME_TO_SEC(DATE_FORMAT(pay_crews.hora_final, "%H:%i:00")) - TIME_TO_SEC(DATE_FORMAT(pay_crews.hora_inicio, "%H:%i:00"))) / 3600
        //             ELSE 0
        //         END) AS dia7,
        //         SUM((TIME_TO_SEC(DATE_FORMAT(pay_crews.hora_final, "%H:%i:00")) - TIME_TO_SEC(DATE_FORMAT(pay_crews.hora_inicio, "%H:%i:00"))) / 3600) AS total
        //     ', $days)
        //     ->join('pay_crews', function($join) {
        //         $join->on('pay_crews.cod_miscellaneous', '=', 'pay_jobs_progresos.cod_miscellaneous')
        //             ->orOn('pay_crews.cod_harvest', '=', 'pay_jobs_progresos.cod_harvest');
        //     })
        //     ->join('pay_lista_empleados_jobs', 'pay_lista_empleados_jobs.cod_crew', '=', 'pay_crews.cod_crew')
        //     ->join('usu_usuarios', 'pay_crews.cod_empleado', '=', 'usu_usuarios.cod_usuario')
        //     ->leftJoin('pay_harvests', 'pay_harvests.cod_harvest', '=', 'pay_crews.cod_harvest')
        //     ->leftJoin('pay_miscellaneous', 'pay_miscellaneous.cod_miscellaneous', '=', 'pay_crews.cod_miscellaneous')
        //     ->leftJoin('far_farms', function($join) {
        //         $join->on('pay_harvests.cod_farm', '=', 'far_farms.cod_farms')
        //             ->orOn('pay_miscellaneous.cod_farm', '=', 'far_farms.cod_farms');
        //     })
        //     ->leftJoin('far_locations', 'pay_miscellaneous.cod_location', '=', 'far_locations.cod_location')
        //     ->leftJoin('far_locations as harvest_location', function($join) {
        //         $join->on('harvest_location.cod_farms', '=', 'pay_harvests.cod_farm')
        //             ->whereIn('harvest_location.location', ['Field-campo', 'Field', 'campo']);
        //     })
        //     ->whereBetween('pay_jobs_progresos.fecha_job', [$initial_date, $final_date])
        //     ->when(count($farms) > 0, function ($query) use ($farms) {
        //         $query->whereIn('far_farms.cod_farms', $farms);
        //     })
        //     ->when(count($locations) > 0, function ($query) use ($locations) {
        //         $query->whereIn(DB::raw('IF(pay_jobs_progresos.cod_harvest IS NULL, far_locations.cod_location, harvest_location.cod_location)'), $locations);
        //     })
        //     ->groupBy('far_farms.farm', 'cod_location');

        $data = DB::table('pay_bitacora_inicio_sesion')
            ->select(
                'pay_bitacora_inicio_sesion.cod_inicio_sesion',
                'far_locations.cod_location',
                'far_locations.location',
                'far_farms.farm',
                DB::raw('CONCAT(far_locations.location, " - ", far_farms.farm) AS name'),
                DB::raw("
                    SUM(CASE WHEN DATE(pay_bitacora_inicio_sesion.fecha_ingreso) = ?
                        THEN (TIME_TO_SEC(DATE_FORMAT(pay_bitacora_inicio_sesion.fecha_egreso, '%H:%i:00')) - TIME_TO_SEC(DATE_FORMAT(pay_bitacora_inicio_sesion.fecha_ingreso, '%H:%i:00'))) / 3600
                        ELSE 0 END) AS dia1
                "),
                DB::raw("
                    SUM(CASE WHEN DATE(pay_bitacora_inicio_sesion.fecha_ingreso) = ?
                        THEN (TIME_TO_SEC(DATE_FORMAT(pay_bitacora_inicio_sesion.fecha_egreso, '%H:%i:00')) - TIME_TO_SEC(DATE_FORMAT(pay_bitacora_inicio_sesion.fecha_ingreso, '%H:%i:00'))) / 3600
                        ELSE 0 END) AS dia2
                "),
                DB::raw("
                    SUM(CASE WHEN DATE(pay_bitacora_inicio_sesion.fecha_ingreso) = ?
                        THEN (TIME_TO_SEC(DATE_FORMAT(pay_bitacora_inicio_sesion.fecha_egreso, '%H:%i:00')) - TIME_TO_SEC(DATE_FORMAT(pay_bitacora_inicio_sesion.fecha_ingreso, '%H:%i:00'))) / 3600
                        ELSE 0 END) AS dia3
                "),
                DB::raw("
                    SUM(CASE WHEN DATE(pay_bitacora_inicio_sesion.fecha_ingreso) = ?
                        THEN (TIME_TO_SEC(DATE_FORMAT(pay_bitacora_inicio_sesion.fecha_egreso, '%H:%i:00')) - TIME_TO_SEC(DATE_FORMAT(pay_bitacora_inicio_sesion.fecha_ingreso, '%H:%i:00'))) / 3600
                        ELSE 0 END) AS dia4
                "),
                DB::raw("
                    SUM(CASE WHEN DATE(pay_bitacora_inicio_sesion.fecha_ingreso) = ?
                        THEN (TIME_TO_SEC(DATE_FORMAT(pay_bitacora_inicio_sesion.fecha_egreso, '%H:%i:00')) - TIME_TO_SEC(DATE_FORMAT(pay_bitacora_inicio_sesion.fecha_ingreso, '%H:%i:00'))) / 3600
                        ELSE 0 END) AS dia5
                "),
                DB::raw("
                    SUM(CASE WHEN DATE(pay_bitacora_inicio_sesion.fecha_ingreso) = ?
                        THEN (TIME_TO_SEC(DATE_FORMAT(pay_bitacora_inicio_sesion.fecha_egreso, '%H:%i:00')) - TIME_TO_SEC(DATE_FORMAT(pay_bitacora_inicio_sesion.fecha_ingreso, '%H:%i:00'))) / 3600
                        ELSE 0 END) AS dia6
                "),
                DB::raw("
                    SUM(CASE WHEN DATE(pay_bitacora_inicio_sesion.fecha_ingreso) = ?
                        THEN (TIME_TO_SEC(DATE_FORMAT(pay_bitacora_inicio_sesion.fecha_egreso, '%H:%i:00')) - TIME_TO_SEC(DATE_FORMAT(pay_bitacora_inicio_sesion.fecha_ingreso, '%H:%i:00'))) / 3600
                        ELSE 0 END) AS dia7
                "),
                DB::raw("
                    SUM((TIME_TO_SEC(DATE_FORMAT(pay_bitacora_inicio_sesion.fecha_egreso, '%H:%i:00')) - TIME_TO_SEC(DATE_FORMAT(pay_bitacora_inicio_sesion.fecha_ingreso, '%H:%i:00'))) / 3600) AS total
                ")
            )
            ->addBinding($days, 'select')
            ->leftJoin('usu_usuarios', 'pay_bitacora_inicio_sesion.cod_usuario', '=', 'usu_usuarios.cod_usuario')
            ->leftJoin('far_farms', 'pay_bitacora_inicio_sesion.cod_farm_ci', '=', 'far_farms.cod_farms')
            ->join('far_locations', function($query){
                $query
                    ->on('pay_bitacora_inicio_sesion.cod_location_ci', '=', 'far_locations.cod_location')
                    ->on('pay_bitacora_inicio_sesion.cod_farm_ci', '=', 'far_locations.cod_farms');
            })
            ->where(DB::raw('DATE(pay_bitacora_inicio_sesion.fecha_ingreso)'), '>=', $initial_date)
            ->where(DB::raw('DATE(pay_bitacora_inicio_sesion.fecha_egreso)'), '<=', $final_date)
            ->when(count($farms) > 0, function ($query) use ($farms) {
                $query->whereIn('pay_bitacora_inicio_sesion.cod_farm_ci', $farms);
            })
            ->when(count($locations) > 0, function ($query) use ($locations) {
                $query->whereIn('pay_bitacora_inicio_sesion.cod_location_ci', $locations);
            })
            ->groupBy('pay_bitacora_inicio_sesion.cod_farm_ci', 'pay_bitacora_inicio_sesion.cod_location_ci');

        // Log::info($data->toRawSql());

        $results = $data->get();

        $activitiesSubquery = "
            (SELECT 2 AS cod_actividad_por_dia, 'Training' AS actividad_por_dia
            UNION ALL
            SELECT 3 AS cod_actividad_por_dia, 'Vacations' AS actividad_por_dia) ta
        ";

        $query = DB::table(DB::raw($activitiesSubquery))
            ->selectRaw('
                ta.cod_actividad_por_dia AS cod_location,
                "Not required" AS location,
                "Not required" AS farm,
                ta.actividad_por_dia AS name,
                COALESCE(SUM(CASE WHEN DATE(pbs.fecha_ingreso) = ? THEN
                    (TIME_TO_SEC(TIME(pbs.fecha_egreso)) - TIME_TO_SEC(TIME(pbs.fecha_ingreso))) / 3600
                    ELSE 0 END), 0) AS dia1,
                COALESCE(SUM(CASE WHEN DATE(pbs.fecha_ingreso) = ? THEN
                    (TIME_TO_SEC(TIME(pbs.fecha_egreso)) - TIME_TO_SEC(TIME(pbs.fecha_ingreso))) / 3600
                    ELSE 0 END), 0) AS dia2,
                COALESCE(SUM(CASE WHEN DATE(pbs.fecha_ingreso) = ? THEN
                    (TIME_TO_SEC(TIME(pbs.fecha_egreso)) - TIME_TO_SEC(TIME(pbs.fecha_ingreso))) / 3600
                    ELSE 0 END), 0) AS dia3,
                COALESCE(SUM(CASE WHEN DATE(pbs.fecha_ingreso) = ? THEN
                    (TIME_TO_SEC(TIME(pbs.fecha_egreso)) - TIME_TO_SEC(TIME(pbs.fecha_ingreso))) / 3600
                    ELSE 0 END), 0) AS dia4,
                COALESCE(SUM(CASE WHEN DATE(pbs.fecha_ingreso) = ? THEN
                    (TIME_TO_SEC(TIME(pbs.fecha_egreso)) - TIME_TO_SEC(TIME(pbs.fecha_ingreso))) / 3600
                    ELSE 0 END), 0) AS dia5,
                COALESCE(SUM(CASE WHEN DATE(pbs.fecha_ingreso) = ? THEN
                    (TIME_TO_SEC(TIME(pbs.fecha_egreso)) - TIME_TO_SEC(TIME(pbs.fecha_ingreso))) / 3600
                    ELSE 0 END), 0) AS dia6,
                COALESCE(SUM(CASE WHEN DATE(pbs.fecha_ingreso) = ? THEN
                    (TIME_TO_SEC(TIME(pbs.fecha_egreso)) - TIME_TO_SEC(TIME(pbs.fecha_ingreso))) / 3600
                    ELSE 0 END), 0) AS dia7,
                COALESCE(SUM((TIME_TO_SEC(TIME(pbs.fecha_egreso)) - TIME_TO_SEC(TIME(pbs.fecha_ingreso))) / 3600), 0) AS total
            ', $days)
            ->leftJoin('pay_bitacora_inicio_sesion as pbs', function ($join) use ($initial_date, $final_date, $farms, $locations) {
                $join->on('ta.cod_actividad_por_dia', '=', 'pbs.cod_actividad_por_dia')
                     ->where('pbs.fecha_ingreso', '>=', $initial_date) // Otra condición con AND
                     ->where('pbs.fecha_egreso', '<=', $final_date) // Otra condición con AND
                     ->when(count($farms) > 0, function ($query) use ($farms) {
                         $query->whereIn('pbs.cod_farm_ci', $farms);
                     })
                    ->when(count($locations) > 0, function ($query) use ($locations) {
                        $query->whereIn('pbs.cod_location_ci', $locations);
                    });
            })
            ->groupBy(['ta.cod_actividad_por_dia', 'ta.actividad_por_dia'])
            ->orderBy('ta.cod_actividad_por_dia');

        $additionalResults = $query->get();

        $merged = $results->merge($additionalResults);

        return $merged;

    }

    public function exportLocationExecutiveSummary(Request $request){

        $week = json_decode($request->input('week'));
        $farms = json_decode($request->input('farms', []));
        $locations = json_decode($request->input('locations', []));
        $format = $request->input('format') ?? 'xlsx';

        $days = $this->getDaysArray($week);

        $initial_date = Carbon::createFromFormat('Y-m-d', $days[0])->startOfDay()->format('Y-m-d H:i:s');
        $final_date = Carbon::createFromFormat('Y-m-d', end($days))->endOfDay()->format('Y-m-d H:i:s');

        $filename = "location_executive_summary_"
            . Carbon::createFromFormat('Y-m-d H:i:s', $initial_date)->format('Y-m-d')."_"
            . Carbon::createFromFormat('Y-m-d H:i:s', $final_date)->format('Y-m-d');

        switch($format){
            case 'xlsx':
                return Excel::download(
                    new LocationExecutiveSummaryExcel(
                        $week,
                        $farms,
                        $locations
                    ),
                    $filename.'.xlsx',
                    null,
                    [
                        'Content-Type' => 'application/octet-stream',
                        'Content-Disposition' => 'attachment; filename="' . $filename . '.xlsx"',
                    ]
                );
            default:
                return response('Invalid format');
        }

    }

    public function getDaysArray($week){
        $days = [];
        $start = Carbon::parse($week->start);
        $end = Carbon::parse($week->end);

        // Loop through the dates
        while ($start->lte($end)) {
            $days[] = $start->format('Y-m-d');
            $start->addDay(); // Increment by one day
        }
        return $days;
    }

}
