@extends('layouts.app')

@section('sidebar')
    <div class="w-100">
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
                <label for="cod_farm_reporte_hours" class="form-label">Farm</label>
                <select class="selectpicker w-100 selectpicker" data-style-base="btn color_principal"
                    data-actions-box="true" name="cod_farm_reporte_hours" id="cod_farm_reporte_hours" multiple>
                    @foreach ($listaGranjas as $granja)
                        <option value="{{ $granja->cod_farms }}">{{ $granja->farm }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-12 pt-3">
                <label for="cod_location_reporte_hours">Location</label>
                <select class="selectpicker w-100" data-style-base="btn color_principal" data-actions-box="true"
                    name="cod_location_reporte_hours" id="cod_location_reporte_hours" multiple>
                </select>
            </div>

            <div class="col-md-12 pt-3">
                <label for="cod_category_reporte">Category</label>
                <select class="selectpicker w-100" data-style-base="btn color_principal" data-actions-box="true"
                    name="cod_category_reporte" id="cod_category_reporte" multiple>
                    <option value="0">Regular</option>
                    <option value="1">Veteran</option>
                    <option value="2">H2A</option>
                </select>
            </div>
            <div class="col-md-12">
                {{-- Dynamic columns selector --}}
                <div class="mt-3">
                    <div class="d-flex flex-col gap-2 align-items-center mb-2">
                        <h6>Add column</h6>
                        <select disabled class="form-select" style="width: fit-content" id="dynamic-column-picker-select">
                        </select>
                    </div>
                    <h5>Columns</h5>
                    <div class="d-flex gap-2 flex-wrap" id="dynamic-columns-selected-wrapper">
                        <div class="column-item-assigned">
                            <button class="column-item-assigned-remove-button">
                                x
                            </button>
                            <span>Full name</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 mb-3">
                {{-- Dynamic group by selector --}}
                <div class="mt-3">
                    <div class="d-flex flex-col gap-2 align-items-center mb-2">
                        <h6>Add group by</h6>
                        <select disabled class="form-select" style="width: fit-content" id="dynamic-group-by-picker-select">
                        </select>
                    </div>
                    <h5>Groups</h5>
                    <div class="d-flex gap-2 flex-wrap" id="dynamic-group-by-selected-wrapper">
                        <div class="column-item-assigned">
                            <button class="column-item-assigned-remove-button">
                                x
                            </button>
                            <span>Full name</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="w-100">
        <div class="row mt-3">
            <div class="col-md-12 mb-1">
                <small>Selected Employees <span id="list_empleados_cantidad"></span></small>
                <div class="d-flex flex-column border rounded px-2 py-1" id="list_empleados"
                    style="max-height:280px;overflow:auto">
                </div>
            </div>
            <div class="col-md-12 mb-3">
                <button class="btn btn-primary btn-sm w-100" id="btn_select_employees" data-bs-toggle="modal"
                    data-bs-target="#modal_seleccionar_empleados">
                    <i class="fa-solid fa-plus"></i> Select employees
                </button>
            </div>
            <div class="col-md-12">
                <button disabled class="btn btn-success w-100" id="btn_generate_report"><i
                        class="fa-solid fa-file-lines"></i>
                    Generate report</button>
                <button hidden class="btn btn-success w-100" id="btn_generate_report_trigger"></button>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div id="contenedor_principal" style="margin-left:300px;width:calc(100vw-300px);height:100vh">
        <div id="contenedor_titulo_pagina" class="mx-auto ps-3 pb-2"
            style="border-bottom: 1px solid black; max-width: 100vw; background-color: white;">
            <div class="row container-fluid px-2">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <p id="titulo_seccion" class="text-left d-flex gap-3">
                            <a class="btn btn-secondary" href="{{ route('home') }}"><i class="fa-solid fa-rotate-left"></i>
                                Go
                                back</a>
                            <span>Dynamic Report</span>
                        </p>
                        <div class="dropdown d-none" id="dropdown_export">
                            <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fa-solid fa-file-export"></i> Export
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" id="btn_export_xlsx" href="#!">XLSX</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-2">
                    <button id="btn_reset_config" class="btn btn-primary" type="button">
                        <i class="fa-solid fa-clipboard-check"></i> Reset config
                    </button>
                </div>
                <div class="col-8">
                    <div class="input-group">
                        <label class="input-group-text" for="select_config"><i class="fa-solid fa-clipboard-check"></i>
                            Configuraciones</label>
                        <select class="form-select" id="select_config">
                            <option selected value="reset">Select</option>
                            @for ($i = 1; $i <= 3; $i++)
                                @if (isset($listaConfiguraciones[$i]))
                                    <option value="config_{{ $i }}">Config
                                        #{{ $listaConfiguraciones[$i]->numero_configuracion }}
                                        @if (!empty($listaConfiguraciones[$i]->comentario))
                                            -
                                            ({{ \Illuminate\Support\Str::limit($listaConfiguraciones[$i]->comentario, 30, '...') }})
                                        @endif
                                    </option>
                                @else
                                    <option value="config_{{ $i }}">Config #{{ $i }}</option>
                                @endif
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="col-2">
                    <button hidden disabled id="btn_mostrar_modal_guardar_configuracion" class="btn btn-success"
                        data-bs-toggle="modal" data-bs-target="#modalGuardarConfiguracion" type="button">
                        <i class="fa-solid fa-clipboard-check"></i> Save
                    </button>
                </div>
            </div>
        </div>
        <div class="mx-auto px-3" style="box-sizing: border-box">
            {{-- Headings --}}
            <div class="row w-100 m-2 d-none" id="heading_wrapper">
                <div class="col-12 mt-2">
                    <div class="row border border-secondary rounded w-100 color_principal text-white"
                        id="heading_columns">

                    </div>
                </div>
            </div>
            {{-- Content --}}
            <div class="row w-100 m-2" id="contenedor_reporte">
                <div class="col-12">
                    <div class="row w-100 border-bottom">
                        <div class="col-12 px-3 py-2 text-center">
                            <span>Fill the fields on the left and click the green "Generate report" button.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modals  --}}
    <div class="modal fade" id="modalGuardarConfiguracion" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Save configuration</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <label for="select_config_modal">Configurations</label>
                            <select class="form-select" id="select_config_modal">
                                <option selected value="reset">Select</option>
                                <option value="1">Config #1</option>
                                <option value="2">Config #2</option>
                                <option value="3">Config #3</option>
                            </select>
                        </div>
                        <div class="col-md-12 pt-3">
                            <label for="select_editar_farm_location_cod_location">Note (Optional)</label>
                            <textarea class="form-control" id="txt_nota_configuracion" rows="3" maxlength="100"
                                placeholder="Enter a note for this configuration"
                                oninput="document.getElementById('note_char_count').textContent = this.value.length + '/100';"></textarea>
                            <small class="text-muted"><span id="note_char_count">0/100</span></small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" disabled data-bs-toggle="modal"
                        data-bs-target="#modalConfirmarGuardarConfiguracion"
                        id="btn_confirmar_guardar_configuracion">Confirm</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalConfirmarGuardarConfiguracion" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Save configuration</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-warning" role="alert">
                                The previous configuration will be replaced by the new one. This action is not reversible.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal"
                        data-bs-target="#modalGuardarConfiguracion" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal"
                        id="btn_guardar_configuracion">Save</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modal_seleccionar_empleados" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Employees to report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="select_employees_wrapper"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="btn_modal_save_selected">Save selected</button>
                </div>
            </div>
        </div>
    </div>

    @vite(['resources/js/reports/dynamic.js'])
@endsection
