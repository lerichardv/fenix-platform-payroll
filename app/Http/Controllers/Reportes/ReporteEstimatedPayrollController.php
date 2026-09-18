<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Granja;
use App\Models\RegistroIngreso;
use App\Models\Locacion;
use App\Models\Usuario;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Exports\EstimatedPayrollExport;

class ReporteEstimatedPayrollController extends Controller
{
    public function index(){
        return view('admin.reportes.reporte_estimated_payroll')
            ->with('listaGranjas', Granja::todasLasActivas());
    }

    public function exportEstimatedPayroll(Request $request){

        // ini_set('max_execution_time', '300');
        // ini_set('max_input_vars', '5000');
        // ini_set('post_max_size', '20M');

        $initial_date = $request->input('initial_date');
        $final_date = $request->input('final_date');
        $cod_farm = $request->input('cod_farm');
        $cod_location = $request->input('cod_location');
        $employees = json_decode($request->input('employees'));
        $format = $request->input('format');

        $filename = "estimated_payroll_"
            . Carbon::createFromFormat('m-d-Y', $initial_date)->format('Y-m-d')."_"
            . Carbon::createFromFormat('m-d-Y', $final_date)->format('Y-m-d');

        switch($format){
            case 'xlsx':
                return Excel::download(
                    new EstimatedPayrollExport(
                        $initial_date,
                        $final_date,
                        $cod_farm,
                        $cod_location,
                        $employees
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

    public function obtenerEstimatedPayrollData(Request $request){

        $initial_date = $request->input('initial_date');
        $final_date = $request->input('final_date');
        $cod_farm = $request->input('cod_farm');
        $cod_location = $request->input('cod_location');
        $employees = json_decode($request->input('employees'));

        $usuarios = $this->generarReporte(
            $initial_date,
            $final_date,
            $cod_farm,
            $cod_location,
            $employees
        );

        return response()->json([
            "success" => true,
            "usuarios" => $usuarios
        ]);

    }

    /**
     * Generar el reporte de payroll estimado
     * @param mixed $initial_date
     * @param mixed $final_date
     * @param mixed $cod_farm
     * @param mixed $cod_location
     * @param mixed $employees
     * @return array
     */
    public function generarReporte(
        $initial_date,
        $final_date,
        $cod_farm,
        $cod_location,
        $employees,
    ){

        if ($initial_date != "" && $final_date != "") {
            $initial_date = Carbon::createFromFormat('m-d-Y', $initial_date)
                ->startOfDay()
                ->format('Y-m-d H:i:s');
            $final_date = Carbon::createFromFormat('m-d-Y', $final_date)
                ->endOfDay()
                ->format('Y-m-d H:i:s');
        }

        $usuarios = [];

        // Recopilando la información de los usuarios, granjas y locaciones para obtener los cálculos
        foreach($employees as $e){
            $usuario = Usuario::find($e->id);
            $registroIngreso = RegistroIngreso::where('cod_usuario', $usuario->cod_usuario)
                ->where('fecha_ingreso', '>=', $initial_date)
                ->where('fecha_egreso', '<=', $final_date);
            if($cod_farm != 0){
                $registroIngreso->where('cod_farm_ci', $cod_farm);
            }
            if($cod_location != 0){
                $registroIngreso->where('cod_location_ci', $cod_location);
            }
            $usuario->registros = $registroIngreso->get();
            array_push($usuarios, $usuario);
        }


        // Recorremos la data para calcular las horas trabajadas dados los parámetros
        foreach($usuarios as $usuario){
            $milisegundos_usuario = 0;
            foreach($usuario->registros as $registro){
                if(!empty($registro->fecha_egreso)){

                    // Eliminar segundos y milisegundos de las fechas
                    $fecha_ingreso_sin_segundos = (new \DateTime($registro->fecha_ingreso))->format('Y-m-d H:i');
                    $fecha_egreso_sin_segundos = (new \DateTime($registro->fecha_egreso))->format('Y-m-d H:i');

                    $milisegundos = $this->calcularMilisegundosEntreFechas(
                        $fecha_ingreso_sin_segundos,
                        $this->ajustarFechaAlFinalDelDiaSi12AM($fecha_egreso_sin_segundos)
                    );

                    if($registro->lunch_acreditado == "1" && $registro->usuario->es_veterano != "1"){
                        $milisegundos = $milisegundos - (30 * 60000);
                    }else if(
                        $registro->lunch_automatico == "1"
                        && $registro->usuario->es_veterano != "1"
                        && $this->milisegundosAHoras($milisegundos) >= 5){
                        $milisegundos = $milisegundos - (30 * 60000);
                    }

                    // $horas_registro = $milisegundos / 3600000;
                    // $registro->horas_trabajadas = $horas_registro < 0 ? 0 : $horas_registro;
                    $registro->horas_trabajadas = $this->convertirMilisegundosAFormato($milisegundos);
                    $milisegundos_usuario += $milisegundos;
                }
            }
            $usuario->horas_trabajadas_formato = $this->convertirMilisegundosAFormato($milisegundos_usuario);
            $usuario->horas_trabajadas = $this->convertirMilisegundosAHoras($milisegundos_usuario);

        }

        return $usuarios;
    }

    public function formatearHora($time, $sin_punto = false){
        $hours = floor($time);
        $minutes = ($time - $hours) * 60;
        $minutes = number_format(($minutes / 60), 2) * 100;
        $decimalTime = $sin_punto
            ? str_replace(
                ".",
                "",
                ($hours > 0 ? $hours : "") . str_pad($minutes, 2, "0", STR_PAD_LEFT)
            )
            : (string) $hours . "." . (string) str_pad($minutes, 2, "0", STR_PAD_LEFT);

        // Log::info(json_encode([$hours, $minutes]));
        return $decimalTime;
    }

    /**
     * Convierte una hora en formato H:i a hora decimal. Ej. 07:25 -> 7.42, lo devuelve en formato de dos dígitos.
     * La parte de los minutos lo toma y lo divide entre 60, lo multiplica por 100 y lo ajusta a dos digitos. Si el resultado es solo 1 digito, entonces hay que hacer un str_pad para la izquierda con ceros.
     * Si la hora es 00:25 el resultado será solo 41.
     * Si los minutos son pocos,
     * Puede también condicionalmente eliminar el punto
     * @param mixed $timeString
     * @param mixed $sin_punto
     * @return float|string
     */
    public function formatearTiempoAHoraDecimal($timeString, $sin_punto = false){

        // Separar la hora y los minutos
        [$hora, $minutos] = explode(':', $timeString);

        // Quitar ceros a la izquierda de la hora
        $hora = ltrim($hora, '0');

        // Convertir los minutos a su parte decimal
        $minutos_decimal = round(($minutos / 60) * 100);

        // Asegurar que los minutos tengan al menos dos dígitos
        $minutos_decimal = str_pad($minutos_decimal, 2, '0', STR_PAD_LEFT);

        // Formatear el resultado como decimal
        $hora_decimal = "{$hora}.{$minutos_decimal}";

        // Si se solicita sin punto, eliminarlo
        if ($sin_punto) {
            $hora_decimal = str_replace('.', '', $hora_decimal);
        }

        // Convertir el resultado al tipo correcto (float o string)
        return $sin_punto ? $hora_decimal : (float) $hora_decimal;
    }

    function formatearHorasATiempo($totalHours) {
        // Extraer horas y minutos
        $hours = floor($totalHours); // Obtener la parte entera
        $minutes = round(($totalHours - $hours) * 60); // Convertir la parte decimal a minutos

        // Formatear como H:i
        return str_pad($hours, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minutes, 2, '0', STR_PAD_LEFT);
    }

    function formatearTiempoAsNumeroHoras($time){
        // Separar las horas y los minutos
        list($hours, $minutes) = explode(':', $time);

        // Convertir los minutos a fracción de hora y sumarlos a las horas
        return $hours + ($minutes / 60);
    }

    function calcularMinutosEntreFechas($fecha_inicial, $fecha_final){

        $fecha_inicial_datetime = new \DateTime($fecha_inicial);
        $fecha_final_datetime = new \DateTime($fecha_final);

        // Calcular la diferencia en minutos
        $interval = $fecha_inicial_datetime->diff($fecha_final_datetime);

        // Calcular la diferencia total en minutos con más precisión
        $minutos =
            ($interval->days * 24 * 60) +   // Diferencia en días a minutos
            ($interval->h * 60) +           // Diferencia en horas a minutos
            ($interval->i) +                // Diferencia en minutos
            ($interval->s / 60);            // Diferencia en segundos a minutos

        return $minutos;
    }

    function calcularMilisegundosEntreFechas($fecha_inicial, $fecha_final){

        $fecha_inicial_datetime = new \DateTime($fecha_inicial);
        $fecha_final_datetime = new \DateTime($fecha_final);

        // Calcular la diferencia entre las dos fechas
        $interval = $fecha_inicial_datetime->diff($fecha_final_datetime);

        // Calcular la diferencia total en milisegundos
        $milisegundos =
            ($interval->days * 24 * 60 * 60 * 1000) + // Diferencia en días a milisegundos
            ($interval->h * 60 * 60 * 1000) +         // Diferencia en horas a milisegundos
            ($interval->i * 60 * 1000) +              // Diferencia en minutos a milisegundos
            ($interval->s * 1000) +                   // Diferencia en segundos a milisegundos
            (($fecha_final_datetime->format('u') - $fecha_inicial_datetime->format('u')) / 1000); // Diferencia en microsegundos a milisegundos

        return $milisegundos;
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

    /**
     * Toma los milisegundos y los convierte a cantidad de horas (decimal)
     * Devuelve el número formateado a dos decimales
     * @param mixed $milisegundos
     * @return float
     */
    function convertirMilisegundosAHoras($milisegundos){
        // Verificar si el valor proporcionado es numérico
        if (!is_numeric($milisegundos)) {
            throw new \InvalidArgumentException("The parameter must be a number.");
        }

        // Convertir milisegundos a horas
        $horas = $milisegundos / (1000 * 60 * 60);

        // Formatear el resultado a dos decimales
        return round($horas, 2);
    }

    function milisegundosAHoras($milisegundos): float {
        // Convertir milisegundos a horas
        $horas = $milisegundos / 3600000; // 3600000 milisegundos = 1 hora

        return $horas;
    }

    function ajustarFechaAlFinalDelDiaSi12AM($fecha) {
        // Crear un objeto DateTime a partir de la fecha proporcionada
        $fecha_datetime = new \DateTime($fecha);

        // Verificar si la hora es exactamente 00:00:00
        if ($fecha_datetime->format('H:i:s') === '00:00:00') {
            // Ajustar la hora al final del día
            $fecha_datetime->setTime(23, 59, 59);
        }

        // Retornar la fecha ajustada como string
        return $fecha_datetime->format('Y-m-d H:i:s');
    }
}
