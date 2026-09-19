import {
    generarLineaEmpleadoSeleccionado,
    _createElementFromHTML,
    createLoader,
    cargarSelectLocacionesPorGranjasMultiple
} from "../common.js";
import ListPicker from "../list-picker.js";

document.addEventListener("DOMContentLoaded", ()=>{

    $('.selectpicker').selectpicker();

    $('#cod_farm').on('change', ()=>{
        cargarSelectLocacionesPorGranjasMultiple(
            'cod_location',
            $('#cod_farm').val(),
            ()=>{
                $('#cod_location').selectpicker('destroy');
                $('#cod_location').selectpicker();
            },
            false
        );
    });

    cargarSelectLocacionesPorGranjasMultiple(
        'cod_location',
        $('#cod_farm').val(),
        ()=>{
            $('#cod_location').selectpicker('destroy');
            $('#cod_location').selectpicker();
        },
        false
    );

    $('#btn_generate_report').on('click', generarReporte);

});

function generarReporte(){

    if($('#week').val() == ''){
        Swal.fire("Please select the week.");
        return false;
    }

    let buttonContent = $(this).html();
    $(this).html(createLoader());
    $(this).prop('disabled', 'disabled');

    $.ajax({
        type: "POST",
        url: window.location.origin + "/api/v1/reportes/obtenerLocationExecutiveData",
        data: {
            week: $('#week').val(),
            farms: $('#cod_farm').val() ?? [],
            locations: $('#cod_location').val() ?? []
        },
        success: (res)=>{
            // console.log(res);
            buildReporte(res.data);
            $('#dropdown_export').removeClass('d-none');
            $('#btn_export_xlsx').off('click').on('click', ()=>{
                exportarArchivo(
                    $('#week').val(),
                    $('#cod_farm').val(),
                    $('#cod_location').val(),
                    'xlsx'
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

function buildReporte(data, query_contenedor = "#contenedor_empleados"){
    let contenedor = $(query_contenedor);
    contenedor.empty();
    let totales = {dia1: 0, dia2: 0, dia3: 0, dia4: 0, dia5: 0, dia6: 0, dia7: 0, granTotal: 0};
    data.forEach((fila)=>{
        totales.dia1 += fila.dia1 ? parseFloat(fila.dia1) : 0;
        totales.dia2 += fila.dia2 ? parseFloat(fila.dia2) : 0;
        totales.dia3 += fila.dia3 ? parseFloat(fila.dia3) : 0;
        totales.dia4 += fila.dia4 ? parseFloat(fila.dia4) : 0;
        totales.dia5 += fila.dia5 ? parseFloat(fila.dia5) : 0;
        totales.dia6 += fila.dia6 ? parseFloat(fila.dia6) : 0;
        totales.dia7 += fila.dia7 ? parseFloat(fila.dia7) : 0;
        totales.granTotal += fila.total ? parseFloat(fila.total) : 0;
        let row = `
            <div class="col-12">
                <div class="row w-100 border-bottom">
                    <div class="col-4 px-2 py-2">
                        ${fila.name}
                    </div>
                    <div class="col-1 px-2 py-2">
                        ${fila.dia1 ? parseFloat(fila.dia1).toFixed(2) : '0.00'}
                    </div>
                    <div class="col-1 px-2 py-2">
                        ${fila.dia2 ? parseFloat(fila.dia2).toFixed(2) : '0.00'}
                    </div>
                    <div class="col-1 px-2 py-2">
                        ${fila.dia3 ? parseFloat(fila.dia3).toFixed(2) : '0.00'}
                    </div>
                    <div class="col-1 px-2 py-2">
                        ${fila.dia4 ? parseFloat(fila.dia4).toFixed(2) : '0.00'}
                    </div>
                    <div class="col-1 px-2 py-2">
                        ${fila.dia5 ? parseFloat(fila.dia5).toFixed(2) : '0.00'}
                    </div>
                    <div class="col-1 px-2 py-2">
                        ${fila.dia6 ? parseFloat(fila.dia6).toFixed(2) : '0.00'}
                    </div>
                    <div class="col-1 px-2 py-2">
                        ${fila.dia7 ? parseFloat(fila.dia7).toFixed(2) : '0.00'}
                    </div>
                    <div class="col-1 px-2 py-2">
                        <b>${fila.total ? parseFloat(fila.total).toFixed(2) : '0.00'}</b>
                    </div>
                </div>
            </div>
        `;
        contenedor.append(row);
    });
    let rowTotales = `
    <div class="col-12">
        <div class="row w-100 border-top border-dark">
            <div class="col-4 px-2 py-2">
                <b>Total</b>
            </div>
            <div class="col-1 px-2 py-2">
                <b>${parseFloat(totales.dia1).toFixed(2)}</b>
            </div>
            <div class="col-1 px-2 py-2">
                <b>${parseFloat(totales.dia2).toFixed(2)}</b>
            </div>
            <div class="col-1 px-2 py-2">
                <b>${parseFloat(totales.dia3).toFixed(2)}</b>
            </div>
            <div class="col-1 px-2 py-2">
                <b>${parseFloat(totales.dia4).toFixed(2)}</b>
            </div>
            <div class="col-1 px-2 py-2">
                <b>${parseFloat(totales.dia5).toFixed(2)}</b>
            </div>
            <div class="col-1 px-2 py-2">
                <b>${parseFloat(totales.dia6).toFixed(2)}</b>
            </div>
            <div class="col-1 px-2 py-2">
                <b>${parseFloat(totales.dia7).toFixed(2)}</b>
            </div>
            <div class="col-1 px-2 py-2">
                <b>${parseFloat(totales.granTotal).toFixed(2)}</b>
            </div>
        </div>
    </div>
    `;
    contenedor.append(rowTotales);
    // contenedor.append(`
    //     <div style="font-size:20px;text-align:right;position:fixed;bottom:0;right:0;margin-left:300px;width:calc(100vw - 300px);padding:20px;background-color:white;box-shadow:5px 0px 5px rgba(0,0,0,0.3);">
    //         <span><b>Total: </b>$${suma.toFixed(2)}</span>
    //     </div>
    // `);
}

function exportarArchivo(week, farms, locations, formato = 'xlsx'){
    // Crear el formulario dinámicamente
    var form = $('<form>', {
        action: window.location.origin + '/v1/empleado/exportLocationExecutiveSummary',  // URL del endpoint
        method: 'POST',  // Método POST
        target: '_blank'  // Abrir en una nueva ventana o pestaña
    });

    // Datos a enviar en el formulario
    var formData = {
        _token: $('meta[name="csrf-token"]').attr('content'),  // Token CSRF
        week: week,
        farms: JSON.stringify(farms ?? []),
        locations: JSON.stringify(locations ?? []),
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
