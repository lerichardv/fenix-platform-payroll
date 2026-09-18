<?php

namespace App\Services;

use App\Models\ActividadPorDia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BitacoraInicioSesionService
{
    /**
     * Obtiene y enriquece los inicios de sesión de un usuario en un rango de fechas,
     * replicando de forma nativa en PHP/Laravel todas las operaciones del Stored Procedure
     * `st_pay_obtener_inicios_sesion_usuario_rango_fecha`.
     *
     * @param int $codUsuario
     * @param string $initialDate Fecha en formato 'Y-m-d'
     * @param string $finalDate Fecha en formato 'Y-m-d'
     * @return array
     */
    public function obtenerIniciosSesion(int $codUsuario, string $initialDate, string $finalDate): array
    {
        $fechaInicio = $initialDate . ' 00:00:00';
        $fechaFin = $finalDate . ' 23:59:59';

        // Verificamos compatibilidad con columnas opcionales de comentarios (si existen en el esquema)
        $hasComentario = Schema::hasColumn('pay_bitacora_inicio_sesion', 'comentario');
        $hasCodUsuarioComent = Schema::hasColumn('pay_bitacora_inicio_sesion', 'cod_usuario_insert_coment');
        $hasFechaComentario = Schema::hasColumn('pay_bitacora_inicio_sesion', 'fecha_comentario');

        $bitacora = DB::table('pay_bitacora_inicio_sesion as pbis')
            ->select(
                'pbis.cod_inicio_sesion',
                'pbis.cod_usuario',
                'pbis.fecha_ingreso as fecha_ingreso_raw',
                $hasComentario ? 'pbis.comentario' : DB::raw('NULL as comentario'),
                $hasCodUsuarioComent ? 'pbis.cod_usuario_insert_coment' : DB::raw('NULL as cod_usuario_insert_coment'),
                $hasFechaComentario ? 'pbis.fecha_comentario' : DB::raw('NULL as fecha_comentario'),
                'pbis.lunch_acreditado',
                'pbis.lunch_automatico',
                'pbis.cod_farm_ci',
                'pbis.cod_location_ci',
                'pbis.cod_farm_ci as granjaClockinId',
                'pbis.cod_location_ci as locationClockinId',
                'pbis.cod_farm_co',
                'pbis.cod_location_co',
                'pbis.cod_actividad_por_dia',
                DB::raw('COALESCE(u.es_veterano, 0) as es_veterano'),
                DB::raw("DATE_FORMAT(pbis.fecha_ingreso, '%H:%i') AS fecha_ingreso_sin_formato"),
                DB::raw("DATE_FORMAT(pbis.fecha_egreso, '%H:%i') AS fecha_egreso_sin_formato"),
                DB::raw("DATE_FORMAT(pbis.fecha_ingreso, '%d-%m-%Y') AS fecha_ingreso_sin_horas"),
                DB::raw("DATE_FORMAT(pbis.fecha_ingreso, '%m-%d-%Y') AS fecha_ingreso_mostrable"),
                DB::raw("DATE_FORMAT(pbis.fecha_ingreso, '%h:%i %p') AS fecha_ingreso"),
                DB::raw("DATE_FORMAT(pbis.fecha_egreso, '%h:%i %p') AS fecha_egreso")
            )
            ->leftJoin('usu_usuarios as u', 'pbis.cod_usuario', '=', 'u.cod_usuario')
            ->where('pbis.cod_usuario', $codUsuario)
            ->where('pbis.fecha_ingreso', '>=', $fechaInicio)
            ->where('pbis.fecha_ingreso', '<=', $fechaFin)
            ->orderBy('pbis.fecha_ingreso', 'asc')
            ->get()
            ->toArray();

        // Replica de forma segura el 'GROUP BY fecha_ingreso' del stored procedure agrupando por el timestamp real de la tabla,
        // eliminando registros duplicados con exactamente la misma marca de tiempo sin violar ONLY_FULL_GROUP_BY en MySQL
        $bitacora = collect($bitacora)
            ->unique('fecha_ingreso_raw')
            ->values()
            ->each(function ($item) {
                unset($item->fecha_ingreso_raw);
            })
            ->all();

        return $this->enriquecerRegistros($bitacora);
    }

    /**
     * Enriquecimiento de datos para el frontend (granjas, locaciones y modelos de actividad).
     *
     * @param array $bitacora
     * @return array
     */
    public function enriquecerRegistros(array $bitacora): array
    {
        if (empty($bitacora)) {
            return [];
        }

        $coleccion = collect($bitacora);

        $codFarms = $coleccion->pluck('cod_farm_ci')
            ->merge($coleccion->pluck('cod_farm_co'))
            ->unique()
            ->filter()
            ->toArray();

        $codLocations = $coleccion->pluck('cod_location_ci')
            ->merge($coleccion->pluck('cod_location_co'))
            ->unique()
            ->filter()
            ->toArray();

        $granjas = DB::table('far_farms')
            ->whereIn('cod_farms', $codFarms)
            ->pluck('farm', 'cod_farms');

        $locations = DB::table('far_locations')
            ->whereIn('cod_location', $codLocations)
            ->pluck('abreviacion', 'cod_location');

        // Precarga de actividades para evitar problema N+1
        $codActividades = $coleccion->pluck('cod_actividad_por_dia')
            ->unique()
            ->filter()
            ->toArray();

        $actividades = ActividadPorDia::whereIn('cod_actividad_por_dia', $codActividades)
            ->get()
            ->keyBy('cod_actividad_por_dia');

        foreach ($bitacora as $registro) {
            $registro->granjaClockin = $granjas[$registro->cod_farm_ci] ?? '';
            $registro->locationClockin = $locations[$registro->cod_location_ci] ?? '';
            $registro->granjaClockinId = $registro->cod_farm_ci;
            $registro->locationClockinId = $registro->cod_location_ci;

            $registro->granjaClockout = $registro->cod_farm_co ? ($granjas[$registro->cod_farm_co] ?? '') : '';
            $registro->locationClockout = $registro->cod_location_co ? ($locations[$registro->cod_location_co] ?? '') : '';

            $registro->usuario_veterano = ((int) ($registro->es_veterano ?? 0)) === 1;
            $registro->datos_tareas = []; // Se cargan dinámicamente vía AJAX según demanda del acordeón
            $registro->actividad = $actividades[$registro->cod_actividad_por_dia] ?? null;
        }

        return $bitacora;
    }
}
