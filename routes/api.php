<?php

use App\Http\Controllers\ActividadesController;
use App\Http\Controllers\AdminRegistroAppController;
use App\Http\Controllers\API\CampoAPIController;
use App\Http\Controllers\API\CropAgeAPIController;
use App\Http\Controllers\API\LocacionAPController;
use App\Http\Controllers\API\RegistroIngresoAPIController;
use App\Http\Controllers\API\TareaAPIController;
use App\Http\Controllers\API\TareaProgramadaAPIController;
use App\Http\Controllers\API\UserAPIController;
use App\Http\Controllers\API\ActividadAPIController;
use App\Http\Controllers\API\BloqueAPIController;
use App\Http\Controllers\API\CrewJobAPIController;
use App\Http\Controllers\API\EmpleadoAPIController;
use App\Http\Controllers\BloquesController;
use App\Http\Controllers\CamposController;
use App\Http\Controllers\ControlDeHorasController;
use App\Http\Controllers\ControlDeTrabajosController;
use App\Http\Controllers\CorreosController;
use App\Http\Controllers\CrewJobController;
use App\Http\Controllers\CrewsController;
use App\Http\Controllers\CropAgeController;
use App\Http\Controllers\EmpleadosController;
use App\Http\Controllers\GestionesController;
use App\Http\Controllers\GranjasController;
use App\Http\Controllers\HarvestsController;
use App\Http\Controllers\LocacionesController;
use App\Http\Controllers\MiscelaneosController;
use App\Http\Controllers\PagosController;
use App\Http\Controllers\PaquetesController;
use App\Http\Controllers\RegistroIngresos;
use App\Http\Controllers\TareasController;
use App\Http\Controllers\TareasProgramadasController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Reportes\ReporteEmployeeHoursLocationController;
use App\Http\Controllers\Reportes\ReportePieceRateReportController;
use App\Http\Controllers\Reportes\ReporteMiscPieceRateReportController;
use App\Http\Controllers\Reportes\ReporteLocationExecutiveSummaryController;
use App\Http\Controllers\Reportes\ReporteEstimatedPayrollController;
use App\Http\Controllers\Reportes\ReporteDynamicReportController;
use App\Http\Controllers\Reportes\ReporteSingSheetsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::prefix('v1')->group(function () {

    // GET /RUTA
    // FileController@index

    // GET /RUTA/create
    // FileController@create

    // POST /RUTA
    // FileController@store

    // GET /RUTA/{id}
    // FileController@show

    // GET /RUTA/{id}/edit
    // FileController@edit

    // PUT/PATCH /RUTA/{id}
    // FileController@update

    // DELETE /RUTA/{id}
    // FileController@destroy
    Route::resources([
        'actividades' => ActividadesController::class,
        'bloques' => BloquesController::class,
        'campos' => CamposController::class,
        'controlDeHoras' => ControlDeHorasController::class,
        'controlDeTrabajos' => ControlDeTrabajosController::class,
        'correos' => CorreosController::class,
        'crews' => CrewsController::class,
        'cropAge' => CropAgeController::class,
        'gestiones' => GestionesController::class,
        'granjas' => GranjasController::class,
        'locaciones' => LocacionesController::class,
        'miscelaneos' => MiscelaneosController::class,
        'pagos' => PagosController::class,
        'paquetes' => PaquetesController::class,
        'registro_ingreso' => RegistroIngresos::class,
        'tareas_programadas' => TareasProgramadasController::class,
        'tareas' => TareasController::class,
        'empleados' => EmpleadosController::class,
        'usuario' => UserController::class,
        'harvest' => HarvestsController::class,
    ]);

    // UserController
    Route::get('/buscarEmpleadosAsignados/{id_jefe}', [UserController::class, 'buscarEmpleadosAsignados']);
    Route::get('/listadoCrewPrevio/{id_jefe}', [UserController::class, 'listadoCrewPrevio']);
    Route::get('/eliminarPersonaDelCrewPrevio/{id_lista_crew_previo}', [UserController::class, 'eliminarPersonaDelCrewPrevio']);
    Route::get('/crearTodosLosPIN', [UserController::class, 'crearPINTodosUsuarios']);
    Route::put('/crearUsuarioNuevo', [UserController::class, 'asignarDatosAUsuarios']);


    // TareasController
    Route::get('/tareasAsignadasPorJefe/{id_jefe}', [TareasController::class, 'tareasAsignadasPorJefe']);
    Route::post('/cambiarEstadoTarea', [TareasController::class, 'cambiarEstadoTarea']);
    Route::post('/agregarCantidadEscaneo', [TareasController::class, 'agregarCantidadEscaneo']);
    Route::post('/agregarCantidadEscaneoPendientes', [TareasController::class, 'agregarCantidadEscaneoPendientes']);
    Route::post('/normalizarCantidadCajasEscaneadas', [TareasController::class, 'normalizarCantidadCajasEscaneadas']);
    Route::post('/actualizarCantidadEspecificaDeCajas', [TareasController::class, 'actualizarCantidadEspecificaDeCajas']);
    Route::post('/actualizarEstadoEmpleado', [TareasController::class, 'actualizarEstadoEmpleado']);
    Route::post('/promediarCajasEnTareaHarvest', [TareasController::class, 'promediarCajasEnTareaHarvest']);
    Route::post('/eliminarTarea', [TareasController::class, 'eliminarTarea']);


    Route::post('/listaPaquetesAsociadosGranjasLocacionInventario', [PaquetesController::class, 'listaPaquetesAsociadosGranjasLocacionInventario']);
    Route::get('/listaTiposPaquetesAsociados', [PaquetesController::class, 'listaTiposPaquetesAsociados']);

    // LocacionesController
    Route::get('/listaLocacionesPorGranja/{id_granja}', [LocacionesController::class, 'listaLocacionesPorGranja']);
    // ActividadesController
    Route::post('/listaActividadesPorGranjaLocacion', [ActividadesController::class, 'listaActividadesPorGranjaLocacion']);

    // CamposController
    Route::post('/campoUsadoEnCropAge', [CamposController::class, 'campoUsadoEnCropAge']);
    Route::post('/obtenerCampos', [CamposController::class, 'obtenerCampos']);

    // BloquesController
    Route::post('/bloquesEnCropAgeAsociadosCampo', [BloquesController::class, 'bloquesEnCropAgeAsociadosCampo']);
    Route::post('/bloqueUsadoEnCropAge', [BloquesController::class, 'bloqueUsadoEnCropAge']);
    Route::post('/bloqueUsadoEnCampoCropAge', [BloquesController::class, 'bloqueUsadoEnCampoCropAge']);

    // CrewJobController
    Route::post('/crearCrewJob', [CrewJobController::class, 'crearCrewJob']);

    // RegistroIngresos
    Route::post('/iniciarSesion', [RegistroIngresos::class, 'IniciarSesion']);
    Route::post('/registrarIngresoMasivo', [RegistroIngresos::class, 'registrarIngresoMasivo']);
    Route::post('/iniciarSesionAdmin', [RegistroIngresos::class, 'IniciarSesionAdmin']);

    Route::get('/cropAsociadasAGranja/{id_granja}', [CropAgeController::class, 'cropAsociadasAGranja']);
    Route::get('/cropAsociadasAGranjaSinVerificarDiasPlantados/{id_granja}', [CropAgeController::class, 'cropAsociadasAGranjaSinVerificarDiasPlantados']);









    //Nueva forma de llamar a las API
    Route::prefix('tareas_frecuentes')->group(function () {
        Route::post('/CulminarTareasCaducadas', [TareaProgramadaAPIController::class, 'CulminarTareasCaducadas']);
        Route::post('/crearRegistrosTiposPaquetesAsociados', [PaquetesController::class, 'crearRegistrosTiposPaquetesAsociados']);
    });
    Route::prefix('tareas_automaticas')->group(function () {
        Route::get('/cargarTodaLaInformacionNecesaria/{id_jefe}', [TareaProgramadaAPIController::class, 'cargarTodaLaInformacionNecesaria']);
    });

    Route::prefix('registroIngresos')->group(function () {
        Route::post('/registroIngresoPINEmpleado', [RegistroIngresoAPIController::class, 'registroIngresoPINEmpleado']);
        Route::post('/registrarIngresosDePINPendientes', [RegistroIngresoAPIController::class, 'registrarIngresosDePINPendientes']);
        // Route::post('/iniciarSesionAdmin', [RegistroIngresoAPIController::class, 'iniciarSesionAdmin']);
    });

    Route::prefix('empleado')->group(function () {
        // UserController
        Route::get('/listaDeEmpleadosPermitidos', [EmpleadoAPIController::class, 'listaDeEmpleadosPermitidos']);
        Route::get('/listaTodosLosEmpleados', [EmpleadoAPIController::class, 'listaTodosLosEmpleados']);
        Route::post('/agregarEmpleadosATareaExistente', [EmpleadoAPIController::class, 'agregarEmpleadosATareaExistente']);
    });
    Route::prefix('user')->group(function () {
        // UserController
        Route::get('/buscarEmpleadosAsignados/{id_jefe}', [UserAPIController::class, 'buscarEmpleadosAsignados']);
        Route::get('/listadoCrewPrevio/{id_jefe}', [UserAPIController::class, 'listadoCrewPrevio']);
        Route::get('/eliminarPersonaDelCrewPrevio/{id_lista_crew_previo}', [UserAPIController::class, 'eliminarPersonaDelCrewPrevio']);
        Route::get('/crearTodosLosPIN', [UserAPIController::class, 'crearPINTodosUsuarios']);
        Route::put('/crearUsuarioNuevo', [UserAPIController::class, 'asignarDatosAUsuarios']);
    });

    Route::prefix('tareas')->group(function () {
        // TareasController
        Route::get('/tareasAsignadasPorJefe/{id_jefe}', [TareaAPIController::class, 'tareasAsignadasPorJefe']);
        Route::post('/cambiarEstadoTarea', [TareaAPIController::class, 'cambiarEstadoTarea']);
        Route::post('/agregarCantidadEscaneo', [TareaAPIController::class, 'agregarCantidadEscaneo']);
        Route::post('/actualizarEstadoEmpleado', [TareaAPIController::class, 'actualizarEstadoEmpleado']);
    });

    Route::prefix('locaciones')->group(function () {
        // LocacionesController
        Route::get('/listaLocacionesPorGranja/{id_granja}', [LocacionAPController::class, 'listaLocacionesPorGranja']);
    });

    Route::prefix('actividades')->group(function () {
        // ActividadesController
        Route::post('/listaActividadesPorGranjaLocacion', [ActividadAPIController::class, 'listaActividadesPorGranjaLocacion']);
    });

    Route::prefix('Campos')->group(function () {
        // CamposController
        Route::post('/campoUsadoEnCropAge', [CampoAPIController::class, 'campoUsadoEnCropAge']);
    });

    Route::prefix('bloques')->group(function () {
        // BloquesController
        Route::post('/bloquesEnCropAgeAsociadosCampo', [BloqueAPIController::class, 'bloquesEnCropAgeAsociadosCampo']);
        Route::post('/bloqueUsadoEnCropAge', [BloqueAPIController::class, 'bloqueUsadoEnCropAge']);
        Route::post('/bloqueUsadoEnCampoCropAge', [BloqueAPIController::class, 'bloqueUsadoEnCampoCropAge']);
    });

    Route::prefix('CrewJobController')->group(function () {
        // CrewJobController
        Route::post('/crearCrewJob', [CrewJobAPIController::class, 'crearCrewJob']);
        Route::post('/crearTareaPendiente', [CrewJobAPIController::class, 'crearTareaPendiente']);
    });
    Route::prefix('CropAgeController')->group(function () {
        // CropAgeController
        Route::get('/cropAsociadasAGranja/{id_granja}', [CropAgeAPIController::class, 'cropAsociadasAGranja']);
    });
    Route::prefix('simular')->group(function () {
        Route::post('/inicioSesionEmpleadoSimulado', [RegistroIngresoAPIController::class, 'inicioSesionEmpleadoSimulado']);
    });

    Route::prefix('empleado_administracion')->group(function () {
        Route::POST('/eliminarVinculoConTarea', [AdminRegistroAppController::class, 'eliminarVinculoConTarea']);
        Route::POST('/cambiarCantidadEscaneoTareaEmpleado', [AdminRegistroAppController::class, 'cambiarCantidadEscaneoTareaEmpleado']);
        Route::POST('/listaTipoPagos', [AdminRegistroAppController::class, 'listaTipoPagos']);
        Route::POST('/listaHarvestsPorGranja', [AdminRegistroAppController::class, 'listaHarvestsPorGranja']);
        Route::POST('/listaMiscelaneosPorGranja', [AdminRegistroAppController::class, 'listaMiscelaneosPorGranja']);
        Route::POST('/asignarTareaExistente', [AdminRegistroAppController::class, 'asignarTareaExistente']);
        Route::POST('/registrosIngresosYEgresos', [AdminRegistroAppController::class, 'registrosIngresosYEgresos']);
        Route::GET('/searchEmpleados', [AdminRegistroAppController::class, 'searchEmpleados']);
        Route::GET('/searchEmpleadosAsJson', [AdminRegistroAppController::class, 'searchEmpleadosAsJson']);
        Route::GET('/searchEmpleadosAsJsonMultiple', [AdminRegistroAppController::class, 'searchEmpleadosAsJsonMultiple']);
        Route::POST('/searchEmpleadosPorGranjaCrop', [ReportePieceRateReportController::class, 'searchEmpleadosPorGranjaCrop']);
        Route::POST('/searchEmpleadosPorGranjaMisc', [ReporteMiscPieceRateReportController::class, 'searchEmpleadosPorGranjaMisc']);
        Route::POST('/alternarActivacionAsingarAlmuerzo', [AdminRegistroAppController::class, 'alternarActivacionAsingarAlmuerzo']);
        Route::POST('/listaLocacionesPorGranjas', [AdminRegistroAppController::class, 'listaLocacionesPorGranjas']);
        Route::POST('/listaLocacionesPorGranjasMultiple', [AdminRegistroAppController::class, 'listaLocacionesPorGranjasMultiple']);
        Route::POST('/nuevoRegistroIngresoYSalida', [AdminRegistroAppController::class, 'nuevoRegistroIngresoYSalida']);
        Route::POST('/nuevoRegistroIngresoYSalidaBulkAdd', [AdminRegistroAppController::class, 'nuevoRegistroIngresoYSalidaBulkAdd']);
        Route::POST('/nuevoRegistroActividadPorGranjaEmpleadosBulkAdd', [AdminRegistroAppController::class, 'nuevoRegistroActividadPorGranjaEmpleadosBulkAdd']);
        Route::POST('/registroActividadHarvestMiscelaneaEmpleadosBulkAdd', [AdminRegistroAppController::class, 'registroActividadHarvestMiscelaneaEmpleadosBulkAdd']);
        Route::POST('/actualizarHorasInicioFinalTarea', [AdminRegistroAppController::class, 'actualizarHorasInicioFinalTarea']);
        Route::POST('/actualizarHorasEntradaSalida', [AdminRegistroAppController::class, 'actualizarHorasEntradaSalida']);
        Route::POST('/actualizarHorasEntrada', [AdminRegistroAppController::class, 'actualizarHorasEntrada']);
        Route::POST('/eliminarTareaBitacoraInicioSesion', [AdminRegistroAppController::class, 'eliminarTareaBitacoraInicioSesion']);
        Route::POST('/actualizarFarmLocationTareaBitacoraInicioSesion', [AdminRegistroAppController::class, 'actualizarFarmLocationTareaBitacoraInicioSesion']);
        Route::POST('/agregarComentarioRegistroIngreso', [AdminRegistroAppController::class, 'agregarComentarioRegistroIngreso']);

        Route::GET('/searchEmpleadosSign', [ReporteSingSheetsController::class, 'searchEmpleadosSign']);

        // Pack type update
        Route::POST('/actualizarPackTypeHarvest', [AdminRegistroAppController::class, 'actualizarPackTypeHarvest']);

        Route::POST('/cargarTareasPorFechaEmpleado', [AdminRegistroAppController::class, 'cargarTareasPorFechaEmpleado']);
        Route::POST('/listaDatosEscaneadosPorCodCrew', [AdminRegistroAppController::class, 'listaDatosEscaneadosPorCodCrew']);
        Route::POST('/guardarRegistroDatosEscaneo', [AdminRegistroAppController::class, 'guardarRegistroDatosEscaneo']);

    });

    Route::prefix('reportes')->group(function(){
        // Route::POST('/obtener_reporte_empleados_horas', [ReporteEmployeeHoursLocationController::class, 'obtenerReporteEmpleadosHoras']);
        Route::POST('/obtener_reporte_empleados_horas', [ReporteEmployeeHoursLocationController::class, 'obtenerReporteEmpleadosHoras']);
        Route::POST('/obtenerCropsEntreFechasAsJson', [ReportePieceRateReportController::class, 'obtenerCropsEntreFechas']);
        Route::POST('/obtenerTodosLosCrops', [ReportePieceRateReportController::class, 'obtenerTodosLosCrops']);
        Route::POST('/obtenerRateReportData', [ReportePieceRateReportController::class, 'obtenerRateReportData']);
        Route::POST('/obtenerMiscRateReportData', [ReporteMiscPieceRateReportController::class, 'obtenerMiscRateReportData']);
        Route::POST('/obtenerLocationExecutiveData', [ReporteLocationExecutiveSummaryController::class, 'obtenerLocationExecutiveData']);
        Route::POST('/obtenerEstimatedPayrollData', [ReporteEstimatedPayrollController::class, 'obtenerEstimatedPayrollData']);
        Route::POST('/obtenerDynamicReportData', [ReporteDynamicReportController::class, 'obtenerDynamicReportData']);
        Route::POST('/guardarConfiguracionReporte', [ReporteDynamicReportController::class, 'guardarConfiguracionReporte']);
        Route::POST('/buscarConfiguracionReporte', [ReporteDynamicReportController::class, 'buscarConfiguracionReporte']);

        Route::POST('/obtenerSignSheetReport', [ReporteSingSheetsController::class, 'obtenerSignSheetReport']);
    });
});
