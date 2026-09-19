import {
    buildEmployeeElement,
    _createElementFromHTML,
    cargarSelectLocacionesPorGranjasMultiple,
    createLoader,
    generarLineaEmpleadoSeleccionado
} from "../common.js";
import ListPicker from "../list-picker.js";
import DynamicOptionsPicker from "../dynamic-options-picker.js";
var buscandoConfiguracion = false;
var locacionesSeleccionadas = [];
var columnasSeleccionadas = [];
var gruposSeleccionadas = [];
var empleadosSeleccionadas = [];
var dataPreConfiguradaCargada = {};
window.listPicker = new ListPicker([], "#select_employees_wrapper");
window.columns = new DynamicOptionsPicker([
    { value: "cod_usuario", text: "User ID" },
    { value: "nombre_completo", text: "Full Name" },
    { value: "identidad", text: "ID" },
    { value: "email", text: "Email" },
    { value: "telefono", text: "Phone" },
    { value: "cod_tipo_usuario", text: "User Type ID" },
    { value: "tipo_usuario", text: "User Type" },
    { value: "pin", text: "Pin" },
    { value: "qcpin", text: "Qcpin" },
    { value: "pay_rate", text: "User Pay Rate" },
    { value: "categoria", text: "Category" },
    { value: "cod_location", text: "Location ID" },
    { value: "location", text: "Location" },
    { value: "cod_farms", text: "Farm ID" },
    { value: "farm", text: "Farm" },
    { value: "horas", text: "Hours" },
    { value: "escaneos", text: "Scans" },
]);
window.group_by = new DynamicOptionsPicker([
    { value: "cod_usuario", text: "User ID" },
    { value: "cod_tipo_usuario", text: "User type" },
    { value: "categoria", text: "User Category" },
    { value: "cod_farm_ci", text: "Farm" },
    { value: "cod_location_ci", text: "Location" }
], "#dynamic-group-by-picker-select", "#dynamic-group-by-selected-wrapper");

