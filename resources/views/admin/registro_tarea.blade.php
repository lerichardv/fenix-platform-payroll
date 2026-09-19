@extends('layouts.app')

{{-- Zonal Izquierda --}}
@section('sidebar')
    @parent
    <div class="w-100" id="contenedor_formulario">
        <div class="row">
            <div class="col-md-12 pt-3">
                <label for="initial_date">Initial Date</label>
                <div class='input-group input-group-sm date' id='div_fecha_inicial'>
                    <span class="input-group-addon">
                        <span class="fa fa-calendar">
                        </span>
                    </span>
                    <input type='text' class="form-control fechas_validacion" id="initial_date" />
                </div>

            </div>
            <div class="col-md-12 pt-3">
                <label for="final_date">Final Date</label>
                <div class='input-group input-group-sm date' id='div_fecha_final'>
                    <span class="input-group-addon">
                        <span class="fa fa-calendar">
                        </span>
                    </span>
                    <input type='text' class="form-control fechas_validacion" id="final_date" />
                </div>
            </div>
            <div class="col-md-12 pt-3">
                <label for="cod_farm">Farm</label>
                <select class="form-select" name="cod_farm" id="cod_farm_nav_izquierdo">
                    <option value="0" selected>All</option>
                    <?php $i = 0; ?>
                    @foreach ($listaGranjas as $granja)
                        <option value="{{ $granja->cod_farms }}">{{ $granja->farm }}</option>
                        <?php $i++; ?>
                    @endforeach
                </select>
            </div>
            <div class="col-md-12 pt-3">
                <label for="cod_location">Location</label>
                <select class="form-select" name="cod_location" id="cod_location">
                    <option value="0" selected>All</option>
                </select>
            </div>

        </div>
    </div>
    <div class="w-100" id="contenedor_empleados">
        <div class="row">
            <div class="col-md-12 pt-3">
                <label id="lbl_empleado">
                    Employees
                </label>

            </div>
            <div class="col-md-12 pt-3">
                <input type="text" id="search" class="form-control" style="background-color: #28005834; color:white"
                    placeholder="Search employees" autocomplete="off">
            </div>
            <div class="col-md-12 pt-3">
            </div>
        </div>
        <div id="contendor_lista_empleados">
            <div id="employee_list">
                {{-- @foreach ($listaEmpleados as $empleado)
                <div onclick="clicEnTarjetaEmpleado({{ $empleado->cod_usuario }})" class="row selector_empleado ">
                    <div class="col-md-10 pt-3">
                        <label class="elemento_seleccionable">
                            <span style="font-weight: bold;"> {{ $empleado->nombre }}</span> <br>
                            {{ $empleado->pin }} -
                            {{ $empleado->qcpin }}
                        </label>
                    </div>
                    <div id="estado_alerta_usuario_{{ $empleado->cod_usuario }}" {{ $empleado->al_menos_un_error ==
                        1 ? '' : 'hidden' }} class="col-md-2 pt-3">
                        <label>
                            <i style="color: red" class="fa-solid fa-circle-exclamation"></i>
                        </label>
                    </div>
                </div>
                @endforeach --}}
            </div>

        </div>
    </div>
@endsection

