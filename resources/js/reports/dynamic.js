import {
	buildEmployeeElement,
	_createElementFromHTML,
	cargarSelectLocacionesPorGranjasMultiple,
	createLoader,
	generarLineaEmpleadoSeleccionado
} from "../common.js";
import ListPicker from "../list-picker.js";
import DynamicOptionsPicker from "../dynamic-options-picker.js";

window.listPicker = new ListPicker([], "#select_employees_wrapper");
window.columns = new DynamicOptionsPicker([
	{ value: "cod_usuario", text: "User ID" },
	{ value: "nombre_completo", text: "Full Name" },
	{ value: "identidad", text: "ID" },
	{ value: "email", text: "Email" },
	{ value: "telefono", text: "Phone" },
	{ value: "cod_tipo_usuario", text: "User Type ID" },
	{ value: "tipo_usuario", text: "User Type" },
	{ value: "pin", text: "Pin" },
	{ value: "qcpin", text: "Qcpin" },
	{ value: "pay_rate", text: "User Pay Rate" },
	{ value: "categoria", text: "Category" },
	{ value: "cod_location", text: "Location ID" },
	{ value: "location", text: "Location" },
	{ value: "cod_farms", text: "Farm ID" },
	{ value: "farm", text: "Farm" },
	{ value: "horas", text: "Hours" },
	{ value: "escaneos", text: "Scans" },
]);
window.group_by = new DynamicOptionsPicker([
	{ value: "cod_usuario", text: "User ID" },
	{ value: "cod_tipo_usuario", text: "User type" },
	{ value: "categoria", text: "User Category" },
	{ value: "cod_farm_ci", text: "Farm" },
	{ value: "cod_location_ci", text: "Location" }
], "#dynamic-group-by-picker-select", "#dynamic-group-by-selected-wrapper");

