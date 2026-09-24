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
use App\Services\CalculoHorasService;

class ReporteEstimatedPayrollController extends Controller
{
    protected $calculoHorasService;

    public function __construct(?CalculoHorasService $calculoHorasService = null)
    {
        $this->calculoHorasService = $calculoHorasService ?? app(CalculoHorasService::class);
    }

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
        $usuarios = [];

        foreach($employees as $e){
            $usuario = Usuario::find($e->id);
            if (!$usuario) {
                continue;
            }

            // Obtenemos los registros deduplicados por fecha_ingreso
            $registros = $this->calculoHorasService->obtenerRegistrosDeduplicados(
                $usuario->cod_usuario,
                $initial_date,
                $final_date,
                $cod_farm,
                $cod_location
            );

            $esVeterano = $usuario->es_veterano ?? 0;
            $totales = $this->calculoHorasService->calcularTotalesRegistros($registros, $esVeterano);

            $usuario->registros = $registros;
            $usuario->horas_trabajadas_formato = $totales['horas_formato'];
            $usuario->horas_trabajadas = $totales['horas_decimal'];

            array_push($usuarios, $usuario);
        }

        return $usuarios;
    }

    public function formatearHora($time, $sin_punto = false){
        return $this->calculoHorasService->formatearHora($time, $sin_punto);
    }

    public function formatearTiempoAHoraDecimal($timeString, $sin_punto = false){
        return $this->calculoHorasService->formatearTiempoAHoraDecimal($timeString, $sin_punto);
    }

    public function formatearHorasATiempo($totalHours) {
        return $this->calculoHorasService->formatearHorasATiempo($totalHours);
    }

    public function formatearTiempoAsNumeroHoras($time){
        return $this->calculoHorasService->formatearTiempoAsNumeroHoras($time);
    }

    public function calcularMinutosEntreFechas($fecha_inicial, $fecha_final){
        return $this->calculoHorasService->calcularMinutosEntreFechas($fecha_inicial, $fecha_final);
    }

    public function calcularMilisegundosEntreFechas($fecha_inicial, $fecha_final){
        return $this->calculoHorasService->calcularMilisegundosEntreFechas($fecha_inicial, $fecha_final);
    }

    public function convertirMilisegundosAFormato($milisegundos) {
        return $this->calculoHorasService->convertirMilisegundosAFormato($milisegundos);
    }

    public function convertirMilisegundosAHoras($milisegundos){
        return $this->calculoHorasService->convertirMilisegundosAHoras($milisegundos);
    }

    public function milisegundosAHoras($milisegundos): float {
        return $this->calculoHorasService->milisegundosAHoras($milisegundos);
    }

    public function ajustarFechaAlFinalDelDiaSi12AM($fecha) {
        return $this->calculoHorasService->ajustarFechaAlFinalDelDiaSi12AM($fecha);
    }
}