@section('content')
    <style>
        .col-width {
            width: 10%;
        }

        .table th,
        .table td {
            text-align: center;
        }

        .datos_iniciales_registro_dia {
            text-align: left !important;
        }


        .registro_con_error {
            background-color: #ff6262 !important;
            color: white !important;
        }

        .registro_sin_error {
            background-color: #ebebeb !important;
            font-weight: 600 !important;
        }

        .packTypeSelects {
            display: inline-block;
        }

        .btnSeleccionables,
        .packTypeSelects {
            background-color: #280058;
            color: white;
            border: none;
            min-height: 26px;
            border-radius: 3px;
            border: 2px solid black;
            transition: background-color 0.3s ease;
            cursor: pointer;
            width: 100% !important;
            text-align: left;
        }

        .btnSeleccionables_small {
            background-color: #280058;
            color: white;
            border: none;
            min-height: 20px;
            border-radius: 3px;
            border: 2px solid black;
            transition: background-color 0.3s ease;
            cursor: pointer;
            /* width: 100% !important; */
            text-align: left;
        }

        #lbl_empleado {
            color: black;
            width: 100%;
            border-bottom: 1px solid black;
            padding-bottom: 6px;

        }

        .tr_no_data {
            background-color: firebrick;
            color: white;
        }

        #contenedor_principal_tabla_empleados {
            background-color: white;
            max-height: 1000px;
            overflow-y: hidden;
            margin: 0;
            padding: 0;
        }

        #contenedor_principa_tabla {
            overflow-y: scroll;
            max-height: 700px;
            min-height: 450px;
        }


        #tabla_registro {
            font-size: 12px;
        }

        .input_texto {
            width: 100%;
        }

        @media (min-height: 900px) {
            /* #contendor_lista_empleados {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    overflow-y: scroll;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    max-height: 5400px;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    min-height: 5400px;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                } */

            #contenedor_principa_tabla {
                overflow-y: scroll;
                max-height: 900px;
            }

            #contenedor_principal_tabla_empleados {
                max-height: 900px;
                overflow-y: hidden;
            }
        }

        @media (min-height: 1200px) {
            #contendor_lista_empleados {
                overflow-y: scroll;
                max-height: 800px;
                min-height: 450px;
            }

            #contenedor_principal_tabla_empleados {
                max-height: 1450px;
                overflow-y: hidden;
            }

            #contenedor_principa_tabla {
                overflow-y: scroll;
                max-height: 1150px;
            }

        }

        .txt_lugares_inicio_salida_registro_sesion {
            font-weight: bold;
        }
    </style>
    <script>
        var cantidadEscaneos = 0;

        function abrirModal(codInicioSesion, cantidadRegistros, fecha) {
            $('#fecha_del_grupo_modal').val(fecha);
            $('#formularioAgregarTarea').modal('show');
        }

        function abrirModalAgregarCantidad(cod_crew, idUnicoFila) {

            $('#listado_datos_escaneados').empty();
            crearloginPantallaCompleta();
            $.ajax({
                url: window.location.href + 'api/v1/empleado_administracion/listaDatosEscaneadosPorCodCrew',
                type: 'POST',
                data: {
                    cod_crew: cod_crew

                },
                success: function(response) {

                    const data = response.data.datosEscaneados;
                    cantidadEscaneos = data.length;
                    if (data.length > 0) {

                        $.each(data, function(i, item) {
                            $('#listado_datos_escaneados').append(`
                                <div id="escaneo_${i}" style="width: 100%; text-align: right;" class="row display-flex">
                                    <div style="float: right; padding: 0px">
                                        <button id="btn_recuperar_${i}" type="button" class="d-none btn btn-info btn-sm" style="border-radius: 0;" onclick="recuperarEscaneado(${i}, 'contenedor_datos_escaneos_')">
                                            <i class="fa-solid fa-rotate-left"></i>
                                        </button>
                                        <button id="btn_eliminar_${i}" type="button" class="btn btn-danger btn-sm" style="border-radius: 0;" onclick="desactivarEscaneo(${i}, 'contenedor_datos_escaneos_')">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                    <div id="contenedor_datos_escaneos_${i}" class="contenedor_datos_escaneos col-12">
                                        <input type="hidden" id="datos_nuevo_escaneo_${i}" value="0">
                                        <input type="hidden" id="vigencia_escaneo_${i}" value="1">
                                        <input type="hidden" id="datos_existento_${i}" value="${item.cod_lista}">
                                        <input type="time" id="hora_escaneo_${i}" class="form-control" style="max-width: 20%;" value="${item.hora_escaneo}" onchange="adjustTimes()">
                                        <input type="text" id="comentario_${i}" value="${item.comentario}" class="form-control" style="width: 60%;">
                                        <div id="pieza_${i}" class="escaneo_previo d-flex justify-content-center align-items-center" style="background-color: grey; width: 10%; color: white; height: 100%;">
                                            ${item.pieces}
                                        </div>
                                    </div>
                                </div>

                            `);
                        });


                    } else {
                        // $('#' + id).append('<option value="-b" >Error loading payment types</option>');
                    }

                    adjustTimes();
                    $('#cantidad_escaneos').text(cantidadEscaneos);
                    $('#cod_crew_actual').val(cod_crew);
                    $('#id_unico_fila').val(idUnicoFila);
                    $('#formCantidadesEscaneadas').modal('show');

                },
                fail: function(xhr) {
                    console.error('Error:', xhr.responseText);
                    const overlay = document.getElementById('global-loading-overlay');
                    if (overlay) {
                        overlay.remove();
                    }
                },
                complete: () => {
                    const overlay = document.getElementById('global-loading-overlay');
                    console.log({
                        overlay
                    })
                    if (overlay) {
                        overlay.remove();
                    }
                }
            });

        }

        function desactivarEscaneo(indiceEscaneo, idContenedorDatosEscaneos) {
            const btnEliminar = $('#btn_eliminar_' + indiceEscaneo);
            const btnRecuperar = $('#btn_recuperar_' + indiceEscaneo);
            $('#vigencia_escaneo_' + indiceEscaneo).val(0);
            const contenedorDatosEscaneo = $('#' + idContenedorDatosEscaneos + indiceEscaneo);

            btnRecuperar.removeClass("d-none");
            btnRecuperar.addClass("elementoVisible");

            btnEliminar.removeClass("elementoVisible");
            btnEliminar.addClass("elementoOculto");


            if (contenedorDatosEscaneo.hasClass("contenedor_datos_escaneos")) {
                contenedorDatosEscaneo.removeClass("contenedor_datos_escaneos");
                contenedorDatosEscaneo.addClass("contenedor_datos_escaneos_eliminado");
            }

            if (contenedorDatosEscaneo.hasClass("contenedor_datos_escaneos_nuevo")) {
                contenedorDatosEscaneo.removeClass("contenedor_datos_escaneos_nuevo");
                contenedorDatosEscaneo.addClass("contenedor_datos_escaneos_nuevo_eliminado");
            }

        }

        function recuperarEscaneado(indiceEscaneo, idContenedorDatosEscaneos) {
            const btnEliminar = $('#btn_eliminar_' + indiceEscaneo);
            const btnRecuperar = $('#btn_recuperar_' + indiceEscaneo);
            $('#vigencia_escaneo_' + indiceEscaneo).val(1);

            const contenedorDatosEscaneo = $('#' + idContenedorDatosEscaneos + indiceEscaneo);
            btnRecuperar.addClass("elementoOculto");
            btnRecuperar.removeClass("elementoVisible");

            btnEliminar.removeClass("elementoOculto");
            btnEliminar.addClass("elementoVisible");

            contenedorDatosEscaneo.removeClass("contenedor_datos_escaneos_eliminado");
            contenedorDatosEscaneo.addClass("contenedor_datos_escaneos");

            if (contenedorDatosEscaneo.hasClass("contenedor_datos_escaneos_eliminado")) {
                contenedorDatosEscaneo.removeClass("contenedor_datos_escaneos_eliminado");
                contenedorDatosEscaneo.addClass("contenedor_datos_escaneos");
            }

            if (contenedorDatosEscaneo.hasClass("contenedor_datos_escaneos_nuevo_eliminado")) {
                contenedorDatosEscaneo.removeClass("contenedor_datos_escaneos_nuevo_eliminado");
                contenedorDatosEscaneo.addClass("contenedor_datos_escaneos_nuevo");
            }

        }

        function guardarDatosEscaneo() {
            crearloginPantallaCompleta();
            $.ajax({
                url: window.location.href + 'api/v1/empleado_administracion/guardarRegistroDatosEscaneo',
                type: 'POST',
                data: {
                    cod_crew: $('#cod_crew_actual').val(),
                    datosEscaneados: recopilarDatosEscaneados(),
                },
                success: function(response) {


                    const data = response;
                    console.log({
                        data
                    });
                    if (data.success === false) {
                        Swal.fire("An error occurred while processing",
                            "Please contact the administrators.", "warning");
                        return;
                    }
                    $('#listado_datos_escaneados').empty();
                    cantidadEscaneos = 0;
                    $('#cantidad_escaneos').text(cantidadEscaneos);
                    $('#formCantidadesEscaneadas').modal('hide');

                    $('#input_cantidad_escaneo_' + $('#id_unico_fila').val()).val(data.data
                        .cantidad_total_piezas);
                    $('#btn_cantidad_escaneo_' + $('#id_unico_fila').val()).text(data.data
                        .cantidad_total_piezas);
                    Swal.fire("Data saved successfully", "", "success");

                },
                fail: function(xhr) {
                    console.error('Error:', xhr.responseText);
                    const overlay = document.getElementById('global-loading-overlay');
                    Swal.fire("An error occurred while processing",
                        "Please contact the administrators.", "warning");
                    if (overlay) {
                        overlay.remove();
                    }
                },
                complete: () => {
                    const overlay = document.getElementById('global-loading-overlay');
                    console.log({
                        overlay
                    })
                    if (overlay) {
                        overlay.remove();
                    }
                }
            });

        }

        function abrirModalAgregarComentario(codInicioSesion) {

            var comentarioData = $('#icon_comentario_' + codInicioSesion).data('comentario');
            var nombreAdmin = $('#icon_comentario_' + codInicioSesion).data('nombre_admin_comentario');
            var fecha = $('#icon_comentario_' + codInicioSesion).data('fecha_comentario');
            if (nombreAdmin == 'null' || nombreAdmin == null || nombreAdmin === undefined) {
                $('#div_datos_comentario').attr('hidden', true);
            } else {
                $('#div_datos_comentario').removeAttr('hidden');
                $('#comentario_autor_nombre').text(nombreAdmin);
                $('#comentario_fecha_creacion').text(fecha);
            }


            $('#comentario_registro_ingreso').val(comentarioData);
            $('#id_registro_ingreso').val(codInicioSesion);
            $('#formularioAgregarComentario').modal('show');
        }

        /**
         * Elimina la tarea actual, se debe pasar el id de la tarea
         */
        function eliminarTarea(cod_inicio_sesion) {
            Swal.fire({
                title: "Are you sure you want to delete the task?",
                showDenyButton: true,
                confirmButtonText: "Delete",
                denyButtonText: `Don't delete`
            }).then((result) => {
                /* Read more about isConfirmed, isDenied below */
                if (result.isConfirmed) {
                    $.ajax({
                        type: "POST",
                        url: "api/v1/empleado_administracion/eliminarTareaBitacoraInicioSesion",
                        data: {
                            cod_inicio_sesion: cod_inicio_sesion
                        },
                        error: (err) => {
                            console.log(err);
                            Swal.fire("An error occurred while processing",
                                "Please contact the administrators.", "warning");
                        },
                        success: (res) => {
                            Swal.fire("Delete the task", "The action was completed sucesfully",
                                "success");
                            $('#cod_usuario').trigger('change');
                        }
                    });
                }
            });
        }

        /**
         * Abre el modal para editar la granja y la location del cod_inicio_sesion dado
         * para poder modificarlo.
         *
         */
        function abrirModalEditarGranjaLocation(cod_inicio_sesion, cod_farm, cod_location) {
            $('#modalEditarFarmLocation').modal('show');
            $('#modal_editar_farm_location_cod_inicio_sesion').val(cod_inicio_sesion);
            $('#select_editar_farm_location_cod_farm').val(cod_farm);
            $('#btn_save_modal_editar_farm_location').prop('disabled', true);
            cargarLocationEnSelectPorId(
                'select_editar_farm_location_cod_location',
                $('#select_editar_farm_location_cod_farm'),
                () => {
                    $('#select_editar_farm_location_cod_location').val(cod_location);
                    $('#btn_save_modal_editar_farm_location').prop('disabled', false);
                }
            );
            $('#select_editar_farm_location_cod_farm').on('change', () => {
                cargarLocationEnSelectPorId(
                    'select_editar_farm_location_cod_location',
                    $('#select_editar_farm_location_cod_farm'),
                    () => {
                        $('#btn_save_modal_editar_farm_location').prop('disabled', false);
                    }
                );
            })
        }

        function cargarLocationEnSelectPorId(id = "cod_location", granjaElement, callback = () => {}) {
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
                success: function(response) {

                    jQuery.ajaxSetup({
                        async: false
                    });

                    const data = response;
                    $('#' + id).empty();

                    if (data.length > 0) {
                        $('#' + id).append('<option value="-b" >--Select--</option>');

                        $.each(data, function(i, item) {
                            $('#' + id).append(
                                `<option value="${item.cod_location}">${item.location}</option>`);
                        });
                    } else {
                        $('#' + id).append('<option value="-b" >No locations found</option>');

                    }

                    jQuery.ajaxSetup({
                        async: true
                    });
                    if (callback) callback();
                },
                error: function(xhr) {
                    console.error('Error:', xhr.responseText);
                    Swal.fire("An error occurred while processing",
                        "Please contact the administrators.", "warning");

                }
            });

        }

        $(document).ready(function() {
            $('#formularioAgregarTarea').on('show.bs.modal', function() {
                // Code to execute when the modal is opened
                $('#nombreGranja').val($('#cod_farm').find('option:selected').text());

                // cargarListaHarvestPorGranjas();
                // cargarListaMiscelaneosPorGranjas();
            });

            $('#formularioAgregarTarea').on('hidden.bs.modal', function() {
                // Code to execute when the modal is closed
            });

        });
    </script>

    <div class="modal fade" tabindex="-1" id="modalEditarFarmLocation">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Location</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="modal_editar_farm_location_cod_inicio_sesion" value="">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="select_editar_farm_location_cod_farm">Farm</label>
                            <select class="form-select" name="select_editar_farm_location_cod_farm"
                                id="select_editar_farm_location_cod_farm">
                                <option value="-b" selected disabled>--SELECT--</option>
                                @foreach ($listaGranjas as $granja)
                                    <option value="{{ $granja->cod_farms }}">{{ $granja->farm }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="select_editar_farm_location_cod_location">Location</label>
                            <select class="form-select" name="select_editar_farm_location_cod_location"
                                id="select_editar_farm_location_cod_location">
                                <option value="-b" selected disabled>--SELECT--</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn color_principal" id="btn_save_modal_editar_farm_location">Save</button>
                </div>
            </div>
        </div>
    </div>


    <div id="contenedor_principal" style="margin-left:300px;width:calc(100vw-300px);height:100vh">
        <div id="contenedor_titulo_pagina" class="mx-auto ps-3"
            style="border-bottom: 1px solid black; max-width: 100vw; background-color: white;">
            <div class="row container-fluid px-2">
                <div class="col-12">
                    <p id="titulo_seccion" class="text-left">Time Card Detail</p>
                </div>
            </div>
        </div>
        <div id="contenedor_principal_tabla_empleados">
            {{-- Zonda Derecha --}}
            <div id="contenedor_principa_tabla">
                <div class="d-flex justify-content-stretch m-0 p-0" style="border-radius: 10px">
                    <div class="color_principal py-3 m-0 p-0 w-100" style="font-size: 20px;  position: sticky; top: 0;">
                        <p class="m-0" id="nombre_empleado_seleccionado" style="text-align: center;">
                            --
                        </p>
                    </div>

                    <div class="color_principal d-flex align-items-center gap-2 px-3">
                        <button class="btn btn-primary text-nowrap" data-bs-toggle="modal"
                            data-bs-target="#formularioAddActivity">
                            <i class="fa-solid fa-tractor"></i>
                        </button>
                        <button class="btn btn-success text-nowrap" data-bs-toggle="modal"
                            data-bs-target="#formularioBulkAdd">
                            <i class="fa-solid fa-list"></i>
                        </button>
                        <p id="btn_agregrar_nuevo_registro_entradasalida" hidden class="m-0">
                            <button class="btn" title="ADD" data-bs-toggle="modal"
                                data-bs-target="#formularioAgregarRegistroHorario" style="background-color: #ffd700"
                                data-bs-toggle="modal">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </p>
                    </div>
                </div>
                <div class="">
                    <div class="p-0 m-0">
                        {{-- Wrapper tabla registro --}}
                        <div id="tabla_registro" class="accordion accordion-flush">

                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Campos ocultos para manejo de las selecciones --}}
        <div hidden class="row container-fluid mt-3 px-5 align-items-center">
            <input type="hidden" name="total_horas_cabecera" id="total_horas_cabecera">

            <div class="col-4 pt-3">
                <label for="cod_usuario">Employee</label>
                <select class="form-select" name="cod_usuario" id="cod_usuario">
                    <option value="-b">--Select--</option>
                    @foreach ($listaEmpleados as $empleado)
                        <option data-nombre="{{ $empleado->nombre }}" data-pin="{{ $empleado->pin }}"
                            data-reducirtiempo="0" data-lunchautomatico="0" data-qcpin="{{ $empleado->qcpin }}"
                            data-esveterano="{{ $empleado->es_veterano }}" value="{{ $empleado->cod_usuario }}">
                            {{ $empleado->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div>
    </div>


    <!-- Modal -->
    <div class="modal fade" id="formularioAgregarTarea" tabindex="-1" role="dialog"
        aria-labelledby="modal_seleccionar" aria-hidden="true" data-backdrop="static" data-keyboard="false"
        data-bs-config={backdrop:true} aria-labelledby="labelModalFormularioAgregarTarea" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="labelModalFormularioAgregarTarea">Add a new task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <input type="hidden" name="fecha_del_grupo_modal" id="fecha_del_grupo_modal"
                            value="2024-08-25">

                        <div class="row">
                            <div class="col-md-12 pt-3 mb-3">
                                <label for="cod_farm">Farm</label>
                                <select class="form-select" name="cod_farm" id="cod_farm">
                                    <option value="-b">--Select--</option>
                                    @foreach ($listaGranjas as $granja)
                                        <option value="{{ $granja->cod_farms }}">{{ $granja->farm }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3 col-md-12">
                                <label for="cods_harvests" class="form-label">Harvest List</label>
                                <select class="form-select" id="cods_harvests">
                                    <option value="-b" selected>--Select--</option>
                                </select>
                            </div>
                            <div class="mb-3 col-md-12">
                                <label for="cods_miscelaneos" class="form-label">Miscellaneous List</label>
                                <select class="form-select" id="cods_miscelaneos">
                                    <option value="-b" selected>--Select--</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">

                            <div class="mb-3 col-md-12">
                                <label for="cantidad_escaneo_modal" class="form-label">Scanning Quantity</label>
                                <input type="number" class="form-control" id="cantidad_escaneo_modal" min="0"
                                    value="0">
                            </div>
                        </div>



                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn color_principal" data-bs-dismiss="modal"
                        id="guardar_tarea_seleccionada">Save
                        changes</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="formularioAgregarRegistroHorario" tabindex="-1" role="dialog"
        aria-labelledby="modal_seleccionar" aria-hidden="true" data-backdrop="static" data-keyboard="false"
        data-bs-config={backdrop:true} aria-labelledby="labelModalFormularioAgregarRegistroHorario" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="labelModalFormularioAgregarRegistroHorario">Add a new clock-in and
                        clock-out record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="cod_farm_registro_horario" class="form-label">Farm</label>
                                <select class="form-select" name="cod_farm_registro_horario"
                                    id="cod_farm_registro_horario">
                                    <option value="-b">--Select--</option>
                                    @foreach ($listaGranjas as $granja)
                                        <option value="{{ $granja->cod_farms }}">{{ $granja->farm }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="cod_locacion" class="form-label">Locations</label>
                                <select class="form-select select_disabled" disabled id="cod_locacion">
                                    <option value="-b" selected>--Select--</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3" id="contenedor_date">
                                <label for="fecha_registro_modal" class="form-label">Date</label>
                                <div class='input-group input-group-sm date' id='div_fecha_registro_modal'>
                                    <span class="input-group-addon">
                                        <span class="fa fa-calendar">
                                        </span>
                                    </span>
                                    <input type='text' class="form-control fechas_validacion"
                                        id="fecha_registro_modal" />
                                </div>
                            </div>

                            <div class="mb-3 col-md-4" id="div_clock_in_time">
                                <label for="hora_entrada" class="form-label">Clock in</label>
                                <input type="time" value="00:00" class="form-control" id="hora_entrada">
                            </div>

                            <div class="mb-3 col-md-4" id="div_clock_out_time">
                                <label for="hora_salida" class="form-label">Clock out</label>
                                <input type="time" value="00:00" class="form-control" id="hora_salida">
                            </div>
                        </div>
                        <div class="mb-3 col-md-4 form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="switch_bulk_add">
                            <label class="form-check-label" for="switch_bulk_add">Bulk add mode</label>
                        </div>

                        <div class="mb-3 col-md-12" id="bulk_add_mode_fields">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="bulk_edit_cod_actividad_por_dia" class="form-label">Activity
                                        (day)</label>
                                    <select class="form-select" name="" id="bulk_edit_cod_actividad_por_dia">
                                        @foreach ($actividades as $key => $actividad)
                                            <option value="{{ $actividad->cod_actividad_por_dia }}"
                                                {{ $key == 0 ? 'selected' : '' }}>
                                                {{ $actividad->actividad_por_dia }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label for="bulk_edit_from_date" class="form-label">From date</label>
                                    <div class='input-group input-group-sm date mb-3' id='div_bulk_edit_from_date'>
                                        <span class="input-group-addon">
                                            <span class="fa fa-calendar">
                                            </span>
                                        </span>
                                        <input type='text' class="form-control fechas_validacion datetimepicker"
                                            id="bulk_edit_from_date" />
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                            id="checkbox_include_weekends_bulk_add_mode">
                                        <label class="form-check-label"
                                            for="checkbox_include_weekends_bulk_add_mode">Include weekends?</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label for="bulk_edit_to_date" class="form-label">To date</label>
                                    <div class='input-group input-group-sm date' id='div_bulk_edit_to_date'>
                                        <span class="input-group-addon">
                                            <span class="fa fa-calendar">
                                            </span>
                                        </span>
                                        <input type='text' class="form-control fechas_validacion datetimepicker"
                                            id="bulk_edit_to_date" />
                                    </div>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn color_principal" data-bs-dismiss="modal"
                        id="guardar_nuevo_registro_ingreso">Save
                        changes</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="formularioBulkAdd" tabindex="-1" role="dialog"
        aria-labelledby="labelModalformularioBulkAdd" aria-hidden="true" data-backdrop="static" data-keyboard="false"
        data-bs-config={backdrop:true} aria-labelledby="labelModalformularioBulkAdd" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="labelModalformularioBulkAdd">Bulk add activities by day to employees
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="cod_actividad_por_dia_ulk_add_registro" class="form-label">Activity
                                    (day)</label>
                                <select class="form-select" name="" id="cod_actividad_por_dia_bulk_add_registro">
                                    @foreach ($actividades as $key => $actividad)
                                        <option value="{{ $actividad->cod_actividad_por_dia }}"
                                            {{ $key == 0 ? 'selected' : '' }}>{{ $actividad->actividad_por_dia }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="cod_farm_registro_horario_bulk_add" class="form-label">Farm</label>
                                <select class="form-select" name="cod_farm_registro_horario"
                                    id="cod_farm_registro_horario_bulk_add">
                                    <option value="-b">--Select--</option>
                                    @foreach ($listaGranjas as $granja)
                                        <option value="{{ $granja->cod_farms }}">{{ $granja->farm }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="cod_locacion_bulk_add_registro" class="form-label">Locations</label>
                                <select class="form-select select_disabled" disabled id="cod_locacion_bulk_add_registro">
                                    <option value="-b" selected>--Select--</option>
                                </select>
                            </div>

                            <div class="mb-3 col-md-3" id="div_clock_in_time">
                                <label for="hora_entrada" class="form-label">Clock in</label>
                                <input type="time" value="00:00" class="form-control"
                                    id="hora_entrada_bulk_add_registro">
                            </div>

                            <div class="mb-3 col-md-3" id="div_clock_out_time">
                                <label for="hora_salida" class="form-label">Clock out</label>
                                <input type="time" value="00:00" class="form-control"
                                    id="hora_salida_bulk_add_registro">
                            </div>

                            <div class="col-md-3">
                                <label for="bulk_edit_actividades_empleados_granjas_from_date" class="form-label">From
                                    date</label>
                                <div class='input-group input-group-sm date mb-3'
                                    id='div_bulk_edit_actividades_empleados_granjas_from_date'>
                                    <span class="input-group-addon">
                                        <span class="fa fa-calendar">
                                        </span>
                                    </span>
                                    <input type='text' class="form-control fechas_validacion datetimepicker"
                                        id="bulk_edit_actividades_empleados_granjas_from_date" />
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="checkbox_include_weekends_bulk_edit">
                                    <label class="form-check-label" for="checkbox_include_weekends_bulk_edit">Include
                                        weekends?</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="bulk_edit_actividades_empleados_granjas_to_date" class="form-label">To
                                    date</label>
                                <div class='input-group input-group-sm date'
                                    id='div_bulk_edit_actividades_empleados_granjas_to_date'>
                                    <span class="input-group-addon">
                                        <span class="fa fa-calendar">
                                        </span>
                                    </span>
                                    <input type='text' class="form-control fechas_validacion datetimepicker"
                                        id="bulk_edit_actividades_empleados_granjas_to_date" />
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label for="bulk_edit_to_date" class="form-label">Employees</label>
                                {{-- Este es el wrapper usado posr ListPicker.js para generar la lista de empleados
                                seleccionables --}}
                                <div id="bulk-add-list-picker"
                                    data-items='{{ collect($listaEmpleados)->map(function ($item) {
                                        return (object) [
                                            'id' => $item->cod_usuario,
                                            'name' => $item->nombre,
                                        ];
                                    }) }}'>
                                </div>
                                {{-- <div class="employee-list-grid">
                                    <div class="employee-list-wrapper" id="unselected">
                                        @foreach ($listaEmpleados as $empleado)
                                        <div class="employee-list-element">
                                            <div class="">
                                                {{ $empleado->nombre }}
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    <div class="employee-list-wrapper" id="selected">

                                    </div>
                                </div> --}}
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer" id="bulk_add_modal_footer">
                    <button type="button" class="btn btn-secondary" id="btn_bulk_add_cancelar"
                        data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn color_principal" id="btn_bulk_add_guardar">Process bulk
                        add</button>
                    <div id="bulk_add_footer_loader_wrapper"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="formularioAddActivity" aria-hidden="true" data-backdrop="static" data-keyboard="false"
        data-bs-config={backdrop:true} aria-labelledby="labelModalformularioAddActivity" aria-hidden="true">

        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="labelModalformularioAddActivity">Add Harvest or Miscelaneous activity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="row">
                            <div class="mb-3 col-md-4" id="">
                                <label for="input_add_activity_date" class="form-label">Date</label>
                                <div class='input-group input-group-sm date' id='div_activity_date'>
                                    <span class="input-group-addon"
                                        style="padding:.375rem .75rem;display:flex;align-items:center">
                                        <span class="fa fa-calendar">
                                        </span>
                                    </span>
                                    <input type='text' class="form-control fechas_validacion"
                                        id="input_add_activity_date" style="padding:.375rem .75rem;font-size:1rem" />
                                </div>
                            </div>
                            <div class="mb-3 col-md-4" id="">
                                <label for="" class="form-label">Time start</label>
                                <input type="time" value="00:00" class="form-control"
                                    id="input_time_start_add_activity">
                            </div>
                            <div class="mb-3 col-md-4" id="">
                                <label for="" class="form-label">Time end</label>
                                <input type="time" value="00:00" class="form-control"
                                    id="input_time_end_add_activity">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="" class="form-label">Activity type</label>
                                <select class="form-select" name="select_activity_type_add_activity"
                                    id="select_activity_type_add_activity">
                                    <option value="" disabled selected>Select an option</option>
                                    <option value="harvest">Harvest</option>
                                    <option value="misc">Miscellaneous</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="select_cod_farm_add_activity_form" class="form-label">Farm</label>
                                <select class="form-select" name="select_cod_farm_add_activity_form"
                                    id="select_cod_farm_add_activity_form">
                                    <option value="-b">--Select--</option>
                                    @foreach ($listaGranjas as $granja)
                                        <option value="{{ $granja->cod_farms }}">{{ $granja->farm }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3 misc-field">
                                <label for="select_location_add_activity_form" class="form-label">Location</label>
                                <select class="form-select" name="select_location_add_activity_form"
                                    id="select_location_add_activity_form">
                                    <option value="">Select an option</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3 misc-field">
                                <label for="select_misc_activity_add_activity_form" class="form-label">Misc
                                    Activity</label>
                                <select class="form-select" name="select_misc_activity_add_activity_form"
                                    id="select_misc_activity_add_activity_form">
                                    <option value="">Select an option</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3 harvest-field misc-field">
                                <label for="select_crop_ages_add_activity_form" class="form-label">Crop age</label>
                                <select class="form-select" name="select_crop_ages_add_activity_form"
                                    id="select_crop_ages_add_activity_form">
                                    <option value="">Select an option</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3 harvest-field misc-field">
                                <label for="select_fields_add_activity_form" class="form-label">Fields</label>
                                <select class="form-select" name="select_fields_add_activity_form"
                                    id="select_fields_add_activity_form">
                                    <option value="">Select an option</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3 harvest-field misc-field">
                                <label for="select_bloque_add_activity_form" class="form-label">Blocks</label>
                                <select class="form-select" name="select_bloque_add_activity_form"
                                    id="select_bloque_add_activity_form">
                                    <option value="">Select an option</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3 harvest-field">
                                <label for="select_pack_type_add_activity_form" class="form-label">Pack type</label>
                                <select class="form-select" name="select_pack_type_add_activity_form"
                                    id="select_pack_type_add_activity_form">
                                    <option value="">Select an option</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label for="" class="form-label">Employees</label>
                                {{-- Este es el wrapper usado posr ListPicker.js para generar la lista de empleados
                                seleccionables --}}
                                <div id="add_activity_list_picker"
                                    data-items='{{ collect($listaEmpleados)->map(function ($item) {
                                        return (object) [
                                            'id' => $item->cod_usuario,
                                            'name' => $item->nombre,
                                        ];
                                    }) }}'>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer" id="bulk_add_modal_footer">
                    <button type="button" class="btn btn-secondary" id="btn_add_activity_close"
                        data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn color_principal" id="btn_add_activity_save">Add activity</button>
                    <div id="bulk_add_footer_loader_wrapper"></div>
                </div>
            </div>
        </div>

    </div>

    <div class="modal fade" id="formularioAgregarComentario" tabindex="-1" role="dialog"
        aria-labelledby="modal_seleccionar" aria-hidden="true" data-backdrop="static" data-keyboard="false"
        data-bs-config={backdrop:true} aria-labelledby="labelModalFormularioAgregarComentario" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="labelModalFormularioAgregarComentario">Comment on the clock-in record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div hidden id="div_datos_comentario">
                        <div class="mb-3">
                            <label class="form-label">Comment by:</label>
                            <span id="comentario_autor_nombre">Edwin Olivera</span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Created at:</label>
                            <span id="comentario_fecha_creacion">Hoy</span>
                        </div>
                    </div>
                    <form>
                        <input type="hidden" name="id_registro_ingreso" id="id_registro_ingreso" value="0">
                        <div class="row">
                            <div class="mb-3 col-md-12">
                                <label for="comentario_registro_ingreso" class="form-label">Comment</label>
                                <input type="text" class="form-control" id="comentario_registro_ingreso"
                                    min="0" value="0">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn color_principal" disabled id="guardar_comentario_en_registro">Save
                        comment</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="formCantidadesEscaneadas" tabindex="-1" role="dialog"
        aria-labelledby="modal_seleccionar" aria-hidden="true" data-backdrop="static" data-keyboard="false"
        data-bs-config={backdrop:true} aria-labelledby="labelModalFormularioAgregarTarea" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: 90%; min-height: 75vh;">
            <div class="modal-content" style="min-height: 75vh;">
                <div class="modal-header">
                    <h5 class="modal-title" id="labelModalFormularioAgregarTarea">Manual scans</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <input type="hidden" name="cod_crew_actual" id="cod_crew_actual" value="0">
                        <input type="hidden" name="id_unico_fila" id="id_unico_fila" value="0">
                        <div style="overflow-y: auto; max-height: 62vh;">
                            <div class="row d-flex justify-content-center mx-0">
                                <div id="listado_datos_escaneados">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <h5 class="modal-title me-2">Scanned Quantities:</h5>
                        <div id="cantidad_escaneos" class="badge bg-secondary" data-bs-toggle="tooltip"
                            data-bs-placement="top" title="Number of scans">0</div>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                            data-bs-toggle="tooltip" data-bs-placement="top" title="Close the modal">Close</button>
                        <button type="button" class="btn btn-success" data-bs-toggle="tooltip" data-bs-placement="top"
                            title="Add a new scanned item" onclick="agregarCantidadEscaneada()"
                            style="max-width: 300px;">Add item</button>
                        <button type="button" class="btn color_principal" data-bs-dismiss="modal"
                            data-bs-toggle="tooltip" data-bs-placement="top" title="Save the scanned quantities"
                            id="guardar_datos_escaneo">Save changes</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $("#cod_farm_nav_izquierdo").on("change", function() {
                cargarSelectLocacionesPorGranja("cod_location", $("#cod_farm_nav_izquierdo").val(),
                    function() {
                        $('#search').trigger('keyup');
                    });
            });
            $("#cod_location").on("change", function() {
                $('#search').trigger('keyup');
            });
            $('#btn_save_modal_editar_farm_location').on('click', () => {
                if (!$('#select_editar_farm_location_cod_location').val()) {
                    Swal.fire("Missing information", "Please select the location.", "warning");
                    return false;
                }
                $.ajax({
                    type: "POST",
                    url: "api/v1/empleado_administracion/actualizarFarmLocationTareaBitacoraInicioSesion",
                    data: {
                        cod_inicio_sesion: $('#modal_editar_farm_location_cod_inicio_sesion').val(),
                        cod_farm: $('#select_editar_farm_location_cod_farm').val(),
                        cod_location: $('#select_editar_farm_location_cod_location').val()
                    },
                    error: (err) => {
                        console.log(err);
                        Swal.fire("An error occurred while processing",
                            "Please contact the administrators.", "warning");
                    },
                    success: (res) => {
                        console.log(res);
                        Swal.fire("Update task location", "The action was completed sucesfully",
                            "success");
                        $('#modalEditarFarmLocation').modal('hide');
                        $('#cod_usuario').trigger('change');
                    }
                });
            });
            $("#guardar_datos_escaneo").on("click", guardarDatosEscaneo);
        });
        var cantidadRegistrosFallidos = 0;
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
        }).on('dp.hide', function(e) {
            $('#btn_agregrar_nuevo_registro_entradasalida').attr('hidden', true);
            $('#nombre_empleado_seleccionado').empty();
            $('#nombre_empleado_seleccionado').append('---');
            $('#tabla_registro').empty();
            $('#search').trigger('keyup');
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
        }).on('dp.hide', function(e) {
            $('#btn_agregrar_nuevo_registro_entradasalida').attr('hidden', true);
            $('#nombre_empleado_seleccionado').empty();
            $('#nombre_empleado_seleccionado').append('---');
            $('#tabla_registro').empty();
            $('#search').trigger('keyup');
        });

        $('#div_fecha_registro_modal').datetimepicker({
            locale: 'ys',
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
        }).on('dp.hide', function(e) {});

        $('.datetimepicker').datetimepicker({
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
        }).on('dp.hide', function(e) {});



        function clicEnTarjetaEmpleado(cod_usuario) {
            $("#cod_usuario").val(cod_usuario);
            $('#cod_usuario').trigger('change');
        }

        function activarEdicion(btnIdentificador, inputIdentificador) {

            let btnActivarInput = $('#' + btnIdentificador);
            let miInput = $('#' + inputIdentificador);
            miInput.toggle(); // Alterna la visibilidad del input
            miInput.prop('disabled', false);
            btnActivarInput.toggle(); // Alterna la visibilidad del input
            miInput.removeAttr('hidden');
            miInput.focus();
        }

        function activarEdicionHorasCabeceras(btnIdentificador, inputIdentificador, codInicioSesion) {

            let btnActivarInput = $('#' + btnIdentificador);
            let miInput = $('#' + inputIdentificador);
            let calculoHorasFila = $('#lbl_cabecera_horas_totales_' + codInicioSesion);
            miInput.toggle(); // Alterna la visibilidad del input
            miInput.prop('disabled', false);
            btnActivarInput.toggle(); // Alterna la visibilidad del input
            miInput.removeAttr('hidden');
            miInput.focus();

            if (calculoHorasFila.text() != "N/D") {
                subtractTime(calculoHorasFila.text());
            }
        }

        function detectarDatosEntradaDeTeclados(event, btnIdentificador, inputIdentificador) {

            let btnActivarInput = $('#' + btnIdentificador);
            let miInput = $('#' + inputIdentificador);
            var valorInput = miInput.val();
            btnActivarInput.text(valorInput);
        }

        function detectarTeclasDeSalida(event, btnIdentificador, inputIdentificador, nombre_de_la_semilla) {

            //IMPLEMENTAR EN EL FUTURO
            let btnActivarInput = $('#' + btnIdentificador);
            let miInput = $('#' + inputIdentificador);

            var valorInput = miInput.val();

            // if (event.keyCode === 13) { //Tecla "Enter"/"Entrar"
            //     miInput.prop('disabled', true);
            //     valorCantidadInicialDeSemillas = parseInt(miInput.val());
            //     guardarCantidadNuevaDeCantidadDeSemillas(parseInt(valorCantidadInicialDeSemillas), parseInt(
            //         valorCantidadInicialDeSemillas), idSemilla, nombre_de_la_semilla, true);

            // }


            // if (event.keyCode === 27) { // Tecla "ESC"
            //     // Aquí puedes ejecutar el código que deseas cuando se presiona "ESC"
            //     valorCantidadInicialDeSemillas = $("#input_cantidad_inicial_semilla_" + idSemilla).val();
            //     let valorParaBoton = parseInt(valorCantidadInicialDeSemillas)
            //     btnActivarInput.text(valorParaBoton.toLocaleString());
            //     miInput.val(valorCantidadInicialDeSemillas);
            //     miInput.prop('disabled', true);

            // }
        }


        function detectarSalirDeInput(btnIdentificador, inputIdentificador, codCrew) {
            let btnActivarInput = $('#' + btnIdentificador);
            let miInput = $('#' + inputIdentificador);
            if (btnActivarInput == undefined || miInput == undefined) return;

            // Deshabilitar el input cuando se pierde el foco
            const cantidadIngresada = parseInt(miInput.val());


            $.ajax({
                url: window.location.href + 'api/v1/empleado_administracion/cambiarCantidadEscaneoTareaEmpleado',
                type: 'POST',
                data: {
                    codCrew: codCrew,
                    cantidadIngresada: cantidadIngresada,

                },
                success: function(response) {


                },
                error: function(xhr) {
                    console.error('Error:', xhr.responseText);
                }
            });
            miInput.toggle(0);
            miInput.prop('disabled', true);
            miInput.attr('hidden', true);
            btnActivarInput.toggle(200); // Alterna la visibilidad del input
        }

        //Funciones para el manejo de las horas (Inicial)
        function detectarSalirDeInputHoraInicio(
            btnIdentificador,
            inputIdentificador,
            lblCalculoHorasFila,
            btnIdentificadorHoraFinal, codCrew) {

            let btnActivarInput = $('#' + btnIdentificador);
            let miInput = $('#' + inputIdentificador);
            const calculoHorasFila = $('#' + lblCalculoHorasFila);
            const horaFinal = $('#' + btnIdentificadorHoraFinal);

            if (btnActivarInput == undefined || miInput == undefined) return;



            let horaSeleccionada = miInput.val();
            let horaFormateada = moment(horaSeleccionada, 'HH:mm').format('h:mm A');
            btnActivarInput.text(horaFormateada);
            calculoHorasFila.text(calcularCantidadHoras(
                horaSeleccionada,
                horaFinal.val()
            ));

            actualizarHorasInicioFinalTarea(codCrew, horaSeleccionada, horaFinal.val());

            miInput.toggle(0);
            miInput.prop('disabled', true);
            miInput.attr('hidden', true);

            btnActivarInput.toggle(200); // Alterna la visibilidad del input

        }

        //Funciones para el manejo de las horas (Finales)
        function detectarSalirDeInputHoraFinal(
            btnIdentificador,
            inputIdentificador,
            lblCalculoHorasFila,
            btnIdentificadorHoraInicio,
            codCrew) {

            const btnActivarInput = $('#' + btnIdentificador);
            const miInput = $('#' + inputIdentificador);
            const calculoHorasFila = $('#' + lblCalculoHorasFila);
            const horaInicio = $('#' + btnIdentificadorHoraInicio);
            if (btnActivarInput == undefined || miInput == undefined) return;

            // guardarCantidadNuevaDeCantidadDeSemillas(parseInt(valorCantidadInicialDeSemillas), parseInt(miInput.val()),
            //     idSemilla, lblCalculoHorasFila);

            // Deshabilitar el input cuando se pierde el foco

            let horaSeleccionada = miInput.val();
            let horaInicialFormateada = moment(horaSeleccionada, 'HH:mm').format('h:mm A');
            btnActivarInput.text(horaInicialFormateada);
            calculoHorasFila.text(calcularCantidadHoras(
                horaInicio.val(),
                horaSeleccionada
            ));

            actualizarHorasInicioFinalTarea(codCrew, horaInicio.val(), horaSeleccionada);
            miInput.toggle(0);
            miInput.prop('disabled', true);
            miInput.attr('hidden', true);

            btnActivarInput.toggle(200); // Alterna la visibilidad del input

        }

        //Funciones para el manejo de las horas (Entrada)
        function detectarSalirDeInputHoraEntrada(identificadorBtnHoraEntrada, identificadorInputHoraEntrada,
            identificadorBtnHoraSalida, identificadorInputHoraSalida, lblTotalHoras, codInicioSesion) {

            let btnActivarInput = $('#' + identificadorBtnHoraEntrada);
            let miInput = $('#' + identificadorInputHoraEntrada);

            let btnActivarInputSalida = $('#' + identificadorBtnHoraSalida);
            let miInputSalida = $('#' + identificadorInputHoraSalida);

            const calculoHorasFila = $('#' + lblTotalHoras);


            if (btnActivarInput == undefined || miInput == undefined) return;

            // // guardarCantidadNuevaDeCantidadDeSemillas(parseInt(valorCantidadInicialDeSemillas), parseInt(miInput.val()),
            // //     idSemilla, lblCalculoHorasFila);


            let horaSeleccionada = miInput.val();
            let horaFormateada = moment(horaSeleccionada, 'HH:mm').format('h:mm A');

            btnActivarInput.text(horaFormateada);

            if (btnActivarInputSalida.text() != '--:--') {
                calculoHorasFila.text(calcularCantidadHoras(
                    horaSeleccionada,
                    miInputSalida.val()
                ));
                if ($('#cod_usuario').find('option:selected').data('reducirtiempo') == 1 && $(
                        "#checkbox_asignar_almuerzo_" + codInicioSesion).is(":checked")) {
                    calculoHorasFila.text(restarTiempoAHoraEspecifica(calculoHorasFila.text(), "00:30"));
                }
                addTime(calculoHorasFila.text())
                guardarHorasEntradaSalidaSeleccionadas(codInicioSesion, horaSeleccionada, miInputSalida.val());

            } else {
                guardarHorasEntradaSeleccionadas(codInicioSesion, horaSeleccionada);
            }
            // // Deshabilitar el input cuando se pierde el foco

            miInput.toggle(0);
            miInput.prop('disabled', true);
            miInput.attr('hidden', true);

            btnActivarInput.toggle(0); // Alterna la visibilidad del input

        }

        //Funciones para el manejo de las horas (Salida)
        function detectarSalirDeInputHoraSalida(
            identificadorBtnHoraSalida, identificadorInputHoraSalida,
            identificadorBtnHoraEntrada, identificadorInputHoraEntrada,
            lblTotalHoras, codInicioSesion) {

            let btnActivarInput = $('#' + identificadorBtnHoraSalida);
            let miInput = $('#' + identificadorInputHoraSalida);

            let btnActivarInputEntrada = $('#' + identificadorBtnHoraEntrada);
            let miInputEntrada = $('#' + identificadorInputHoraEntrada);

            const calculoHorasFila = $('#' + lblTotalHoras);


            if (btnActivarInput == undefined || miInput == undefined) return;

            // // guardarCantidadNuevaDeCantidadDeSemillas(parseInt(valorCantidadInicialDeSemillas), parseInt(miInput.val()),
            // //     idSemilla, lblCalculoHorasFila);


            let horaSeleccionada = miInput.val();
            let horaFormateada = moment(horaSeleccionada, 'HH:mm').format('h:mm A');

            btnActivarInput.text(horaFormateada);
            if (btnActivarInputEntrada.text() != '--:--') {

                if (calculoHorasFila.text() == "N/D") {
                    cantidadRegistrosFallidos = localStorage.getItem('cantidad_registros_fallidos') ?? 1;
                    cantidadRegistrosFallidos--;
                    localStorage.setItem('cantidad_registros_fallidos', cantidadRegistrosFallidos);

                }
                calculoHorasFila.text(calcularCantidadHoras(
                    miInputEntrada.val(),
                    horaSeleccionada
                ));

                if ($('#cod_usuario').find('option:selected').data('reducirtiempo') == 1 && $(
                        "#checkbox_asignar_almuerzo_" + codInicioSesion).is(":checked")) {
                    calculoHorasFila.text(restarTiempoAHoraEspecifica(calculoHorasFila.text(), "00:30"));
                }
                addTime(calculoHorasFila.text())

                $('#td_cabecera_principal_' + codInicioSesion).removeClass('registro_con_error');
                $('#td_cabecera_secundaria_' + codInicioSesion).removeClass('registro_con_error');

                $('#td_cabecera_principal_' + codInicioSesion).addClass('registro_sin_error');
                $('#td_cabecera_secundaria_' + codInicioSesion).addClass('registro_sin_error');
                guardarHorasEntradaSalidaSeleccionadas(codInicioSesion, miInputEntrada.val(), horaSeleccionada);
            }
            // // Deshabilitar el input cuando se pierde el foco

            miInput.toggle(0);
            miInput.prop('disabled', true);
            miInput.attr('hidden', true);

            btnActivarInput.toggle(0); // Alterna la visibilidad del input

        }

        function guardarHorasEntradaSalidaSeleccionadas(codInicioSesion, horaEntrada, horaSalida) {
            $.ajax({
                url: window.location.href + 'api/v1/empleado_administracion/actualizarHorasEntradaSalida',
                type: 'POST',
                data: {
                    codInicioSesion: codInicioSesion,
                    horaEntrada: horaEntrada,
                    horaSalida: horaSalida

                },
                success: function(response) {

                    refrescarHorasTotalesCabeceras();
                },
                error: function(xhr) {
                    console.error('Error:', xhr.responseText);
                }
            });
        }

        function guardarHorasEntradaSeleccionadas(codInicioSesion, horaEntrada) {
            $.ajax({
                url: window.location.href + 'api/v1/empleado_administracion/actualizarHorasEntrada',
                type: 'POST',
                data: {
                    codInicioSesion: codInicioSesion,
                    horaEntrada: horaEntrada

                },
                success: function(response) {

                },
                error: function(xhr) {
                    console.error('Error:', xhr.responseText);
                }
            });
        }

        function actualizarHorasInicioFinalTarea(codCrew, horaInicio, horaFinal) {
            $.ajax({
                url: window.location.href + 'api/v1/empleado_administracion/actualizarHorasInicioFinalTarea',
                type: 'POST',
                data: {
                    codCrew: codCrew,
                    horaInicio: horaInicio,
                    horaFinal: horaFinal,

                },
                success: function(response) {


                },
                error: function(xhr) {
                    console.error('Error:', xhr.responseText);
                }
            });
        }

        function calcularCantidadHoras(HORA_ENTRADA, HORA_SALIDA) {
            if (HORA_ENTRADA === HORA_SALIDA) {
                return "00:00";
            }
            // Dividir la cadena en horas y minutos
            const [hours, minutes] = HORA_ENTRADA.split(':').map(Number);
            const [hoursOut, minutesOut] = HORA_SALIDA.split(':').map(Number);

            // Crear objetos Date para los tiempos específicos
            const time1 = new Date();
            const time2 = new Date();

            time1.setHours(hours, minutes, 0);
            time2.setHours(hoursOut, minutesOut, 0);

            // Ajustar la fecha si el segundo tiempo es al día siguiente
            if (time2 <= time1) {
                time2.setDate(time2.getDate() + 1);
            }

            // Calcular la diferencia en milisegundos
            const diffMilliseconds = time2 - time1;

            // Convertir la diferencia a horas
            const diffHours = diffMilliseconds / (1000 * 60 * 60);

            return `${Math.floor(diffHours)}:${Math.round((diffHours % 1) * 60).toString().padStart(2, '0')}`;
        }

        function eliminarVinculoConTarea(codCrew, idFilaTarea) {
            $.ajax({
                url: window.location.href + 'api/v1/empleado_administracion/eliminarVinculoConTarea',
                type: 'POST',
                data: {
                    codCrew: codCrew,

                },
                success: function(response) {

                    if (response["estadoEliminacion"] == true) {
                        $('#' + idFilaTarea).remove();
                    }


                },
                error: function(xhr) {
                    console.error('Error:', xhr.responseText);
                }
            });
        }

        function cargarListaTipoPago(id = "cod_tipo_pago") {
            $.ajax({
                url: window.location.href + 'api/v1/empleado_administracion/listaTipoPagos',
                type: 'POST',
                data: {},
                success: function(response) {

                    const data = response;
                    $('#' + id).empty();

                    if (data.length > 0) {
                        $('#' + id).append('<option value="-b" >--Select--</option>');

                        $.each(data, function(i, item) {
                            $('#' + id).append(`<option value="${item.cod_tipo_pago}"
                            data-tipopago="${item.tipo_pago}"
                            data-abreviatura="${item.abreviatura}"
                            >${item.tipo_de_pago}</option>`);
                        });
                    } else {
                        $('#' + id).append('<option value="-b" >Error loading payment types</option>');

                    }

                },
                error: function(xhr) {
                    console.error('Error:', xhr.responseText);
                }
            });
        }

        function cargarListaHarvestPorGranjas(id = "cods_harvests", fecha = '2024-08-25') {
            $.ajax({
                url: window.location.href + 'api/v1/empleado_administracion/listaHarvestsPorGranja',
                type: 'POST',
                data: {
                    cod_farm: $("#cod_farm").val(),
                    fecha_job: fecha

                },
                success: function(response) {

                    const data = response;
                    $('#' + id).empty();

                    if (data.length > 0) {
                        $('#' + id).append('<option value="-b" >--Select--</option>');

                        $.each(data, function(i, item) {
                            $('#' + id).append(`<option value="${item.cod_harvest}"
                        data-abreviaturatipopago="${item.abreviatura_tipo_pago}"
                        data-codjob="${item.cod_job}"
                        data-horafinal="${item.hora_final}"
                        data-horainicio="${item.hora_inicio}"
                        >${item.job}</option>`);
                        });
                    } else {
                        $('#' + id).append('<option value="-b" >No Harvest found</option>');

                    }

                },
                error: function(xhr) {
                    console.error('Error:', xhr.responseText);
                }
            });
        }

        function cargarListaMiscelaneosPorGranjas(id = "cods_miscelaneos", fecha = '2024-08-25') {
            $.ajax({
                url: window.location.href + 'api/v1/empleado_administracion/listaMiscelaneosPorGranja',
                type: 'POST',
                data: {
                    cod_farm: $("#cod_farm").val(),
                    fecha_job: fecha

                },
                success: function(response) {

                    const data = response;
                    $('#' + id).empty();
                    if (data.length > 0) {
                        $('#' + id).append('<option value="-b" >--Select--</option>');

                        $.each(data, function(i, item) {
                            $('#' + id).append(`<option value="${item.cod_miscellaneous}"
                        data-abreviaturatipopago="${item.abreviatura_tipo_pago}"
                        data-codjob="${item.cod_job}"
                        data-horafinal="${item.hora_final}"
                        data-horainicio="${item.hora_inicio}"
                        >${item.job}</option>`);
                        });
                    } else {
                        $('#' + id).append('<option value="-b" >No miscellaneous tasks found</option>');

                    }

                },
                error: function(xhr) {
                    console.error('Error:', xhr.responseText);
                }
            });
        }

        function alternarAsinacionAlmuerzo(codInicioSesion, lblTotalHoras) {
            const calculoHorasFila = $('#' + lblTotalHoras);

            $.ajax({
                url: window.location.href + 'api/v1/empleado_administracion/alternarActivacionAsingarAlmuerzo',
                type: 'POST',
                data: {
                    cod_inicio_sesion: codInicioSesion,
                    estado_activacion: $("#checkbox_asignar_almuerzo_" + codInicioSesion).is(":checked") ? 1 : 0
                },
                success: function(response) {

                    if ($('#cod_usuario').find('option:selected').data('reducirtiempo') == 1) {

                        if (response["data"] == 1) {
                            subtractTime("00:30")
                            calculoHorasFila.text(restarTiempoAHoraEspecifica(calculoHorasFila.text(),
                                "00:30"));
                            // calculoHorasFila.text("4444444444");
                            // restarTiempoAHoraEspecifica("00:30", "00:00");
                        } else {
                            addTime("00:30");
                            calculoHorasFila.text(sumarTiempoAHoraEspecifica(calculoHorasFila.text(),
                                "00:30"));
                            // $('#lbl_hora_almuerzo_asignado_' + codInicioSesion).attr('hidden', true);
                            // calculoHorasFila.text("51454646464");
                        }
                        refrescarHorasTotalesCabeceras();
                    }

                    if (response["data"] == 1) {
                        $('#lbl_hora_almuerzo_asignado_' + codInicioSesion).removeAttr('hidden');
                    } else {
                        $('#lbl_hora_almuerzo_asignado_' + codInicioSesion).attr('hidden', true);

                    }

                },
                error: function(xhr) {
                    console.error('Error:', xhr.responseText);
                }
            });
        }

        function addTime(time) {

            totalDeHoras = $('#total_horas_cabecera').val();
            totalDeHoras = parseInt(totalDeHoras);

            const [hours, minutes] = time.split(':').map(Number);
            totalDeHoras += hours * 60 + minutes;

            $('#total_horas_cabecera').val(totalDeHoras);

            return formatTime(totalDeHoras);
        }

        function subtractTime(time) {

            totalDeHoras = $('#total_horas_cabecera').val();
            totalDeHoras = parseInt(totalDeHoras);

            const [hours, minutes] = time.split(':').map(Number);
            totalDeHoras -= hours * 60 + minutes;

            $('#total_horas_cabecera').val(totalDeHoras);

            return formatTime(totalDeHoras);
        }

        function refrescarHorasTotalesCabeceras() {
            cantidadRegistrosFallidos = localStorage.getItem('cantidad_registros_fallidos') ?? 1;
            if (cantidadRegistrosFallidos < 1) {
                $('#total_horas_sumadas').text("Total Time: " + formatTime($('#total_horas_cabecera').val()));

                $("#estado_alerta_usuario_" + $('#cod_usuario').val()).attr('hidden', true);
            }
        }

        function formatTime(minutes) {
            const totalHours = Math.floor(Math.abs(minutes) / 60);
            const remainingMinutes = Math.abs(minutes) % 60;
            const sign = minutes < 0 ? '-' : '';

            return `${sign}${totalHours}:${remainingMinutes.toString().padStart(2, '0')}`;
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

        function cargarSelectLocacionesPorGranja(id_select, cod_farm, oncomplete = null) {
            $.ajax({
                url: window.location.href + 'api/v1/empleado_administracion/listaLocacionesPorGranjas',
                type: 'POST',
                data: {
                    cod_farm: cod_farm
                },
                success: function(response) {

                    jQuery.ajaxSetup({
                        async: false
                    });

                    console.log({
                        response
                    });
                    const data = response;
                    $('#' + id_select).empty();

                    if (data.length > 0) {
                        $('#' + id_select).append('<option value="0" selected>All</option>');

                        $.each(data, function(i, item) {
                            $('#' + id_select).append(
                                `<option value="${item.cod_location}">${item.location}</option>`);
                        });
                    } else {
                        $('#' + id_select).append('<option value="-b" >No locations found</option>');

                    }

                    jQuery.ajaxSetup({
                        async: true
                    });

                    if (oncomplete != null && typeof oncomplete == "function") {
                        oncomplete();
                    }
                },
                error: function(xhr) {
                    console.error('Error:', xhr.responseText);

                }
            });
        }

        function cargarTareasPorFechaEmpleado(idFilaInterna, codUsuario, fechaIngreso) {
            // jQuery.ajaxSetup({
            //     async: false
            // });

            const estadoAperturaFila = localStorage.getItem('fila_abierta_' + idFilaInterna);


            if (estadoAperturaFila != undefined && estadoAperturaFila == 'true') {
                localStorage.setItem('fila_abierta_' + idFilaInterna, false);

                return;
            }
            localStorage.setItem('fila_abierta_' + idFilaInterna, true);
            $('#' + idFilaInterna).empty();

            // Show loading spinner
            $('#' + idFilaInterna).html(
                '<tr><td colspan="10" class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>'
            );
            $.ajax({
                url: window.location.href + 'api/v1/empleado_administracion/cargarTareasPorFechaEmpleado',
                type: 'POST',
                data: {
                    codUsuario: codUsuario,
                    fechaIngreso: fechaIngreso
                },
                success: function(response) {
                    let tableRow = ``;


                    if (response.hasOwnProperty('data') && Array.isArray(response.data
                            .registroTareasInificadas) && response.data.registroTareasInificadas.length > 0) {

                        let idUnicoFila = 0;

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
                        // Get the pack types
                        let pack_types = JSON.parse(response.data.pack_types);
                        console.log({
                            pack_types
                        })
                        let packs_farms_category_assignment = JSON.parse(response.data
                            .packs_farms_category_assignment);

                        response.data.registroTareasInificadas.forEach((item) => {

                            tableRow += `<tr id="fila_tarea_${item["cod_crew"]}">
                                            <td style="width: 10%;">${item["nombre_granja"]}</td>
                                            <td style="width: 40%; text-align: left !important;">${item["job"]}</td>
                                            <td style="width: 5%;">${item["abreviatura_tipo_pago"]}</td>
                                            <td style="width: 9%;">
                                                <button class="btnSeleccionables" onclick="activarEdicion('btn_cantidad_escaneo_${idUnicoFila}', 'input_cantidad_escaneo_${idUnicoFila}')" id="btn_cantidad_escaneo_${idUnicoFila}">${item["cantidad_escaneo"]}</button>
                                                <input class="input_texto" hidden type="number" min="0"  id="input_cantidad_escaneo_${idUnicoFila}" onblur="detectarSalirDeInput('btn_cantidad_escaneo_${idUnicoFila}', 'input_cantidad_escaneo_${idUnicoFila}', '${item["cod_crew"]}')" value="${item["cantidad_escaneo"]}" onkeydown="detectarTeclasDeSalida(event,'btn_cantidad_escaneo_${idUnicoFila}', 'input_cantidad_escaneo_${idUnicoFila}','${item["cantidad_escaneo"]}')" oninput="detectarDatosEntradaDeTeclados(event,'btn_cantidad_escaneo_${idUnicoFila}', 'input_cantidad_escaneo_${idUnicoFila}')" placeholder="">
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
                                                    <div class="col-6">
                                                        <button class="btn btn-danger" data-toggle="tooltip" title="Delete" onclick="eliminarVinculoConTarea(${item["cod_crew"]}, 'fila_tarea_${item["cod_crew"]}')" style="height: 25px; width: 25px; font-size: 12px; padding:0px;">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </div>
                                                    <div class="col-6">
                                                        <button class="btn btn-info" data-toggle="tooltip" title="Scanned Quantities" data-bs-toggle="modal" onclick="abrirModalAgregarCantidad(${item["cod_crew"]}, ${idUnicoFila})" style="height: 25px; width: 25px; font-size: 12px; padding:0px;">
                                                            <i class="fa-solid fa-barcode"></i>
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
                                <td colspan="9" class="text-center text-danger fw-bold">No tasks found</td>
                            </tr>
                        `;
                    }
                    $('#' + idFilaInterna).empty();

                    $('#' + idFilaInterna).append(tableRow);

                    jQuery.ajaxSetup({
                        async: true
                    });
                    // refrescarHorasTotalesCabeceras();
                },
                error: function(xhr) {
                    console.error('Error:', xhr.responseText);
                }
            });
            // jQuery.ajaxSetup({
            //     async: true
            // });

        }

        /**
         * Funcion que construye un select con los pack_types dados, cod_harvest y pack_type_selected
         * Asigna el listener onchange para actualizar el harvest en caso que se cambie
         * @param {array} pack_types
         * @param {mixed} cod_harvest
         * @param {mixed} pack_type_selected
         * @returns
         */
        function construirPackTypeSelectHarvest(pack_types, packs_farms_category_assignment, cod_harvest, cod_farm,
            cod_category, current_pack_type_selected = null) {

            let corresponding_pcas = packs_farms_category_assignment.filter((pca) => {
                if (pca.cod_farm == cod_farm &&
                    (pca.cod_location == "1" || pca.cod_location == "4" || pca.cod_location == "7" || pca
                        .cod_location == "10" || pca.cod_location == "13") &&
                    pca.cod_categoria == cod_category) {
                    return pca
                }
            });
            console.log({
                pca: corresponding_pcas
            });
            let corresponding_pack_types = pack_types.filter((p) => {
                let packTypeFound = false;
                corresponding_pcas.forEach((pca) => {
                    if (pca.cod_tipo_pack == p.cod_tipo_pack) {
                        packTypeFound = true;
                    }
                });

                console.log({
                    p: p,
                    packTypeFound
                });
                return packTypeFound;
            });

            // Build the pack types select
            let pack_type_select =
                `<select class="packTypeSelects" onchange="window.cambiarPackTypeActividad(event, ${cod_harvest}, ${current_pack_type_selected})">`;
            pack_type_select += `<option disabled>Select one</option>`
            console.log({
                pack_types: corresponding_pack_types
            });
            corresponding_pack_types.forEach((p) => {
                let option =
                    `<option value="${p.cod_tipo_pack}" ${current_pack_type_selected != null && current_pack_type_selected == p.cod_tipo_pack ? "selected" : ""}>${p.tipo_pack}</option>`;
                pack_type_select += option;
            });
            pack_type_select += `</select>`;
            return pack_type_select;

        }

        function agregarCantidadEscaneada() {
            cantidadEscaneos++;
            $('#listado_datos_escaneados').append(`
            <div id="escaneo_${cantidadEscaneos}" style="width: 100%; text-align: right;" class="row display-flex">
                <div style="float: right; padding: 0px">
                    <button id="btn_recuperar_${cantidadEscaneos}" type="button" class="d-none btn btn-info btn-sm" style="border-radius: 0;" onclick="recuperarEscaneado(${cantidadEscaneos}, 'contenedor_datos_escaneos_')">
                        <i class="fa-solid fa-rotate-left"></i>
                    </button>
                    <button id="btn_eliminar_${cantidadEscaneos}" type="button" class="btn btn-danger btn-sm" style="border-radius: 0;" onclick="desactivarEscaneo(${cantidadEscaneos}, 'contenedor_datos_escaneos_')">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
                <div id="contenedor_datos_escaneos_${cantidadEscaneos}" class="contenedor_datos_escaneos_nuevo col-12">
                    <input type="hidden" id="datos_nuevo_escaneo_${cantidadEscaneos}" value="1">
                    <input type="hidden" id="vigencia_escaneo_${cantidadEscaneos}" value="1">
                    <input type="time" id="hora_escaneo_${cantidadEscaneos}" class="form-control" style="max-width: 20%;" value="${asignarHoraActual()}" onchange="adjustTimes()">
                    <input type="text" id="comentario_${cantidadEscaneos}" value="" class="form-control" style="width: 60%;">
                    <div id="pieza_${cantidadEscaneos}" class="d-flex justify-content-center align-items-center" style="background-color: grey; width: 10%; color: white; height: 100%;">
                        1
                    </div>
                </div>
            </div>
            `);
            $('#cantidad_escaneos').text(cantidadEscaneos);
            adjustTimes();
        }

        function asignarHoraActual() {
            let maxTime = moment("00:00", "HH:mm");
            $(".contenedor_datos_escaneos, .contenedor_datos_escaneos_nuevo")
                .find("input[type='time']")
                .each(function() {
                    const timeStr = $(this).val();
                    if (timeStr) {
                        const current = moment(timeStr, "HH:mm");
                        if (current.isAfter(maxTime)) {
                            maxTime = current;
                        }
                    }
                });
            maxTime.add(5, "minutes");
            return maxTime.format("HH:mm");
            // return moment().format('HH:mm');
        }

        function adjustTimes() {
            var $inputs = $('.contenedor_datos_escaneos, .contenedor_datos_escaneos_nuevo').find('input[type="time"]');
            var times = [];
            // Recorre cada input y asegúrate de que tenga un valor
            $inputs.each(function() {
                var t = $(this).val();
                if (!t) {
                    t = moment().format("HH:mm");
                    $(this).val(t);
                }
                times.push({
                    elem: $(this),
                    time: moment(t, "HH:mm")
                });
            });

            // Ordena los inputs según su tiempo
            times.sort(function(a, b) {
                return a.time.diff(b.time);
            });

            let horaAjustada = false;
            // Recorre y ajusta para que la diferencia entre cada uno sea de al menos 30 segundos
            for (var i = 1; i < times.length; i++) {
                var prev = times[i - 1];
                var curr = times[i];
                var diff = curr.time.diff(prev.time, 'seconds');
                if (diff <= 30) {
                    curr.time = moment(prev.time).add(30, 'seconds');
                    curr.elem.val(curr.time.format("HH:mm"));
                    horaAjustada = true;

                }
            }
            if (horaAjustada) {
                if (!$('#ajusteMensaje').length) {
                    $('body').append(
                        '<div id="ajusteMensaje" style="position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); background: rgba(119,221,119,0.6); color: #000000; padding: 8px 12px; border-radius: 4px; font-size: 14px; z-index: 10000; display: none;">The selected times have been adjusted</div>'
                    );
                }
                $('#ajusteMensaje').fadeIn(300).delay(2000).fadeOut(300);
            }
        }

        function recopilarDatosEscaneados() {
            // Crear un array para almacenar los datos
            var datosEscaneadosArray = [];

            // Iterar sobre cada contenedor de datos escaneados nuevo
            $('.contenedor_datos_escaneos_nuevo').each(function() {
                // Buscar y obtener los valores correspondientes
                var vigencia = $(this).find('[id^="vigencia_escaneo_"]').val();
                if (vigencia == 1) {
                    var datosnNuevo = $(this).find('[id^="datos_nuevo_escaneo_"]').val();

                    var hora = $(this).find('[id^="hora_escaneo_"]').val();
                    var comentario = $(this).find('[id^="comentario_"]').val();
                    var pieza = $(this).find('[id^="pieza_"]').text().trim();
                    datosEscaneadosArray.push({
                        nuevo: datosnNuevo,
                        hora: hora,
                        comentario: comentario,
                        pieza: pieza,
                        vigencia: vigencia,
                    });
                }

            });
            // Iterar sobre cada contenedor de datos escaneados viejos
            $('.contenedor_datos_escaneos').each(function() {
                // Buscar y obtener los valores correspondientes
                var datosnNuevo = $(this).find('[id^="datos_nuevo_escaneo_"]').val();
                var vigencia = $(this).find('[id^="vigencia_escaneo_"]').val();
                var hora = $(this).find('[id^="hora_escaneo_"]').val();
                var comentario = $(this).find('[id^="comentario_"]').val();
                var pieza = $(this).find('[id^="pieza_"]').text().trim();
                var cod_lista = $(this).find('[id^="datos_existento_"]').val();

                // Crear un objeto con los valores extraídos y almacenarlo en el array
                datosEscaneadosArray.push({
                    nuevo: datosnNuevo,
                    hora: hora,
                    comentario: comentario,
                    pieza: pieza,
                    cod_lista: cod_lista,
                    vigencia: vigencia,
                });
            });
            // Iterar sobre cada contenedor de datos escaneados desactivados (tanto eliminados como nuevos eliminados)
            $('.contenedor_datos_escaneos_eliminado, .contenedor_datos_escaneos_nuevo_eliminado').each(function() {
                var datosnNuevo = $(this).find('[id^="datos_nuevo_escaneo_"]').val();
                var vigencia = $(this).find('[id^="vigencia_escaneo_"]').val();
                var hora = $(this).find('[id^="hora_escaneo_"]').val();
                var comentario = $(this).find('[id^="comentario_"]').val();
                var pieza = $(this).find('[id^="pieza_"]').text().trim();
                var cod_lista = $(this).find('[id^="datos_existento_"]').val();

                datosEscaneadosArray.push({
                    nuevo: datosnNuevo,
                    hora: hora,
                    comentario: comentario,
                    pieza: pieza,
                    cod_lista: cod_lista,
                    vigencia: vigencia,
                });
            });
            console.log({
                datosEscaneadosArray
            });
            return datosEscaneadosArray;
        }

        function crearloginPantallaCompleta() {
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
            loadingOverlay.innerHTML =
                `<div class="spinner-border text-primary" style="width: 4rem; height: 4rem;" role="status"><span class="sr-only">Saving scan data...</span></div>`;
            document.body.appendChild(loadingOverlay);
        }
    </script>
    @vite(['resources/js/app.js'])
@endsection
