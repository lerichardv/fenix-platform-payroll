// import './bootstrap';
import ListPicker from './list-picker';
window.$ = window.jQuery = $;

// Declaramos el ListPicker para el bulkAddListPicker
window.bulkAddListPicker = new ListPicker([], '#bulk-add-list-picker');
window.addActivityListPicker = new ListPicker([], '#add_activity_list_picker');

$(document).ready(function () {

    const idNombreEmpleado = 'nombre_empleado_seleccionado';
    const idTablaPrincipal = 'tabla_registro';
    var totalDeHoras = 0;

    $('#input_add_activity_date').datetimepicker({
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
    });

    var initialComentarioValue = "";

    $('#formularioAgregarComentario').on('show.bs.modal', function () {
        initialComentarioValue = $('#comentario_registro_ingreso').val();
        initialComentarioValue = initialComentarioValue != null ? initialComentarioValue.trim() : "";
        $('#guardar_comentario_en_registro').prop('disabled', true);

    });

    $('#comentario_registro_ingreso').on('input', function () {
        const currentValue = $(this).val().trim();
        if (currentValue !== initialComentarioValue) {
            $('#guardar_comentario_en_registro').prop('disabled', false);
        } else {
            $('#guardar_comentario_en_registro').prop('disabled', true);

        }
    });

    $('#cod_farm').on('change', function () {
        cargarListaHarvestPorGranjas("cods_harvests", $("#fecha_del_grupo_modal").val());
        cargarListaMiscelaneosPorGranjas("cods_miscelaneos", $("#fecha_del_grupo_modal").val())
    });

    $('#cod_farm_registro_horario').on('change', function () {
        cargarLocationEnSelectPorId("cod_locacion", this);
    });

    $('#cod_farm_registro_horario_bulk_add').on('change', function () {
        cargarLocationEnSelectPorId("cod_locacion_bulk_add_registro", this);
    });

    $('#cod_usuario').on('change', function () {

        let fechaInicial = $('#initial_date').val();
        let fechaFinal = $('#final_date').val();
        // console.log({ fechaInicial, fechaFinal });
        if (fechaInicial == '' || fechaFinal == '' || fechaInicial == undefined ||
            fechaFinal == undefined) {
            // fechaInicial = "2024-08-01";
            // fechaFinal = "2024-09-28";
            alert('Select a date range');
            return;
        }

        var cod_usuario = $(this).val();


        var nombreSeleccionado = $(this).find('option:selected').text();
        var dataPin = $(this).find('option:selected').data('pin');
        var dataQCPin = $(this).find('option:selected').data('qcpin');
        var dataEsVeterano = $(this).find('option:selected').data('esveterano');

        totalDeHoras = 0;
        $('#btn_agregrar_nuevo_registro_entradasalida').attr('hidden', true);
        $('#' + idNombreEmpleado).empty();
        $('#' + idNombreEmpleado).append(`<b>${nombreSeleccionado} ${dataEsVeterano == 1 ? ' <span class="badge rounded-pill text-bg-success">Veteran</span>' : ""}</span></b> <br> <span style="font-size: 16px;">PIN: ${dataPin} | QCPIN: ${dataQCPin} | <span id="total_horas_sumadas">Total Time: ${totalDeHoras}</span>`);
        $('#' + idTablaPrincipal).empty();
        $('#' + idTablaPrincipal).append('<tr><td colspan="9">Loading ...</td></tr>');
        localStorage.setItem('cantidad_registros_fallidos', 0);

        let idUnicoFila = 0;
        $.ajax({
            url: window.location.href + 'api/v1/empleado_administracion/registrosIngresosYEgresos',
            type: 'POST',
            data: {
                cod_usuario: cod_usuario,
                initial_date: fechaInicial,
                final_date: fechaFinal
            },
            success: function (response) {

                jQuery.ajaxSetup({ async: false });
                let tableRow = ``;
                let claseParaEstado = 'registro_sin_error';

                let registros_fechas = response["data"]["registros_fechas"];

                // Get the pack types
                let pack_types = JSON.parse(response["data"]["pack_types"]);
                let packs_farms_category_assignment = JSON.parse(response["data"]["packs_farms_category_assignment"]);

                $('#' + idTablaPrincipal).empty();
                $('#btn_agregrar_nuevo_registro_entradasalida').attr('hidden', false);

                if (registros_fechas.length == 0) {
                    $('#' + idTablaPrincipal).append('<div style="width:100%;text-align:center;margin-top:15px;font-weight:bold">No records found.</div>');
                    return;
                }

                $.each(registros_fechas, function (index, registro) {
                    // Perform operations on each registro
                    let almenosUnaHoraFallida = false;
                    let cantidadRegistrosFallidos = localStorage.getItem('cantidad_registros_fallidos') ?? 0;
                    let CALCULO_HORAS = "N/D";
                    let codInicioSesion = registro["cod_inicio_sesion"];

                    let nombre_admin_comentario = registro["nombre_admin_comentario"];
                    let fecha_comentario = registro["fecha_comentario"];

                    let lunch_acreditado = registro["lunch_acreditado"];

                    let lunch_automatico = registro["lunch_automatico"];

                    let granjaClockin = registro["granjaClockin"];
                    let granjaClockinId = registro["granjaClockinId"];
                    let locationClockin = registro["locationClockin"];
                    let locationClockinId = registro["locationClockinId"];
                    let granjaClockout = registro["granjaClockout"];
                    let locationClockout = registro["locationClockout"];
                    let comentario = registro["comentario"];

                    dataEsVeterano = registro["usuario_veterano"];
                    $("#cod_usuario").find('option:selected').data('esveterano', dataEsVeterano);
                    $("#cod_usuario").find('option:selected').data('reducirtiempo', dataEsVeterano == false ? 1 : 0);
                    $("#cod_usuario").find('option:selected').data('lunchautomatico', lunch_automatico);

                    if (dataEsVeterano == 1) {
                        lunch_acreditado = 1;
                    }
                    let FECHA_ACTUAL = registro["fecha_ingreso_sin_horas"];
                    let FECHA_ACTUAL_formateada = registro["fecha_ingreso_mostrable"];
                    const HORA_ENTRADA = registro["fecha_ingreso"];
                    const HORA_SALIDA = registro["fecha_egreso"];
                    const HORA_ENTRADA_SIN_FORMATO = registro["fecha_ingreso_sin_formato"];
                    const HORA_SALIDA_SIN_FORMATO = registro["fecha_egreso_sin_formato"];
                    if (HORA_SALIDA_SIN_FORMATO != null) {
                        claseParaEstado = 'registro_sin_error';
                        CALCULO_HORAS = calcularCantidadHoras(
                            HORA_ENTRADA_SIN_FORMATO,
                            HORA_SALIDA_SIN_FORMATO
                        );

                        if (isGreaterThanFiveHours(CALCULO_HORAS)) {
                            if (dataEsVeterano == false && lunch_automatico == 1) {

                                lunch_acreditado = 1;
                                CALCULO_HORAS = restarTiempoAHoraEspecifica(CALCULO_HORAS, "00:30");

                            } else if (dataEsVeterano == false && lunch_acreditado == 1) {
                                CALCULO_HORAS = restarTiempoAHoraEspecifica(CALCULO_HORAS, "00:30");
                            }

                            // Code to execute if CALCULO_HORAS is greater than 5 hours
                            // console.log("CALCULO_HORAS (" + CALCULO_HORAS + ") is greater than 5 hours");
                        } else {
                            if (dataEsVeterano == false && lunch_acreditado == 1) {
                                CALCULO_HORAS = restarTiempoAHoraEspecifica(CALCULO_HORAS, "00:30");
                            }
                            // Code to execute if CALCULO_HORAS is not greater than 5 hours
                            // console.log("CALCULO_HORAS (" + CALCULO_HORAS + ") is not greater than 5 hours");
                        }
                    } else {
                        // Example of saving data to localStorage
                        cantidadRegistrosFallidos++;
                        localStorage.setItem('cantidad_registros_fallidos', cantidadRegistrosFallidos);
                        almenosUnaHoraFallida = true;
                        claseParaEstado = 'registro_con_error';
                    }
                    let cantidadRegistros = registro["datos_tareas"].length;

                    addTime(CALCULO_HORAS == "N/D" ? "00:00" : CALCULO_HORAS);
                    tableRow = `
                        <table class="table container-fluid accordion-item" style="width: 100%;margin-bottom:0px">
                            <thead style="background-color: black; width: 100%;" class="accordion-header">
                                <tr>
                                    <td id="td_cabecera_principal_${codInicioSesion}" class="datos_iniciales_registro_dia ${claseParaEstado}"colspan="7">
                                        ${FECHA_ACTUAL_formateada} | Activity: ${registro['actividad']['actividad_por_dia']} | Location: <button type="button" style="cursor:pointer;border:none;background-color:transparent" onclick="abrirModalEditarGranjaLocation(${codInicioSesion}, ${granjaClockinId}, ${locationClockinId})"><span class="txt_lugares_inicio_salida_registro_sesion" >${granjaClockin} ${locationClockin}</span></button> | CLOCK IN:

                                        <span>
                                            <button class="btnSeleccionables_small" style="width: auto;" onclick="activarEdicionHorasCabeceras('btn_hora_entrada_${codInicioSesion}', 'input_hora_entrada_${codInicioSesion}','${codInicioSesion}')" id="btn_hora_entrada_${codInicioSesion}">${HORA_ENTRADA ?? "--:--"}</button>
                                            <input type="time"  value="${HORA_ENTRADA_SIN_FORMATO ?? "00:00"}" class="input_texto" hidden  id="input_hora_entrada_${codInicioSesion}"
                                            onblur="detectarSalirDeInputHoraEntrada(
                                                                                        'btn_hora_entrada_${codInicioSesion}',
                                                                                        'input_hora_entrada_${codInicioSesion}',
                                                                                        'btn_hora_salida_${codInicioSesion}',
                                                                                        'input_hora_salida_${codInicioSesion}',
                                                                                        'lbl_cabecera_horas_totales_${codInicioSesion}',
                                                                                        '${codInicioSesion}'
                                                                                    )"
                                            onkeydown="detectarTeclasDeSalida(event,'btn_hora_entrada_${codInicioSesion}', 'input_hora_entrada_${codInicioSesion}','${HORA_ENTRADA_SIN_FORMATO}')" oninput="detectarDatosEntradaDeTeclados(event,'btn_hora_entrada_${codInicioSesion}', 'input_hora_entrada_${codInicioSesion}')" placeholder="">
                                        </span>
                                        |

                                        CLOCK OUT: <!--<span class="txt_lugares_inicio_salida_registro_sesion" >${granjaClockout} ${locationClockout}</span>-->
                                        <span>
                                            <button class="btnSeleccionables_small" style="width: auto;" onclick="activarEdicionHorasCabeceras('btn_hora_salida_${codInicioSesion}', 'input_hora_salida_${codInicioSesion}', '${codInicioSesion}')" id="btn_hora_salida_${codInicioSesion}">${HORA_SALIDA ?? "--:--"}</button>
                                            <input type="time"  value="${HORA_SALIDA_SIN_FORMATO ?? "00:00"}" class="input_texto" hidden  id="input_hora_salida_${codInicioSesion}"
                                            onblur="detectarSalirDeInputHoraSalida(
                                                                                        'btn_hora_salida_${codInicioSesion}',
                                                                                        'input_hora_salida_${codInicioSesion}',
                                                                                        'btn_hora_entrada_${codInicioSesion}',
                                                                                        'input_hora_entrada_${codInicioSesion}',
                                                                                        'lbl_cabecera_horas_totales_${codInicioSesion}',
                                                                                        '${codInicioSesion}'
                                                                                    )"
                                            onkeydown="detectarTeclasDeSalida(event,'btn_hora_salida_${codInicioSesion}', 'input_hora_salida_${codInicioSesion}','${HORA_ENTRADA_SIN_FORMATO}')" oninput="detectarDatosEntradaDeTeclados(event,'btn_hora_salida_${codInicioSesion}', 'input_hora_salida_${codInicioSesion}')" placeholder="">
                                        </span>
                                        |

                                        Total: <span id="lbl_cabecera_horas_totales_${codInicioSesion}">${CALCULO_HORAS}</span> ${`${!dataEsVeterano ? `<span id="lbl_hora_almuerzo_asignado_${codInicioSesion}" ${(lunch_acreditado == 0) ? 'hidden' : ''}> , Lunch 30 min.</span>` : ""}`}
                                    </td>
                                    <td id="td_cabecera_secundaria_${codInicioSesion}" colspan="2" class="col-width ${claseParaEstado}"  >
                                        <div class="w-full gap-2 d-flex flex-row align-items-center justify-content-end">
                                            <div ${dataEsVeterano == 1 ? 'hidden' : ''}>
                                                <input class="form-check-input" type="checkbox" ${lunch_acreditado == 1 ? "checked" : ""} onclick="alternarAsinacionAlmuerzo('${codInicioSesion}', 'lbl_cabecera_horas_totales_${codInicioSesion}')" id="checkbox_asignar_almuerzo_${codInicioSesion}" style="height: 25px; width: 25px; margin-top: 0px;">
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <button class="btn btn-info" data-toggle="tooltip" title="Add comment" onclick="abrirModalAgregarComentario(${codInicioSesion})" style="height: 25px; width: 25px; font-size: 12px; padding:0px;">
                                                    <i id="icon_comentario_${codInicioSesion}" data-comentario='${comentario}' data-nombre_admin_comentario='${nombre_admin_comentario}' data-fecha_comentario='${fecha_comentario}' class=" ${comentario == '' ? "fa-regular" : "fa-solid"} fa-comment"></i>
                                                </button>
                                                <button class="btn btn-danger" data-toggle="tooltip" title="Delete entry" onclick="eliminarTarea(${codInicioSesion})" style="height: 25px; width: 25px; font-size: 12px; padding:0px;">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                                <button class="btn btn-success " data-toggle="tooltip" title="ADD" data-bs-toggle="modal" onclick="abrirModal('${codInicioSesion}', '${cantidadRegistros}', '${FECHA_ACTUAL}')" style="height: 25px; width: 25px; font-size: 12px; padding:0px;">
                                                    <i class="fa-solid fa-plus"></i>
                                                </button>
                                            </div>
                                            <div>
                                                <a href="#!" onclick="cargarTareasPorFechaEmpleado('collapse${index}','${cod_usuario}', '${FECHA_ACTUAL}')" class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse${index}" aria-expanded="true" aria-controls="collapse${index}" style="padding:2px;border-radius:3px">

                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </thead>`;
                    localStorage.setItem('fila_abierta_collapse' + index, false);

                    tableRow += ` <tbody id="collapse${index}" class="accordion-collapse collapse" data-bs-parent="#tabla_registro">`;
                    if (cantidadRegistros > 0) {
                        tableRow += `
                        <tr>
                            <th scope="col" style="width: 10%;">Farm</th>
                            <th scope="col" style="width: 40%;">Job</th>
                            <th scope="col" style="width: 5%;">Type</th>
                            <th scope="col" style="width: 9%;">QTY</th>
                            <th scope="col" style="width: 10%;">PCK</th>
                            <th scope="col" style="width: 8%;">Start</th>
                            <th scope="col" style="width: 8%;">Stop</th>
                            <th scope="col" style="width: 5%;">Reg</th>
                            <th colspan="3" style="width: 5%;"
                                scope="col d-flex justify-content-center align-items-center py-3 mx-0 px-0">
                                Actions
                            </th>
                        </tr>`;
                    }
                    if (registro["datos_tareas"].length > 0) {
                        $.each(registro["datos_tareas"], function (index, item) {

                            tableRow += `<tr id="fila_tarea_${item["cod_crew"]}">
                                            <td style="width: 10%;">${item["nombre_granja"]}</td>
                                            <td style="width: 40%;" style="text-align: left">${item["job"]}</td>
                                            <td style="width: 5%;">${item["abreviatura_tipo_pago"]}</td>
                                            <td style="width: 9%;">
                                                <button class="btnSeleccionables" onclick="activarEdicion('btn_cantidad_escaneo${idUnicoFila}', 'input_cantidad_escaneo${idUnicoFila}')" id="btn_cantidad_escaneo${idUnicoFila}">${item["cantidad_escaneo"]}</button>
                                                <input class="input_texto" hidden type="number" min="0"  id="input_cantidad_escaneo${idUnicoFila}" onblur="detectarSalirDeInput('btn_cantidad_escaneo${idUnicoFila}', 'input_cantidad_escaneo${idUnicoFila}', '${item["cod_crew"]}')" value="${item["cantidad_escaneo"]}" onkeydown="detectarTeclasDeSalida(event,'btn_cantidad_escaneo${idUnicoFila}', 'input_cantidad_escaneo${idUnicoFila}','${item["cantidad_escaneo"]}')" oninput="detectarDatosEntradaDeTeclados(event,'btn_cantidad_escaneo${idUnicoFila}', 'input_cantidad_escaneo${idUnicoFila}')" placeholder="">
                                            </td>
                                            <td style="width: 10%;">
                                                ${item["cod_tipo_pack"] != undefined ? construirPackTypeSelectHarvest(
                                pack_types,
                                packs_farms_category_assignment,
                                item["cod_harvest"],
                                item["cod_farms"],
                                item["cod_categoria"],
                                item["cod_tipo_pack"]
                            ) : ""}
                                            </td>
                                            <td style="width: 8%;">
                                                <button class="btnSeleccionables" onclick="activarEdicion('btn_hora_inicio_${idUnicoFila}', 'input_hora_inicio_${idUnicoFila}')" id="btn_hora_inicio_${idUnicoFila}">${item["hora_inicio"]}</button>
                                                <input class="input_texto" hidden type="time" id="input_hora_inicio_${idUnicoFila}"
                                                            onblur="detectarSalirDeInputHoraInicio(
                                                                    'btn_hora_inicio_${idUnicoFila}',
                                                                    'input_hora_inicio_${idUnicoFila}',
                                                                    'label_calculo_horas_fila_${idUnicoFila}',
                                                                    'input_hora_final_${idUnicoFila}',
                                                                    '${item["cod_crew"]}',
                                                                )"
                                                value="${item["hora_inicio_sin_formato"]}" onkeydown="detectarTeclasDeSalida(event,'btn_hora_inicio_${idUnicoFila}', 'input_hora_inicio_${idUnicoFila}','${item["hora_inicio"]}')" oninput="detectarDatosEntradaDeTeclados(event,'btn_hora_inicio_${idUnicoFila}', 'input_hora_inicio_${idUnicoFila}')" placeholder="">
                                            </td>
                                            <td style="width: 8%;">
                                                <button class="btnSeleccionables" onclick="activarEdicion('btn_hora_final_${idUnicoFila}', 'input_hora_final_${idUnicoFila}')" id="btn_hora_final_${idUnicoFila}">${item["hora_final"] ?? '--:--'}</button>
                                                <input class="input_texto" hidden type="time" id="input_hora_final_${idUnicoFila}"
                                                        onblur="detectarSalirDeInputHoraFinal(
                                                            'btn_hora_final_${idUnicoFila}',
                                                            'input_hora_final_${idUnicoFila}',
                                                            'label_calculo_horas_fila_${idUnicoFila}',
                                                            'input_hora_inicio_${idUnicoFila}',
                                                            '${item["cod_crew"]}'
                                                        )"
                                                value="${item["hora_final_sin_formato"] ?? '00:00'}" onkeydown="detectarTeclasDeSalida(event,'btn_hora_final_${idUnicoFila}', 'input_hora_final_${idUnicoFila}','${item["hora_final"]}')" oninput="detectarDatosEntradaDeTeclados(event,'btn_hora_final_${idUnicoFila}', 'input_hora_final_${idUnicoFila}')" placeholder="">
                                            </td>
                                            <td style="width: 5%;" id="label_calculo_horas_fila_${idUnicoFila}">${item["hora_final_sin_formato"] == null ? '--:--' : calcularCantidadHoras(
                                item["hora_inicio_sin_formato"],
                                item["hora_final_sin_formato"]
                            )}
                                            </td>
                                            <td style="width: 5%;">
                                                <div class="row container-fluid justify-content-around">
                                                    <div class="col-12">
                                                        <button class="btn btn-danger" data-toggle="tooltip" title="Delete" onclick="eliminarVinculoConTarea(${item["cod_crew"]}, 'fila_tarea_${item["cod_crew"]}')" style="height: 25px; width: 25px; font-size: 12px; padding:0px;">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    `;
                            idUnicoFila++;

                        });

                    } else {
                        tableRow += `
                            <tr>
                                <td colspan="9">No data found</td>
                            </tr>
                        `;
                    }


                    tableRow += `
                            </tbody>
                        </table>`;

                    $('#' + idTablaPrincipal).append(tableRow);
                    if (almenosUnaHoraFallida) {
                        $('#total_horas_sumadas').text("Total Time: N/D");
                    } else {

                        $('#total_horas_sumadas').text(cantidadRegistrosFallidos > 0 ? "N/D" : "Total Time: " + formatTime(totalDeHoras));
                    }
                    $('#total_horas_cabecera').val(totalDeHoras);
                    jQuery.ajaxSetup({ async: true });

                });

                jQuery.ajaxSetup({ async: true });
            },
            error: function (xhr) {
                console.error('Error:', xhr.responseText);
                $('#' + idTablaPrincipal).empty();
                $('#' + idTablaPrincipal).append('<tr><td colspan="9">No records found.</td></tr>');

            }
        });

    });

    $('#search').on('keyup', function () {
        var query = $(this).val();
        $('#employee_list').empty();
        $('#employee_list').html('<tr><td colspan="9">Loading ...</td></tr>');
        $.ajax({
            url: window.location.href + 'api/v1/empleado_administracion/searchEmpleados',
            type: "GET",
            data: {
                'query': query,
                'fecha_inicial': $('#initial_date').val(),
                'fecha_final': $('#final_date').val(),
                'cod_farm': $('#cod_farm').val(),
                'cod_location': $('#cod_location').val()
            },
            success: function (data) {
                // console.log({ data })
                $('#employee_list').html(data);
            }
        });
    });

    $('#search').trigger('keyup');

    $('#cods_harvests').on('change', function () {
        if ($(this).val() != '-b') {
            $('#cods_miscelaneos').val('-b');
            $('#cods_miscelaneos').prop('disabled', true);
            $('#cods_miscelaneos').removeClass('select_enabled');
            $('#cods_miscelaneos').addClass('select_disabled');
        } else {

            $('#cods_miscelaneos').prop('disabled', false);
            $('#cods_miscelaneos').addClass('select_enabled');
            $('#cods_miscelaneos').removeClass('select_disabled');
        }

        var abreviaturatipopago = $(this).find('option:selected').data('abreviaturatipopago');
        var horainicio = $(this).find('option:selected').data('horainicio');
        var horafinal = $(this).find('option:selected').data('horafinal');
        $('#tipo_pago_modal').val(abreviaturatipopago);
        if (horainicio != null && horafinal != null) {
            $('#hora_inicio_modal').val(horainicio);
            $('#hora_final_modal').val(horafinal);
            $('#total_horas_modal').val(calcularCantidadHoras(
                convertTo24HourFormat(horainicio),
                convertTo24HourFormat(horafinal)
            ));
        } else {
            $('#hora_inicio_modal').val("--:--");
            $('#hora_final_modal').val("--:--");
            $('#total_horas_modal').val("--:--");
        }

    });

    $('#cods_miscelaneos').on('change', function () {
        if ($(this).val() != '-b') {
            $('#cods_harvests').val('-b');
            $('#cods_harvests').prop('disabled', true);
            $('#cods_harvests').removeClass('select_enabled');
            $('#cods_harvests').addClass('select_disabled');
        } else {

            $('#cods_harvests').prop('disabled', false);
            $('#cods_harvests').addClass('select_enabled');
            $('#cods_harvests').removeClass('select_disabled');
        }

        var abreviaturatipopago = $(this).find('option:selected').data('abreviaturatipopago');
        var codjob = $(this).find('option:selected').data('codjob');
        var horainicio = $(this).find('option:selected').data('horainicio');
        var horafinal = $(this).find('option:selected').data('horafinal');
        $('#tipo_pago_modal').val(abreviaturatipopago);
        if (horainicio != null && horafinal != null) {
            $('#hora_inicio_modal').val(horainicio);
            $('#hora_final_modal').val(horafinal);
            $('#total_horas_modal').val(calcularCantidadHoras(
                convertTo24HourFormat(horainicio),
                convertTo24HourFormat(horafinal)
            ));
        } else {
            $('#hora_inicio_modal').val("--:--");
            $('#hora_final_modal').val("--:--");
            $('#total_horas_modal').val("--:--");
        }

    });

    $('#guardar_tarea_seleccionada').on('click', function () {
        // Add your code here
        // console.log($('#cods_harvests').val())
        // console.log($('#cods_miscelaneos').val())
        if ($('#cods_harvests').val() == '-b' && $('#cods_miscelaneos').val() == '-b') {
            alert('Select a harvest or a miscellaneous');
            return;
        }

        $.ajax({
            url: window.location.href + 'api/v1/empleado_administracion/asignarTareaExistente',
            type: 'POST',
            data: {
                cod_harvest: $('#cods_harvests').val(),
                cod_miscelaneos: $('#cods_miscelaneos').val(),
                cantidad_escaneos: $('#cantidad_escaneo_modal').val(),
                cod_empleado: $('#cod_usuario').val(),
            },
            success: function (response) {
                // console.log({ response });
                if (response["success"] == true) {
                    $('#cod_usuario').trigger('change');
                    alert(response["message"]);
                } else {
                    alert(response["message"]);

                }

            },
            error: function (xhr) {
                console.error('Error:', xhr.responseText);
            }
        });


        //Restablece el estado inicial del modal

        $('#cod_farm').val('-b');

        $('#cods_harvests').empty();
        $('#cods_miscelaneos').empty();

        $('#cods_harvests').append('<option value="-b" >--Select--</option>');
        $('#cods_harvests').prop('disabled', false);
        $('#cods_harvests').addClass('select_enabled');
        $('#cods_harvests').removeClass('select_disabled');

        $('#cods_miscelaneos').prop('disabled', false);
        $('#cods_miscelaneos').addClass('select_enabled');
        $('#cods_miscelaneos').removeClass('select_disabled');
        $('#cods_miscelaneos').append('<option value="-b">--Select--</option>');

        $('#tipo_pago_modal').val("--");
        $('#cantidad_escaneo_modal').val(0);
        $('#hora_inicio_modal').val("--:--");
        $('#hora_final_modal').val("--:--");
        $('#total_horas_modal').val("--:--");

        $('#formularioAgregarTarea').modal('hide');


    });
    $('#guardar_nuevo_registro_ingreso').on('click', function () {
        if ($('#cod_locacion').val() == '-b') {
            alert('Please select a location.');
            return false;
        }
        if (!esMayorElTiempo($("#hora_salida").val(), $("#hora_entrada").val())) {
            alert('The Clock out time must be greather than clock in.');
            return false;
        }
        if ($("#switch_bulk_add").prop("checked")) {
            $.ajax({
                url: window.location.href + 'api/v1/empleado_administracion/nuevoRegistroIngresoYSalidaBulkAdd',
                type: 'POST',
                data: {
                    cod_usuario: $('#cod_usuario').val(),
                    cod_farm: $('#cod_farm_registro_horario').val(),
                    cod_locacion: $('#cod_locacion').val(),
                    // fecha_registro: $('#fecha_registro_modal').val(),
                    cod_actividad_por_dia: $('#bulk_edit_cod_actividad_por_dia').val(),
                    fecha_from: $("#bulk_edit_from_date").val(),
                    fecha_to: $("#bulk_edit_to_date").val(),
                    incluir_fines_de_semana: $("#checkbox_include_weekends_bulk_add_mode").prop('checked') ? "yes" : "no",
                    hora_entrada: $('#hora_entrada').val(),
                    hora_salida: $('#hora_salida').val(),
                },
                success: function (response) {
                    // console.log(response.message);
                    // console.log({ response });

                    if (response["success"] == true) {
                        $('#cod_usuario').trigger('change');
                        alert(response["message"]);
                    } else {
                        alert(response["message"]);
                    }

                },
                error: function (xhr) {
                    console.error('Error:', xhr.responseText);
                }
            })
        } else {
            $.ajax({
                url: window.location.href + 'api/v1/empleado_administracion/nuevoRegistroIngresoYSalida',
                type: 'POST',
                data: {
                    cod_usuario: $('#cod_usuario').val(),
                    cod_farm: $('#cod_farm_registro_horario').val(),
                    cod_locacion: $('#cod_locacion').val(),
                    fecha_registro: $('#fecha_registro_modal').val(),
                    hora_entrada: $('#hora_entrada').val(),
                    hora_salida: $('#hora_salida').val(),
                },
                success: function (response) {
                    // console.log(response.message);
                    // console.log({ response });

                    if (response["success"] == true) {
                        $('#cod_usuario').trigger('change');
                        alert(response["message"]);
                    } else {
                        alert(response["message"]);

                    }

                },
                error: function (xhr) {
                    console.error('Error:', xhr.responseText);
                }
            });
        }

        //Restablece el estado inicial del modal

        $('#cod_farm').val('-b');

        $('#cods_harvests').empty();
        $('#cods_miscelaneos').empty();

        $('#cods_harvests').append('<option value="-b" >--Select--</option>');
        $('#cods_harvests').prop('disabled', false);
        $('#cods_harvests').addClass('select_enabled');
        $('#cods_harvests').removeClass('select_disabled');

        $('#cods_miscelaneos').prop('disabled', false);
        $('#cods_miscelaneos').addClass('select_enabled');
        $('#cods_miscelaneos').removeClass('select_disabled');
        $('#cods_miscelaneos').append('<option value="-b">--Select--</option>');

        $('#tipo_pago_modal').val("--");
        $('#cantidad_escaneo_modal').val(0);
        $('#hora_inicio_modal').val("--:--");
        $('#hora_final_modal').val("--:--");
        $('#total_horas_modal').val("--:--");

        $('#formularioAgregarTarea').modal('hide');
        toggleBulkAddModeOnAddNewClockRecordModal(false);

    });

    $('#btn_bulk_add_guardar').on('click', function () {
        let employeesSelected = window.bulkAddListPicker.selected();
        if ($("#cod_actividad_por_dia_bulk_add_registro").val() == "" ||
            $("#cod_locacion_bulk_add_registro").val() == "" ||
            $("#hora_entrada_bulk_add_registro").val() == "" ||
            $("#hora_salida_bulk_add_registro").val() == "" ||
            $("#bulk_edit_actividades_empleados_granjas_from_date").val() == "" ||
            $("#bulk_edit_actividades_empleados_granjas_to_date").val() == "") {
            Swal.fire("All the fields are required.");
            return false;
        }
        if (!esMayorElTiempo($("#hora_salida_bulk_add_registro").val(), $("#hora_entrada_bulk_add_registro").val())) {
            Swal.fire("The Clock out time must be greather than clock in.");
            return false;
        }
        if (employeesSelected.length == 0) {
            Swal.fire("You have to select the employees before.");
            return false;
        }
        let employeeNames = employeesSelected.map((employee) => {
            return employee.name;
        });

        Swal.fire({
            title: "Are you sure you want to process the bulk add for the following employees?",
            text: employeeNames.join(", "),
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: "Process",
            denyButtonText: `Don't process`
        }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
                $("#btn_bulk_add_cancelar").hide();
                $("#btn_bulk_add_guardar").hide();
                $("#bulk_add_footer_loader_wrapper").append(`<i class="fa-solid fa-circle-notch fa-2xl fa-spin" style="--fa-animation-duration: 0.7s;"></i>`);
                $.ajax({
                    type: "POST",
                    url: window.location.href + 'api/v1/empleado_administracion/nuevoRegistroActividadPorGranjaEmpleadosBulkAdd',
                    data: {
                        empleados: JSON.stringify(employeesSelected),
                        cod_farm: $('#cod_farm_registro_horario_bulk_add').val(),
                        cod_locacion: $('#cod_locacion_bulk_add_registro').val(),
                        cod_actividad_por_dia: $('#cod_actividad_por_dia_bulk_add_registro').val(),
                        fecha_from: $("#bulk_edit_actividades_empleados_granjas_from_date").val(),
                        fecha_to: $("#bulk_edit_actividades_empleados_granjas_to_date").val(),
                        incluir_fines_de_semana: $("#checkbox_include_weekends_bulk_edit").prop('checked') ? "yes" : "no",
                        hora_entrada: $('#hora_entrada_bulk_add_registro').val(),
                        hora_salida: $('#hora_salida_bulk_add_registro').val()
                    },
                    success: (response) => {
                        // console.log(response);
                        Swal.fire({
                            title: "Bulk add employees activities",
                            text: "The action was completed sucesfully.",
                            icon: "success"
                        });

                        // Restaurar el estado del modal
                        $("#btn_bulk_add_cancelar").show();
                        $("#btn_bulk_add_guardar").show();
                        $("#bulk_add_footer_loader_wrapper").empty();

                        $("#cod_farm_registro_horario_bulk_add").val("-b");
                        $("#cod_locacion_bulk_add_registro").val("-b");
                        $("#cod_actividad_por_dia_bulk_add_registro").val(1);
                        $("#hora_entrada_bulk_add_registro").val("00:00");
                        $("#hora_salida_bulk_add_registro").val("00:00");

                        window.bulkAddListPicker.reset();
                    },
                    error: function (xhr) {
                        console.error('Error:', xhr.responseText);
                        Swal.fire({
                            title: "Bulk add employees activities",
                            text: "The action could not be completed, please contact the administrator.",
                            icon: "error"
                        });
                    }
                });

            } else if (result.isDenied) {
                Swal.fire("Changes are not saved", "", "info");
            }
        });
    });

    $("#switch_bulk_add").change(function () {
        if ($(this).prop("checked")) {
            toggleBulkAddModeOnAddNewClockRecordModal();
        } else {
            toggleBulkAddModeOnAddNewClockRecordModal(false);
        }
    });

    // Actions for the add harvest or miscelaneous activity for employees modal
    $(".misc-field, .harvest-field").hide();
    $("#select_activity_type_add_activity").on("change", (e) => {
        $("#select_cod_farm_add_activity_form").trigger("change");
        $(".misc-field, .harvest-field").hide();
        if ($("#select_activity_type_add_activity").val() == "harvest") {
            $(".harvest-field").show();
        } else {
            $(".misc-field").show();
        }
    });
    $("#select_cod_farm_add_activity_form").on("change", (e) => {
        cargarCropsPorGranjaEnSelect("select_crop_ages_add_activity_form", e.target.value, $("#select_activity_type_add_activity").val() == "misc" ? true : false);
        cargarLocationEnSelectPorId("select_location_add_activity_form", $("#select_cod_farm_add_activity_form"));
    })
    $('#select_crop_ages_add_activity_form').on("change", (e) => {
        let value = JSON.parse($('#select_crop_ages_add_activity_form').val());
        cargarCamposPorCrop(value.cods_fields, value.cod_plantacion, "select_fields_add_activity_form");
        cargarlistaPaquetesAsociadosGranjasLocacionInventario(
            $("#select_cod_farm_add_activity_form").val(),
            value.cod_semilla,
            value.cod_categoria,
            "select_pack_type_add_activity_form"
        );
    });
    $('#select_fields_add_activity_form').on("change", (e) => {
        let valueCrop = JSON.parse($('#select_crop_ages_add_activity_form').val());
        cargarBloquesPorGranjaSemillaCampo(
            $("#select_cod_farm_add_activity_form").val(),
            $('#select_fields_add_activity_form').val(),
            valueCrop.cod_semilla,
            valueCrop.cod_plantacion,
            "select_bloque_add_activity_form"
        );
    });
    $("#btn_add_activity_save").on("click", () => {
        let valueCrop;
        try {
            valueCrop = JSON.parse($('#select_crop_ages_add_activity_form').val());
        } catch (error) {
            valueCrop = null; // Null if the json cannot be parsed
        }
        crearActividadHarvestOMiscelanea(
            $("#input_add_activity_date").val(),
            $("#input_time_start_add_activity").val(),
            $("#input_time_end_add_activity").val(),
            $("#select_activity_type_add_activity").val(),
            $("#select_cod_farm_add_activity_form").val(),
            valueCrop ? valueCrop.cod_crop_age : null,
            $("#select_fields_add_activity_form").val() ? $("#select_fields_add_activity_form").val() : null,
            $("#select_bloque_add_activity_form").val() ? $("#select_bloque_add_activity_form").val() : null,
            $("#select_pack_type_add_activity_form").val() ? $("#select_pack_type_add_activity_form").val() : null,
            $("#select_location_add_activity_form").val(),
            $("#select_misc_activity_add_activity_form").val(),
            window.addActivityListPicker.selected(),
            valueCrop ? valueCrop.cod_plantacion : null
        );
    });
    $("#select_location_add_activity_form").on("change", (e) => {
        cargarActividadesPorGranjaLocation(
            $("#select_cod_farm_add_activity_form").val(),
            $("#select_location_add_activity_form").val(),
            "select_misc_activity_add_activity_form"
        );
    });

    $('#guardar_comentario_en_registro').on('click', function () {

        // Mostrar loading
        $('#guardar_comentario_en_registro').prop('disabled', true);
        $('#guardar_comentario_en_registro').append('<span id="comentario_loading_spinner" class="spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true"></span>');

        $.ajax({
            url: window.location.href + 'api/v1/empleado_administracion/agregarComentarioRegistroIngreso',
            type: 'POST',
            data: {
                comentario_registro_ingreso: $('#comentario_registro_ingreso').val().trim(),
                id_registro_ingreso: $('#id_registro_ingreso').val(),
                fecha_comentario: getCurrentFormattedDate(),
            },
            success: function (response) {
                $('#guardar_comentario_en_registro').prop('disabled', false);
                $('#guardar_comentario_en_registro').find('#comentario_loading_spinner').remove();
                // console.log({ response });
                if (response["success"] == true) {
                    alert(response["message"]);
                    const iconId = `#icon_comentario_${$('#id_registro_ingreso').val()}`;
                    $(iconId).data('comentario', $('#comentario_registro_ingreso').val());


                    $(iconId).data('nombre_admin_comentario', response["data"]['nombre_admin_comentario']);
                    $(iconId).data('fecha_comentario', response["data"]['fecha_comentario']);

                    if ($('#comentario_registro_ingreso').val() == null || $('#comentario_registro_ingreso').val() == "") {
                        $(iconId).removeClass('fa-solid').addClass('fa-regular');

                    } else {
                        $(iconId).removeClass('fa-regular').addClass('fa-solid');
                    }
                    $('#comentario_registro_ingreso').val("");
                    $('#id_registro_ingreso').val("");

                    $('#formularioAgregarComentario').modal('hide');
                } else {
                    alert(response["message"]);

                }

            },
            error: function (xhr) {
                $('#guardar_comentario_en_registro').prop('disabled', false);
                $('#guardar_comentario_en_registro').find('#comentario_loading_spinner').remove();
                console.error('Error:', xhr.responseText);
            }, finally: function () {
                $('#guardar_comentario_en_registro').prop('disabled', false);
                $('#guardar_comentario_en_registro').find('#comentario_loading_spinner').remove();
            }
        });

    });

    function getCurrentFormattedDate() {
        const now = new Date();
        const day = now.getDate().toString().padStart(2, '0');
        const month = (now.getMonth() + 1).toString().padStart(2, '0');
        const year = now.getFullYear();
        const hours = now.getHours().toString().padStart(2, '0');
        const minutes = now.getMinutes().toString().padStart(2, '0');
        return `${day}-${month}-${year} ${hours}:${minutes}`;
    }

    toggleBulkAddModeOnAddNewClockRecordModal(false);

    function toggleBulkAddModeOnAddNewClockRecordModal(bulkAddModeEnabled = true) {
        $("#contenedor_date").toggle(!bulkAddModeEnabled);
        $("#bulk_add_mode_fields").toggle(bulkAddModeEnabled);
        if (bulkAddModeEnabled) {
            $("#div_clock_in_time").removeClass("col-md-4").addClass("col-md-6");
            $("#div_clock_out_time").removeClass("col-md-4").addClass("col-md-6");
        } else {
            $("#div_clock_in_time").removeClass("col-md-6").addClass("col-md-4");
            $("#div_clock_out_time").removeClass("col-md-6").addClass("col-md-4");
            if ($("#switch_bulk_add").prop("checked")) {
                $("#switch_bulk_add").prop("checked", false);
            }
        }
    }

    function cargarLocationEnSelectPorId(id = "cod_location", granjaElement, callback = () => { }) {
        var cod_farm = $(granjaElement).val();

        if (cod_farm == '-b') {
            $('#' + id).val('-b');
            $('#' + id).prop('disabled', true);
            $('#' + id).removeClass('select_enabled');
            $('#' + id).addClass('select_disabled');
        } else {

            $('#' + id).prop('disabled', false);
            $('#' + id).addClass('select_enabled');
            $('#' + id).removeClass('select_disabled');
        }

        $.ajax({
            url: window.location.href + 'api/v1/empleado_administracion/listaLocacionesPorGranjas',
            type: 'POST',
            data: {
                cod_farm: cod_farm
            },
            success: function (response) {

                jQuery.ajaxSetup({ async: false });

                // console.log({ response });
                const data = response;
                $('#' + id).empty();

                if (data.length > 0) {
                    $('#' + id).append('<option value="-b" >--Select--</option>');

                    $.each(data, function (i, item) {
                        $('#' + id).append(`<option value="${item.cod_location}">${item.location}</option>`);
                    });
                } else {
                    $('#' + id).append('<option value="-b" >No locations found</option>');

                }

                jQuery.ajaxSetup({ async: true });
                if (callback) callback();
            },
            error: function (xhr) {
                console.error('Error:', xhr.responseText);

            }
        });

    }

    function cargarCropsPorGranjaEnSelect(id_select_crops, cod_farm, include_not_required = false, callback = () => { }) {
        $.ajax({
            url: window.location.href + `api/v1/cropAsociadasAGranja/${cod_farm}`,
            type: 'GET',
            data: {
                cod_farm: cod_farm
            },
            success: function (response) {
                console.log(response);
                const data = response;
                $('#' + id_select_crops).empty();
                if (data.length > 0) {
                    $('#' + id_select_crops).append('<option value="" disabled selected>--Select--</option>');
                    if (include_not_required) {
                        $('#' + id_select_crops).append('<option value="0">Not required</option>');
                    }
                    $.each(data, function (i, item) {
                        $('#' + id_select_crops).append(`<option value='${JSON.stringify({ cod_crop_age: item.cod_crop_age, cods_fields: item.cods_fields, cod_plantacion: item.cod_plantacion, cod_semilla: item.cod_semilla, cod_categoria: item.cod_categoria })}'>${item.datos_semillas}</option>`);
                    });
                } else {
                    $('#' + id_select_crops).append('<option value="" >No crops found</option>');
                }
                if (callback) callback();
            },
            error: function (xhr) {
                console.error('Error:', xhr.responseText);

            }
        });

    }

    function cargarCamposPorCrop(codsCampos, cod_plantacion, id_select_campos, oncomplete = () => { }) {
        // console.log([codsCampos, cod_plantacion]);
        $.ajax({
            type: "POST",
            url: window.location.href + "api/v1/campoUsadoEnCropAge",
            data: {
                codsCampos: codsCampos,
                cod_plantacion: cod_plantacion
            },
            success: (data) => {
                $('#' + id_select_campos).empty();
                if (data.length > 0) {
                    $('#' + id_select_campos).append('<option value="-b" disabled selected>--Select--</option>');
                    $.each(data, function (i, item) {
                        $('#' + id_select_campos).append(`<option value='${item.cod_field}'>${item.field}</option>`);
                    });
                } else {
                    $('#' + id_select_campos).append('<option value="-b" >No fields found</option>');
                }
                if (oncomplete) oncomplete();
            },
            error: (err) => {
                console.log(err);
            }
        });
    }

    function cargarBloquesPorGranjaSemillaCampo(cod_farm, cods_fields, cod_semilla, cod_plantacion, id_select, oncomplete = () => { }) {
        $.ajax({
            type: "POST",
            url: window.location.href + "api/v1/bloquesEnCropAgeAsociadosCampo",
            data: {
                cod_farm: cod_farm,
                cods_fields: cods_fields,
                cod_semilla: cod_semilla,
                cod_plantacion: cod_plantacion,
            },
            success: (data) => {
                $('#' + id_select).empty();
                if (data.length > 0) {
                    $('#' + id_select).append('<option value="-b" disabled selected>--Select--</option>');
                    $.each(data, function (i, item) {
                        $('#' + id_select).append(`<option value='${item.cod_bloque}'>${item.bloque}</option>`);
                    });
                } else {
                    $('#' + id_select).append('<option value="-b" >No block found</option>');
                }
                if (oncomplete) oncomplete();
            },
            error: (err) => {
                console.log(err);
            }
        });
    }

    function cargarlistaPaquetesAsociadosGranjasLocacionInventario(cod_farm, cod_semilla, cod_categoria, id_select, cod_locacion = 0) {
        $.ajax({
            type: "POST",
            url: window.location.href + "api/v1/listaPaquetesAsociadosGranjasLocacionInventario",
            data: {
                cod_farm: cod_farm,
                cod_semilla: cod_semilla,
                cod_categoria: cod_categoria,
                cod_locacion: cod_locacion
            },
            success: (data) => {
                console.log(data);
                $('#' + id_select).empty();
                if (data.length > 0) {
                    $('#' + id_select).append('<option value="-b" disabled selected>--Select--</option>');
                    $.each(data, function (i, item) {
                        $('#' + id_select).append(`<option value='${item.cod_tipo_pack}'>${item.tipo_pack}</option>`);
                    });
                } else {
                    $('#' + id_select).append('<option value="-b" >No pack type found</option>');
                }
            },
            fail: (err) => {
                console.log(err);
            }
        })
    }

    function cargarActividadesPorGranjaLocation(cod_farm, cod_location, id_select_actividades) {
        $.ajax({
            type: "POST",
            url: window.location.href + "api/v1/actividades/listaActividadesPorGranjaLocacion",
            data: {
                cod_farm: cod_farm,
                cod_location: cod_location
            },
            success: (data) => {
                $('#' + id_select_actividades).empty();
                if (data.length > 0) {
                    $('#' + id_select_actividades).append('<option value="-b" disabled selected>--Select--</option>');
                    $.each(data, function (i, item) {
                        $('#' + id_select_actividades).append(`<option value='${item.cod_activity}'>${item.activity}</option>`);
                    });
                } else {
                    $('#' + id_select_actividades).append('<option value="-b" >No activities found</option>');
                }
            },
            error: (err) => {
                console.log(err);
            }
        })
    }

    function crearActividadHarvestOMiscelanea(
        date, time_start, time_end,
        activity_type, cod_farm, crop_age,
        cod_field, cod_block, pack_type,
        cod_location, cod_actividad, employees,
        cod_plantacion, oncomplete = () => { }
    ) {

        // Validations
        let errors = validateAddActivityForm(
            date, time_start, time_end,
            activity_type, cod_farm, crop_age,
            cod_field, cod_block, pack_type,
            cod_location, cod_actividad, employees
        );

        if (errors.length > 0) {
            let htmlString = "";
            errors.forEach((err) => {
                htmlString += err + "<br/>";
            });
            Swal.fire({
                title: "Please check the fields",
                html: htmlString,
                icon: "error"
            });
            return false;
        }

        $.ajax({
            type: "POST",
            url: window.location.href + "api/v1/empleado_administracion/registroActividadHarvestMiscelaneaEmpleadosBulkAdd",
            data: {
                date: date,
                time_start: time_start,
                time_end: time_end,
                activity_type: activity_type,
                cod_farm: cod_farm,
                crop_age: crop_age ?? "null",
                cod_field: cod_field ?? "null",
                cod_block: cod_block ?? "null",
                pack_type: pack_type ?? "null",
                cod_location: cod_location,
                cod_actividad: cod_actividad,
                cod_plantacion: cod_plantacion,
                employees: JSON.stringify(employees.map((e) => { return e.id }))
            },
            success: (res) => {
                Swal.fire({
                    title: "Added successfully",
                    html: "The activity was added successfully",
                    icon: "success"
                });
                window.addActivityListPicker.reset();
                if (oncomplete) oncomplete();
            },
            error: (err) => {
                console.log(err);
            }
        })
    }

    function validateAddActivityForm(
        date, time_start, time_end,
        activity_type, cod_farm, crop_age,
        cod_field, cod_block, pack_type,
        cod_location, cod_actividad, employees
    ) {
        let errors = [];

        if (date == "date" || date == null || date == undefined) {
            errors.push("The date is required.");
        }
        if (time_start == "time_start" || time_start == null || time_start == undefined) {
            errors.push("The start time is required.");
        }
        if (time_end == "time_end" || time_end == null || time_end == undefined) {
            errors.push("The end time is required.");
        }
        if (activity_type == "activity_type" || activity_type == null || activity_type == undefined) {
            errors.push("The activity type is required.");
        }
        if (cod_farm == "cod_farm" || cod_farm == null || cod_farm == undefined || cod_farm == "-b") {
            errors.push("The farm is required.");
        }

        if (activity_type == "harvest") {
            if (crop_age == "" || crop_age == null || crop_age == undefined) {
                errors.push("The crop age is required.");
            }
            if (cod_field == "" || cod_field == null || cod_field == undefined) {
                errors.push("The field is required.");
            }
            if (cod_block == "" || cod_block == null || cod_block == undefined) {
                errors.push("The block is required.");
            }
            if (pack_type == "" || pack_type == null || pack_type == undefined) {
                errors.push("The pack type is required.");
            }
        }
        if (activity_type == "misc") {
            if (cod_location == "" || cod_location == null || cod_location == undefined || cod_location == "-b") {
                errors.push("The location is required.");
            }
            if (cod_actividad == "" || cod_actividad == null || cod_actividad == undefined || cod_actividad == "-b") {
                errors.push("The misc activity is required.");
            }
        }
        if (employees.length == 0) {
            errors.push("Please select at least one employee.");
        }
        return errors;
    }

    function esMayorElTiempo(expectedGreaterTime, timeToCompare) {
        // Split the times into hours and minutes
        const [hours1, minutes1] = expectedGreaterTime.split(':').map(Number);
        const [hours2, minutes2] = timeToCompare.split(':').map(Number);

        // Convert times to total minutes
        const totalMinutes1 = hours1 * 60 + minutes1;
        const totalMinutes2 = hours2 * 60 + minutes2;

        // Compare the times
        return totalMinutes1 > totalMinutes2;
    }

    function restarTiempoAHoraEspecifica(time1, time2) {
        const [hours1, minutes1] = time1.split(":");
        const [hours2, minutes2] = time2.split(":");

        let totalMinutes1 = parseInt(hours1) * 60 + parseInt(minutes1);
        let totalMinutes2 = parseInt(hours2) * 60 + parseInt(minutes2);

        let difference = totalMinutes1 - totalMinutes2;

        let hours = Math.floor(difference / 60);
        let minutes = difference % 60;

        return `${hours.toString().padStart(2, "0")}:${minutes.toString().padStart(2, "0")}`;
    }

    function sumarTiempoAHoraEspecifica(time1, time2) {
        const [hours1, minutes1] = time1.split(":");
        const [hours2, minutes2] = time2.split(":");

        let totalMinutes1 = parseInt(hours1) * 60 + parseInt(minutes1);
        let totalMinutes2 = parseInt(hours2) * 60 + parseInt(minutes2);

        let sum = totalMinutes1 + totalMinutes2;

        let hours = Math.floor(sum / 60);
        let minutes = sum % 60;

        return `${hours.toString().padStart(2, "0")}:${minutes.toString().padStart(2, "0")}`;
    }

    function isGreaterThanFiveHours(time) {
        const [hours, minutes] = time.split(":").map(Number);
        const totalMinutes = hours * 60 + minutes;
        return totalMinutes > 300; // 300 minutes = 5 hours
    }

    function convertTo24HourFormat(time) {
        time = time ?? '00:00'
        const [hour, minute, period] = time.split(/:| /);
        // console.log({ hour, minute, period });
        let hour24 = parseInt(hour);
        if (period.toLowerCase() === 'PM' && hour24 !== 12) {
            hour24 += 12;
        } else if (period.toLowerCase() === 'AM' && hour24 === 12) {
            hour24 = 0;
        }
        return hour24.toString().padStart(2, '0') + ':' + minute;
    }

    function calcularCantidadHoras(HORA_ENTRADA, HORA_SALIDA) {
        try {
            if (HORA_ENTRADA === HORA_SALIDA) {
                return '00:00';
            }
            // Dividir la cadena en horas y minutos
            const [hours, minutes] = HORA_ENTRADA.split(':').map(Number);
            const [hoursOut, minutesOut] = HORA_SALIDA.split(':').map(Number);

            // Crear objetos Date para los tiempos específicos
            const time1 = new Date();
            const time2 = new Date();

            // time1.setHours(22, 10, 0); // 10:10 p. m.
            time1.setHours(hours, minutes, 0); // 10:10 p. m.
            time2.setHours(hoursOut, minutesOut, 0);  // 5:10 a. m.

            // Ajustar la fecha si el segundo tiempo es al día siguiente
            if (time2 <= time1) {
                time2.setDate(time2.getDate() + 1);
            }

            // Calcular la diferencia en milisegundos
            const diffMilliseconds = time2 - time1;

            // Convertir la diferencia a horas
            const diffHours = diffMilliseconds / (1000 * 60 * 60);
            var cantidadDeHoras = hoursOut - hours - (minutes - minutesOut);

            return `${Math.floor(diffHours)}:${Math.round((diffHours % 1) * 60).toString().padStart(2, '0')}`;
        } catch (error) {
            // console.log({ HORA_ENTRADA, HORA_SALIDA });
            console.error({ error })
        }

    }

    function addTime(time) {
        const [hours, minutes] = time.split(':').map(Number);
        totalDeHoras += hours * 60 + minutes;

        return formatTime(totalDeHoras);
    }

    function formatTime(minutes) {
        const totalHours = Math.floor(Math.abs(minutes) / 60);
        const remainingMinutes = Math.abs(minutes) % 60;
        const sign = minutes < 0 ? '-' : '';

        return `${sign}${totalHours}:${remainingMinutes.toString().padStart(2, '0')}`;
    }

});

