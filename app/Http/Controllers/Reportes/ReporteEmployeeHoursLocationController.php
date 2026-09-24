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
use App\Services\CalculoHorasService;

class ReporteEmployeeHoursLocationController extends Controller
{
    protected $calculoHorasService;

    public function __construct(?CalculoHorasService $calculoHorasService = null)
    {
        $this->calculoHorasService = $calculoHorasService ?? app(CalculoHorasService::class);
    }

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
        $usuarios = [];

        foreach ($employees as $e) {
            $usuario = Usuario::find($e->id);
            if (!$usuario) {
                continue;
            }

            // Obtener registros deduplicados por fecha_ingreso
            $registros = $this->calculoHorasService->obtenerRegistrosDeduplicados(
                $usuario->cod_usuario,
                $initial_date,
                $final_date,
                $cod_farm,
                $cod_location
            );

            // Calcular horas de cada registro
            $this->calculoHorasService->calcularTotalesRegistros($registros, $usuario->es_veterano ?? 0);

            $usuario->registros = $registros;
            array_push($usuarios, $usuario);
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
        $usuarios = [];

        // Recopilando la información de las granjas y locaciones base
        $granjasBase = $cod_farm == 0 ? Granja::todasLasActivas() : Granja::where('cod_farms', $cod_farm)->get();

        foreach ($employees as $e) {
            $usuario = Usuario::find($e->id);
            if (!$usuario) {
                continue;
            }

            // Clonar granjas y locaciones para garantizar colecciones e instancias independientes por usuario
            $granjasUsuario = [];
            foreach ($granjasBase as $granjaOriginal) {
                $granja = clone $granjaOriginal;
                if ($cod_location == 0) {
                    $locaciones = Locacion::where('activo', '1')
                        ->where('cod_farms', $granja->cod_farms)
                        ->get();
                } else {
                    $locaciones = Locacion::where('cod_location', $cod_location)->get();
                }

                $locationsGranja = [];
                foreach ($locaciones as $locOriginal) {
                    $locationsGranja[] = clone $locOriginal;
                }
                $granja->locations = collect($locationsGranja);
                $granjasUsuario[] = $granja;
            }

            $usuario->granjas = collect($granjasUsuario);
            array_push($usuarios, $usuario);
        }

        // Recorremos la data para calcular las horas trabajadas dados los parámetros
        foreach ($usuarios as $usuario) {
            $milisegundosUsuario = 0;
            $es_veterano = $usuario->es_veterano ?? 0;

            foreach ($usuario->granjas as $granja) {
                $milisegundosGranja = 0;

                foreach ($granja->locations as $location) {
                    // Obtener registros deduplicados para esta granja y locación
                    $registros = $this->calculoHorasService->obtenerRegistrosDeduplicados(
                        $usuario->cod_usuario,
                        $initial_date,
                        $final_date,
                        $granja->cod_farms,
                        $location->cod_location
                    );

                    $totalesLoc = $this->calculoHorasService->calcularTotalesRegistros($registros, $es_veterano);

                    $location->horas_trabajadas = $totalesLoc['horas_formato'];
                    $location->horas_trabajadas_decimales = $totalesLoc['horas_decimal_formato'];
                    $milisegundosGranja += $totalesLoc['milisegundos'];
                }
                $granja->horas_trabajadas = $this->calculoHorasService->convertirMilisegundosAFormato($milisegundosGranja);
                $granja->horas_trabajadas_decimales = $this->calculoHorasService->formatearTiempoAHoraDecimal($granja->horas_trabajadas);
                $milisegundosUsuario += $milisegundosGranja;
            }
            $usuario->horas_trabajadas = $this->calculoHorasService->convertirMilisegundosAFormato($milisegundosUsuario);
            $usuario->horas_trabajadas_decimales = $this->calculoHorasService->formatearTiempoAHoraDecimal($usuario->horas_trabajadas);
        }

        HelpController::desconectarBaseDatos();
        return $usuarios;
    }

    public function formatearHora($time, $sin_punto = false)
    {
        return $this->calculoHorasService->formatearHora($time, $sin_punto);
    }

    public function formatearTiempoAHoraDecimal($timeString, $sin_punto = false)
    {
        return $this->calculoHorasService->formatearTiempoAHoraDecimal($timeString, $sin_punto);
    }

    public function formatearHorasATiempo($totalHours)
    {
        return $this->calculoHorasService->formatearHorasATiempo($totalHours);
    }

    public function formatearTiempoAsNumeroHoras($time)
    {
        return $this->calculoHorasService->formatearTiempoAsNumeroHoras($time);
    }

    public function calcularMinutosEntreFechas($fecha_inicial, $fecha_final)
    {
        return $this->calculoHorasService->calcularMinutosEntreFechas($fecha_inicial, $fecha_final);
    }

    public function calcularMilisegundosEntreFechas($fecha_inicial, $fecha_final)
    {
        return $this->calculoHorasService->calcularMilisegundosEntreFechas($fecha_inicial, $fecha_final);
    }

    public function convertirMilisegundosAFormato($milisegundos)
    {
        return $this->calculoHorasService->convertirMilisegundosAFormato($milisegundos);
    }

    public function milisegundosAHoras($milisegundos): float
    {
        return $this->calculoHorasService->milisegundosAHoras($milisegundos);
    }

    public function ajustarFechaAlFinalDelDiaSi12AM($fecha)
    {
        return $this->calculoHorasService->ajustarFechaAlFinalDelDiaSi12AM($fecha);
    }
}
