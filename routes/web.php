<?php

use App\Http\Controllers\AdminRegistroAppController;
use App\Http\Controllers\TokenSesionController;
use App\Http\Controllers\Reportes\ReporteEmployeeHoursLocationController;
use App\Http\Controllers\Reportes\ReportePieceRateReportController;
use App\Http\Controllers\Reportes\ReporteMiscPieceRateReportController;
use App\Http\Controllers\Reportes\ReporteLocationExecutiveSummaryController;
use App\Http\Controllers\Reportes\ReporteEstimatedPayrollController;
use App\Http\Controllers\Reportes\ReporteDynamicReportController;
use App\Http\Controllers\Reportes\ReporteSingSheetsController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\VerificarInicioSesion;

// Route::get('/', function () {
//     return view('welcome');
// });
// Route::get('admin_app_lmf', function () {
//     return view('welcome');
// });

Route::middleware(VerificarInicioSesion::class)
    ->get('/', [AdminRegistroAppController::class, 'index'])->name('home');
// Route::middleware(VerificarInicioSesion::class)
//     ->get('/', function () {
//         return view('admin.registro_tarea');
//     });
// Route::get('/', [AdminRegistroAppController::class, 'index'])->name('home');
Route::post('/public/access_token', [TokenSesionController::class, 'manejarAccessToken']);
Route::post('/access_token', [TokenSesionController::class, 'manejarAccessToken']);
Route::get('/task_registration_app', [AdminRegistroAppController::class, 'index']);

Route::prefix('/reportes')->group(function(){
    Route::get('/employee_hours_location', [ReporteEmployeeHoursLocationController::class, 'index'])
        ->name('employee_hours_location');
    Route::get('/piece_rate_report', [ReportePieceRateReportController::class, 'index'])
        ->name('piece_rate_report');
    Route::get('/misc_piece_rate_report', [ReporteMiscPieceRateReportController::class, 'index'])
        ->name('misc_piece_rate_report');
    Route::get('/location_executive_report', [ReporteLocationExecutiveSummaryController::class, 'index'])
        ->name('location_executive_report');
    Route::get('/estimated_payroll', [ReporteEstimatedPayrollController::class, 'index'])
        ->name('estimated_payroll');
    Route::get('/dynamic_report', [ReporteDynamicReportController::class, 'index'])
        ->name('dynamic_report');

        Route::get('/sing_sheets_report', [ReporteSingSheetsController::class, 'index'])
        ->name('sing_sheets_report');
});

Route::prefix('v1')->group(function () {

    Route::prefix('empleado')->group(function () {
        // UserController
        Route::POST('/listaEmpleadoPorGranja', [AdminRegistroAppController::class, 'listaEmpleadoPorGranja']);
        Route::POST('/registrosIngresosYEgresos', [AdminRegistroAppController::class, 'registrosIngresosYEgresos']);
        Route::POST('/registroDeTareasPorUsuarioYFecha', [AdminRegistroAppController::class, 'registroDeTareasPorUsuarioYFecha']);

        Route::POST('/actualizarHorasEntradaSalida', [AdminRegistroAppController::class, 'actualizarHorasEntradaSalida']);
        Route::POST('/actualizarHorasInicioFinalTarea', [AdminRegistroAppController::class, 'actualizarHorasInicioFinalTarea']);

        Route::POST('/exportEmployeeHoursLocation', [ReporteEmployeeHoursLocationController::class, 'exportEmployeeHoursLocation'])
            ->name('exportEmployeeHoursLocation');
        Route::POST('/exportPieceRateFile', [ReportePieceRateReportController::class, 'exportPieceRateFile'])
            ->name('exportPieceRateFile');
        Route::POST('/exportMiscPieceRateFile', [ReporteMiscPieceRateReportController::class, 'exportMiscPieceRateFile'])
            ->name('exportMiscPieceRateFile');
        Route::POST('/exportLocationExecutiveSummary', [ReporteLocationExecutiveSummaryController::class, 'exportLocationExecutiveSummary'])
            ->name('exportLocationExecutiveSummary');
        Route::POST('/exportEstimatedPayroll', [ReporteEstimatedPayrollController::class, 'exportEstimatedPayroll'])
            ->name('exportEstimatedPayroll');
        Route::POST('/exportDynamicReport', [ReporteDynamicReportController::class, 'exportDynamicReport'])
            ->name('exportDynamicReport');

            Route::POST('/exportSignSheetReport', [ReporteSingSheetsController::class, 'exportSignSheetReport'])
            ->name('exportSignSheetReport');
    });
});