document.addEventListener("DOMContentLoaded", function () {

    $('.selectpicker').selectpicker();

    $('#div_fecha_inicial').datetimepicker({
        locale: 'us',
        format: 'MM-DD-YYYY',
        defaultDate: moment().subtract(7, 'days').toDate(),
        icons: {
            time: "fa fa-clock-o",
            date: "fa fa-calendar",
            up: "fa fa-arrow-up",
            down: "fa fa-arrow-down",
            previous: 'fa fa-arrow-left',
            next: 'fa fa-arrow-right',
        }
    }).on('dp.hide', function (e) {
        cargarEmpleadosEntreLasFechasGranjaLocationMultiple(
            $('#initial_date').val(),
            $('#final_date').val(),
            $('#cod_farm_reporte_hours').val(),
            $('#cod_location_reporte_hours').val(),
            $('#cod_category_reporte').val(),
            () => {
                $('#list_empleados_cantidad').html(`(${window.listPicker.items.length} found)`);
            }
        );
        verficarDatosSeleccionados();

    });

    $('#div_fecha_final').datetimepicker({
        locale: 'us',
        format: 'MM-DD-YYYY',
        defaultDate: new Date(),
        icons: {
            time: "fa fa-clock-o",
            date: "fa fa-calendar",
            up: "fa fa-arrow-up",
            down: "fa fa-arrow-down",
            previous: 'fa fa-arrow-left',
            next: 'fa fa-arrow-right',
        }
    }).on('dp.hide', function (e) {
        cargarEmpleadosEntreLasFechasGranjaLocationMultiple(
            $('#initial_date').val(),
            $('#final_date').val(),
            $('#cod_farm_reporte_hours').val(),
            $('#cod_location_reporte_hours').val(),
            $('#cod_category_reporte').val(),
            () => {
                $('#list_empleados_cantidad').html(`(${window.listPicker.items.length} found)`);
            }
        );
        verficarDatosSeleccionados();

    });

    $('#select_config_modal').on('change', () => {
        if ($('#select_config_modal').val() == 'reset') {
            $('#btn_guardar_configuracion').prop('disabled', true);
            $('#btn_confirmar_guardar_configuracion').prop('disabled', true);
            return;
        }
        $('#btn_guardar_configuracion').prop('disabled', false);
        $('#btn_confirmar_guardar_configuracion').prop('disabled', false);
    });
    $('#cod_farm_reporte_hours').on('change', () => {
        console.log("Granjas seleccionadas: ", $('#cod_farm_reporte_hours').val());
        (async () => {
            //Verifica el estado de las granjas seleccionadas
            verficarDatosSeleccionados();

            await new Promise((resolve) => {

                cargarSelectLocacionesPorGranjasMultiple(
                    'cod_location_reporte_hours',
                    $('#cod_farm_reporte_hours').val(),
                    () => {
                        $('#cod_location_reporte_hours').selectpicker('destroy');
                        $('#cod_location_reporte_hours').selectpicker();
                        resolve();
                    },
                    false,
                    locacionesSeleccionadas,
                );
                locacionesSeleccionadas = []; // Limpiamos la variable para que no se repita en futuros cambios
            });

            await new Promise((resolve) => {
                cargarEmpleadosEntreLasFechasGranjaLocationMultiple(
                    $('#initial_date').val(),
                    $('#final_date').val(),
                    $('#cod_farm_reporte_hours').val(),
                    $('#cod_location_reporte_hours').val(),
                    $('#cod_category_reporte').val(),
                    () => {
                        $('#list_empleados_cantidad').html(`(${window.listPicker.items.length} found)`);
                        resolve();
                    }
                );
            });
            //Verifica el estado de las locaciones seleccionadas
            verficarDatosSeleccionados();


        })();
    });

    $('#cod_location_reporte_hours').on('change', () => {
        cargarEmpleadosEntreLasFechasGranjaLocationMultiple(
            $('#initial_date').val(),
            $('#final_date').val(),
            $('#cod_farm_reporte_hours').val(),
            $('#cod_location_reporte_hours').val(),
            $('#cod_category_reporte').val(),
            () => {
                $('#list_empleados_cantidad').html(`(${window.listPicker.items.length} found)`);
            }
        );
        verficarDatosSeleccionados();

    });

    $('#cod_category_reporte').on('change', () => {
        cargarEmpleadosEntreLasFechasGranjaLocationMultiple(
            $('#initial_date').val(),
            $('#final_date').val(),
            $('#cod_farm_reporte_hours').val(),
            $('#cod_location_reporte_hours').val(),
            $('#cod_category_reporte').val(),
            () => {
                $('#list_empleados_cantidad').html(`(${window.listPicker.items.length} found)`);
            }
        );
        verficarDatosSeleccionados();

    });

    cargarEmpleadosEntreLasFechasGranjaLocationMultiple(
        $('#initial_date').val(),
        $('#final_date').val(),
        $('#cod_farm_reporte_hours').val(),
        $('#cod_location_reporte_hours').val(),
        $('#cod_category_reporte').val(),
        () => {
            $('#list_empleados_cantidad').html(`(${window.listPicker.items.length} found)`);
        }
    );

    $("#btn_modal_save_selected").on('click', function () {
        refrescarListaEmpleadosSeleccionados(
            "#list_empleados",
            window.listPicker.selected()
        );
        $('#modal_seleccionar_empleados').modal('hide');
    });

    $('#btn_generate_report').on('click', generarReporte);
    $('#btn_guardar_configuracion').on('click', guardarConfiguracionReporte);
    $('#btn_generate_report_trigger').on('click', () => {
        generarReporte(false);
    });

    $('#btn_reset_config').on('click', () => {
        resetearConfiguracionReporte();

    });
    $('#dynamic-column-picker-select').on('change', () => {


        generarReporte(false);
    });
    $('#dynamic-group-by-picker-select').on('change', () => {

        generarReporte(false);
    });
    $('#select_config').on('change', () => {

        buscarConfiguracionReporte();
    });


});


function resetearConfiguracionReporte(completa = true) {

    $('#contenedor_reporte').empty();
    $('#contenedor_reporte').append(`
                <div class="col-12">
                    <div class="row w-100 border-bottom">
                        <div class="col-12 px-3 py-2 text-center">
                            <span>Fill the fields on the left and click the green "Generate report" button.</span>
                        </div>
                    </div>
                </div>
                    `);
    $('#dropdown_export').addClass('d-none');


    $('#txt_nota_configuracion').val("");
    $('#initial_date').val(moment().subtract(7, 'days').format('MM-DD-YYYY'));
    $('#final_date').val(moment().format('MM-DD-YYYY'));

    $('#cod_farm_reporte_hours').selectpicker('val', []);
    $('#cod_location_reporte_hours').empty();
    $('#cod_category_reporte').selectpicker('val', []);
    $('#cod_location_reporte_hours').selectpicker('destroy');
    $('#cod_location_reporte_hours').selectpicker();

    if (completa) {

        $('#select_config').val('reset');
        $('#select_config_modal').val('reset');
        $('#cod_farm_reporte_hours').trigger('change');
    }

    verficarDatosSeleccionados();
}

