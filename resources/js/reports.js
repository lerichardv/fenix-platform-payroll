import {
	buildEmployeeElement,
	_createElementFromHTML,
	cargarSelectLocacionesPorGranja,
	createLoader
} from "./common.js";
import ListPicker from "./list-picker.js";

window.listPicker = new ListPicker([], "#select_employees_wrapper");

document.addEventListener('DOMContentLoaded', () => {

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
		cargarEmpleadosEntreLasFechasGranjaLocation(
			$('#initial_date').val(),
			$('#final_date').val(),
			$('#cod_farm_reporte_hours').val(),
			$('#cod_location_reporte_hours').val(),
			$('#cod_category_reporte').val(),
			() => {
				$('#list_empleados_cantidad').html(`(${window.listPicker.items.length} found)`);
			}
		);
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
		cargarEmpleadosEntreLasFechasGranjaLocation(
			$('#initial_date').val(),
			$('#final_date').val(),
			$('#cod_farm_reporte_hours').val(),
			$('#cod_location_reporte_hours').val(),
			$('#cod_category_reporte').val(),
			() => {
				$('#list_empleados_cantidad').html(`(${window.listPicker.items.length} found)`);
			}
		);
	});

	$("#btn_modal_save_selected").on('click', function () {
		refrescarListaEmpleadosSeleccionados(
			"#list_empleados",
			window.listPicker.selected()
		);
		$('#modal_seleccionar_empleados').modal('hide');
	});

	$('#cod_category_reporte').on('change', function () {
		cargarEmpleadosEntreLasFechasGranjaLocation(
			$('#initial_date').val(),
			$('#final_date').val(),
			$('#cod_farm_reporte_hours').val(),
			$('#cod_location_reporte_hours').val(),
			$('#cod_category_reporte').val(),
			() => {
				$('#list_empleados_cantidad').html(`(${window.listPicker.items.length} found)`);
			}
		);
	});

	$('#btn_generate_report').on('click', generarReporte);

	$('#cod_farm_reporte_hours').on('change', () => {
		cargarSelectLocacionesPorGranja(
			'cod_location_reporte_hours',
			$('#cod_farm_reporte_hours').val()
		);
	});

	refrescarListaEmpleadosSeleccionados(
		"#list_empleados",
		window.listPicker.selected()
	);

	cargarEmpleadosEntreLasFechasGranjaLocation(
		$('#initial_date').val(),
		$('#final_date').val(),
		$('#cod_farm_reporte_hours').val(),
		$('#cod_location_reporte_hours').val(),
		() => {
			$('#list_empleados_cantidad').html(`(${window.listPicker.items.length} found)`);
		}
	);

});

function generarReporte() {

	let initial_date = new Date($('#initial_date').val());
	let final_date = new Date($('#final_date').val());

	if (initial_date >= final_date) {
		Swal.fire('The initial date must be earlier than the final date.');
		return false;
	}

	if (window.listPicker.selected().length == 0) {
		Swal.fire('Please select at least one employee.');
		return false;
	}

	let buttonContent = $(this).html();
	$(this).html(createLoader());
	$(this).prop('disabled', 'disabled');

	$.ajax({
		type: 'POST',
		// url: window.location.origin + "/public/api/v1/reportes/obtener_reporte_empleados_horas",
		url: window.location.origin + "/api/v1/reportes/obtener_reporte_empleados_horas",
		data: {
			initial_date: $('#initial_date').val(),
			final_date: $('#final_date').val(),
			employees: JSON.stringify(window.listPicker.selected()),
			cod_farm: $('#cod_farm_reporte_hours').val(),
			cod_location: $('#cod_location_reporte_hours').val()
		},
		success: (res) => {
			buildReporte(res.usuarios);
			// Mostramos dropdown de export to excel and txt
			$('#dropdown_export').removeClass('d-none');
			$('#btn_export_xlsx').off('click').on('click', () => {
				exportarArchivo(
					$('#initial_date').val(),
					$('#final_date').val(),
					JSON.stringify(window.listPicker.selected()),
					$('#cod_farm_reporte_hours').val(),
					$('#cod_location_reporte_hours').val(),
					'xlsx'
				);
			});
			$('#btn_export_txt').off('click').on('click', () => {
				exportarArchivo(
					$('#initial_date').val(),
					$('#final_date').val(),
					JSON.stringify(window.listPicker.selected()),
					$('#cod_farm_reporte_hours').val(),
					$('#cod_location_reporte_hours').val(),
					'txt'
				);
			});
		},
		fail: (err) => {
			console.log(err);
		},
		complete: () => {
			$(this).html(buttonContent);
			$(this).prop('disabled', false);
		}
	})

}

