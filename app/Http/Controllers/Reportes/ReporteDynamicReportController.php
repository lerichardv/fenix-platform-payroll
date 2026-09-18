<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Granja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Exports\DynamicReportExport;

class ReporteDynamicReportController extends Controller
{
    public function index(){
        return view('admin.reportes.reporte_dynamic')
            ->with('listaGranjas', Granja::todasLasActivas());
    }

    public function obtenerDynamicReportData(Request $request){

        $fecha_inicial = $request->input('fecha_inicial') ?? "";
        $fecha_final = $request->input('fecha_final') ?? "";
        $cod_granja = json_decode($request->input('cod_farm')) ?? [];
        $cod_location = json_decode($request->input('cod_location')) ?? [];
        $cod_categories = json_decode($request->input('cod_category')) ?? [];
        $empleados = json_decode($request->input('empleados')) ?? [];
        $columns = json_decode($request->input('columns')) ?? [
            'usu_usuarios.cod_usuario',
            "CONCAT(usu_usuarios.nombre_1, ' ', usu_usuarios.apellido_1) AS nombre_completo",
            'usu_usuarios.email',
            'usu_usuarios.identidad',
            'usu_usuarios.telefono_1',
            'usu_tipo_usuario.cod_tipo_usuario',
            'usu_tipo_usuario.etiqueta_english AS tipo_usuario',
            "IF(usu_usuarios.es_veterano = 0, 'Standard', IF(usu_usuarios.es_veterano = 1, 'Veteran', 'H2A')) AS categoria",
            'usu_usuarios.pin',
            'usu_usuarios.qcpin'
        ];
        $group_by = json_decode($request->input('group_by')) ?? [
            'usu_usuarios.cod_usuario'
        ];

        Log::info(json_encode($empleados));

        $results = $this->generarDynamicReportData(
            $fecha_inicial,
            $fecha_final,
            $cod_granja,
            $cod_location,
            $cod_categories,
            $this->pluckIdEmpleados($empleados),
            $columns,
            $group_by
        );

        return response()->json([
            "success" => true,
            "data" => $results
        ]);

    }

    public function generarDynamicReportData(
        $fecha_inicial,
        $fecha_final,
        $cod_granja,
        $cod_location,
        $cod_categories,
        $empleados,
        $columns,
        $group_by
    ){

        $fecha_inicial = Carbon::createFromFormat('m-d-Y', $fecha_inicial)
                            ->startOfDay()
                            ->format('Y-m-d');
        $fecha_final = Carbon::createFromFormat('m-d-Y', $fecha_final)
                            ->endOfDay()
                            ->format('Y-m-d');

        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");

        $vienenEscaneos = false;
        foreach($columns as $column){
            // verificar si vienen escaneos
            if(str_contains($column, 'escaneos')){
                $vienenEscaneos = true;
                break;
            }
        }
        $this->fixColumns($columns, $vienenEscaneos);
        $this->fixGroupBy($group_by, $vienenEscaneos);

        $columnsString = "";
        $i = 0;
        foreach($columns as $column){
            // Generando el raw string para el select
            $columnsString .= $column;
            if($i < count($columns) - 1){
                $columnsString .= ", ";
            }
            $i++;
        }

        $groupString = "";
        $i = 0;
        foreach($group_by as $g){
            // generando el raw string para el group by
            if($g != ""){
                $groupString .= $g;
                if($i < count($group_by) - 1){
                    $groupString .= ", ";
                }
            }
            $i++;
        }

        if($vienenEscaneos){
            $query = DB::table('usu_usuarios')
                ->selectRaw($columnsString)
                ->join('pay_crews', 'usu_usuarios.cod_usuario', '=', 'pay_crews.cod_empleado')
                ->join('pay_lista_empleados_jobs', 'pay_crews.cod_crew', '=', 'pay_lista_empleados_jobs.cod_crew')
                ->leftJoin('pay_harvests', 'pay_crews.cod_harvest', '=', 'pay_harvests.cod_harvest')
                ->leftJoin('far_farms', 'pay_harvests.cod_farm', '=', 'far_farms.cod_farms')
                ->leftJoin('usu_tipo_usuario', 'usu_usuarios.cod_tipo_usuario', '=', 'usu_tipo_usuario.cod_tipo_usuario')
                ->leftJoin('pay_jobs_progresos', 'pay_harvests.cod_harvest', '=', 'pay_jobs_progresos.cod_harvest')
                ->where('pay_jobs_progresos.fecha_job', '>=', $fecha_inicial)
                ->where('pay_jobs_progresos.fecha_job', '<=', $fecha_final);
        }else{
            $query = DB::table('pay_bitacora_inicio_sesion')
                ->selectRaw($columnsString)
                ->leftJoin('usu_usuarios', 'pay_bitacora_inicio_sesion.cod_usuario', '=', 'usu_usuarios.cod_usuario')
                ->leftJoin('far_farms', 'pay_bitacora_inicio_sesion.cod_farm_ci', '=', 'far_farms.cod_farms')
                ->leftJoin('far_locations', 'pay_bitacora_inicio_sesion.cod_location_ci', '=', 'far_locations.cod_location')
                ->leftJoin('usu_tipo_usuario', 'usu_usuarios.cod_tipo_usuario', '=', 'usu_tipo_usuario.cod_tipo_usuario')
                ->where('pay_bitacora_inicio_sesion.fecha_ingreso', '>=', $fecha_inicial)
                ->where('pay_bitacora_inicio_sesion.fecha_egreso', '<=', $fecha_final);
        }


        if(count($cod_granja) > 0){
            if($vienenEscaneos){
                $query->whereIn('far_farms.cod_farms', $cod_granja);
            }else{
                $query->whereIn('pay_bitacora_inicio_sesion.cod_farm_ci', $cod_granja);
            }
        }
        if(count($cod_location) > 0 && !$vienenEscaneos){
            $query->whereIn('far_locations.cod_location', $cod_location);
        }
        if(count($cod_categories) > 0){
            $query->whereIn('usu_usuarios.es_veterano', $cod_categories);
        }
        if(count($empleados) > 0){
            if($vienenEscaneos){
                $query->whereIn('usu_usuarios.cod_usuario', $empleados);
            }else{
                $query->whereIn('pay_bitacora_inicio_sesion.cod_usuario', $empleados);
            }
        }
        if(count($group_by) > 0){
            $query->groupByRaw($groupString);
        }else{
            $query->groupBy('usu_usuarios.cod_usuario');
        }
        $result = $query->orderBy('usu_usuarios.nombre_1')->get();


        return $result;

    }

