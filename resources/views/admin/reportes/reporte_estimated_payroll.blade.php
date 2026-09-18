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
            <select class="form-select" name="cod_farm_reporte_hours"
                id="cod_farm_reporte_hours">
                <option value="0">All</option>
                @foreach ($listaGranjas as $granja)
                    <option value="{{ $granja->cod_farms }}">{{ $granja->farm }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-12 pt-3">
            <label for="cod_location_reporte_hours">Location</label>
            <select class="form-select" name="cod_location_reporte_hours" id="cod_location_reporte_hours">
                <option value="0" selected>All</option>
            </select>
        </div>

        <div class="col-md-12 pt-3">
            <label for="cod_category_reporte">Category</label>
            <select class="selectpicker w-100" data-style-base="btn color_principal" data-actions-box="true" name="cod_category_reporte" id="cod_category_reporte" multiple>
                <option value="0">Regular</option>
                <option value="1">Veteran</option>
                <option value="2">H2A</option>
            </select>
        </div>

        {{-- <div class="col-md-12 pt-3">
            <label for="cod_farm">Farm</label>
            <select class="form-select" name="cod_farm" id="cod_farm_nav_izquierdo">
                <option value="0" selected>All</option>
                <?php // $i=0; ?>
                @foreach ($listaGranjas as $granja)
                <option value="{{ $granja->cod_farms }}">{{ $granja->farm }}</option>
                <?php // $i++; ?>
                @endforeach
            </select>
        </div> --}}

    </div>
</div>

<div class="w-100">
    <div class="row mt-3">
        <div class="col-md-12 mb-1">
            <small>Selected Employees <span id="list_empleados_cantidad"></span></small>
            <div class="d-flex flex-column border rounded px-2 py-1" id="list_empleados"
                style="max-height:280px;overflow:auto">
                {{-- <div><small>1. Nombre del empleadoasdsad (123 - 3321)</small></div>
                <div><small>2. Nombre del empleadoasdas (123 - 3321)</small></div> --}}
            </div>
        </div>
        <div class="col-md-12 mb-3">
            <button class="btn btn-primary btn-sm w-100" id="btn_select_employees" data-bs-toggle="modal" data-bs-target="#modal_seleccionar_empleados">
                <i class="fa-solid fa-plus"></i> Select employees
            </button>
        </div>
        <div class="col-md-12">
            <button class="btn btn-success w-100" id="btn_generate_report"><i class="fa-solid fa-file-lines"></i> Generate report</button>
        </div>
    </div>
</div>
@endsection

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
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
<div id="contenedor_principal" style="margin-left:300px;width:calc(100vw-300px);height:100vh">
    <div id="contenedor_titulo_pagina" class="mx-auto ps-3"
        style="border-bottom: 1px solid black; max-width: 100vw; background-color: white;">
        <div class="row container-fluid px-2">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <p id="titulo_seccion" class="text-left d-flex gap-3">
                        <a class="btn btn-secondary" href="{{ route('home') }}"><i class="fa-solid fa-rotate-left"></i> Go
                            back</a>
                        <span>Estimated Payroll</span>
                    </p>
                    <div class="dropdown d-none" id="dropdown_export">
                        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-file-export"></i> Export
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" id="btn_export_xlsx" href="#!">XLSX</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="mx-auto px-3" style="box-sizing: border-box">
        {{-- Headings --}}
        <div class="row w-100 m-2">
            <div class="col-12 mt-2">
                <div class="row border border-secondary rounded w-100 color_principal text-white">
                    <div class="col-4 px-3 py-2">
                        <span>Employee name / ID</span>
                    </div>
                    <div class="col-2 px-3 py-2">
                        Total Hours
                    </div>
                    <div class="col-2 px-3 py-2">
                        Hourly Rate
                    </div>
                    <div class="col-2 px-3 py-2">
                        Estimated Wage
                    </div>
                    <div class="col-2 px-3 py-2">
                        Estimated Total
                    </div>
                </div>
            </div>
        </div>
        {{-- Content --}}
        <div class="row w-100 m-2" id="contenedor_empleados">
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
@vite(['resources/js/reports/estimated-payroll.js'])
@endsection
