import {
    generarLineaEmpleadoSeleccionado,
    _createElementFromHTML,
    createLoader,
    cargarSelectLocacionesPorGranjasMultiple
} from "../common.js";
import ListPicker from "../list-picker.js";
var selectedEmployeeCodes = [];

document.addEventListener("DOMContentLoaded", () => {
    localStorage.removeItem('listadoCodigosEmpleadosSeleccionados');

    $('#search').on('keyup', function () {
        var query = $(this).val();
        var storedCodes = localStorage.getItem("listadoCodigosEmpleadosSeleccionados");
        if (storedCodes) {
            selectedEmployeeCodes = JSON.parse(storedCodes);
        }

        $('#employee_list').empty();
        $('#employee_list').html('<tr><td colspan="9">Loading ...</td></tr>');
        $.ajax({
            url: window.location.origin + "/api/v1/empleado_administracion/searchEmpleadosSign",
            type: "GET",
            data: {
                'query': query,
                'fecha_inicial': $('#initial_date').val(),
                'fecha_final': $('#final_date').val(),
                'cod_farm': $('#cod_farm').val(),
                'cod_location': $('#cod_location').val(),
                codigo_usuarios: selectedEmployeeCodes
            },
            success: function (data) {
                // console.log({ data })
                $('#employee_list').html(data);
            }
        });
    });

    $('#search').trigger('keyup');

    $('.selectpicker').selectpicker();

    $('#cod_farm').on('change', () => {
        cargarSelectLocacionesPorGranjasMultiple(
            'cod_location',
            $('#cod_farm').val(),
            () => {
                $('#cod_location').selectpicker('destroy');
                $('#cod_location').selectpicker();
            },
            false
        );
    });

    cargarSelectLocacionesPorGranjasMultiple(
        'cod_location',
        $('#cod_farm').val(),
        () => {
            $('#cod_location').selectpicker('destroy');
            $('#cod_location').selectpicker();
        },
        false
    );

    $('#btn_generate_report').on('click', generarReporte);

});

function generarReporte() {

    if ($('#week').val() == '') {
        Swal.fire("Please select the week.");
        return false;
    }

    let buttonContent = $(this).html();
    $(this).html(createLoader());
    $(this).prop('disabled', 'disabled');
    if ($('#cod_farm').val() == null || $('#cod_farm').val() == undefined || $('#cod_farm').val() == '') {
        Swal.fire("Please select at least one farm.");
        $(this).html(buttonContent);
        $(this).prop('disabled', false);
        return false;
    }

    if ($('#cod_location').val() == null || $('#cod_location').val() == undefined || $('#cod_location').val() == '') {
        Swal.fire("Please select at least one location.");
        $(this).html(buttonContent);
        $(this).prop('disabled', false);
        return false;
    }


    var storedCodes = localStorage.getItem("listadoCodigosEmpleadosSeleccionados");
    if (storedCodes) {
        selectedEmployeeCodes = JSON.parse(storedCodes);
    }
    $.ajax({
        type: "POST",
        url: window.location.origin + "/api/v1/reportes/obtenerSignSheetReport",
        data: {
            week: $('#week').val(),
            farms: $('#cod_farm').val() ?? [],
            locations: $('#cod_location').val() ?? [],
            categorias_empleados: $("#cod_categoria_empleado").val() ?? [],
            codigo_usuarios: selectedEmployeeCodes ?? []
        },
        success: (res) => {
            // console.log(res);
            if (res.data && res.data.length > 0) {
                $(this).prop('disabled', false);

                buildReporte(res.data);
                $('#dropdown_export').removeClass('d-none');
                $('#btn_export_xlsx').off('click').on('click', () => {
                    exportarArchivo(
                        $('#week').val(),
                        $('#cod_farm').val(),
                        $('#cod_location').val(),
                        selectedEmployeeCodes,
                        $("#cod_categoria_empleado").val(),
                        "xlsx"
                    );
                });
            } else {
                let contenedor = $("#contenedor_datos_empleados");
                contenedor.empty();
                Swal.fire("No data available for the selected criteria.");
            }


        },
        fail: (err) => {
            console.error(err);
            $(this).prop('disabled', false);

        },
        complete: () => {
            $(this).html(buttonContent);
            $(this).prop('disabled', false);
        }
    });
}

