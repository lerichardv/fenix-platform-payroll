<?php

namespace App\Services;

use App\Http\Controllers\Helpers\HelpController;
use Illuminate\Support\Facades\DB;

class TareaHarvestService
{
    /**
     * Obtiene las tareas de harvest terminadas para un empleado en una fecha específica,
     * implementando en Laravel la lógica del Stored Procedure
     * `st_pay_obtener_tarea_hartvest_terminada_por_empleado_fecha` y calculando los escaneos
     * totales sin depender de `st_pay_cantidad_escaneos_por_crew`.
     *
     * @param int $codUsuario
     * @param string $fechaJob Fecha en formato 'Y-m-d'
     * @return array
     */
    public function obtenerTareasHarvestTerminadas(int $codUsuario, string $fechaJob): array
    {
        // Verificamos si existe al menos un harvest para este empleado y fecha
        $existeHarvest = DB::table('pay_jobs_progresos')
            ->join('pay_crews', 'pay_crews.cod_harvest', '=', 'pay_jobs_progresos.cod_harvest')
            ->where('pay_crews.cod_empleado', $codUsuario)
            ->where('pay_crews.cod_estado_job', 3)
            ->where('pay_jobs_progresos.fecha_job', $fechaJob)
            ->where('pay_jobs_progresos.cod_harvest', '>', 0)
            ->exists();

        if (!$existeHarvest) {
            return [];
        }

        // Relajamos sql_mode para consultas complejas con GROUP BY pay_jobs_progresos.cod_job
        HelpController::setDatabaseModeParaGrandesQuerys();

        $tareasHarvest = DB::table('pay_jobs_progresos')
            ->join('pay_crews', 'pay_crews.cod_harvest', '=', 'pay_jobs_progresos.cod_harvest')
            ->join('pay_lista_empleados_jobs', 'pay_lista_empleados_jobs.cod_crew', '=', 'pay_crews.cod_crew')
            ->join('pay_harvests', 'pay_harvests.cod_harvest', '=', 'pay_jobs_progresos.cod_harvest')
            ->join('far_farms', 'far_farms.cod_farms', '=', 'pay_harvests.cod_farm')
            ->leftJoin('far_crop_semillas_bloques', 'far_crop_semillas_bloques.cod_semilla_bloque', '=', 'pay_harvests.crop_age')
            ->leftJoin('bw_inventario_semilla', 'bw_inventario_semilla.cod_inventario', '=', 'far_crop_semillas_bloques.cod_semilla')
            ->leftJoin('pay_harvests_blocks', 'pay_harvests_blocks.cod_harvest', '=', 'pay_harvests.cod_harvest')
            ->leftJoin('pay_harvests_fields', 'pay_harvests_fields.cod_harvest', '=', 'pay_harvests.cod_harvest')
            ->leftJoin('far_bloques', 'far_bloques.cod_bloque', '=', 'pay_harvests_blocks.cod_block')
            ->leftJoin('far_fields', 'far_fields.cod_field', '=', 'pay_harvests_fields.cod_field')
            ->leftJoin('pay_tipo_pagos', 'pay_tipo_pagos.cod_tipo_pago', '=', 'pay_harvests.cod_tipo_pago')
            ->leftJoin('pay_tipo_packs', 'pay_harvests.cod_tipo_pack', '=', 'pay_tipo_packs.cod_tipo_pack')
            ->select(
                'far_farms.cod_farms',
                'bw_inventario_semilla.cod_categoria',
                'far_farms.farm AS nombre_granja',
                'pay_jobs_progresos.cod_job',
                'pay_lista_empleados_jobs.cod_crew AS cod_crew_lista',
                'pay_jobs_progresos.cod_estado_job',
                'pay_jobs_progresos.cod_harvest',
                DB::raw("COALESCE(DATE_FORMAT(pay_crews.hora_inicio, '%h:%i %p'), '00:00') AS hora_inicio"),
                DB::raw("COALESCE(DATE_FORMAT(pay_crews.hora_final, '%h:%i %p'), '00:00') AS hora_final"),
                DB::raw("COALESCE(DATE_FORMAT(pay_crews.hora_inicio, '%H:%i'), '00:00') AS hora_inicio_sin_formato"),
                DB::raw("COALESCE(DATE_FORMAT(pay_crews.hora_final, '%H:%i'), '00:00') AS hora_final_sin_formato"),
                'pay_crews.cod_crew',
                'pay_crews.cod_empleado',
                DB::raw('SUM(pay_lista_empleados_jobs.pieces) AS cantidad_escaneo'),
                'pay_harvests.cod_harvest',
                'far_crop_semillas_bloques.cod_semilla_bloque',
                DB::raw("COALESCE(CONCAT(
                    GROUP_CONCAT(DISTINCT far_bloques.bloque SEPARATOR ' - '), ' ',
                    GROUP_CONCAT(DISTINCT far_fields.field SEPARATOR ' - '), ' ',
                    bw_inventario_semilla.nombre_semilla, ' H. ', ', ',
                    COALESCE(DATE_FORMAT(pay_jobs_progresos.hora_inicio, '%h:%i %p'), '00:00'), ' - ',
                    COALESCE(DATE_FORMAT(pay_jobs_progresos.hora_final, '%h:%i %p'), '00:00')
                ), 'Missing data') AS job"),
                'pay_tipo_pagos.tipo_pago',
                'pay_tipo_pagos.abreviatura AS abreviatura_tipo_pago',
                'pay_tipo_packs.cod_tipo_pack',
                'pay_tipo_packs.tipo_pack'
            )
            ->where('pay_crews.cod_empleado', $codUsuario)
            ->where('pay_crews.cod_estado_job', 3)
            ->where('pay_jobs_progresos.fecha_job', $fechaJob)
            ->groupBy('pay_jobs_progresos.cod_job')
            ->orderBy('pay_jobs_progresos.hora_inicio', 'asc')
            ->get()
            ->toArray();

        // Calculamos la cantidad real de escaneos por crew directamente sin SP
        if (!empty($tareasHarvest)) {
            $codCrews = collect($tareasHarvest)->pluck('cod_crew')->filter()->unique()->toArray();
            if (!empty($codCrews)) {
                $escaneosTotales = $this->obtenerCantidadEscaneosPorCrews($codCrews);
                foreach ($tareasHarvest as $registroHarvest) {
                    $registroHarvest->cantidad_escaneo = $escaneosTotales[$registroHarvest->cod_crew] ?? 0;
                }
            }
        }

        return $tareasHarvest;
    }

    /**
     * Calcula la suma de escaneos (piezas) agrupado por cod_crew,
     * reemplazando la llamada a `st_pay_cantidad_escaneos_por_crew`.
     *
     * @param array $codCrews
     * @return array Mapa [cod_crew => cantidad_escaneo]
     */
    public function obtenerCantidadEscaneosPorCrews(array $codCrews): array
    {
        if (empty($codCrews)) {
            return [];
        }

        return DB::table('pay_lista_empleados_jobs')
            ->select('cod_crew', DB::raw('SUM(pieces) AS cantidad_escaneo'))
            ->whereIn('cod_crew', $codCrews)
            ->groupBy('cod_crew')
            ->pluck('cantidad_escaneo', 'cod_crew')
            ->toArray();
    }
}