    public function exportDynamicReport(Request $request){

        $fecha_inicial = $request->input('fecha_inicial') ?? "";
        $fecha_final = $request->input('fecha_final') ?? "";
        $cod_granja = json_decode($request->input('cod_farm')) ?? [];
        $cod_location = json_decode($request->input('cod_location')) ?? [];
        $cod_categories = json_decode($request->input('cod_category')) ?? [];
        $empleados = json_decode($request->input('empleados')) ?? [];
        $columns = json_decode($request->input('columns')) ?? [
            'usu_usuarios.cod_usuario',
            "CONCAT(usu_usuarios.nombre_1, ' ', usu_usuarios.apellido_1) AS nombre_completo",
            'usu_usuarios.email',
            'usu_usuarios.identidad',
            'usu_usuarios.telefono_1',
            'usu_tipo_usuario.cod_tipo_usuario',
            'usu_tipo_usuario.etiqueta_english AS tipo_usuario',
            "IF(usu_usuarios.es_veterano = 0, 'Standard', IF(usu_usuarios.es_veterano = 1, 'Veteran', 'H2A')) AS categoria",
            'usu_usuarios.pin',
            'usu_usuarios.qcpin'
        ];
        $group_by = json_decode($request->input('group_by')) ?? [
            'usu_usuarios.cod_usuario'
        ];

        $filename = "dynamic_report_"
            . Carbon::createFromFormat('m-d-Y', $fecha_inicial)->format('Y-m-d')."_"
            . Carbon::createFromFormat('m-d-Y', $fecha_final)->format('Y-m-d');

        return Excel::download(
            new DynamicReportExport(
                $fecha_inicial,
                $fecha_final,
                $cod_granja,
                $cod_location,
                $cod_categories,
                $empleados,
                $columns,
                $group_by
            ),
            $filename.'.xlsx',
            null,
            [
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="' . $filename . '.xlsx"',
            ]
        );

    }