function buildReporte(data, query_contenedor = "#contenedor_datos_empleados") {
    let contenedor = $(query_contenedor);
    contenedor.empty();

    let totals = { dia1: 0, dia2: 0, dia3: 0, dia4: 0, dia5: 0, dia6: 0, dia7: 0, granTotal: 0 };
    let content = "";
    let count = 0;

    if (data.length > 0) {
        data.sort((a, b) => a.name.localeCompare(b.name));
        // console.log({ data });
        let groupedData = {};
        data.forEach(item => {
            if (!groupedData[item.name + item.cod_usuario]) {
                groupedData[item.name + item.cod_usuario] = [];
            }
            groupedData[item.name + item.cod_usuario].push(item);
        });
        console.log({ groupedData });

        Object.keys(groupedData).forEach(key => {
            count++;
            let group = groupedData[key];
            let groupTotals = { dia1: 0, dia2: 0, dia3: 0, dia4: 0, dia5: 0, dia6: 0, dia7: 0, granTotal: 0 };
            var cantidadReducida = 0;
            var sumanTotal = 0;
            group.forEach(item => {
                if (!groupTotals.granjas) {
                    groupTotals.granjas = [];
                }
                if (item.granja && !groupTotals.granjas.includes(item.granja + item.cod_usuario + item.locacion)) {
                    groupTotals.granjas.push(item.granja + item.cod_usuario + item.locacion);
                    sumanTotal = 0;
                    cantidadReducida = 0;
                    let esVeteranoArrayAnterior = item.es_veterano_concat.split(', ');
                    let lunchAcreditadoConcatArrayAnterior = item.lunch_acreditado_concat.split(', ');
                    let lunchAutomaticoConcatArrayAnterior = item.lunch_automatico_concat.split(', ');


                    lunchAutomaticoConcatArrayAnterior = item.lunch_automatico_concat.split(', ');
                    let codInicioSesionArray = item.cod_inicio_sesion_concat.split(', ');

                    let arrayAsociativoLunchAutomaticoCodInicioSesion = lunchAutomaticoConcatArrayAnterior.reduce((acc, curr, index) => {
                        acc[codInicioSesionArray[index]] = curr;
                        return acc;
                    }, {});

                    let arrayAsociativoLunchAcreditadoCodInicioSesion = lunchAcreditadoConcatArrayAnterior.reduce((acc, curr, index) => {
                        acc[codInicioSesionArray[index]] = curr;
                        return acc;
                    }, {});

                    groupTotals.dia1 += item.dia1 ? parseFloat(item.dia1) + calcularDeducionPorAlmuerzo(parseFloat(item.dia1), esVeteranoArrayAnterior[0], obtenerValorArrayAsociativo(arrayAsociativoLunchAcreditadoCodInicioSesion, codInicioSesionArray), obtenerValorArrayAsociativo(arrayAsociativoLunchAutomaticoCodInicioSesion, codInicioSesionArray)) : 0;
                    groupTotals.dia2 += item.dia2 ? parseFloat(item.dia2) + calcularDeducionPorAlmuerzo(parseFloat(item.dia2), esVeteranoArrayAnterior[1], obtenerValorArrayAsociativo(arrayAsociativoLunchAcreditadoCodInicioSesion, codInicioSesionArray), obtenerValorArrayAsociativo(arrayAsociativoLunchAutomaticoCodInicioSesion, codInicioSesionArray)) : 0;
                    groupTotals.dia3 += item.dia3 ? parseFloat(item.dia3) + calcularDeducionPorAlmuerzo(parseFloat(item.dia3), esVeteranoArrayAnterior[2], obtenerValorArrayAsociativo(arrayAsociativoLunchAcreditadoCodInicioSesion, codInicioSesionArray), obtenerValorArrayAsociativo(arrayAsociativoLunchAutomaticoCodInicioSesion, codInicioSesionArray)) : 0;
                    groupTotals.dia4 += item.dia4 ? parseFloat(item.dia4) + calcularDeducionPorAlmuerzo(parseFloat(item.dia4), esVeteranoArrayAnterior[3], obtenerValorArrayAsociativo(arrayAsociativoLunchAcreditadoCodInicioSesion, codInicioSesionArray), obtenerValorArrayAsociativo(arrayAsociativoLunchAutomaticoCodInicioSesion, codInicioSesionArray)) : 0;
                    groupTotals.dia5 += item.dia5 ? parseFloat(item.dia5) + calcularDeducionPorAlmuerzo(parseFloat(item.dia5), esVeteranoArrayAnterior[4], obtenerValorArrayAsociativo(arrayAsociativoLunchAcreditadoCodInicioSesion, codInicioSesionArray), obtenerValorArrayAsociativo(arrayAsociativoLunchAutomaticoCodInicioSesion, codInicioSesionArray)) : 0;
                    groupTotals.dia6 += item.dia6 ? parseFloat(item.dia6) + calcularDeducionPorAlmuerzo(parseFloat(item.dia6), esVeteranoArrayAnterior[5], obtenerValorArrayAsociativo(arrayAsociativoLunchAcreditadoCodInicioSesion, codInicioSesionArray), obtenerValorArrayAsociativo(arrayAsociativoLunchAutomaticoCodInicioSesion, codInicioSesionArray)) : 0;
                    groupTotals.dia7 += item.dia7 ? parseFloat(item.dia7) + calcularDeducionPorAlmuerzo(parseFloat(item.dia7), esVeteranoArrayAnterior[6], obtenerValorArrayAsociativo(arrayAsociativoLunchAcreditadoCodInicioSesion, codInicioSesionArray), obtenerValorArrayAsociativo(arrayAsociativoLunchAutomaticoCodInicioSesion, codInicioSesionArray)) : 0;
                    groupTotals.granTotal += item.total ? parseFloat(item.total) : 0;
                    // sumanTotal += parseFloat(groupTotals.dia1) + parseFloat(groupTotals.dia2) + parseFloat(groupTotals.dia3) + parseFloat(groupTotals.dia4) + parseFloat(groupTotals.dia5) + parseFloat(groupTotals.dia6) + parseFloat(groupTotals.dia7)
                    // console.log({ sumanTotal });
                    // groupTotals.granTotal = parseFloat(sumanTotal);
                }

            });

            group.forEach((item, index) => {
                if (!totals.granjas) {
                    totals.granjas = [];
                }

                //Verifica si la granja ya fue agregada al arreglo de granjas para no repetir la suma de los valores
                if (item.granja && !totals.granjas.includes(item.granja + item.cod_usuario + item.locacion)) {
                    totals.granjas.push(item.granja + item.cod_usuario + item.locacion);
                    sumanTotal = 0;
                    cantidadReducida = 0;
                    let esVeteranoArrayAnterior = item.es_veterano_concat.split(', ');
                    let lunchAcreditadoConcatArrayAnterior = item.lunch_acreditado_concat.split(', ');
                    let lunchAutomaticoConcatArrayAnterior = item.lunch_automatico_concat.split(', ');


                    lunchAutomaticoConcatArrayAnterior = item.lunch_automatico_concat.split(', ');
                    let codInicioSesionArray = item.cod_inicio_sesion_concat.split(', ');

                    let arrayAsociativoLunchAutomaticoCodInicioSesion = lunchAutomaticoConcatArrayAnterior.reduce((acc, curr, index) => {
                        acc[codInicioSesionArray[index]] = curr;
                        return acc;
                    }, {});

                    let arrayAsociativoLunchAcreditadoCodInicioSesion = lunchAcreditadoConcatArrayAnterior.reduce((acc, curr, index) => {
                        acc[codInicioSesionArray[index]] = curr;
                        return acc;
                    }, {});

                    item.dia1 = item.dia1 ? parseFloat(item.dia1) + calcularDeducionPorAlmuerzo(parseFloat(item.dia1), esVeteranoArrayAnterior[0], obtenerValorArrayAsociativo(arrayAsociativoLunchAcreditadoCodInicioSesion, codInicioSesionArray), obtenerValorArrayAsociativo(arrayAsociativoLunchAutomaticoCodInicioSesion, codInicioSesionArray)) : 0;
                    item.dia2 = item.dia2 ? parseFloat(item.dia2) + calcularDeducionPorAlmuerzo(parseFloat(item.dia2), esVeteranoArrayAnterior[1], obtenerValorArrayAsociativo(arrayAsociativoLunchAcreditadoCodInicioSesion, codInicioSesionArray), obtenerValorArrayAsociativo(arrayAsociativoLunchAutomaticoCodInicioSesion, codInicioSesionArray)) : 0;
                    item.dia3 = item.dia3 ? parseFloat(item.dia3) + calcularDeducionPorAlmuerzo(parseFloat(item.dia3), esVeteranoArrayAnterior[2], obtenerValorArrayAsociativo(arrayAsociativoLunchAcreditadoCodInicioSesion, codInicioSesionArray), obtenerValorArrayAsociativo(arrayAsociativoLunchAutomaticoCodInicioSesion, codInicioSesionArray)) : 0;
                    item.dia4 = item.dia4 ? parseFloat(item.dia4) + calcularDeducionPorAlmuerzo(parseFloat(item.dia4), esVeteranoArrayAnterior[3], obtenerValorArrayAsociativo(arrayAsociativoLunchAcreditadoCodInicioSesion, codInicioSesionArray), obtenerValorArrayAsociativo(arrayAsociativoLunchAutomaticoCodInicioSesion, codInicioSesionArray)) : 0;
                    item.dia5 = item.dia5 ? parseFloat(item.dia5) + calcularDeducionPorAlmuerzo(parseFloat(item.dia5), esVeteranoArrayAnterior[4], obtenerValorArrayAsociativo(arrayAsociativoLunchAcreditadoCodInicioSesion, codInicioSesionArray), obtenerValorArrayAsociativo(arrayAsociativoLunchAutomaticoCodInicioSesion, codInicioSesionArray)) : 0;
                    item.dia6 = item.dia6 ? parseFloat(item.dia6) + calcularDeducionPorAlmuerzo(parseFloat(item.dia6), esVeteranoArrayAnterior[5], obtenerValorArrayAsociativo(arrayAsociativoLunchAcreditadoCodInicioSesion, codInicioSesionArray), obtenerValorArrayAsociativo(arrayAsociativoLunchAutomaticoCodInicioSesion, codInicioSesionArray)) : 0;
                    item.dia7 = item.dia7 ? parseFloat(item.dia7) + calcularDeducionPorAlmuerzo(parseFloat(item.dia7), esVeteranoArrayAnterior[6], obtenerValorArrayAsociativo(arrayAsociativoLunchAcreditadoCodInicioSesion, codInicioSesionArray), obtenerValorArrayAsociativo(arrayAsociativoLunchAutomaticoCodInicioSesion, codInicioSesionArray)) : 0;
                    // sumanTotal += parseFloat(item.dia1) + parseFloat(item.dia2) + parseFloat(item.dia3) + parseFloat(item.dia4) + parseFloat(item.dia5) + parseFloat(item.dia6) + parseFloat(item.dia7)

                    // item.total = parseFloat(sumanTotal);

                    totals.dia1 += item.dia1 ? parseFloat(item.dia1) : 0;
                    totals.dia2 += item.dia2 ? parseFloat(item.dia2) : 0;
                    totals.dia3 += item.dia3 ? parseFloat(item.dia3) : 0;
                    totals.dia4 += item.dia4 ? parseFloat(item.dia4) : 0;
                    totals.dia5 += item.dia5 ? parseFloat(item.dia5) : 0;
                    totals.dia6 += item.dia6 ? parseFloat(item.dia6) : 0;
                    totals.dia7 += item.dia7 ? parseFloat(item.dia7) : 0;
                    totals.granTotal += item.total ? parseFloat(item.total) : 0;

                    if (index === 0) {
                        content = `<div id="td_cabecera_secundaria_${item.cod_usuario}" class="col-12 px-2 py-2 row">
                            <div class="col-4 px-0 py-2 desplegar_lista" type="button" data-bs-toggle="collapse" data-bs-target="#collapse${item.cod_usuario}" aria-expanded="true" aria-controls="collapse${item.cod_usuario}">${count}. ${item.name} <b>${item.es_veterano == 0 ? "(Regular)" : item.es_veterano == 1 ? "(Veteran)" : "(H2A)"} PIN: ${item.pin}</b></div>
                            <div class="col-1 px-0 py-2">${parseFloat(groupTotals.dia1).toFixed(2)} </div>
                            <div class="col-1 px-0 py-2">${parseFloat(groupTotals.dia2).toFixed(2)} </div>
                            <div class="col-1 px-0 py-2">${parseFloat(groupTotals.dia3).toFixed(2)} </div>
                            <div class="col-1 px-0 py-2">${parseFloat(groupTotals.dia4).toFixed(2)} </div>
                            <div class="col-1 px-0 py-2">${parseFloat(groupTotals.dia5).toFixed(2)} </div>
                            <div class="col-1 px-0 py-2">${parseFloat(groupTotals.dia6).toFixed(2)} </div>
                            <div class="col-1 px-0 py-2">${parseFloat(groupTotals.dia7).toFixed(2)} </div>
                            <div class="col-1 px-0 py-2">${parseFloat(groupTotals.granTotal).toFixed(2)}</div>
                        </div>`;
                    }

                    content += `<div  ${item.mostrar == 0 ? "hidden" : ""} id="collapse${item.cod_usuario}" class="accordion-collapse collapse row w-100 m-2 relative" data-bs-parent="#contenedor_datos_empleados">
                        <div class="col-12">
                            <div class="row w-100 border-bottom">
                                <div class="col-2 px-2 py-2">${item.name}</div>
                                <div class="col-1 px-2 py-2">${item.granja}</div>
                                <div class="col-1 px-2 py-2">${item.locacion}</div>
                                <div class="col-1 px-2 py-2">${item.dia1 ? parseFloat(item.dia1).toFixed(2) : "0.00"}</div>
                                <div class="col-1 px-2 py-2">${item.dia2 ? parseFloat(item.dia2).toFixed(2) : "0.00"}</div>
                                <div class="col-1 px-2 py-2">${item.dia3 ? parseFloat(item.dia3).toFixed(2) : "0.00"}</div>
                                <div class="col-1 px-2 py-2">${item.dia4 ? parseFloat(item.dia4).toFixed(2) : "0.00"}</div>
                                <div class="col-1 px-2 py-2">${item.dia5 ? parseFloat(item.dia5).toFixed(2) : "0.00"}</div>
                                <div class="col-1 px-2 py-2">${item.dia6 ? parseFloat(item.dia6).toFixed(2) : "0.00"}</div>
                                <div class="col-1 px-2 py-2">${item.dia7 ? parseFloat(item.dia7).toFixed(2) : "0.00"}</div>
                                <div class="col-1 px-2 py-2"><b>${item.total ? parseFloat(item.total).toFixed(2) : "0.00"}</b></div>
                            </div>
                        </div>
                    </div>`;
                }

            });

            contenedor.append(content);
        });

        let totalsContent = `
            <div class="col-12">
                <div class="row w-100 border-top border-dark">
                    <div class="col-1 px-2 py-2">
                        <b>Total</b>
                    </div>
                    <div class="col-1 px-2 py-2">
                        <b></b>
                    </div>
                    <div class="col-1 px-2 py-2">
                        <b></b>
                    </div>
                    <div class="col-1 px-2 py-2">
                        <b></b>
                    </div>
                    <div class="col-1 px-2 py-2">
                        <b>${parseFloat(totals.dia1).toFixed(2)}</b>
                    </div>
                    <div class="col-1 px-2 py-2">
                        <b>${parseFloat(totals.dia2).toFixed(2)}</b>
                    </div>
                    <div class="col-1 px-2 py-2">
                        <b>${parseFloat(totals.dia3).toFixed(2)}</b>
                    </div>
                    <div class="col-1 px-2 py-2">
                        <b>${parseFloat(totals.dia4).toFixed(2)}</b>
                    </div>
                    <div class="col-1 px-2 py-2">
                        <b>${parseFloat(totals.dia5).toFixed(2)}</b>
                    </div>
                    <div class="col-1 px-2 py-2">
                        <b>${parseFloat(totals.dia6).toFixed(2)}</b>
                    </div>
                    <div class="col-1 px-2 py-2">
                        <b>${parseFloat(totals.dia7).toFixed(2)}</b>
                    </div>
                    <div class="col-1 px-2 py-2">
                        <b>${parseFloat(totals.granTotal).toFixed(2)}</b>
                    </div>
                </div>
            </div>
        `;

        contenedor.append(totalsContent);
    } else {
        contenedor.append(`
            <div class="col-12">
                <div class="row w-100 border-top border-dark">
                    <div class="col-12 px-2 py-2">
                        No data available
                    </div>
                </div>
            </div>
        `);
    }
}