document.addEventListener("DOMContentLoaded", function () {

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
	}).on('dp.hide', function (e) {
		cargarEmpleadosEntreLasFechasGranjaLocationMultiple(
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
		cargarEmpleadosEntreLasFechasGranjaLocationMultiple(
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

	$('#cod_farm_reporte_hours').on('change', () => {
		cargarSelectLocacionesPorGranjasMultiple(
			'cod_location_reporte_hours',
			$('#cod_farm_reporte_hours').val(),
			() => {
				$('#cod_location_reporte_hours').selectpicker('destroy');
				$('#cod_location_reporte_hours').selectpicker();
			},
			false
		);
		cargarEmpleadosEntreLasFechasGranjaLocationMultiple(
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

	$('#cod_location_reporte_hours').on('change', () => {
		cargarEmpleadosEntreLasFechasGranjaLocationMultiple(
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

	$('#cod_category_reporte').on('change', () => {
		cargarEmpleadosEntreLasFechasGranjaLocationMultiple(
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

	cargarEmpleadosEntreLasFechasGranjaLocationMultiple(
		$('#initial_date').val(),
		$('#final_date').val(),
		$('#cod_farm_reporte_hours').val(),
		$('#cod_location_reporte_hours').val(),
		$('#cod_category_reporte').val(),
		() => {
			$('#list_empleados_cantidad').html(`(${window.listPicker.items.length} found)`);
		}
	);

	$("#btn_modal_save_selected").on('click', function () {
		refrescarListaEmpleadosSeleccionados(
			"#list_empleados",
			window.listPicker.selected()
		);
		$('#modal_seleccionar_empleados').modal('hide');
	});

	$('#btn_generate_report').on('click', generarReporte);

});

function cargarEmpleadosEntreLasFechasGranjaLocationMultiple(
	initial_date,
	final_date,
	cod_farm,
	cod_location,
	cod_categories = [],
	oncomplete = () => { }
) {

	window.listPicker.destroy();

	$.ajax({
		type: "GET",
		url: window.location.origin + "/api/v1/empleado_administracion/searchEmpleadosAsJsonMultiple",
		data: {
			query: "",
			fecha_inicial: initial_date,
			fecha_final: final_date,
			cod_farm: JSON.stringify(cod_farm),
			cod_location: JSON.stringify(cod_location),
			cod_category: JSON.stringify(cod_categories)
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

function generarReporte() {

	let initial_date = new Date($('#initial_date').val());
	let final_date = new Date($('#final_date').val());

	if (initial_date > final_date) {
		Swal.fire('The initial date must be earlier than the final date.');
		return false;
	}

	if (window.listPicker.selected().length == 0) {
		Swal.fire('Please select at least one employee.');
		return false;
	}

	// validamos que los campos columns y groups sean correctos
	let validationResponse = columnsAndGroupsValid();
	if (!validationResponse.valid) {
		Swal.fire({
			icon: "warning",
			title: validationResponse.title,
			text: validationResponse.message,
		});
		return false;
	}

	let buttonContent = $(this).html();
	$(this).html(createLoader());
	$(this).prop('disabled', 'disabled');

	$.ajax({
		type: 'POST',
		// url: window.location.origin + "/public/api/v1/reportes/obtenerDynamicReportData",
		url: window.location.origin + "/api/v1/reportes/obtenerDynamicReportData",
		data: {
			fecha_inicial: $('#initial_date').val(),
			fecha_final: $('#final_date').val(),
			cod_farm: JSON.stringify($('#cod_farm_reporte_hours').val()),
			cod_location: JSON.stringify($('#cod_location_reporte_hours').val()),
			cod_category: JSON.stringify($('#cod_category_reporte').val()),
			empleados: JSON.stringify(window.listPicker.selected()),
			columns: JSON.stringify(window.columns.selected()),
			group_by: JSON.stringify(window.group_by.selected())
		},
		success: (res) => {
			buildReporte(res.data);
			// Mostramos dropdown de export to excel and txt
			$('#dropdown_export').removeClass('d-none');
			$('#btn_export_xlsx').off('click').on('click', () => {
				exportarArchivo(
					$('#initial_date').val(),
					$('#final_date').val(),
					JSON.stringify($('#cod_farm_reporte_hours').val()),
					JSON.stringify($('#cod_location_reporte_hours').val()),
					JSON.stringify($('#cod_category_reporte').val()),
					JSON.stringify(window.listPicker.selected()),
					JSON.stringify(window.columns.selected()),
					JSON.stringify(window.group_by.selected())
				);
			});
			// $('#btn_export_txt').off('click').on('click', ()=>{
			//     exportarArchivo(
			//         $('#initial_date').val(),
			//         $('#final_date').val(),
			//         JSON.stringify(window.listPicker.selected()),
			//         $('#cod_farm_reporte_hours').val(),
			//         $('#cod_location_reporte_hours').val(),
			//         'txt'
			//     );
			// });
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

function buildReporte(
	data,
	query_contenedor = "#contenedor_reporte",
	query_heading = "#heading_wrapper",
	query_heading_columns = "#heading_columns"
) {
	// Variables
	let contenedor = $(query_contenedor);
	let headingWrapper = $(query_heading);
	let headingColumns = $(query_heading_columns);

	// Process
	contenedor.empty();
	headingWrapper.removeClass('d-none');
	headingColumns.empty();

	//Build columns
	window.columns.selectedRaw().forEach((c) => {
		headingColumns.append(`
            <div class="col px-3 py-2" style="width: ${100 / window.columns.selected().length}%">
                <div style="font-size: 0.8em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${c.text}</div>
            </div>
        `);
	});

	data.forEach((usuario) => {

		let cols = "";
		Object.entries(usuario).forEach(([key, value]) => {
			cols += `
                <div class="col px-3 py-2" style="width: ${100 / Object.entries(usuario).length}%">
                    <div style="font-size: 0.8em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${value}</div>
                </div>
            `;
		});

		let row = `
            <div class="col-12">
                <div class="row w-100 border-bottom">
                    ${cols}
                </div>
            </div>
        `;

		contenedor.append(row);

	});
}

function columnsAndGroupsValid() {

	// Asegurarse que haya seleccionado al menos una columna
	if (window.columns.selected().length == 0) {
		return {
			valid: false,
			title: "Columns warning",
			message: "Please select at least one column."
		};
	}

	// Asegurarse que no se seleccionen hours y scans en el mismo llamado
	let hoursScansArray = window.columns.selectedRaw().filter((c) => {
		if (c.value == "horas" || c.value == "escaneos") {
			return c;
		}
	});
	if (hoursScansArray.length > 1) {
		return {
			valid: false,
			title: "Columns warning",
			message: "You cannot select both hours and scans columns, this will lead to uncertain information. Please remove one of them."
		};
	}

	return {
		valid: true
	}

}

function exportarArchivo(
	fecha_inicial,
	fecha_final,
	cod_farm,
	cod_location,
	cod_category,
	empleados,
	columns,
	group_by,
	formato = 'xlsx'
) {
	// Crear el formulario dinámicamente
	var form = $('<form>', {
		// action: window.location.origin + '/public/v1/empleado/exportDynamicReport',  // URL del endpoint
		action: window.location.origin + '/v1/empleado/exportDynamicReport',  // URL del endpoint
		method: 'POST',  // Método POST
		target: '_blank'  // Abrir en una nueva ventana o pestaña
	});

	// Datos a enviar en el formulario
	var formData = {
		_token: $('meta[name="csrf-token"]').attr('content'),  // Token CSRF
		fecha_inicial: fecha_inicial,
		fecha_final: fecha_final,
		cod_farm: cod_farm,
		cod_location: cod_location,
		cod_category: cod_category,
		empleados: empleados,
		columns: columns,
		group_by: group_by,
		formato: formato
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
