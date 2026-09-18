<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\HelpController;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Granja;
use App\Models\RegistroIngreso;
use App\Models\Locacion;
use App\Models\Usuario;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Exports\EmployeeHoursLocationExport;
use Illuminate\Support\Facades\DB;

class ReporteEmployeeHoursLocationController extends Controller
{
    public function index()
    {
        return view('admin.reportes.reporte_employee_hours_location')
            ->with('listaGranjas', Granja::todasLasActivas());
    }

    public function exportEmployeeHoursLocation(Request $request)
    {

        // ini_set('max_execution_time', '300');
        // ini_set('max_input_vars', '5000');
        // ini_set('post_max_size', '20M');

        $initial_date = $request->input('initial_date');
        $final_date = $request->input('final_date');
        $cod_farm = $request->input('cod_farm');
        $cod_location = $request->input('cod_location');
        $employees = json_decode($request->input('employees'));
        $format = $request->input('format');

        $filename = "employee_hours_location_"
            . Carbon::createFromFormat('m-d-Y', $initial_date)->format('Y-m-d') . "_"
            . Carbon::createFromFormat('m-d-Y', $final_date)->format('Y-m-d');

        switch ($format) {
            case 'xlsx':
                return Excel::download(
                    new EmployeeHoursLocationExport(
                        $initial_date,
                        $final_date,
                        $cod_farm,
                        $cod_location,
                        $employees
                    ),
                    $filename . '.xlsx',
                    null,
                    [
                        'Content-Type' => 'application/octet-stream',
                        'Content-Disposition' => 'attachment; filename="' . $filename . '.xlsx"',
                    ]
                );
            case 'txt':
                // Establecer las cabeceras para la descarga del archivo
                return response()->stream(function () use ($initial_date, $final_date, $cod_farm, $cod_location, $employees) {
                    // Obteniendo usuarios
                    $usuarios = $this->generarReporteUsuarios(
                        $initial_date,
                        $final_date,
                        $cod_farm,
                        $cod_location,
                        $employees
                    );

                    // Abrir el "stream" de salida
                    $handle = fopen('php://output', 'w');

                    $location = null;
                    if ($cod_location != 0) {
                        $location = Locacion::find($cod_location);
                    }

                    // Generamos las lineas
                    foreach ($usuarios as $usuario) {
                        foreach ($usuario->registros as $registro) {
                            $line = str_pad('0', 9, ' ') // Primer campo con 9 caracteres (relleno de espacios)
                                . str_pad($usuario->pin, 6, ' ', pad_type: STR_PAD_RIGHT) // Pin del usuario
                                . str_pad($usuario->es_veterano == 0 ? 'Reg' : ($usuario->es_veterano == 1 ? 'Reg' : 'H2A'), 6, ' ') // Reg, Vac, H2A
                                . str_pad("1" . (!empty($usuario->granja) ? $usuario->granja->estado->acronimo : "FL") . Carbon::createFromFormat('Y-m-d H:i:s', $registro->fecha_ingreso)->format('m/d/Y'), 13, ' ') // Acronimo de estado y fecha
                                . str_pad(0, 4, ' ') // Weeks Worked
                                . str_pad(0, 4, ' ') // Days Worked
                                . str_pad($this->formatearTiempoAHoraDecimal($registro->horas_trabajadas, true), 9, ' ', STR_PAD_LEFT) // Horas trabajadas
                                . str_pad('', 9, ' ') // Hours Overtime
                                . str_pad('', 9, ' ') // Hours Double Time
                                . str_pad('', 11, ' ') // total pieces
                                . str_pad('', 11, ' ') // Pieces Overtime
                                . str_pad('', 11, ' ') // Pieces Double Time
                                . str_pad('', 9, ' ') // rate
                                . str_pad('', 11, ' ') // Amount
                                . str_pad('Y', 1, ' ', STR_PAD_LEFT) // Un carácter fijo ('Y')
                                . str_pad('', 12, ' ') // Crew Worked
                                . str_pad('', 6, ' ') // Department ID
                                . str_pad('', 12, ' ') // GL Account ID
                                . str_pad($registro->locacion != null ? $registro->locacion->cost_center : ($location != null ? $location->cost_center : 0), 12, ' ', STR_PAD_RIGHT) // Cost center
                                . str_pad($registro->locacion != null ? $registro->locacion->labor_phase : ($location != null ? $location->labor_phase : 0), 6, ' ', STR_PAD_RIGHT) // Labor phase
                                . str_pad('', 12, ' ') // Grower Block ID
                                . str_pad('', 8, ' ') // Worker Comp Code
                                . str_pad('', 12, ' ') // Equipment ID 1
                                . str_pad('', 12, ' ') // Equipment ID 2
                                . str_pad('', 12, ' ') // Equipment ID 3
                                . str_pad('', 12, ' ') // Lot
                                . str_pad('', 1, ' ') // Guarantee Minimum Wage
                                . str_pad('', 1, ' '); // Piece Rate Flag
                            // Escribir la línea en el archivo "stream"
                            fwrite($handle, $line . PHP_EOL);
                        }
                    }

                    // Cerrar el "stream"
                    fclose($handle);
                }, 200, [
                    'Content-Type' => 'text/plain', // Tipo de contenido del archivo
                    'Content-Disposition' => 'attachment; filename="' . $filename . '.txt"', // Indica que es una descarga
                ]);
            default:
                return response('Invalid format');
        }
    }