function cargarEmpleadosEntreLasFechasGranjaLocationMultiple(
    initial_date,
    final_date,
    cod_farm,
    cod_location,
    cod_categories = [],
    oncomplete = () => { }
) {

    window.listPicker.destroy();

    $.ajax({
        type: "GET",
        url: window.location.origin + "/api/v1/empleado_administracion/searchEmpleadosAsJsonMultiple",
        data: {
            query: "",
            fecha_inicial: initial_date,
            fecha_final: final_date,
            cod_farm: JSON.stringify(cod_farm),
            cod_location: JSON.stringify(cod_location),
            cod_category: JSON.stringify(cod_categories)
        },
        success: (res) => {
            let items = [];
            if (res.employees.length) {
                res.employees.forEach((e) => {
                    items.push({
                        id: e.cod_usuario,
                        name: `${e.nombre_1} ${e.apellido_1} (${e.pin} - ${e.qcpin}) ${e.es_veterano == 0 ? "Regular" : (e.es_veterano == 1 ? "Veteran" : "H2A")}`
                    })
                });
                window.listPicker = new ListPicker(items, "#select_employees_wrapper");
            }
            refrescarListaEmpleadosSeleccionados(
                "#list_empleados",
                window.listPicker.selected()
            );
            oncomplete();
        },
        fail: (err) => {
            console.log(err);
        }
    })

}

function refrescarListaEmpleadosSeleccionados(selector, items) {
    let wrapper = document.querySelector(selector);
    if (wrapper) {
        wrapper.innerHTML = "";
        if (items.length > 0) {
            items.forEach((i, index) => {
                wrapper.appendChild(generarLineaEmpleadoSeleccionado(i.name, index));
            });
        } else {
            wrapper.innerHTML = "<small class='text-secondary'>No employees selected</small>"
        }
    }
}

