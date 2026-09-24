<?php

namespace App\Services;

use App\Models\RegistroIngreso;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CalculoHorasService
{
    /**
     * Normaliza el rango de fechas para consultas SQL.
     * Si vienen en formato 'm-d-Y', las convierte a 'Y-m-d 00:00:00' y 'Y-m-d 23:59:59'.
     *
     * @param string|null $initialDate
     * @param string|null $finalDate
     * @return array [string $fechaInicio, string $fechaFin]
     */
    public function normalizarRangoFechas(?string $initialDate, ?string $finalDate): array
    {
        $fechaInicio = $initialDate;
        $fechaFin = $finalDate;

        if (!empty($initialDate) && !empty($finalDate)) {
            $fechaInicio = Carbon::createFromFormat('m-d-Y', $initialDate)
                ->startOfDay()
                ->format('Y-m-d H:i:s');
            $fechaFin = Carbon::createFromFormat('m-d-Y', $finalDate)
                ->endOfDay()
                ->format('Y-m-d H:i:s');
        }

        return [$fechaInicio, $fechaFin];
    }

    /**
     * Obtiene los registros de ingreso de un usuario en un rango de fechas y filtros opcionales de granja y locación,
     * deduplicando registros que tengan la misma marca de tiempo exacta de ingreso (fecha_ingreso).
     *
     * @param int $codUsuario
     * @param string|null $initialDate
     * @param string|null $finalDate
     * @param int $codFarm
     * @param int $codLocation
     * @return Collection
     */
    public function obtenerRegistrosDeduplicados(
        int $codUsuario,
        ?string $initialDate,
        ?string $finalDate,
        $codFarm = 0,
        $codLocation = 0
    ): Collection {
        [$fechaInicio, $fechaFin] = $this->normalizarRangoFechas($initialDate, $finalDate);

        $query = RegistroIngreso::where('cod_usuario', $codUsuario)
            ->where('fecha_ingreso', '>=', $fechaInicio)
            ->where('fecha_egreso', '<=', $fechaFin);

        if (!empty($codFarm) && $codFarm != 0) {
            $query->where('cod_farm_ci', $codFarm);
        }

        if (!empty($codLocation) && $codLocation != 0) {
            $query->where('cod_location_ci', $codLocation);
        }

        // Ordenamos por fecha_egreso desc para priorizar registros que tengan fecha de egreso completa
        return $query->orderBy('fecha_egreso', 'desc')
            ->get()
            ->unique(fn($r) => (string) $r->fecha_ingreso)
            ->values();
    }

    /**
     * Calcula los milisegundos trabajados para un registro individual,
     * aplicando el descuento de 30 minutos de lunch si corresponde.
     *
     * @param object $registro
     * @param mixed $esVeterano
     * @return int
     */
    public function calcularMilisegundosRegistro($registro, $esVeterano = 0): int
    {
        if (empty($registro->fecha_ingreso) || empty($registro->fecha_egreso)) {
            return 0;
        }

        // Eliminar segundos y milisegundos de las fechas
        $fechaIngresoSinSegundos = (new \DateTime($registro->fecha_ingreso))->format('Y-m-d H:i');
        $fechaEgresoSinSegundos = (new \DateTime($registro->fecha_egreso))->format('Y-m-d H:i');

        $milisegundos = $this->calcularMilisegundosEntreFechas(
            $fechaIngresoSinSegundos,
            $this->ajustarFechaAlFinalDelDiaSi12AM($fechaEgresoSinSegundos)
        );

        $esVeteranoVal = (string) $esVeterano;

        if ($registro->lunch_acreditado == "1" && $esVeteranoVal !== "1") {
            $milisegundos -= (30 * 60000);
        } else if (
            $registro->lunch_automatico == "1"
            && $esVeteranoVal !== "1"
            && $this->milisegundosAHoras($milisegundos) >= 5
        ) {
            $milisegundos -= (30 * 60000);
        }

        return max(0, (int) $milisegundos);
    }

    /**
     * Procesa una colección de registros deduplicados:
     * - Asigna a cada registro su $registro->horas_trabajadas formateado ('HH:MM')
     * - Calcula el total de milisegundos, formato 'HH:MM' y horas decimales.
     *
     * @param iterable $registros
     * @param mixed $esVeterano
     * @return array
     */
    public function calcularTotalesRegistros(iterable $registros, $esVeterano = 0): array
    {
        $milisegundosTotales = 0;

        foreach ($registros as $registro) {
            $ms = $this->calcularMilisegundosRegistro($registro, $esVeterano);
            $registro->horas_trabajadas = $this->convertirMilisegundosAFormato($ms);
            $milisegundosTotales += $ms;
        }

        $horasFormato = $this->convertirMilisegundosAFormato($milisegundosTotales);
        $horasDecimal = $this->convertirMilisegundosAHoras($milisegundosTotales);
        $horasDecimalDesdeFormato = $this->formatearTiempoAHoraDecimal($horasFormato);

        return [
            'milisegundos' => $milisegundosTotales,
            'horas_formato' => $horasFormato,
            'horas_decimal' => $horasDecimal,
            'horas_decimal_formato' => $horasDecimalDesdeFormato,
        ];
    }

    /**
     * Calcula la diferencia total en milisegundos entre dos fechas.
     */
    public function calcularMilisegundosEntreFechas(string $fechaInicial, string $fechaFinal): float
    {
        $fechaInicialDatetime = new \DateTime($fechaInicial);
        $fechaFinalDatetime = new \DateTime($fechaFinal);

        $interval = $fechaInicialDatetime->diff($fechaFinalDatetime);

        return ($interval->days * 24 * 60 * 60 * 1000)
            + ($interval->h * 60 * 60 * 1000)
            + ($interval->i * 60 * 1000)
            + ($interval->s * 1000)
            + (($fechaFinalDatetime->format('u') - $fechaInicialDatetime->format('u')) / 1000);
    }

    /**
     * Calcula la diferencia total en minutos entre dos fechas.
     */
    public function calcularMinutosEntreFechas(string $fechaInicial, string $fechaFinal): float
    {
        $fechaInicialDatetime = new \DateTime($fechaInicial);
        $fechaFinalDatetime = new \DateTime($fechaFinal);

        $interval = $fechaInicialDatetime->diff($fechaFinalDatetime);

        return ($interval->days * 24 * 60)
            + ($interval->h * 60)
            + ($interval->i)
            + ($interval->s / 60);
    }

    /**
     * Ajusta la fecha al final del día (23:59:59) si la hora es exactamente 00:00:00.
     */
    public function ajustarFechaAlFinalDelDiaSi12AM(string $fecha): string
    {
        $fechaDatetime = new \DateTime($fecha);

        if ($fechaDatetime->format('H:i:s') === '00:00:00') {
            $fechaDatetime->setTime(23, 59, 59);
        }

        return $fechaDatetime->format('Y-m-d H:i:s');
    }

    /**
     * Convierte milisegundos al formato de tiempo 'HH:MM'.
     */
    public function convertirMilisegundosAFormato(int|float $milisegundos): string
    {
        $segundosTotales = (int) floor($milisegundos / 1000);
        $horas = floor($segundosTotales / 3600);
        $minutos = floor(($segundosTotales % 3600) / 60);

        return sprintf('%02d:%02d', $horas, $minutos);
    }

    /**
     * Convierte milisegundos a horas decimales redondeado a dos cifras.
     */
    public function convertirMilisegundosAHoras(int|float $milisegundos): float
    {
        if (!is_numeric($milisegundos)) {
            throw new \InvalidArgumentException("The parameter must be a number.");
        }

        $horas = $milisegundos / (1000 * 60 * 60);
        return round($horas, 2);
    }

    /**
     * Convierte milisegundos a horas decimales sin redondear.
     */
    public function milisegundosAHoras(int|float $milisegundos): float
    {
        return $milisegundos / 3600000;
    }

    /**
     * Convierte una hora en formato H:i a hora decimal. Ej. 07:25 -> 7.42.
     */
    public function formatearTiempoAHoraDecimal(string $timeString, bool $sinPunto = false): float|string
    {
        [$hora, $minutos] = explode(':', $timeString);
        $hora = ltrim($hora, '0');
        $minutosDecimal = round(($minutos / 60) * 100);
        $minutosDecimal = str_pad($minutosDecimal, 2, '0', STR_PAD_LEFT);
        $horaDecimal = "{$hora}.{$minutosDecimal}";

        if ($sinPunto) {
            $horaDecimal = str_replace('.', '', $horaDecimal);
        }

        return $sinPunto ? $horaDecimal : (float) $horaDecimal;
    }

    /**
     * Convierte horas a string decimal.
     */
    public function formatearHora(float|int $time, bool $sinPunto = false): string
    {
        $hours = floor($time);
        $minutes = ($time - $hours) * 60;
        $minutes = number_format(($minutes / 60), 2) * 100;

        return $sinPunto
            ? str_replace(".", "", ($hours > 0 ? $hours : "") . str_pad($minutes, 2, "0", STR_PAD_LEFT))
            : (string) $hours . "." . (string) str_pad($minutes, 2, "0", STR_PAD_LEFT);
    }

    /**
     * Formatea horas decimales a formato 'HH:MM'.
     */
    public function formatearHorasATiempo(float|int $totalHours): string
    {
        $hours = floor($totalHours);
        $minutes = round(($totalHours - $hours) * 60);

        return str_pad($hours, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minutes, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Convierte string 'HH:MM' a número decimal de horas.
     */
    public function formatearTiempoAsNumeroHoras(string $time): float
    {
        [$hours, $minutes] = explode(':', $time);
        return $hours + ($minutes / 60);
    }
}