    private function fixColumns(&$columns, $vienenEscaneos = false){
        foreach($columns as &$column){
            switch($column){
                case 'cod_usuario':
                    $column = "usu_usuarios.cod_usuario";
                    break;
                case 'nombre_completo':
                    $column = "concat(usu_usuarios.nombre_1, ' ', usu_usuarios.apellido_1) as nombre_completo";
                    break;
                case 'identidad':
                    $column = "usu_usuarios.identidad";
                    break;
                case 'email':
                    $column = "usu_usuarios.email";
                    break;
                case 'telefono':
                    $column = "usu_usuarios.telefono_1";
                    break;
                case 'cod_tipo_usuario':
                    $column = "usu_tipo_usuario.cod_tipo_usuario";
                    break;
                case 'tipo_usuario':
                    $column = "usu_tipo_usuario.etiqueta_english as tipo_usuario";
                    break;
                case 'pin':
                    $column = "usu_usuarios.pin";
                    break;
                case 'qcpin':
                    $column = "usu_usuarios.qcpin";
                    break;
                case 'pay_rate':
                    $column = "usu_usuarios.pay_rate";
                    break;
                case 'categoria':
                    $column = 'if(usu_usuarios.es_veterano = 0, "Standard", if(usu_usuarios.es_veterano = 1, "Veteran", "H2A")) as categoria';
                    break;
                case 'cod_location':
                    if(!$vienenEscaneos){
                        $column = "far_locations.cod_location";
                    }else{
                        $column = "'Field'";
                    }
                    break;
                case 'location':
                    if(!$vienenEscaneos){
                        $column = "far_locations.location";
                    }else{
                        $column = "'Field'";
                    }
                    break;
                case 'cod_farms':
                    $column = "far_farms.cod_farms";
                    break;
                case 'farm':
                    $column = "far_farms.farm";
                    break;
                case 'escaneos':
                    $column = "sum(pay_lista_empleados_jobs.pieces) as escaneos";
                    break;
                case 'horas':
                    $column = "
                            CONCAT(
                                LEFT(
                                    SEC_TO_TIME(
                                        ROUND(
                                            SUM((((TIME_TO_SEC(
                                                    IF(TIME(pay_bitacora_inicio_sesion.fecha_egreso) = '00:00:00',
                                                    DATE_FORMAT(pay_bitacora_inicio_sesion.fecha_egreso - INTERVAL 1 SECOND, '%H:%i:00'),
                                                    DATE_FORMAT(pay_bitacora_inicio_sesion.fecha_egreso, '%H:%i:00'))
                                                ) - TIME_TO_SEC(DATE_FORMAT(pay_bitacora_inicio_sesion.fecha_ingreso, '%H:%i:00')))
                                                * 1000) - (IF(pay_bitacora_inicio_sesion.lunch_acreditado = 1 AND usu_usuarios.es_veterano != 1, 1.8e6, IF(pay_bitacora_inicio_sesion.lunch_automatico = 1 AND usu_usuarios.es_veterano != 1 AND ROUND(TIMESTAMPDIFF(SECOND, pay_bitacora_inicio_sesion.fecha_ingreso, pay_bitacora_inicio_sesion.fecha_egreso) / 3600, 2) >= 5, 1.8e6, 0)))) / 3.6e6) * 3600, 0)
                                    ), 5
                                ), ' (',
                                ROUND(
                                    SUM((((TIME_TO_SEC(
                                            IF(TIME(pay_bitacora_inicio_sesion.fecha_egreso) = '00:00:00',
                                            DATE_FORMAT(pay_bitacora_inicio_sesion.fecha_egreso - INTERVAL 1 SECOND, '%H:%i:00'),
                                            DATE_FORMAT(pay_bitacora_inicio_sesion.fecha_egreso, '%H:%i:00'))
                                        ) - TIME_TO_SEC(DATE_FORMAT(pay_bitacora_inicio_sesion.fecha_ingreso, '%H:%i:00')))
                                        * 1000) - (IF(pay_bitacora_inicio_sesion.lunch_acreditado = 1 AND usu_usuarios.es_veterano != 1, 1.8e6, IF(pay_bitacora_inicio_sesion.lunch_automatico = 1 AND usu_usuarios.es_veterano != 1 AND ROUND(TIMESTAMPDIFF(SECOND, pay_bitacora_inicio_sesion.fecha_ingreso, pay_bitacora_inicio_sesion.fecha_egreso) / 3600, 2) >= 5, 1.8e6, 0)))) / 3.6e6),
                                    2)
                                , ')'
                            ) AS horas";
                    break;
            }
        }
        return $columns;
    }

    private function fixGroupBy(&$group_by, $vienenEscaneos = false){
        foreach($group_by as &$g){
            switch($g){
                case "cod_usuario":
                    $g = "usu_usuarios.cod_usuario";
                    break;
                case "cod_tipo_usuario":
                    $g = "usu_tipo_usuario.cod_tipo_usuario";
                    break;
                case "categoria":
                    $g = "categoria";
                    break;
                case "cod_farm_ci":
                    if(!$vienenEscaneos){
                        $g = "pay_bitacora_inicio_sesion.cod_farm_ci";
                    }else{
                        $g = "far_farms.cod_farms as cod_farm_ci";
                    }
                    break;
                case "cod_location_ci":
                    if(!$vienenEscaneos){
                        $g = "pay_bitacora_inicio_sesion.cod_location_ci";
                    }else{
                        $g = "";
                    }
                    break;
            }
        }
        return $group_by;
    }

    public function pluckIdEmpleados($empleados){
        $e = [];
        foreach($empleados as $empleado){
            array_push($e, $empleado->id);
        }
        return $e;
    }
}
