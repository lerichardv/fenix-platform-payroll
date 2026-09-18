<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\HelpController;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Granja;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Exports\LocationExecutiveSummaryExcel;
use App\Http\Exports\SignSheetSummaryExcel;

class ReporteSingSheetsController extends Controller
{
    public function index()
    {

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

        return view('admin.reportes.reporte_sign _sheets')
            ->with('listaGranjas', Granja::todasLasActivas())
            ->with('weeks', $weeks);
    }



    public function exportLocationExecutiveSummary(Request $request)
    {

        $week = json_decode($request->input('week'));
        $farms = json_decode($request->input('farms', []));
        $locations = json_decode($request->input('locations', []));
        $format = $request->input('format') ?? 'xlsx';

        $days = $this->getDaysArray($week);

        $initial_date = Carbon::createFromFormat('Y-m-d', $days[0])->startOfDay()->format('Y-m-d H:i:s');
        $final_date = Carbon::createFromFormat('Y-m-d', end($days))->endOfDay()->format('Y-m-d H:i:s');

        $filename = "location_executive_summary_"
            . Carbon::createFromFormat('Y-m-d H:i:s', $initial_date)->format('Y-m-d') . "_"
            . Carbon::createFromFormat('Y-m-d H:i:s', $final_date)->format('Y-m-d');

        switch ($format) {
            case 'xlsx':
                return Excel::download(
                    new LocationExecutiveSummaryExcel(
                        $week,
                        $farms,
                        $locations
                    ),
                    $filename . '.xlsx',
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

    public function exportSignSheetReport(Request $request)
    {

        $week = json_decode($request->input('week'));
        $farms = json_decode($request->input('farms', []));
        $locations = json_decode($request->input('locations', []));
        $format = $request->input('format') ?? 'xlsx';

        $farms = array_map('intval', $farms);
        $locations = array_map('intval', $locations);

        $categorias_empleados = $request->input('categorias_empleados', []);
        $empleadosSeleccionados = [];

        $codigoUsuarios = $request->get('codigo_usuarios');
        if (!is_array($codigoUsuarios)) {
            $codigoUsuarios = json_decode($codigoUsuarios, true);
        }
        if (!is_array($categorias_empleados)) {
            $categorias_empleados = explode(',', $categorias_empleados);
        }
        // return $categorias_empleados;
        foreach ($codigoUsuarios ?? [] as $empleadoSeleccionado) {
            $empleadosSeleccionados[] = $empleadoSeleccionado['cod_usuario'];
        }
        $days = $this->getDaysArray($week);

        $initial_date = Carbon::createFromFormat('Y-m-d', $days[0])->startOfDay()->format('Y-m-d H:i:s');
        $final_date = Carbon::createFromFormat('Y-m-d', end($days))->endOfDay()->format('Y-m-d H:i:s');

        $filename = "sign_sheets_summary_"
            . Carbon::createFromFormat('Y-m-d H:i:s', $initial_date)->format('Y-m-d') . "_"
            . Carbon::createFromFormat('Y-m-d H:i:s', $final_date)->format('Y-m-d');

        switch ($format) {
            case 'xlsx':
                return Excel::download(
                    new SignSheetSummaryExcel(
                        $week,
                        $farms,
                        $locations,
                        $empleadosSeleccionados,
                        $categorias_empleados
                    ),
                    $filename . '.xlsx',
                    null,
                    [
                        'Content-Type' => 'application/octet-stream',
                        'Content-Disposition' => 'attachment; filename="' . $filename . '.xlsx"',
                    ]
                );
            default:
                return response('Invalid format');
        }

        // return response()->json([
        //     'week' => $week,
        //     'farms' => $farms,
        //     'locations' => $locations,
        //     'empleadosSeleccionados' => $empleadosSeleccionados,
        //     'codigoUsuarios' => $codigoUsuarios,
        //     'request' => $request->all(),
        //     'categorias_empleados' => $categorias_empleados
        // ]);
    }

    public function getDaysArray($week)
    {
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

    public function obtenerSignSheetReport(Request $request)
    {
        $week = $request->input('week');
        $farms = $request->input('farms', []);
        $locations = $request->input('locations', []);
        $categorias_empleados = $request->input('categorias_empleados', []);
        $empleadosSeleccionados = [];
        foreach ($request->get('codigo_usuarios') ?? [] as $empleadoSeleccionado) {
            $empleadosSeleccionados[] = $empleadoSeleccionado['cod_usuario'];
        }

        $week = json_decode($week);
        $days = $this->getDaysArray($week);
        // return $locations;
        // return $empleadosSeleccionados;
        // die();
        $farms = array_map('intval', $farms);
        $locations = array_map('intval', $locations);
        $results = $this->generarSignSheetReportData(
            $days,
            $farms,
            $locations,
            $empleadosSeleccionados,
            $categorias_empleados,
        );

        return response()->json([
            "success" => true,
            "data" => $results
        ]);
    }

    public function generarSignSheetReportData($days, $farms, $locations, $empleadosSeleccionados, $categorias_empleados, $realizarAgrupacionExtendida = true)
    {



        $initial_date = Carbon::createFromFormat('Y-m-d', $days[0])->startOfDay()->format('Y-m-d H:i:s');
        $final_date = Carbon::createFromFormat('Y-m-d', end($days))->endOfDay()->format('Y-m-d H:i:s');

        HelpController::setDatabaseModeParaAgrupacionesGrandes();

        $favoritos = DB::table('usu_usuarios_favorito_granja_locacion as favori')
            ->select('favori.cod_usuario', 'favori.cod_locacion', 'favori.cod_farm')
            // ->whereIn('favori.cod_usuario', $empleadosSeleccionados)
            ->when(count($empleadosSeleccionados) > 0, function ($query) use ($empleadosSeleccionados) {
                $query->whereIn('favori.cod_usuario', $empleadosSeleccionados);
            })
            ->whereIn('favori.cod_farm', $farms)
            ->whereIn('favori.cod_locacion', $locations)
            // ->when(count($locations) > 0, function ($query) use ($locations) {
            //     $query->whereIn('favori.cod_locacion', $locations);
            // })
            ->groupBy('favori.cod_usuario', 'favori.cod_locacion', 'favori.cod_farm')
            ->get();

        $empleadosNoDeseados = [];
        $empleadosFavoritos = [];
        $empleadosFavoritosFiltrados = [];
        $granjasFavoritos = [];
        $locacionesFavoritos = [];
        $alMenosUnEmpleadoSeleccionado = empty($empleadosSeleccionados);
        // return $favoritos;
        // die();
        foreach ($favoritos as $favorito) {

            // Pasar al siguiente elemento cuando el empleado no esta como favorito en la granja seleccionada
            if (!in_array($favorito->cod_farm, $farms) || !in_array($favorito->cod_locacion, $locations)) {
                $empleadosSeleccionados = array_diff($empleadosSeleccionados, [$favorito->cod_usuario]);
                $empleadosNoDeseados[] = $favorito->cod_usuario;
                // return $empleadosNoDeseados;
                // die();

                continue;
            }
            if (!$alMenosUnEmpleadoSeleccionado) {

                if (in_array($favorito->cod_usuario, $empleadosSeleccionados)) {
                    $empleadosFavoritos[] = $favorito->cod_usuario;
                    $empleadosFavoritosFiltrados[] = $favorito->cod_usuario;
                    $granjasFavoritos[] = $favorito->cod_farm;
                    $locacionesFavoritos[] = $favorito->cod_locacion;
                } else {
                    // $empleadosSeleccionados = array_diff($empleadosSeleccionados, [$favorito->cod_usuario]);
                    // $empleadosNoDeseados[] = $favorito->cod_usuario;

                }
            } else {
                $empleadosSeleccionados[] = $favorito->cod_usuario;
                $empleadosFavoritos[] = $favorito->cod_usuario;
                $granjasFavoritos[] = $favorito->cod_farm;
                $empleadosFavoritosFiltrados[] = $favorito->cod_usuario;
                // $empleadosNoDeseados[] = $favorito->cod_usuario;
                $locacionesFavoritos[] = $favorito->cod_locacion;
            }
        }


        $query = DB::table('pay_bitacora_inicio_sesion as bita')
            ->select(
                'bita.cod_usuario',
                // DB::raw("true as mostrar"),
                DB::raw("CONCAT(usu.nombre_1, ' ', usu.apellido_1) AS name"),
                DB::raw("CONCAT(usu.nombre_1, ' ', usu.apellido_1) AS es_favorito"),
                DB::raw("IF(favori.cod_usuario IS NULL, false, true) AS mostrar"),
                DB::raw("CONCAT(COALESCE(usu.nombre_1, ''),
                            ' ',
                            COALESCE(usu.nombre_2, ''),
                            ' ',
                            COALESCE(usu.apellido_1, ''),
                            ' ',
                            COALESCE(usu.apellido_2, '')) AS full_name"),
                'usu.pin',
                'usu.es_veterano',
                DB::raw("GROUP_CONCAT(usu.es_veterano SEPARATOR ', ') AS es_veterano_concat"),
                DB::raw("GROUP_CONCAT(bita.lunch_acreditado SEPARATOR ', ') AS lunch_acreditado_concat"),
                DB::raw("CONCAT( GROUP_CONCAT(bita.lunch_acreditado SEPARATOR ', '), ' ',GROUP_CONCAT(bita.cod_inicio_sesion SEPARATOR ', ')) AS lunch_acreditado_concat_espacial"),
                DB::raw("GROUP_CONCAT(bita.lunch_automatico SEPARATOR ', ') AS lunch_automatico_concat"),
                DB::raw("GROUP_CONCAT(bita.cod_inicio_sesion SEPARATOR ', ') AS cod_inicio_sesion_concat"),
                'far.farm AS granja',
                'locat.location AS locacion',
                DB::raw("
                    ROUND(SUM(CASE 
                        WHEN DATE(bita.fecha_ingreso) = ? THEN 
                            (TIME_TO_SEC(DATE_FORMAT(bita.fecha_egreso, '%H:%i:00')) - TIME_TO_SEC(DATE_FORMAT(bita.fecha_ingreso, '%H:%i:00'))) / 3600 
                            - (CASE 
                                WHEN (usu.es_veterano = 0 OR usu.es_veterano = 2) AND (bita.lunch_automatico = 1 OR bita.lunch_acreditado = 1) THEN 0.5 
                                ELSE 0 
                            END)
                        ELSE 0 
                    END), 2) AS dia1
                "),
                DB::raw("
                    ROUND(SUM(CASE 
                        WHEN DATE(bita.fecha_ingreso) = ? THEN 
                            (TIME_TO_SEC(DATE_FORMAT(bita.fecha_egreso, '%H:%i:00')) - TIME_TO_SEC(DATE_FORMAT(bita.fecha_ingreso, '%H:%i:00'))) / 3600 
                            - (CASE 
                                WHEN  (usu.es_veterano = 0 OR usu.es_veterano = 2) AND (bita.lunch_automatico = 1 OR bita.lunch_acreditado = 1) THEN 0.5 
                                ELSE 0 
                            END)
                        ELSE 0 
                    END), 2) AS dia2
                "),
                DB::raw("
                    ROUND(SUM(CASE 
                        WHEN DATE(bita.fecha_ingreso) = ? THEN 
                            (TIME_TO_SEC(DATE_FORMAT(bita.fecha_egreso, '%H:%i:00')) - TIME_TO_SEC(DATE_FORMAT(bita.fecha_ingreso, '%H:%i:00'))) / 3600 
                            - (CASE 
                                WHEN (usu.es_veterano = 0 OR usu.es_veterano = 2) AND (bita.lunch_automatico = 1 OR bita.lunch_acreditado = 1) THEN 0.5 
                                ELSE 0 
                            END)
                        ELSE 0 
                    END), 2) AS dia3
                "),
                DB::raw("
                    ROUND(SUM(CASE 
                        WHEN DATE(bita.fecha_ingreso) = ? THEN 
                            (TIME_TO_SEC(DATE_FORMAT(bita.fecha_egreso, '%H:%i:00')) - TIME_TO_SEC(DATE_FORMAT(bita.fecha_ingreso, '%H:%i:00'))) / 3600 
                            - (CASE 
                                WHEN (usu.es_veterano = 0 OR usu.es_veterano = 2) AND (bita.lunch_automatico = 1 OR bita.lunch_acreditado = 1) THEN 0.5 
                                ELSE 0 
                            END)
                        ELSE 0 
                    END), 2) AS dia4
                "),
                DB::raw("
                    ROUND(SUM(CASE 
                        WHEN DATE(bita.fecha_ingreso) = ? THEN 
                            (TIME_TO_SEC(DATE_FORMAT(bita.fecha_egreso, '%H:%i:00')) - TIME_TO_SEC(DATE_FORMAT(bita.fecha_ingreso, '%H:%i:00'))) / 3600 
                            - (CASE 
                                WHEN (usu.es_veterano = 0 OR usu.es_veterano = 2) AND (bita.lunch_automatico = 1 OR bita.lunch_acreditado = 1) THEN 0.5 
                                ELSE 0 
                            END)
                        ELSE 0 
                    END), 2) AS dia5
                "),
                DB::raw("
                    ROUND(SUM(CASE 
                        WHEN DATE(bita.fecha_ingreso) = ? THEN 
                            (TIME_TO_SEC(DATE_FORMAT(bita.fecha_egreso, '%H:%i:00')) - TIME_TO_SEC(DATE_FORMAT(bita.fecha_ingreso, '%H:%i:00'))) / 3600 
                            - (CASE 
                                WHEN (usu.es_veterano = 0 OR usu.es_veterano = 2) AND (bita.lunch_automatico = 1 OR bita.lunch_acreditado = 1) THEN 0.5 
                                ELSE 0 
                            END)
                        ELSE 0 
                    END), 2) AS dia6
                "),
                DB::raw("
                    ROUND(SUM(CASE 
                        WHEN DATE(bita.fecha_ingreso) = ? THEN 
                            (TIME_TO_SEC(DATE_FORMAT(bita.fecha_egreso, '%H:%i:00')) - TIME_TO_SEC(DATE_FORMAT(bita.fecha_ingreso, '%H:%i:00'))) / 3600 
                            - (CASE 
                                WHEN (usu.es_veterano = 0 OR usu.es_veterano = 2) AND (bita.lunch_automatico = 1 OR bita.lunch_acreditado = 1) THEN 0.5 
                                ELSE 0 
                            END)
                        ELSE 0 
                    END), 2) AS dia7
                "),
                DB::raw("
                    ROUND(SUM((TIME_TO_SEC(DATE_FORMAT(bita.fecha_egreso, '%H:%i:00')) - TIME_TO_SEC(DATE_FORMAT(bita.fecha_ingreso, '%H:%i:00'))) / 3600 
                    - (CASE 
                        WHEN (usu.es_veterano = 0 OR usu.es_veterano = 2) AND (bita.lunch_automatico = 1 OR bita.lunch_acreditado = 1) THEN 0.5 
                        ELSE 0 
                    END)), 2) AS total 
                ")
            )
            ->addBinding($days, 'select')
            ->leftJoin('usu_usuarios as usu', 'bita.cod_usuario', '=', 'usu.cod_usuario')
            ->leftJoin('far_farms AS far', 'far.cod_farms', '=', 'bita.cod_farm_ci')
            ->leftJoin('far_locations AS locat', 'locat.cod_location', '=', 'bita.cod_location_ci')
            ->leftJoin('usu_usuarios_favorito_granja_locacion AS favori', function ($join) {
                $join->on('favori.cod_usuario', '=', 'bita.cod_usuario')
                    ->on('favori.cod_farm', '=', 'bita.cod_farm_ci')
                    ->on('favori.cod_locacion', '=', 'bita.cod_location_ci');
            })
            ->where(DB::raw('DATE(bita.fecha_ingreso)'), '>=', $initial_date)
            ->where(DB::raw('DATE(bita.fecha_egreso)'), '<=', $final_date)
            ->when(count($empleadosSeleccionados) > 0, function ($query) use ($empleadosSeleccionados) {
                $query->whereIn('bita.cod_usuario', $empleadosSeleccionados);
            })
            ->when(count($empleadosNoDeseados) > 0, function ($query) use ($empleadosNoDeseados) {
                $query->whereNotIn('bita.cod_usuario', $empleadosNoDeseados);
            })
            ->whereIn('usu.es_veterano', $categorias_empleados)
            ->when(count($locacionesFavoritos) > 0, function ($query) use ($locacionesFavoritos) {
                $query->whereIn('bita.cod_location_ci', $locacionesFavoritos);
            })
            ->when(function ($query) use ($granjasFavoritos) {
                $query->whereIn('bita.cod_farm_ci', $granjasFavoritos);
            })
            ->groupBy('bita.cod_usuario')
            ->when($realizarAgrupacionExtendida, function ($query) {
                $query->groupBy('far.farm', 'locat.location', 'bita.cod_usuario');
            })
            ->when(!$realizarAgrupacionExtendida, function ($query) {
                $query->groupBy('bita.cod_usuario');
            })
            ->orderBy('usu.nombre_1');

        $dataReporte = $query->get();
        if (!empty($empleadosFavoritos)) {
            $dataReporteArray = $dataReporte->toArray();
            usort($dataReporteArray, function ($a, $b) {
                return strcmp($a->full_name, $b->full_name);
            });

            $arrayEmpleadosObtenidos = [];
            foreach ($dataReporteArray as $itemRegistroPrincipal) {
                $arrayEmpleadosObtenidos[] = $itemRegistroPrincipal->cod_usuario;
            }

            foreach ($empleadosFavoritos as $empleadoFavorito) {
                if (!in_array($empleadoFavorito, $arrayEmpleadosObtenidos)) {
                    // $empleadosFavoritos = array_diff($empleadosFavoritos, [$empleadoFavorito]);
                }
            }
            // if (empty($arrayEmpleadosObtenidos)) {
            //     $empleadosFavoritos = [];
            // }
            $queryEmpleadosFavoritos = $this->obtenerRegistrosSignSheetEmpleadosFavoritos($days, $granjasFavoritos, $locacionesFavoritos, $empleadosFavoritos, $categorias_empleados, $realizarAgrupacionExtendida);
            $queryEmpleadosFavoritosFiltrados = $this->obtenerRegistrosSignSheetEmpleadosFavoritosFiltrados($days, $granjasFavoritos, $locations, $empleadosFavoritosFiltrados, $categorias_empleados, $realizarAgrupacionExtendida);
            $dataReporte = $dataReporte->merge($queryEmpleadosFavoritos);
            // $dataReporte = $dataReporte->merge($queryEmpleadosFavoritosFiltrados);
            // $dataReporte = $queryEmpleadosFavoritosFiltrados;
        }
        return $dataReporte;
    }

    public function obtenerRegistrosSignSheetEmpleadosFavoritos($days, $farms, $locations, $empleadosFavoritos, $categorias_empleados, $realizarAgrupacionExtendida = true)
    {


        $initial_date = Carbon::createFromFormat('Y-m-d', $days[0])->startOfDay()->format('Y-m-d H:i:s');
        $final_date = Carbon::createFromFormat('Y-m-d', end($days))->endOfDay()->format('Y-m-d H:i:s');

        HelpController::setDatabaseModeParaAgrupacionesGrandes();



        $query = DB::table('pay_bitacora_inicio_sesion as bita')
            ->select(
                'bita.cod_usuario',
                DB::raw("false as mostrar"),
                DB::raw("CONCAT(usu.nombre_1, ' ', usu.apellido_1) AS name"),
                DB::raw("CONCAT(COALESCE(usu.nombre_1, ''),
                            ' ',
                            COALESCE(usu.nombre_2, ''),
                            ' ',
                            COALESCE(usu.apellido_1, ''),
                            ' ',
                            COALESCE(usu.apellido_2, '')) AS full_name"),
                'usu.pin',
                'usu.es_veterano',
                DB::raw("GROUP_CONCAT(usu.es_veterano SEPARATOR ', ') AS es_veterano_concat"),
                DB::raw("GROUP_CONCAT(bita.lunch_acreditado SEPARATOR ', ') AS lunch_acreditado_concat"),
                DB::raw("CONCAT( GROUP_CONCAT(bita.lunch_acreditado SEPARATOR ', '), ' ',GROUP_CONCAT(bita.cod_inicio_sesion SEPARATOR ', ')) AS lunch_acreditado_concat_espacial"),
                DB::raw("GROUP_CONCAT(bita.lunch_automatico SEPARATOR ', ') AS lunch_automatico_concat"),
                DB::raw("GROUP_CONCAT(bita.cod_inicio_sesion SEPARATOR ', ') AS cod_inicio_sesion_concat"),
                'far.farm AS granja',
                'locat.location AS locacion',
                DB::raw("
                    ROUND(SUM(CASE 
                        WHEN DATE(bita.fecha_ingreso) = ? THEN 
                            (TIME_TO_SEC(DATE_FORMAT(bita.fecha_egreso, '%H:%i:00')) - TIME_TO_SEC(DATE_FORMAT(bita.fecha_ingreso, '%H:%i:00'))) / 3600 
                            - (CASE 
                                WHEN (usu.es_veterano = 0 OR usu.es_veterano = 2) AND (bita.lunch_automatico = 1 OR bita.lunch_acreditado = 1) THEN 0.5 
                                ELSE 0 
                            END)
                        ELSE 0 
                    END), 2) AS dia1
                "),
                DB::raw("
                    ROUND(SUM(CASE 
                        WHEN DATE(bita.fecha_ingreso) = ? THEN 
                            (TIME_TO_SEC(DATE_FORMAT(bita.fecha_egreso, '%H:%i:00')) - TIME_TO_SEC(DATE_FORMAT(bita.fecha_ingreso, '%H:%i:00'))) / 3600 
                            - (CASE 
                                WHEN  (usu.es_veterano = 0 OR usu.es_veterano = 2) AND (bita.lunch_automatico = 1 OR bita.lunch_acreditado = 1) THEN 0.5 
                                ELSE 0 
                            END)
                        ELSE 0 
                    END), 2) AS dia2
                "),
                DB::raw("
                    ROUND(SUM(CASE 
                        WHEN DATE(bita.fecha_ingreso) = ? THEN 
                            (TIME_TO_SEC(DATE_FORMAT(bita.fecha_egreso, '%H:%i:00')) - TIME_TO_SEC(DATE_FORMAT(bita.fecha_ingreso, '%H:%i:00'))) / 3600 
                            - (CASE 
                                WHEN (usu.es_veterano = 0 OR usu.es_veterano = 2) AND (bita.lunch_automatico = 1 OR bita.lunch_acreditado = 1) THEN 0.5 
                                ELSE 0 
                            END)
                        ELSE 0 
                    END), 2) AS dia3
                "),
                DB::raw("
                    ROUND(SUM(CASE 
                        WHEN DATE(bita.fecha_ingreso) = ? THEN 
                            (TIME_TO_SEC(DATE_FORMAT(bita.fecha_egreso, '%H:%i:00')) - TIME_TO_SEC(DATE_FORMAT(bita.fecha_ingreso, '%H:%i:00'))) / 3600 
                            - (CASE 
                                WHEN (usu.es_veterano = 0 OR usu.es_veterano = 2) AND (bita.lunch_automatico = 1 OR bita.lunch_acreditado = 1) THEN 0.5 
                                ELSE 0 
                            END)
                        ELSE 0 
                    END), 2) AS dia4
                "),
                DB::raw("
                    ROUND(SUM(CASE 
                        WHEN DATE(bita.fecha_ingreso) = ? THEN 
                            (TIME_TO_SEC(DATE_FORMAT(bita.fecha_egreso, '%H:%i:00')) - TIME_TO_SEC(DATE_FORMAT(bita.fecha_ingreso, '%H:%i:00'))) / 3600 
                            - (CASE 
                                WHEN (usu.es_veterano = 0 OR usu.es_veterano = 2) AND (bita.lunch_automatico = 1 OR bita.lunch_acreditado = 1) THEN 0.5 
                                ELSE 0 
                            END)
                        ELSE 0 
                    END), 2) AS dia5
                "),
                DB::raw("
                    ROUND(SUM(CASE 
                        WHEN DATE(bita.fecha_ingreso) = ? THEN 
                            (TIME_TO_SEC(DATE_FORMAT(bita.fecha_egreso, '%H:%i:00')) - TIME_TO_SEC(DATE_FORMAT(bita.fecha_ingreso, '%H:%i:00'))) / 3600 
                            - (CASE 
                                WHEN (usu.es_veterano = 0 OR usu.es_veterano = 2) AND (bita.lunch_automatico = 1 OR bita.lunch_acreditado = 1) THEN 0.5 
                                ELSE 0 
                            END)
                        ELSE 0 
                    END), 2) AS dia6
                "),
                DB::raw("
                    ROUND(SUM(CASE 
                        WHEN DATE(bita.fecha_ingreso) = ? THEN 
                            (TIME_TO_SEC(DATE_FORMAT(bita.fecha_egreso, '%H:%i:00')) - TIME_TO_SEC(DATE_FORMAT(bita.fecha_ingreso, '%H:%i:00'))) / 3600 
                            - (CASE 
                                WHEN (usu.es_veterano = 0 OR usu.es_veterano = 2) AND (bita.lunch_automatico = 1 OR bita.lunch_acreditado = 1) THEN 0.5 
                                ELSE 0 
                            END)
                        ELSE 0 
                    END), 2) AS dia7
                "),
                DB::raw("
                    ROUND(SUM((TIME_TO_SEC(DATE_FORMAT(bita.fecha_egreso, '%H:%i:00')) - TIME_TO_SEC(DATE_FORMAT(bita.fecha_ingreso, '%H:%i:00'))) / 3600 
                    - (CASE 
                        WHEN (usu.es_veterano = 0 OR usu.es_veterano = 2) AND (bita.lunch_automatico = 1 OR bita.lunch_acreditado = 1) THEN 0.5 
                        ELSE 0 
                    END)), 2) AS total 
                ")
            )
            ->addBinding($days, 'select')
            ->leftJoin('usu_usuarios as usu', 'bita.cod_usuario', '=', 'usu.cod_usuario')
            ->leftJoin('far_farms AS far', 'far.cod_farms', '=', 'bita.cod_farm_ci')
            ->leftJoin('far_locations AS locat', 'locat.cod_location', '=', 'bita.cod_location_ci')
            ->where(DB::raw('DATE(bita.fecha_ingreso)'), '>=', $initial_date)
            ->where(DB::raw('DATE(bita.fecha_egreso)'), '<=', $final_date)
            ->when(count($empleadosFavoritos) > 0, function ($query) use ($empleadosFavoritos) {
                $query->whereIn('bita.cod_usuario', $empleadosFavoritos);
            })
            ->whereIn('usu.es_veterano', $categorias_empleados)
            // ->when(count($locations) > 0, function ($query) use ($locations) {
            //     $query->whereNotIn('bita.cod_location_ci', $locations);
            // })
            ->when(function ($query) use ($farms) {
                $query->whereNotIn('bita.cod_farm_ci', $farms);
            })
            ->groupBy('bita.cod_usuario')
            ->when($realizarAgrupacionExtendida, function ($query) {
                $query->groupBy('far.farm', 'locat.location', 'bita.cod_usuario');
            })
            ->when(!$realizarAgrupacionExtendida, function ($query) {
                $query->groupBy('bita.cod_usuario');
            })
            ->orderBy('usu.nombre_1');

        $results = $query->get();



        return $results;
    }
    public function obtenerRegistrosSignSheetEmpleadosFavoritosFiltrados($days, $farms, $locations, $empleadosFavoritosFiltrados, $categorias_empleados, $realizarAgrupacionExtendida = true)
    {


        $initial_date = Carbon::createFromFormat('Y-m-d', $days[0])->startOfDay()->format('Y-m-d H:i:s');
        $final_date = Carbon::createFromFormat('Y-m-d', end($days))->endOfDay()->format('Y-m-d H:i:s');

        HelpController::setDatabaseModeParaAgrupacionesGrandes();



        $query = DB::table('pay_bitacora_inicio_sesion as bita')
            ->select(
                'bita.cod_usuario',
                DB::raw("false as mostrar"),
                DB::raw("CONCAT(usu.nombre_1, ' ', usu.apellido_1) AS name"),
                DB::raw("CONCAT(COALESCE(usu.nombre_1, ''),
                            ' ',
                            COALESCE(usu.nombre_2, ''),
                            ' ',
                            COALESCE(usu.apellido_1, ''),
                            ' ',
                            COALESCE(usu.apellido_2, '')) AS full_name"),
                'usu.pin',
                'usu.es_veterano',
                DB::raw("GROUP_CONCAT(usu.es_veterano SEPARATOR ', ') AS es_veterano_concat"),
                DB::raw("GROUP_CONCAT(bita.lunch_acreditado SEPARATOR ', ') AS lunch_acreditado_concat"),
                DB::raw("CONCAT( GROUP_CONCAT(bita.lunch_acreditado SEPARATOR ', '), ' ',GROUP_CONCAT(bita.cod_inicio_sesion SEPARATOR ', ')) AS lunch_acreditado_concat_espacial"),
                DB::raw("GROUP_CONCAT(bita.lunch_automatico SEPARATOR ', ') AS lunch_automatico_concat"),
                DB::raw("GROUP_CONCAT(bita.cod_inicio_sesion SEPARATOR ', ') AS cod_inicio_sesion_concat"),
                'far.farm AS granja',
                'locat.location AS locacion',
                DB::raw("
                    ROUND(SUM(CASE 
                        WHEN DATE(bita.fecha_ingreso) = ? THEN 
                            (TIME_TO_SEC(DATE_FORMAT(bita.fecha_egreso, '%H:%i:00')) - TIME_TO_SEC(DATE_FORMAT(bita.fecha_ingreso, '%H:%i:00'))) / 3600 
                            - (CASE 
                                WHEN (usu.es_veterano = 0 OR usu.es_veterano = 2) AND (bita.lunch_automatico = 1 OR bita.lunch_acreditado = 1) THEN 0.5 
                                ELSE 0 
                            END)
                        ELSE 0 
                    END), 2) AS dia1
                "),
                DB::raw("
                    ROUND(SUM(CASE 
                        WHEN DATE(bita.fecha_ingreso) = ? THEN 
                            (TIME_TO_SEC(DATE_FORMAT(bita.fecha_egreso, '%H:%i:00')) - TIME_TO_SEC(DATE_FORMAT(bita.fecha_ingreso, '%H:%i:00'))) / 3600 
                            - (CASE 
                                WHEN  (usu.es_veterano = 0 OR usu.es_veterano = 2) AND (bita.lunch_automatico = 1 OR bita.lunch_acreditado = 1) THEN 0.5 
                                ELSE 0 
                            END)
                        ELSE 0 
                    END), 2) AS dia2
                "),
                DB::raw("
                    ROUND(SUM(CASE 
                        WHEN DATE(bita.fecha_ingreso) = ? THEN 
                            (TIME_TO_SEC(DATE_FORMAT(bita.fecha_egreso, '%H:%i:00')) - TIME_TO_SEC(DATE_FORMAT(bita.fecha_ingreso, '%H:%i:00'))) / 3600 
                            - (CASE 
                                WHEN (usu.es_veterano = 0 OR usu.es_veterano = 2) AND (bita.lunch_automatico = 1 OR bita.lunch_acreditado = 1) THEN 0.5 
                                ELSE 0 
                            END)
                        ELSE 0 
                    END), 2) AS dia3
                "),
                DB::raw("
                    ROUND(SUM(CASE 
                        WHEN DATE(bita.fecha_ingreso) = ? THEN 
                            (TIME_TO_SEC(DATE_FORMAT(bita.fecha_egreso, '%H:%i:00')) - TIME_TO_SEC(DATE_FORMAT(bita.fecha_ingreso, '%H:%i:00'))) / 3600 
                            - (CASE 
                                WHEN (usu.es_veterano = 0 OR usu.es_veterano = 2) AND (bita.lunch_automatico = 1 OR bita.lunch_acreditado = 1) THEN 0.5 
                                ELSE 0 
                            END)
                        ELSE 0 
                    END), 2) AS dia4
                "),
                DB::raw("
                    ROUND(SUM(CASE 
                        WHEN DATE(bita.fecha_ingreso) = ? THEN 
                            (TIME_TO_SEC(DATE_FORMAT(bita.fecha_egreso, '%H:%i:00')) - TIME_TO_SEC(DATE_FORMAT(bita.fecha_ingreso, '%H:%i:00'))) / 3600 
                            - (CASE 
                                WHEN (usu.es_veterano = 0 OR usu.es_veterano = 2) AND (bita.lunch_automatico = 1 OR bita.lunch_acreditado = 1) THEN 0.5 
                                ELSE 0 
                            END)
                        ELSE 0 
                    END), 2) AS dia5
                "),
                DB::raw("
                    ROUND(SUM(CASE 
                        WHEN DATE(bita.fecha_ingreso) = ? THEN 
                            (TIME_TO_SEC(DATE_FORMAT(bita.fecha_egreso, '%H:%i:00')) - TIME_TO_SEC(DATE_FORMAT(bita.fecha_ingreso, '%H:%i:00'))) / 3600 
                            - (CASE 
                                WHEN (usu.es_veterano = 0 OR usu.es_veterano = 2) AND (bita.lunch_automatico = 1 OR bita.lunch_acreditado = 1) THEN 0.5 
                                ELSE 0 
                            END)
                        ELSE 0 
                    END), 2) AS dia6
                "),
                DB::raw("
                    ROUND(SUM(CASE 
                        WHEN DATE(bita.fecha_ingreso) = ? THEN 
                            (TIME_TO_SEC(DATE_FORMAT(bita.fecha_egreso, '%H:%i:00')) - TIME_TO_SEC(DATE_FORMAT(bita.fecha_ingreso, '%H:%i:00'))) / 3600 
                            - (CASE 
                                WHEN (usu.es_veterano = 0 OR usu.es_veterano = 2) AND (bita.lunch_automatico = 1 OR bita.lunch_acreditado = 1) THEN 0.5 
                                ELSE 0 
                            END)
                        ELSE 0 
                    END), 2) AS dia7
                "),
                DB::raw("
                    ROUND(SUM((TIME_TO_SEC(DATE_FORMAT(bita.fecha_egreso, '%H:%i:00')) - TIME_TO_SEC(DATE_FORMAT(bita.fecha_ingreso, '%H:%i:00'))) / 3600 
                    - (CASE 
                        WHEN (usu.es_veterano = 0 OR usu.es_veterano = 2) AND (bita.lunch_automatico = 1 OR bita.lunch_acreditado = 1) THEN 0.5 
                        ELSE 0 
                    END)), 2) AS total 
                ")
            )
            ->addBinding($days, 'select')
            ->leftJoin('usu_usuarios as usu', 'bita.cod_usuario', '=', 'usu.cod_usuario')
            ->leftJoin('far_farms AS far', 'far.cod_farms', '=', 'bita.cod_farm_ci')
            ->leftJoin('far_locations AS locat', 'locat.cod_location', '=', 'bita.cod_location_ci')
            ->where(DB::raw('DATE(bita.fecha_ingreso)'), '>=', $initial_date)
            ->where(DB::raw('DATE(bita.fecha_egreso)'), '<=', $final_date)
            ->when(count($empleadosFavoritosFiltrados) > 0, function ($query) use ($empleadosFavoritosFiltrados) {
                $query->whereIn('bita.cod_usuario', $empleadosFavoritosFiltrados);
            })
            ->whereIn('usu.es_veterano', $categorias_empleados)
            // ->when(count($locations) > 0, function ($query) use ($locations) {
            //     $query->whereNotIn('bita.cod_location_ci', $locations);
            // })
            ->when(function ($query) use ($farms) {
                $query->whereIn('bita.cod_farm_ci', $farms);
            })
            ->groupBy('bita.cod_usuario')
            ->when($realizarAgrupacionExtendida, function ($query) {
                $query->groupBy('far.farm', 'locat.location', 'bita.cod_usuario');
            })
            ->when(!$realizarAgrupacionExtendida, function ($query) {
                $query->groupBy('bita.cod_usuario');
            })
            ->orderBy('usu.nombre_1');

        $results = $query->get();



        return $results;
    }

    public function searchEmpleadosSign(Request $request)
    {
        if ($request->ajax()) {
            $query = $request->get('query');
            $cod_granja = $request->get('cod_farm') ?? 0;
            $cod_location = $request->get('cod_location') ?? 0;
            $empleadosSeleccionados = $request->get('codigo_usuarios') ?? [];
            // $empleadosSeleccionados[0]["pin"] = "1734";
            // $empleadosSeleccionados = $empleadosSeleccionados ?? [];
            $employees = DB::table('usu_usuarios')
                ->distinct()
                ->select(
                    'usu_usuarios.cod_usuario',
                    'usuario',
                    'pin',
                    'qcpin',
                    'es_veterano',
                    'nombre_1',
                    'apellido_1',
                    'cod_tipo_usuario',
                    DB::raw("CONCAT(nombre_1, ' ', apellido_1) AS nombre")
                )
                ->leftJoin('pay_bitacora_inicio_sesion', 'usu_usuarios.cod_usuario', '=', 'pay_bitacora_inicio_sesion.cod_usuario');

            $employees->whereAny([
                "nombre_1",
                "nombre_2",
                "apellido_1",
                "apellido_2",
                "pin",
                "qcpin"
            ], 'LIKE', '%' . $query . '%');

            $employees->where('usu_usuarios.activo', 1)
                ->whereNotIn('cod_tipo_usuario', [1, 2, 3])
                // ->whereBetween('pay_bitacora_inicio_sesion.fecha_ingreso', [$fecha_inicial, $fecha_final])
                ->orderBy('nombre_1');
            $employees = $employees->get();

            $output = '';

            if (count($employees) > 0) {
                foreach ($employees as $employee) {
                    if ($employee->cod_tipo_usuario != 1 && $employee->cod_tipo_usuario != 2 && $employee->cod_tipo_usuario != 3) {
                        $checked = '';
                        foreach ($empleadosSeleccionados as $empleadoSeleccionado) {
                            if ($empleadoSeleccionado["cod_usuario"] == $employee->cod_usuario) {
                                $checked = 'checked';
                                break;
                            }
                        }
                        // $output .= '<div class="row selector_empleado" onclick="this.querySelector(\'input[type=checkbox]\').click(); guardarEmpleadosSeleccionados(this); ">';
                        $output .= '<div class="row selector_empleado" onclick="guardarEmpleadosSeleccionados(this);">';
                        $output .= '<div class="col-md-10 pt-3">';
                        $output .= '<label class="elemento_seleccionable">';
                        $output .= '<input type="checkbox" name="empleados[]" value="' . $employee->cod_usuario . '" ' . $checked . '> ';
                        $output .= '<span style="font-weight: bold;">' . $employee->nombre . '</span> <br>' . $employee->pin . ' - ' . $employee->qcpin;
                        $output .= '</label>';
                        $output .= '</div>';
                        $output .= '</div>';
                    }
                }
            } else {
                $output .= '<p>No results found</p>';
            }

            return $output;
        } else {
            return response()->json(['success' => false, 'message' => 'The request is not ajax', 'data' => $request->all()]);
        }
    }



    public function calcularDeducionPorAlmuerzo($valorDelDia, $lunchAcreditado, $lunchAutomatico)
    {
        if ($valorDelDia == 0 || $valorDelDia == null) {
            return 0;
        } elseif ($lunchAcreditado == 1 || $lunchAutomatico == 1) {
            return -0.30;
        }
        return 0;
    }

    public function obtenerValorArrayAsociativo($arrayAsociativo, $codInicioSesionArray)
    {
        foreach ($codInicioSesionArray as $cod) {
            if (array_key_exists($cod, $arrayAsociativo)) {
                return $arrayAsociativo[$cod];
            }
        }
        return null;
    }
}