function generarReporte(mostrarMensaje = true) {

    let initial_date = new Date($('#initial_date').val());
    let final_date = new Date($('#final_date').val());


    if (initial_date > final_date) {
        if (mostrarMensaje) {
            Swal.fire('The initial date must be earlier than the final date.');
        }
        return false;
    }

    if (window.listPicker.selected().length == 0) {
        if (mostrarMensaje) {
            Swal.fire('Please select at least one employee.');
        }
        return false;
    }

    // validamos que los campos columns y groups sean correctos
    let validationResponse = columnsAndGroupsValid();
    if (!validationResponse.valid) {
        Swal.fire({
            icon: "warning",
            title: validationResponse.title,
            text: validationResponse.message,
        });
        return false;
    }
    if (!mostrarMensaje) {
        //Se mostrará solo cuando se interactúe con elementos que no sean el botón de generar reporte
        // Mostrar loading en toda la pantalla
        const loadingOverlay = document.createElement('div');
        loadingOverlay.id = 'global-loading-overlay';
        loadingOverlay.style.position = 'fixed';
        loadingOverlay.style.top = '0';
        loadingOverlay.style.left = '0';
        loadingOverlay.style.width = '100vw';
        loadingOverlay.style.height = '100vh';
        loadingOverlay.style.background = 'rgba(255,255,255,0.7)';
        loadingOverlay.style.zIndex = '9999';
        loadingOverlay.style.display = 'flex';
        loadingOverlay.style.alignItems = 'center';
        loadingOverlay.style.justifyContent = 'center';
        loadingOverlay.innerHTML = `<div class="spinner-border text-primary" style="width: 4rem; height: 4rem;" role="status"><span class="sr-only">Loading...</span></div>`;
        document.body.appendChild(loadingOverlay);
    }
    let buttonContent = $(this).html();
    $(this).html(createLoader());
    $(this).prop('disabled', 'disabled');

    $.ajax({
        type: 'POST',
        url: window.location.origin + "/api/v1/reportes/obtenerDynamicReportData",
        data: {
            fecha_inicial: $('#initial_date').val(),
            fecha_final: $('#final_date').val(),
            cod_farm: JSON.stringify($('#cod_farm_reporte_hours').val()),
            cod_location: JSON.stringify($('#cod_location_reporte_hours').val()),
            cod_category: JSON.stringify($('#cod_category_reporte').val()),
            empleados: JSON.stringify(window.listPicker.selected()),
            columns: JSON.stringify(window.columns.selected()),
            group_by: JSON.stringify(window.group_by.selected())
        },
        success: (res) => {
            buildReporte(res.data);
            // Mostramos dropdown de export to excel and txt
            $('#dropdown_export').removeClass('d-none');
            $('#btn_export_xlsx').off('click').on('click', () => {
                exportarArchivo(
                    $('#initial_date').val(),
                    $('#final_date').val(),
                    JSON.stringify($('#cod_farm_reporte_hours').val()),
                    JSON.stringify($('#cod_location_reporte_hours').val()),
                    JSON.stringify($('#cod_category_reporte').val()),
                    JSON.stringify(window.listPicker.selected()),
                    JSON.stringify(window.columns.selected()),
                    JSON.stringify(window.group_by.selected())
                );
            });
            // $('#btn_export_txt').off('click').on('click', ()=>{
            //     exportarArchivo(
            //         $('#initial_date').val(),
            //         $('#final_date').val(),
            //         JSON.stringify(window.listPicker.selected()),
            //         $('#cod_farm_reporte_hours').val(),
            //         $('#cod_location_reporte_hours').val(),
            //         'txt'
            //     );
            // });
        },
        fail: (err) => {
            console.log(err);
        },
        complete: () => {
            $(this).html(buttonContent);
            $(this).prop('disabled', false);

            if (!mostrarMensaje) {
                const overlay = document.getElementById('global-loading-overlay');
                if (overlay) {
                    overlay.remove();
                }
            }
        }
    })

}

function buildReporte(
    data,
    mostrarMensaje = false,
    query_contenedor = "#contenedor_reporte",
    query_heading = "#heading_wrapper",
    query_heading_columns = "#heading_columns"
) {
    // Variables
    let contenedor = $(query_contenedor);
    let headingWrapper = $(query_heading);
    let headingColumns = $(query_heading_columns);

    // Process
    contenedor.empty();
    headingWrapper.removeClass('d-none');
    headingColumns.empty();

    //Build columns
    window.columns.selectedRaw().forEach((c) => {
        headingColumns.append(`
            <div class="col px-3 py-2" style="width: ${100 / window.columns.selected().length}%">
                <div style="font-size: 0.8em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${c.text}</div>
            </div>
        `);
    });

    data.forEach((usuario) => {

        let cols = "";
        Object.entries(usuario).forEach(([key, value]) => {
            cols += `
                <div class="col px-3 py-2" style="width: ${100 / Object.entries(usuario).length}%">
                    <div style="font-size: 0.8em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${value}</div>
                </div>
            `;
        });

        let row = `
            <div class="col-12">
                <div class="row w-100 border-bottom">
                    ${cols}
                </div>
            </div>
        `;

        contenedor.append(row);

    });

    if (mostrarMensaje) {
        const overlay = document.getElementById('global-loading-overlay');
        if (overlay) {
            overlay.remove();
        }
        Swal.fire({
            icon: "success",
            title: data.title || "Success",
            text: data.message || "Configuration loaded successfully",
        });

    }
}

function columnsAndGroupsValid() {

    // Asegurarse que haya seleccionado al menos una columna
    if (window.columns.selected().length == 0) {
        return {
            valid: false,
            title: "Columns warning",
            message: "Please select at least one column."
        };
    }

    // Asegurarse que no se seleccionen hours y scans en el mismo llamado
    let hoursScansArray = window.columns.selectedRaw().filter((c) => {
        if (c.value == "horas" || c.value == "escaneos") {
            return c;
        }
    });
    if (hoursScansArray.length > 1) {
        return {
            valid: false,
            title: "Columns warning",
            message: "You cannot select both hours and scans columns, this will lead to uncertain information. Please remove one of them."
        };
    }

    return {
        valid: true
    }

}

