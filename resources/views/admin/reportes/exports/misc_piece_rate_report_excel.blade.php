<table>
    <thead>
        <tr>
            <td colspan="14">
                <p><b>Misc Piece Rate Report</b></p>
            </td>
            <td colspan="5">
                Created on {{ $today }}
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <p>{{ $initial_date }}-{{ $final_date }}</p>
            </td>
        </tr>
        <tr>
            <td><b>Supervisor</b></td>
            <td><b>Farm</b></td>
            <td><b>Location</b></td>
            <td><b>Activity</b></td>
            <td><b>Employee Name</b></td>
            <td><b>Employee PIN</b></td>
            <td><b>Date</b></td>
            <td><b>Crop name</b></td>
            <td><b>Variety</b></td>
            <td><b>Age</b></td>
            <td><b>Field(s)</b></td>
            <td><b>Block(s)</b></td>
            <td><b>Acreage</b></td>
            <td><b>Type</b></td>
            <td><b>PW hr (Piece Work Hours)</b></td>
            <td><b>Units</b></td>
            <td><b>Units (before average)</b></td>
            <td><b>Units/HR</b></td>
            <td><b>Rate</b></td>
            <td><b>Total</b></td>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $row)
            <tr>
                {{-- <span>${usuario.pin}: ${usuario.apellido_1}${usuario.apellido_2 ? `-${usuario.apellido_2}` : ""}, ${usuario.nombre_1} </span> --}}
                <td style="border-bottom: 1px solid #c0c0c0">{{ $row->usuario_supervisor }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ $row->farm }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ $row->location }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ $row->activity }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ $row->nombre_empleado }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ $row->pin }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ $row->fecha_job }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ $row->nombre_categoria }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ $row->nombre_semilla }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ $row->edad }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ $row->fields }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ $row->bloques }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ $row->acreage }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ $row->tipo_pago }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ $row->pwhr }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ $row->units }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ $row->units_original }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ $row->pwhr_rate }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ $row->pay_rate }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ $row->total }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