/**
 * Funcion que construye un select con los pack_types dados, cod_harvest y pack_type_selected
 * Asigna el listener onchange para actualizar el harvest en caso que se cambie
 * @param {array} pack_types
 * @param {mixed} cod_harvest
 * @param {mixed} pack_type_selected
 * @returns
 */
function construirPackTypeSelectHarvest(pack_types, packs_farms_category_assignment, cod_harvest, cod_farm, cod_category, current_pack_type_selected = null) {

    let corresponding_pcas = packs_farms_category_assignment.filter((pca) => {
        if (pca.cod_farm == cod_farm
            && (pca.cod_location == "1" || pca.cod_location == "4" || pca.cod_location == "7" || pca.cod_location == "10" || pca.cod_location == "13")
            && pca.cod_categoria == cod_category) {
            return pca
        }
    });

    let corresponding_pack_types = pack_types.filter((p) => {
        let packTypeFound = false;
        corresponding_pcas.forEach((pca) => {
            if (pca.cod_tipo_pack == p.cod_tipo_pack) {
                packTypeFound = true;
            }
        });
        return packTypeFound;
    });

    // Build the pack types select
    let pack_type_select = `<select class="packTypeSelects" onchange="window.cambiarPackTypeActividad(event, ${cod_harvest}, ${current_pack_type_selected})">`;
    pack_type_select += `<option disabled>Select one</option>`
    corresponding_pack_types.forEach((p) => {
        let option = `<option value="${p.cod_tipo_pack}" ${current_pack_type_selected != null && current_pack_type_selected == p.cod_tipo_pack ? "selected" : ""}>${p.tipo_pack}</option>`;
        pack_type_select += option;
    });
    pack_type_select += `</select>`;
    return pack_type_select;

}

/**
 * Cambia a través del api el pack_type seleccionado
 * @param {InputEvent} event
 * @param {*} cod_harvest
 */
window.cambiarPackTypeActividad = function (event, cod_harvest, current_pack_type_selected = null) {
    $.ajax({
        type: "POST",
        url: window.location.href + 'api/v1/empleado_administracion/actualizarPackTypeHarvest',
        data: {
            new_pack_type: event.target.value,
            cod_harvest: cod_harvest,
            current_pack_type_selected: current_pack_type_selected
        },
        success: function (res) {
            console.log(res);
        },
        error: function (err) {
            console.log(err);
        }
    });
}