function exportarArchivo(
    fecha_inicial,
    fecha_final,
    cod_farm,
    cod_location,
    cod_category,
    empleados,
    columns,
    group_by,
    formato = 'xlsx'
) {
    // Crear el formulario dinámicamente
    var form = $('<form>', {
        action: window.location.origin + '/v1/empleado/exportDynamicReport',  // URL del endpoint
        method: 'POST',  // Método POST
        target: '_blank'  // Abrir en una nueva ventana o pestaña
    });

    // Datos a enviar en el formulario
    var formData = {
        _token: $('meta[name="csrf-token"]').attr('content'),  // Token CSRF
        fecha_inicial: fecha_inicial,
        fecha_final: fecha_final,
        cod_farm: cod_farm,
        cod_location: cod_location,
        cod_category: cod_category,
        empleados: empleados,
        columns: columns,
        group_by: group_by,
        formato: formato
    };

    // Añadir los campos al formulario
    $.each(formData, function (key, value) {
        form.append($('<input>', { type: 'hidden', name: key, value: value }));
    });

    // Añadir el formulario al body del documento
    $('body').append(form);

    // Enviar el formulario
    form.submit();

    // Eliminar el formulario después de enviarlo (limpieza)
    form.remove();
}

function verficarDatosSeleccionados() {

    const cod_farm = $('#cod_farm_reporte_hours').val();
    const cod_location = $('#cod_location_reporte_hours').val();
    const cod_category = $('#cod_category_reporte').val();
    if (new Date($('#initial_date').val()) > new Date($('#final_date').val())) {
        limpiarEstadoColumnasYGrupos();
        habilitarBotonGuardarConfiguracion(false)
        return false;
    }


    if (
        cod_farm == null || cod_farm === '-b' ||
        cod_location == null || cod_location === '-b'
        || Array.isArray(cod_farm) && cod_farm.length === 0
        || Array.isArray(cod_location) && cod_location.length === 0
    ) {
        limpiarEstadoColumnasYGrupos();
        habilitarBotonGuardarConfiguracion(false)
        return false;
    }
    $('#btn_generate_report').prop('disabled', false);
    $('#dynamic-column-picker-select').prop('disabled', false);
    $('#dynamic-group-by-picker-select').prop('disabled', false);
    habilitarBotonGuardarConfiguracion(true);
    if ((Array.isArray(columnasSeleccionadas) && columnasSeleccionadas.length > 0) ||
        (Array.isArray(gruposSeleccionadas) && gruposSeleccionadas.length > 0) ||
        (Array.isArray(empleadosSeleccionadas) && empleadosSeleccionadas.length > 0)) {

        window.columns.selectByKeys(columnasSeleccionadas);
        window.group_by.selectByKeys(gruposSeleccionadas);
        columnasSeleccionadas = [];
        gruposSeleccionadas = [];
        buildReporte(dataPreConfiguradaCargada, true);
        window.listPicker.selectItems(empleadosSeleccionadas)

        refrescarListaEmpleadosSeleccionados(
            "#list_empleados",
            window.listPicker.selected()
        );
    }
    return true;
}

function habilitarBotonGuardarConfiguracion(estadoBoton = true) {
    $('#btn_mostrar_modal_guardar_configuracion').prop('hidden', !estadoBoton).prop('disabled', !estadoBoton);
}

function limpiarEstadoColumnasYGrupos() {
    $('#btn_generate_report').prop('disabled', true);
    $('#dynamic-column-picker-select').prop('disabled', true);
    $('#dynamic-group-by-picker-select').prop('disabled', true);
    $('#dynamic-columns-selected-wrapper').empty();
    $('#dynamic-group-by-selected-wrapper').empty();
    $('#dynamic-columns-selected-wrapper').append(`<div class="text-secondary" style="border-radius: 5px; font-size:0.8em; background-color: rgba(0,0,0,0.05); padding: 10px; width: 100%">None selected</div>`);
    $('#dynamic-group-by-selected-wrapper').append(`<div class="text-secondary" style="border-radius: 5px; font-size:0.8em; background-color: rgba(0,0,0,0.05); padding: 10px; width: 100%">None selected</div>`);
}

