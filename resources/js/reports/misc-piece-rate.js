import {
    generarLineaEmpleadoSeleccionado,
    _createElementFromHTML,
    cargarSelectLocacionesPorGranja,
    createLoader
} from "../common.js";
import ListPicker from "../list-picker.js";

window.listPicker = new ListPicker([], "#select_employees_wrapper");

document.addEventListener("DOMContentLoaded", ()=>{

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
    }).on('dp.hide', function(e) {
        // cargarCropsEntreLasFechas(
        //     $('#initial_date').val(),
        //     $('#final_date').val(),
        //     $('#cod_farm_reporte_piece_rate').val(),
        //     ()=>{
                cargarEmpleadosEntreLasFechasGranja(
                    $('#initial_date').val(),
                    $('#final_date').val(),
                    $('#cod_farm_reporte_piece_rate').val(),
                    // $('#cod_crops_reporte_piece_rate').val(),
                    ()=>{
                        $('#list_empleados_cantidad').html(`(${window.listPicker.items.length} found)`);
                    }
                );
        //     }
        // );
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
        // cargarCropsEntreLasFechas(
        //     $('#initial_date').val(),
        //     $('#final_date').val(),
        //     $('#cod_farm_reporte_piece_rate').val(),
        //     ()=>{
                cargarEmpleadosEntreLasFechasGranja(
                    $('#initial_date').val(),
                    $('#final_date').val(),
                    $('#cod_farm_reporte_piece_rate').val(),
                    // $('#cod_crops_reporte_piece_rate').val(),
                    ()=>{
                        $('#list_empleados_cantidad').html(`(${window.listPicker.items.length} found)`);
                    }
                );
        //     }
        // );
    });

    $('#cod_farm_reporte_piece_rate').on('change', ()=>{
        // cargarCropsEntreLasFechas(
        //     $('#initial_date').val(),
        //     $('#final_date').val(),
        //     $('#cod_farm_reporte_piece_rate').val(),
        //     ()=>{
                cargarEmpleadosEntreLasFechasGranja(
                    $('#initial_date').val(),
                    $('#final_date').val(),
                    $('#cod_farm_reporte_piece_rate').val(),
                    // $('#cod_crops_reporte_piece_rate').val(),
                    ()=>{
                        $('#list_empleados_cantidad').html(`(${window.listPicker.items.length} found)`);
                    }
                );
        //     }
        // );
    });

    // $('#cod_crops_reporte_piece_rate').on('change', ()=>{
    //     cargarEmpleadosEntreLasFechasGranja(
    //         $('#initial_date').val(),
    //         $('#final_date').val(),
    //         $('#cod_farm_reporte_piece_rate').val(),
    //         // $('#cod_crops_reporte_piece_rate').val(),
    //         ()=>{
    //             $('#list_empleados_cantidad').html(`(${window.listPicker.items.length} found)`);
    //         }
    //     );
    // })

    $("#btn_modal_save_selected").on('click', function(){
        refrescarListaEmpleadosSeleccionados(
            "#list_empleados",
            window.listPicker.selected()
        );
        $('#modal_seleccionar_empleados').modal('hide');
    });

    cargarEmpleadosEntreLasFechasGranja(
        $('#initial_date').val(),
        $('#final_date').val(),
        $('#cod_farm_reporte_piece_rate').val(),
        // $('#cod_crops_reporte_piece_rate').val(),
        ()=>{
            $('#list_empleados_cantidad').html(`(${window.listPicker.items.length} found)`);
        }
    );

    // cargarCropsEntreLasFechas(
    //     $('#initial_date').val(),
    //     $('#final_date').val(),
    //     $('#cod_farm_reporte_piece_rate').val()
    // );
    // cargarTodosLosCrops();

    $('#btn_generate_report').on('click', generarReporte);

});