function exportarArchivo(initial_date, final_date, employees, cod_farm, cod_location, formato = 'xlsx') {
	// Crear el formulario dinámicamente
	var form = $('<form>', {
		// action: window.location.origin + '/public/v1/empleado/exportEmployeeHoursLocation',  // URL del endpoint
		action: window.location.origin + '/v1/empleado/exportEmployeeHoursLocation',  // URL del endpoint
		method: 'POST',  // Método POST
		target: '_blank'  // Abrir en una nueva ventana o pestaña
	});

	// Datos a enviar en el formulario
	var formData = {
		_token: $('meta[name="csrf-token"]').attr('content'),  // Token CSRF
		initial_date: initial_date,
		final_date: final_date,
		cod_farm: cod_farm,
		cod_location: cod_location,
		employees: employees,
		format: formato
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

function cargarEmpleadosEntreLasFechasGranjaLocation(
	initial_date,
	final_date,
	cod_farm,
	cod_location,
	cod_category,
	oncomplete = () => { }
) {

	window.listPicker.destroy();

	$.ajax({
		type: "GET",
		url: window.location.origin + "/api/v1/empleado_administracion/searchEmpleadosAsJson",
		data: {
			query: "",
			fecha_inicial: initial_date,
			fecha_final: final_date,
			cod_farm: cod_farm,
			cod_location: cod_location,
			cod_category: cod_category
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

function buildReporte(data, query_contenedor = "#contenedor_empleados") {
	let contenedor = $(query_contenedor);
	contenedor.empty();
	data.forEach((usuario) => {
		if (usuario.horas_trabajadas != 0) {
			let granjasCol = ``;
			usuario.granjas.forEach((granja) => {
				if (granja.horas_trabajadas != 0) {
					granjasCol += `
                        <div>
                            <b>${granja.farm}:</b> ${roundHours(granja.horas_trabajadas)}
                        </div>
                    `;
					granja.locations.forEach((location) => {
						if (location.horas_trabajadas != 0) {
							granjasCol += `
                                <div style="margin-left: 20px">
                                    ${location.location}: ${roundHours(location.horas_trabajadas)}
                                </div>
                            `;
						}
					});
				}
			});
			let row = `
                <div class="col-12">
                    <div class="row w-100 border-bottom">
                        <div class="col-6 px-3 py-2">
                            <span>${usuario.pin}: ${usuario.apellido_1}${usuario.apellido_2 ? `-${usuario.apellido_2}` : ""}, ${usuario.nombre_1} - ${usuario.es_veterano == 0 ? "Regular" : (usuario.es_veterano == 1 ? "Veteran" : "H2A")}</span>
                        </div>
                        <div class="col-4 px-3 py-2">
                            <span>${granjasCol}</span>
                        </div>
                        <div class="col-2 px-3 py-2">
                            <span><b>Total: </b>${roundHours(usuario.horas_trabajadas)}</span>
                        </div>
                    </div>
                </div>
            `;
			contenedor.append(row);
		}
	});
}

function roundHours(hours) {
	// Redondear a la unidad más cercana
	let rounded = Math.round(hours * 2) / 2;
	return rounded;
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

function generarLineaEmpleadoSeleccionado(texto, index = null, onclick = () => { }) {
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