function guardarConfiguracionReporte() {

    let initial_date = new Date($('#initial_date').val());
    let final_date = new Date($('#final_date').val());


    if (initial_date > final_date) {
        Swal.fire('The initial date must be earlier than the final date.');
        return false;
    }

    if (window.listPicker.selected().length == 0) {
        Swal.fire('Please select at least one employee.');
        return false;
    }

    // validamos que los campos columns y groups sean correctos
    let validationResponse = columnsAndGroupsValid();
    if (!validationResponse.valid) {
        Swal.fire({
            icon: "warning",
            title: validationResponse.title,
            text: validationResponse.message,
        });
        return false;
    }
    //Se mostrará solo cuando se interactúe con elementos que no sean el botón de generar reporte
    // Mostrar loading en toda la pantalla
    const loadingOverlay = document.createElement('div');
    loadingOverlay.id = 'global-loading-overlay';
    loadingOverlay.style.position = 'fixed';
    loadingOverlay.style.top = '0';
    loadingOverlay.style.left = '0';
    loadingOverlay.style.width = '100vw';
    loadingOverlay.style.height = '100vh';
    loadingOverlay.style.background = 'rgba(255,255,255,0.7)';
    loadingOverlay.style.zIndex = '9999';
    loadingOverlay.style.display = 'flex';
    loadingOverlay.style.alignItems = 'center';
    loadingOverlay.style.justifyContent = 'center';
    loadingOverlay.innerHTML = `<div class="spinner-border text-primary" style="width: 4rem; height: 4rem;" role="status"><span class="sr-only">Loading...</span></div>`;
    document.body.appendChild(loadingOverlay);
    let buttonContent = $(this).html();
    $(this).html(createLoader());
    $(this).prop('disabled', 'disabled');

    $.ajax({
        type: 'POST',
        url: window.location.origin + "/api/v1/reportes/guardarConfiguracionReporte",
        data: {
            fecha_inicial: $('#initial_date').val(),
            fecha_final: $('#final_date').val(),
            cod_farm: JSON.stringify($('#cod_farm_reporte_hours').val()),
            cod_location: JSON.stringify($('#cod_location_reporte_hours').val()),
            cod_category: JSON.stringify($('#cod_category_reporte').val()),
            empleados: JSON.stringify(window.listPicker.selected()),
            columns: JSON.stringify(window.columns.selected()),
            group_by: JSON.stringify(window.group_by.selected()),
            cod_config: $('#select_config_modal').val(),
            nota_configuracion: $('#txt_nota_configuracion').val(),
            cod_reporte: 1,// Asumiendo que este es el ID del reporte dinamico
        },
        success: (res) => {

            if (res.success === true) {
                Swal.fire({
                    icon: "success",
                    title: res.title || "Success",
                    text: res.message || "Configuration saved successfully",
                });
                buildReporte(res.data);
                // Mostramos dropdown de export to excel and txt
                $('#dropdown_export').removeClass('d-none');
                $('#btn_export_xlsx').off('click').on('click', () => {
                    exportarArchivo(
                        $('#initial_date').val(),
                        $('#final_date').val(),
                        JSON.stringify($('#cod_farm_reporte_hours').val()),
                        JSON.stringify($('#cod_location_reporte_hours').val()),
                        JSON.stringify($('#cod_category_reporte').val()),
                        JSON.stringify(window.listPicker.selected()),
                        JSON.stringify(window.columns.selected()),
                        JSON.stringify(window.group_by.selected())
                    );
                });

                $('#select_config').val('config_' + $('#select_config_modal').val());
                $('#txt_nota_configuracion').val("");
                $('#select_config_modal').val('reset');
            } else {
                Swal.fire({
                    icon: "error",
                    title: res.title || "Error saving configuration",
                    text: res.message || "An error occurred while saving the configuration."
                });
            }
        },
        fail: (err) => {
            console.log(err);
        },
        complete: () => {
            $(this).html(buttonContent);
            $(this).prop('disabled', false);

            const overlay = document.getElementById('global-loading-overlay');
            if (overlay) {
                overlay.remove();
            }
        }
    })

}
function buscarConfiguracionReporte() {
    if (!buscandoConfiguracion) {
        buscandoConfiguracion = true;
        // Mostrar loading en toda la pantalla
        const loadingOverlay = document.createElement('div');
        loadingOverlay.id = 'global-loading-overlay';
        loadingOverlay.style.position = 'fixed';
        loadingOverlay.style.top = '0';
        loadingOverlay.style.left = '0';
        loadingOverlay.style.width = '100vw';
        loadingOverlay.style.height = '100vh';
        loadingOverlay.style.background = 'rgba(255,255,255,0.7)';
        loadingOverlay.style.zIndex = '9999';
        loadingOverlay.style.display = 'flex';
        loadingOverlay.style.alignItems = 'center';
        loadingOverlay.style.justifyContent = 'center';
        loadingOverlay.innerHTML = `<div class="spinner-border text-primary" style="width: 4rem; height: 4rem;" role="status"><span class="sr-only">Loading...</span></div>`;
        document.body.appendChild(loadingOverlay);
        let buttonContent = $(this).html();
        $(this).html(createLoader());
        $(this).prop('disabled', 'disabled');
        // Usar async/await para esperar la solicitud
        const COD_CONFIG = $('#select_config').val();
        (async () => {
            try {
                await $.ajax({
                    type: 'POST',
                    url: window.location.origin + "/api/v1/reportes/buscarConfiguracionReporte",
                    data: {
                        cod_config: COD_CONFIG,
                        cod_reporte: 1,// Asumiendo que este es el ID del reporte dinamico
                    },
                    success: (res) => {

                        $('#select_config').val(COD_CONFIG);
                        if (res.success === true) {
                            resetearConfiguracionReporte(false);

                            $('#initial_date').val(res.jsonConfiguracion.fecha_inicial);
                            $('#final_date').val(res.jsonConfiguracion.fecha_final);

                            $('#cod_farm_reporte_hours').selectpicker('val', res.jsonConfiguracion.cod_farm);

                            locacionesSeleccionadas = res.jsonConfiguracion.cod_location;
                            columnasSeleccionadas = res.jsonConfiguracion.columns;
                            gruposSeleccionadas = res.jsonConfiguracion.group_by;
                            empleadosSeleccionadas = res.jsonConfiguracion.empleados;

                            $('#cod_farm_reporte_hours').trigger('change');

                            // window.columns.render();
                            $('#cod_location_reporte_hours').selectpicker('val', res.jsonConfiguracion.cod_location);
                            $('#cod_location_reporte_hours').trigger('change');
                            $('#cod_category_reporte').selectpicker('val', res.jsonConfiguracion.cod_category);

                            dataPreConfiguradaCargada = res.data;
                            dataPreConfiguradaCargada.title = res.title || "Success loading configuration";
                            dataPreConfiguradaCargada.message = res.message || "Configuration loaded successfully";
                            // buildReporte(res.data);

                            // Mostramos dropdown de export to excel and txt
                            $('#dropdown_export').removeClass('d-none');
                            $('#btn_export_xlsx').off('click').on('click', () => {
                                exportarArchivo(
                                    $('#initial_date').val(),
                                    $('#final_date').val(),
                                    JSON.stringify($('#cod_farm_reporte_hours').val()),
                                    JSON.stringify($('#cod_location_reporte_hours').val()),
                                    JSON.stringify($('#cod_category_reporte').val()),
                                    JSON.stringify(window.listPicker.selected()),
                                    JSON.stringify(window.columns.selected()),
                                    JSON.stringify(window.group_by.selected())
                                );
                            });

                        } else {
                            Swal.fire({
                                icon: "error",
                                title: res.title || "Error loading configuration",
                                text: res.message || "An error occurred while loading the configuration."
                            });
                        }
                    },
                    error: (err) => {
                        resetearConfiguracionReporte(false);
                        console.log(err);
                        Swal.fire({
                            icon: "error",
                            title: err.title || "Error loading configuration",
                            text: err.message || "An error occurred while loading the configuration."
                        });
                    },
                    complete: () => {
                        buscandoConfiguracion = false;

                        $(this).html(buttonContent);
                        $(this).prop('disabled', false);


                    }
                });
            } catch (e) {
                buscandoConfiguracion = false;
                $(this).html(buttonContent);
                $(this).prop('disabled', false);
                const overlay = document.getElementById('global-loading-overlay');
                if (overlay) {
                    overlay.remove();
                }
                Swal.fire({
                    icon: "error",
                    title: "Error loading configuration",
                    text: "An unexpected error occurred."
                });
            }
        })();
    }
}