function generarReporte(){

    if(window.listPicker.selected().length <= 0){
        Swal.fire("Please select at least one employee.");
        return false;
    }

    let employees = [];
    window.listPicker.selected().forEach((e)=>{
        employees.push(e.id);
    });

    let buttonContent = $(this).html();
    $(this).html(createLoader());
    $(this).prop('disabled', 'disabled');

    $.ajax({
        type: "POST",
        url: window.location.origin + "/api/v1/reportes/obtenerMiscRateReportData",
        data: {
            employees: employees,
            start_date: $('#initial_date').val(),
            end_date: $('#final_date').val(),
            // crop_ids: $('#cod_crops_reporte_piece_rate').val(),
            farm_ids: $('#cod_farm_reporte_piece_rate').val()
        },
        success: (res)=>{
            // Mostramos dropdown de export to excel and txt
            $('#dropdown_export').removeClass('d-none');
            buildReporte(res.data);
            $('#btn_export_xlsx').off('click').on('click', ()=>{
                exportarArchivo(
                    $('#initial_date').val(),
                    $('#final_date').val(),
                    window.listPicker.selected(),
                    $('#cod_farm_reporte_piece_rate').val(),
                    // $('#cod_crops_reporte_piece_rate').val(),
                    'xlsx'
                );
            });
            $('#btn_export_csv').off('click').on('click', ()=>{
                exportarArchivo(
                    $('#initial_date').val(),
                    $('#final_date').val(),
                    window.listPicker.selected(),
                    $('#cod_farm_reporte_piece_rate').val(),
                    // $('#cod_crops_reporte_piece_rate').val(),
                    'csv'
                );
            });
        },
        fail: (err)=>{
            console.error(err);
        },
        complete: ()=>{
            $(this).html(buttonContent);
            $(this).prop('disabled', false);
        }
    });
}

