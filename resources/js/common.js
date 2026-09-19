import { isArray } from "jquery";

export function buildEmployeeElement(cod_usuario, nombre, pin, qcpin, onclick = () => { }) {
    let element = `
        <div class="row selector_empleado" data-id="${cod_usuario}">
            <div class="col-md-10 pt-3">
                <label class="elemento_seleccionable">
                    <span style="font-weight: bold;">${nombre}</span> <br>${pin} - ${qcpin}
                </label>
            </div>
        </div>
    `;
    let htmlElement = _createElementFromHTML(element);
    htmlElement.addEventListener("click", () => {
        onclick();
    });
    return htmlElement;
}

export function createLoader() {
    let element = `
        <i class="fa-solid fa-circle-notch fa-2xl fa-spin" style="--fa-animation-duration: 0.7s;"></i>
    `;
    let htmlElement = _createElementFromHTML(element);
    return htmlElement;
}

export function _createElementFromHTML(htmlString) {
    var div = document.createElement('div');
    div.innerHTML = htmlString.trim();
    // Change this to div.childNodes to support multiple top-level nodes.
    return div.firstChild;
}

export function cargarSelectLocacionesPorGranja(id_select, cod_farm, oncomplete = null, incluirAll = true) {
    let url = window.location.origin + '/api/v1/empleado_administracion/listaLocacionesPorGranjas';
    let method = "POST";
    $.ajax({
        method: method,
        url: url,
        // url: 'https://payrollapp.lmfdata.com/api/v1/empleado_administracion/listaLocacionesPorGranjas',
        data: {
            cod_farm: cod_farm
        },
        success: function (response) {

            jQuery.ajaxSetup({ async: false });

            const data = response;
            $('#' + id_select).empty();

            if (data.length > 0) {
                if (incluirAll) { $('#' + id_select).append('<option value="0" selected>All</option>'); }

                $.each(data, function (i, item) {
                    $('#' + id_select).append(`<option value="${item.cod_location}">${item.location}</option>`);
                });
            } else if (cod_farm == 0) {
                if (incluirAll) { $('#' + id_select).append('<option value="0" selected>All</option>'); }
            } else {
                $('#' + id_select).append('<option value="-b" >No locations found</option>');

            }

            jQuery.ajaxSetup({ async: true });

            if (oncomplete != null && typeof oncomplete == "function") {
                oncomplete();
            }
        },
        error: function (xhr) {
            console.error('Error:', xhr.responseText);

        }
    });
}

export function cargarSelectLocacionesPorGranjasMultiple(id_select, cod_farm, oncomplete = null, incluirAll = true, locacionesSeleccionadas = []) {
    let url = window.location.origin + '/api/v1/empleado_administracion/listaLocacionesPorGranjasMultiple';
    let method = "POST";
    $.ajax({
        type: method,
        url: url,
        data: {
            cod_farms: JSON.stringify(cod_farm)
        },
        success: function (response) {

            jQuery.ajaxSetup({ async: false });

            const data = response;
            $('#' + id_select).empty();

            if (data.length > 0) {
                if (incluirAll) { $('#' + id_select).append('<option value="0" selected>All</option>'); }

                $.each(data, function (i, item) {
                    $('#' + id_select).append(`<option value="${item.cod_location}">${item.location}</option>`);
                });
            } else if (cod_farm == 0) {
                if (incluirAll) { $('#' + id_select).append('<option value="0" selected>All</option>'); }
            } else {
                $('#' + id_select).append('<option value="-b" >No locations found</option>');

            }

            console.log("locacionesSeleccionadas", locacionesSeleccionadas);
            if (Array.isArray(locacionesSeleccionadas)) {
                locacionesSeleccionadas.forEach((locacion) => {
                    $('#' + id_select).find(`option[value="${locacion}"]`).prop('selected', true);
                });
                locacionesSeleccionadas = [];
            }
            jQuery.ajaxSetup({ async: true });

            if (oncomplete != null && typeof oncomplete == "function") {
                oncomplete();
            }
        },
        error: function (xhr) {
            console.error('Error:', xhr.responseText);

        }
    });
}

export function generarLineaEmpleadoSeleccionado(texto, index = null, onclick = () => { }) {
    let elementString = `
        <div>
            <small>
                ${index != null ? `${index + 1}. ` : ""}${texto}
            </small>
        </div>
    `;
    let element = _createElementFromHTML(elementString);
    element.addEventListener('click', onclick);
    return element;
}