    public function obtenerReporteEmpleadosHoras(Request $request)
    {

        $initial_date = $request->input('initial_date');
        $final_date = $request->input('final_date');
        $cod_farm = $request->input('cod_farm');
        $cod_location = $request->input('cod_location');
        $employees = json_decode($request->input('employees'));

        $usuarios = $this->generarReporteUsuariosConsolidado(
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
     * Generar el reporte de horas por usuarios, generando una linea por cada día
     * @param mixed $initial_date
     * @param mixed $final_date
     * @param mixed $cod_farm
     * @param mixed $cod_location
     * @param mixed $employees
     * @return array
     */
    public function generarReporteUsuarios(
        $initial_date,
        $final_date,
        $cod_farm,
        $cod_location,
        $employees,
    ) {

        // dd($initial_date);

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
        foreach ($employees as $e) {
            $usuario = Usuario::find($e->id);
            $registroIngreso = RegistroIngreso::where('cod_usuario', $usuario->cod_usuario)
                ->where('fecha_ingreso', '>=', $initial_date)
                ->where('fecha_egreso', '<=', $final_date);
            if ($cod_farm != 0) {
                $registroIngreso->where('cod_farm_ci', $cod_farm);
            }
            if ($cod_location != 0) {
                $registroIngreso->where('cod_location_ci', $cod_location);
            }
            $usuario->registros = $registroIngreso->get();
            array_push($usuarios, $usuario);
        }


        // Recorremos la data para calcular las horas trabajadas dados los parámetros
        foreach ($usuarios as $usuario) {
            $horas_usuario = 0;
            foreach ($usuario->registros as $registro) {
                if (!empty($registro->fecha_egreso)) {

                    // Eliminar segundos y milisegundos de las fechas
                    $fecha_ingreso_sin_segundos = (new \DateTime($registro->fecha_ingreso))->format('Y-m-d H:i');
                    $fecha_egreso_sin_segundos = (new \DateTime($registro->fecha_egreso))->format('Y-m-d H:i');

                    $milisegundos = $this->calcularMilisegundosEntreFechas(
                        $fecha_ingreso_sin_segundos,
                        $this->ajustarFechaAlFinalDelDiaSi12AM($fecha_egreso_sin_segundos)
                    );

                    if ($registro->lunch_acreditado == "1" && $registro->usuario->es_veterano != "1") {
                        $milisegundos = $milisegundos - (30 * 60000);
                    } else if (
                        $registro->lunch_automatico == "1"
                        && $registro->usuario->es_veterano != "1"
                        && $this->milisegundosAHoras($milisegundos) >= 5
                    ) {
                        $milisegundos = $milisegundos - (30 * 60000);
                    }

                    // $horas_registro = $milisegundos / 3600000;
                    // $registro->horas_trabajadas = $horas_registro < 0 ? 0 : $horas_registro;
                    $registro->horas_trabajadas = $this->convertirMilisegundosAFormato($milisegundos);
                }
            }
            $usuario->horas_trabajadas += $horas_usuario;
        }

        return $usuarios;
    }

    public function generarReporteUsuariosConsolidado(
        $initial_date,
        $final_date,
        $cod_farm,
        $cod_location,
        $employees,
    ) {

        // dd($initial_date);

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
        $granjasSeleccionadas = [];
        $locacionesSeleccionadas = [];
        // if ($cod_farm == 0) {
        //     $granjasSeleccionadas = Granja::todasLasActivas();
        // } else {
        //     $granjasSeleccionadas = Granja::where('cod_farms', $cod_farm)->get();
        // }
        // if ($cod_location == 0) {
        //     $locacionesSeleccionadas = Locacion::todasLasActivas();
        // } else {
        //     $locacionesSeleccionadas = Locacion::where('cod_location', $cod_location)->get();
        // }

        foreach ($employees as $e) {
            $usuario = Usuario::find($e->id);
            if ($cod_farm == 0) {
                // $usuario->granjas = Granja::todasLasActivas();
                $granjasSeleccionadas = Granja::todasLasActivas();
            } else {
                $granjasSeleccionadas = Granja::where('cod_farms', $cod_farm)->get();
                // $usuario->granjas = Granja::where('cod_farms', $cod_farm)->get();
            }
            $usuario->granjas = $granjasSeleccionadas;
            foreach ($usuario->granjas as $granja) {
                if ($cod_location == 0) {
                    // $granja->locations = Locacion::todasLasActivas();
                    $locacionesSeleccionadas = Locacion::todasLasActivas();
                } else {
                    $locacionesSeleccionadas = Locacion::where('cod_location', $cod_location)->get();
                    // $granja->locations = Locacion::where('cod_location', $cod_location)->get();
                }
                $granja->locations = $locacionesSeleccionadas;
            }
            array_push($usuarios, $usuario);
        }

        // $granjasSeleccionadas = $cod_farm == 0 ? Granja::todasLasActivas() : Granja::where('cod_farms', $cod_farm)->get();
        // $locacionesSeleccionadas = $cod_location == 0 ? Locacion::todasLasActivas() : Locacion::where('cod_location', $cod_location)->get();
        // foreach ($employees as $e) {
        //     $usuario = Usuario::find($e->id);
        //     $usuario->granjas = $granjasSeleccionadas;
        //     foreach ($usuario->granjas as $granja) {
        //         $granja->locations = $locacionesSeleccionadas;
        //     }
        //     array_push($usuarios, $usuario);
        // }


        // Recorremos la data para calcular las horas trabajadas dados los parámetros
        foreach ($usuarios as $usuario) {
            $milisegundosUsuario = 0;
            foreach ($usuario->granjas as $granja) {
                $milisegundosGranja = 0;
                //  Log::info("========== Farm $granja->cod_farms ==========");
                foreach ($granja->locations as $location) {
                    //    Log::info("========== Location $location->cod_location ==========");
                    $registros = RegistroIngreso::where('cod_usuario', $usuario->cod_usuario)
                        ->where('fecha_ingreso', '>=', $initial_date)
                        ->where('fecha_egreso', '<=', $final_date)
                        ->where('cod_farm_ci', $granja->cod_farms)
                        ->where('cod_location_ci', $location->cod_location);
                    // $registros = DB::table('pay_bitacora_inicio_sesion as bita')
                    //     ->join('usu_usuarios as usu', 'usu.cod_usuario', '=', 'bita.cod_usuario')
                    //     ->select(
                    //         'bita.cod_usuario',
                    //         'bita.fecha_egreso',
                    //         'bita.fecha_ingreso',
                    //         'bita.lunch_acreditado',
                    //         'bita.lunch_automatico',
                    //         'usu.es_veterano'
                    //     )
                    //     ->where('bita.cod_usuario', '>=', $usuario->cod_usuario)
                    //     ->where('bita.fecha_ingreso', '>=', $initial_date)
                    //     ->where('bita.fecha_egreso', '<=', $final_date)
                    //     ->where('bita.cod_farm_ci', $granja->cod_farms)
                    //     ->where('bita.cod_location_ci', $location->cod_location);
                    $result = $registros->get();
                    // if(count($result) > 0){
                    //     Log::info(json_encode([$initial_date, $final_date, $granja->cod_farms, $location->cod_location]));
                    // }
                    $milisegundosTrabajados = 0;
                    if (count($result) == 0) {
                        //Log::info($registros->toRawSql());
                    }

                    foreach ($result as $registro) {

                        //Log::info("Fechas: " . json_encode([$registro->fecha_ingreso, $registro->fecha_egreso]));

                        // Eliminar segundos y milisegundos de las fechas
                        $fecha_ingreso_sin_segundos = (new \DateTime($registro->fecha_ingreso))->format('Y-m-d H:i');
                        $fecha_egreso_sin_segundos = (new \DateTime($registro->fecha_egreso))->format('Y-m-d H:i');

                        $milisegundos = $this->calcularMilisegundosEntreFechas(
                            $fecha_ingreso_sin_segundos,
                            $this->ajustarFechaAlFinalDelDiaSi12AM($fecha_egreso_sin_segundos)
                        );

                        //Log::info(message: "Milisegundos antes: " . $milisegundos);

                        if ($registro->lunch_acreditado == "1" && $registro->usuario->es_veterano != "1") {
                        // if ($registro->lunch_acreditado == "1" && $registro->es_veterano != "1") {
                            $milisegundos = $milisegundos - (30 * 60000);
                        } else if (
                            $registro->lunch_automatico == "1"
                            && $registro->usuario->es_veterano != "1"
                            // && $registro->es_veterano != "1"
                            && $this->milisegundosAHoras($milisegundos) >= 5
                        ) {
                            $milisegundos = $milisegundos - (30 * 60000);
                        }

                        //   Log::info(message: "Milisegundos despues: " . $milisegundos);

                        $milisegundosTrabajados += $milisegundos;

                        //  Log::info("Horas trabajadas: " . $this->convertirMilisegundosAFormato($milisegundos));
                    }
                    // $location->registrosTEMP = $result;

                    $location->horas_trabajadas = $this->convertirMilisegundosAFormato($milisegundosTrabajados);
                    $location->horas_trabajadas_decimales = $this->formatearTiempoAHoraDecimal($this->convertirMilisegundosAFormato($milisegundosTrabajados));
                    $milisegundosGranja += $milisegundosTrabajados;
                }
                $granja->horas_trabajadas = $this->convertirMilisegundosAFormato($milisegundosGranja);
                $granja->horas_trabajadas_decimales = $this->formatearTiempoAHoraDecimal($this->convertirMilisegundosAFormato($milisegundosGranja));
                $milisegundosUsuario += $milisegundosGranja;
            }
            $usuario->horas_trabajadas = $this->convertirMilisegundosAFormato($milisegundosUsuario);
            $usuario->horas_trabajadas_decimales = $this->formatearTiempoAHoraDecimal($this->convertirMilisegundosAFormato($milisegundosUsuario));
        }


        HelpController::desconectarBaseDatos();
        return $usuarios;
    }

    public function formatearHora($time, $sin_punto = false)
    {
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
    public function formatearTiempoAHoraDecimal($timeString, $sin_punto = false)
    {

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

    function formatearHorasATiempo($totalHours)
    {
        // Extraer horas y minutos
        $hours = floor($totalHours); // Obtener la parte entera
        $minutes = round(($totalHours - $hours) * 60); // Convertir la parte decimal a minutos

        // Formatear como H:i
        return str_pad($hours, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minutes, 2, '0', STR_PAD_LEFT);
    }

    function formatearTiempoAsNumeroHoras($time)
    {
        // Separar las horas y los minutos
        list($hours, $minutes) = explode(':', $time);

        // Convertir los minutos a fracción de hora y sumarlos a las horas
        return $hours + ($minutes / 60);
    }

    function calcularMinutosEntreFechas($fecha_inicial, $fecha_final)
    {

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

    function calcularMilisegundosEntreFechas($fecha_inicial, $fecha_final)
    {

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

    function milisegundosAHoras($milisegundos): float
    {
        // Convertir milisegundos a horas
        $horas = $milisegundos / 3600000; // 3600000 milisegundos = 1 hora

        return $horas;
    }

    function ajustarFechaAlFinalDelDiaSi12AM($fecha)
    {
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