function exportarArchivo(week, farms, locations, employeeCodes, employeeCategories, formato = 'xlsx') {
    // Crear el formulario dinámicamente
    var form = $('<form>', {
        action: window.location.origin + '/v1/empleado/exportSignSheetReport',  // URL del endpoint
        method: 'POST',  // Método POST
        target: '_blank'  // Abrir en una nueva ventana o pestaña
    });

    // Datos a enviar en el formulario
    var formData = {
        _token: $('meta[name="csrf-token"]').attr('content'),  // Token CSRF
        week: week,
        farms: JSON.stringify(farms ?? []),
        locations: JSON.stringify(locations ?? []),
        format: formato,
        codigo_usuarios: JSON.stringify(employeeCodes ?? []),
        categorias_empleados: employeeCategories ?? []
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


function restarSiEsveterano(es_veterano, dia, total) {
    if (es_veterano == 1) {
        return total - dia;
    }
    return total;


}

// NOTA: No es necesario verificar si es veterano
function calcularDeducionPorAlmuerzo(valorDelDia, esVeterano, lunchAcreditado, lunchAutomatico, mostrarLogs = false) {
    if (mostrarLogs) {

        console.log("-------------------- CALCULAR DEDUCCION POR ALMUERZO ---------------------");
        console.log({ valorDelDia })
        console.log({ esVeterano })
        console.log({ lunchAcreditado })
        console.log({ lunchAutomatico })
        console.log("-------------------- ///  ---------------------");

    }
    if (valorDelDia == 0 || valorDelDia == null || valorDelDia == undefined) {
        return 0;
    } else if (lunchAcreditado == 1 || lunchAutomatico == 1) {
        // return -0.30;
        return 0;
    }
    return 0;
}




function obtenerValorArrayAsociativo(arrayAsociativo, codInicioSesionArray) {
    return arrayAsociativo[codInicioSesionArray.find(cod => arrayAsociativo.hasOwnProperty(cod))];
}

// arrayAsociativoLunchAcreditadoCodInicioSesion[codInicioSesionArray.find(cod => arrayAsociativoLunchAcreditadoCodInicioSesion.hasOwnProperty(cod))]