function cargarEmpleadosEntreLasFechasGranja(
    initial_date,
    final_date,
    cod_farm = [],
    oncomplete = ()=>{}
){

    window.listPicker.destroy();
    // console.log([
    //     initial_date, final_date, cod_farm, cod_crop
    // ]);

    $.ajax({
        type: "POST",
        url: window.location.origin + "/api/v1/empleado_administracion/searchEmpleadosPorGranjaMisc",
        data: {
            fecha_inicial: initial_date,
            fecha_final: final_date,
            cod_farm: JSON.stringify(cod_farm),
            // cod_crop: JSON.stringify(cod_crop)
        },
        success: (res)=>{
            let items = [];
            if(res.employees.length){
                res.employees.forEach((e)=>{
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
        fail: (err)=>{
            console.log(err);
        }
    })

}

function buildReporte(data, query_contenedor = "#contenedor_empleados"){
    let contenedor = $(query_contenedor);
    contenedor.empty();
    let suma = 0;
    console.log(data);
    data.forEach((fila)=>{
        let row = `
            <div class="col-12">
                <div class="row w-100 border-bottom">
                    <div class="col-2 px-2 py-1">
                        <div>
                            <small>${fila.nombre_empleado}</small>
                        </div>
                        <small style="color:gray;font-size:0.8em">
                            Pin: ${fila.pin}
                        </small><br/>
                    </div>
                    <div class="col-1 px-2 py-1">
                        <div><small>${fila.farm}</small></div>
                        <small>${fila.fecha_job}</small>
                    </div>
                    <div class="col-1 px-2 py-1">
                        <small>${fila.activity}</small>
                    </div>
                    <div class="col-2 px-2 py-1">
                        <small><span style="color:gray">Commodity:</span> ${fila.nombre_semilla != null ? fila.nombre_semilla : "Not used"}</small><br/>
                        <small><span style="color:gray">Variety:</span> ${fila.nombre_categoria != null ? fila.nombre_categoria : "Not used"}</small><br/>
                        <small><span style="color:gray">Age:</span> ${fila.edad != null ? fila.edad : "Not used"}</small>
                    </div>
                    <div class="col-2 px-2 py-1">
                        <small>
                            <span style="color:gray">Fields:</span> ${fila.fields}
                        </small><br/>
                        <small>
                            <span style="color:gray">Blocks:</span> ${fila.bloques}
                        </small><br/>
                        <small>
                            <span style="color:gray">Acreage:</span> ${fila.acreage}
                        </small>
                    </div>
                    <div class="col-1 px-2 py-1">
                        <div><small>${fila.pwhr_horas}</small></div>
                        <small>${fila.pwhr} Hours</small>
                    </div>
                    <div class="col-1 px-2 py-1">
                        <div><small>${fila.units}</small></div>
                        <small>${fila.pwhr_rate}/h</small>
                        <div><small>Original: ${fila.units_original}</small></div>
                    </div>
                    <div class="col-1 px-2 py-1">
                        <small>$${fila.pay_rate ?? 0}</small>
                    </div>
                    <div class="col-1 px-2 py-1">
                        <small>$${fila.total}</small>
                    </div>
                </div>
            </div>
        `;
        contenedor.append(row);
        suma += parseFloat(fila.total);
    });
    contenedor.append(`
        <div style="font-size:20px;text-align:right;position:fixed;bottom:0;right:0;margin-left:300px;width:calc(100vw - 300px);padding:20px;background-color:white;box-shadow:5px 0px 5px rgba(0,0,0,0.3);">
            <span><b>Total: </b>$${suma.toFixed(2)}</span>
        </div>
    `);
}

function numberFormat(value, decimals = 0, decimalSeparator = '.', thousandsSeparator = ',') {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    }).format(value).replace('.', decimalSeparator).replace(/,/g, thousandsSeparator);
}

// function cargarCropsEntreLasFechas(initial_date, final_date, granjas = [], oncomplete = ()=>{}){
//     $.ajax({
//         type: "POST",
//         url: window.location.origin + "/api/v1/reportes/obtenerCropsEntreFechasAsJson",
//         data: {
//             fecha_inicial: initial_date,
//             fecha_final: final_date,
//             granjas: JSON.stringify(granjas)
//         },
//         success: (res)=>{
//             asignarOpcionesCropsDropdown(res.crops);
//             oncomplete();
//         },
//         fail: (err)=>{
//             console.log(err);
//         }
//     })
// }

function cargarTodosLosCrops(oncomplete = ()=>{}){
    $.ajax({
        type: "POST",
        url: window.location.origin + "/api/v1/reportes/obtenerTodosLosCrops",
        success: (res)=>{
            asignarOpcionesCropsDropdown(res.crops);
            oncomplete();
        },
        fail: (err)=>{
            console.log(err);
        }
    })
}

// function asignarOpcionesCropsDropdown(crops = []){
//     $('#cod_crops_reporte_piece_rate').empty();
//     crops.forEach((c)=>{
//         $('#cod_crops_reporte_piece_rate').append(`<option value="${c.cod_inventario}">${c.nombre_semilla}</option>`);
//     });
//     $('#cod_crops_reporte_piece_rate').selectpicker('destroy');
//     $('#cod_crops_reporte_piece_rate').selectpicker();
// }

function refrescarListaEmpleadosSeleccionados(selector, items){
    let wrapper = document.querySelector(selector);
    if(wrapper){
        wrapper.innerHTML = "";
        if(items.length > 0){
            items.forEach((i, index)=>{
                wrapper.appendChild(generarLineaEmpleadoSeleccionado(i.name, index));
            });
        }else{
            wrapper.innerHTML = "<small class='text-secondary'>No employees selected</small>"
        }
    }
}

function exportarArchivo(initial_date, final_date, employees, farms, formato = 'xlsx'){
    // Crear el formulario dinámicamente
    var form = $('<form>', {
        action: window.location.origin + '/v1/empleado/exportMiscPieceRateFile',  // URL del endpoint
        method: 'POST',  // Método POST
        target: '_blank'  // Abrir en una nueva ventana o pestaña
    });

    // Datos a enviar en el formulario
    var formData = {
        _token: $('meta[name="csrf-token"]').attr('content'),  // Token CSRF
        employees: JSON.stringify(employees),
        start_date: initial_date,
        end_date: final_date,
        farm_ids: JSON.stringify(farms),
        // crop_ids: JSON.stringify(crops),
        format: formato
    };

    // Añadir los campos al formulario
    $.each(formData, function(key, value) {
        form.append($('<input>', { type: 'hidden', name: key, value: value }));
    });

    // Añadir el formulario al body del documento
    $('body').append(form);

    // Enviar el formulario
    form.submit();

    // Eliminar el formulario después de enviarlo (limpieza)
    form.remove();
}
