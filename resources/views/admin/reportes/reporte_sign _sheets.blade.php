<style>
    #contendor_lista_empleados {
        overflow: auto;
        height: 40vh !important;
    }

    .desplegar_lista {
        color: #0d6efd;
        
    }

    @media (min-height: 1200px) {
        #contendor_lista_empleados {
            overflow-y: scroll;
            max-height: 800px;
            min-height: 450px;
        }
    }
</style>
@extends('layouts.app')

@section('sidebar')
    <div class="w-100">
        <div class="row">

            <div class="col-md-12 pt-3">
                <div>
                    <label for="week">Week</label>
                </div>
                <select class="selectpicker w-100" data-style-base="btn color_principal" data-actions-box="true" name="week"
                    id="week">
                    @foreach ($weeks as $week)
                        <option value="{{ json_encode($week->dates) }}">{{ $week->identifier }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-12 pt-3">
                <div>
                    <label for="cod_farm" class="form-label">Farm</label>
                </div>
                <select class="selectpicker w-100 selectpicker" data-style-base="btn color_principal"
                    data-actions-box="true" name="cod_farm" id="cod_farm" multiple>
                    @foreach ($listaGranjas as $granja)
                        <option value="{{ $granja->cod_farms }}">{{ $granja->farm }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-12 pt-3">
                <div>
                    <label for="cod_location">Location</label>
                </div>
                <select class="selectpicker w-100" data-style-base="btn color_principal" data-actions-box="true"
                    name="cod_location" id="cod_location" multiple></select>
            </div>
            <div class="col-md-12 pt-3">
                <label for="cod_categoria_empleado">Category</label>
                <select class="selectpicker w-100" data-style-base="btn color_principal" data-actions-box="true"
                    name="cod_categoria_empleado" id="cod_categoria_empleado" multiple>
                    <option value="0" selected>Regular</option>
                    <option value="1">Veteran</option>
                    <option value="2">H2A</option>
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
            <button id="select_all" class="btn btn-primary my-2" style="width: 71%; margin-left: 15%;"
                onclick="selectAllEmployees()">Select All</button>
            <div id="employee_list">
            </div>

        </div>
    </div>
    <div class="w-100">
        <div class="row mt-4">
            <div class="col-md-12">
                <button class="btn btn-success w-100" id="btn_generate_report"><i class="fa-solid fa-file-lines"></i>
                    Generate report</button>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div id="contenedor_principal" style="margin-left:300px;width:calc(100vw-300px);height:100vh">
        <div id="contenedor_titulo_pagina" class="mx-auto ps-3"
            style="border-bottom: 1px solid black; max-width: 100vw; background-color: white;">
            <div class="row container-fluid px-2">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <p id="titulo_seccion" class="text-left d-flex gap-3">
                            <a class="btn btn-secondary" href="{{ route('home') }}"><i class="fa-solid fa-rotate-left"></i>
                                Go
                                back</a>
                            <span>Sign Sheets Report</span>
                        </p>
                        <div class="dropdown d-none" id="dropdown_export">
                            <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fa-solid fa-file-export"></i> Export
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" id="btn_export_xlsx" href="#!">Bulk XLSX</a></li>
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
                        <div class="col-2 px-2 py-2">
                            Name
                        </div>
                        <div class="col-1 px-2 py-2">
                            Farm
                        </div>
                        <div class="col-1 px-2 py-2">
                            location
                        </div>
                        {{-- <div class="col-1 px-2 py-2">
                            Pin
                        </div> --}}
                        <div class="col-1 px-2 py-2">
                            Monday
                        </div>
                        <div class="col-1 px-2 py-2">
                            Tuesday
                        </div>
                        <div class="col-1 px-2 py-2">
                            Wednesday
                        </div>
                        <div class="col-1 px-2 py-2">
                            Thursday
                        </div>
                        <div class="col-1 px-2 py-2">
                            Friday
                        </div>
                        <div class="col-1 px-2 py-2">
                            Saturday
                        </div>
                        <div class="col-1 px-2 py-2">
                            Sunday
                        </div>
                        <div class="col-1 px-2 py-2">
                            Total
                        </div>
                    </div>
                </div>
            </div>
            {{-- Content --}}
            <div class="row w-100 m-2 relative accordion accordion-flush" id="contenedor_datos_empleados"
                style="padding-bottom:120px">
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

    <script>
        $(document).ready(function() {
            //      $('#search').trigger('keyup');
            // $('#search').trigger('keyup');
            // $('#search').value;

            // alert('hola');	

            localStorage.removeItem('listadoCodigosEmpleadosSeleccionados');

        });
        var listadoCodigosEmpleadosSeleccionados = [];

        function selectAllEmployees() {
            const checkboxes = document.querySelectorAll('#employee_list input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = true;
            });
            guardarEmpleadosSeleccionados();
        }

        function guardarEmpleadosSeleccionados() {
            var selectedEmployees = [];

            var checkboxes = document.querySelectorAll('.selector_empleado input[type="checkbox"]:checked');
            checkboxes.forEach(function(checkbox) {
                selectedEmployees.push(checkbox.value);
            });

            var searchValue = document.getElementById('search').value;

            if (searchValue == '') {

                listadoCodigosEmpleadosSeleccionados = selectedEmployees.map(employee => ({
                    cod_usuario: employee
                }));
            } else {
                if (searchValue != '') {
                    selectedEmployees.forEach(employee => {
                        if (!listadoCodigosEmpleadosSeleccionados.some(e => e.cod_usuario === employee)) {
                            listadoCodigosEmpleadosSeleccionados.push({
                                cod_usuario: employee
                            });
                        }
                    });
                }
            }

            localStorage.setItem('listadoCodigosEmpleadosSeleccionados', JSON.stringify(
                listadoCodigosEmpleadosSeleccionados));
        }
    </script>
    @vite(['resources/js/reports/sign-sheets-report.js'])
@endsection
