<?php

namespace App\Services;

use App\Http\Controllers\Helpers\HelpController;
use Illuminate\Support\Facades\DB;

class TareaMiscelaneaService
{
    public function __construct(
        protected TareaHarvestService $tareaHarvestService
    ) {}

    /**
     * Obtiene las tareas de misceláneos terminadas para un empleado en una fecha específica,
     * implementando en Laravel la lógica del Stored Procedure
     * `st_pay_obtener_tarea_miscelaneas_terminada_por_empleado_fecha` y calculando los escaneos
     * totales reales por crew.
     *
     * @param int $codUsuario
     * @param string $fechaJob Fecha en formato 'Y-m-d'
     * @return array
     */
    public function obtenerTareasMiscelaneasTerminadas(int $codUsuario, string $fechaJob): array
    {
        // Verificamos si existe al menos una tarea de misceláneo para este empleado y fecha
        $existeMiscelaneo = DB::table('pay_jobs_progresos')
            ->join('pay_crews', 'pay_crews.cod_miscellaneous', '=', 'pay_jobs_progresos.cod_miscellaneous')
            ->where('pay_crews.cod_empleado', $codUsuario)
            ->where('pay_crews.cod_estado_job', 3)
            ->where('pay_jobs_progresos.fecha_job', $fechaJob)
            ->where('pay_jobs_progresos.cod_miscellaneous', '>', 0)
            ->exists();

        if (!$existeMiscelaneo) {
            return [];
        }

        // Relajamos sql_mode para consultas complejas con GROUP BY pay_jobs_progresos.cod_job
        HelpController::setDatabaseModeParaGrandesQuerys();

        $tareasMiscelaneas = DB::table('pay_jobs_progresos')
            ->join('pay_crews', 'pay_crews.cod_miscellaneous', '=', 'pay_jobs_progresos.cod_miscellaneous')
            ->join('pay_lista_empleados_jobs', 'pay_lista_empleados_jobs.cod_crew', '=', 'pay_crews.cod_crew')
            ->join('pay_miscellaneous', 'pay_miscellaneous.cod_miscellaneous', '=', 'pay_jobs_progresos.cod_miscellaneous')
            ->join('far_farms', 'far_farms.cod_farms', '=', 'pay_miscellaneous.cod_farm')
            ->leftJoin('far_crop_semillas_bloques', 'far_crop_semillas_bloques.cod_semilla_bloque', '=', 'pay_miscellaneous.crop_age')
            ->leftJoin('bw_inventario_semilla', 'bw_inventario_semilla.cod_inventario', '=', 'far_crop_semillas_bloques.cod_semilla')
            ->leftJoin('pay_miscelaneos_blocks', 'pay_miscelaneos_blocks.cod_miscelaneos', '=', 'pay_miscellaneous.cod_miscellaneous')
            ->leftJoin('pay_miscelaneos_fields', 'pay_miscelaneos_fields.cod_miscelaneos', '=', 'pay_miscellaneous.cod_miscellaneous')
            ->leftJoin('far_bloques', 'far_bloques.cod_bloque', '=', 'pay_miscelaneos_blocks.cod_block')
            ->leftJoin('far_fields', 'far_fields.cod_field', '=', 'pay_miscelaneos_fields.cod_field')
            ->leftJoin('far_locations', 'far_locations.cod_location', '=', 'pay_miscellaneous.cod_location')
            ->leftJoin('pay_activities', 'pay_activities.cod_activity', '=', 'pay_miscellaneous.cod_activity')
            ->leftJoin('pay_tipo_pagos', 'pay_tipo_pagos.cod_tipo_pago', '=', 'pay_miscellaneous.cod_tipo_pago')
            ->select(
                'far_farms.cod_farms',
                'bw_inventario_semilla.cod_categoria',
                'far_farms.farm AS nombre_granja',
                'pay_jobs_progresos.cod_job',
                'pay_jobs_progresos.cod_estado_job',
                'pay_jobs_progresos.cod_miscellaneous',
                DB::raw("COALESCE(DATE_FORMAT(pay_crews.hora_inicio, '%h:%i %p'), '00:00') AS hora_inicio"),
                DB::raw("COALESCE(DATE_FORMAT(pay_crews.hora_final, '%h:%i %p'), '00:00') AS hora_final"),
                DB::raw("COALESCE(DATE_FORMAT(pay_crews.hora_inicio, '%H:%i'), '00:00') AS hora_inicio_sin_formato"),
                DB::raw("COALESCE(DATE_FORMAT(pay_crews.hora_final, '%H:%i'), '00:00') AS hora_final_sin_formato"),
                'pay_crews.cod_crew',
                'pay_crews.cod_empleado',
                DB::raw('SUM(pay_lista_empleados_jobs.pieces) AS cantidad_escaneo'),
                'far_crop_semillas_bloques.cod_semilla_bloque',
                DB::raw("COALESCE(
                    CONCAT(
                        GROUP_CONCAT(DISTINCT far_bloques.bloque SEPARATOR ' - '), ' ',
                        GROUP_CONCAT(DISTINCT far_fields.field SEPARATOR ' - '), ' ',
                        bw_inventario_semilla.nombre_semilla, ' M.', ', ',
                        DATE_FORMAT(pay_jobs_progresos.hora_inicio, '%h:%i %p'), ' - ',
                        DATE_FORMAT(pay_jobs_progresos.hora_final, '%h:%i %p')
                    ),
                    CONCAT(
                        COALESCE(GROUP_CONCAT(DISTINCT far_bloques.bloque SEPARATOR ' - '), ''), ' ',
                        COALESCE(GROUP_CONCAT(DISTINCT far_fields.field SEPARATOR ' - '), ''), ' ',
                        COALESCE(far_locations.location, ''), ' ', 
                        COALESCE(pay_activities.activity, ''), ' M.', ', ',
                        COALESCE(DATE_FORMAT(pay_jobs_progresos.hora_inicio, '%h:%i %p'), '00:00'), ' - ',
                        COALESCE(DATE_FORMAT(pay_jobs_progresos.hora_final, '%h:%i %p'), '00:00')
                    )
                ) AS job"),
                'pay_tipo_pagos.tipo_pago',
                'pay_tipo_pagos.abreviatura AS abreviatura_tipo_pago',
                'pay_activities.cod_activity',
                'pay_activities.activity',
                'pay_activities.codigo AS codigo_actividad',
                'far_locations.cod_location',
                'far_locations.location',
                'far_locations.abreviacion AS abreviacion_locacion'
            )
            ->where('pay_crews.cod_empleado', $codUsuario)
            ->where('pay_crews.cod_estado_job', 3)
            ->where('pay_jobs_progresos.fecha_job', $fechaJob)
            ->where('pay_jobs_progresos.cod_miscellaneous', '>', 0)
            ->groupBy('pay_jobs_progresos.cod_job')
            ->orderBy('pay_jobs_progresos.hora_inicio', 'asc')
            ->get()
            ->toArray();

        // Sobrescribimos cantidad_escaneo con la suma real directa por crew para evitar distorsiones por joins
        if (!empty($tareasMiscelaneas)) {
            $codCrews = collect($tareasMiscelaneas)->pluck('cod_crew')->filter()->unique()->toArray();
            if (!empty($codCrews)) {
                $escaneosTotales = $this->tareaHarvestService->obtenerCantidadEscaneosPorCrews($codCrews);
                foreach ($tareasMiscelaneas as $registroMiscelaneo) {
                    $registroMiscelaneo->cantidad_escaneo = $escaneosTotales[$registroMiscelaneo->cod_crew] ?? 0;
                }
            }
        }

        return $tareasMiscelaneas;
    }
}
