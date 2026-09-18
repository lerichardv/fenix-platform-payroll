<table>
    <thead>
        <tr>
            <td colspan="10">
                <p><b>Harvest Piece Rate Report</b></p>
            </td>
            <td colspan="4">
                Created on {{ $today }}
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <p>{{ $initial_date }}-{{ $final_date }}</p>
            </td>
        </tr>
        <tr>
            <td><b>Farm</b></td>
            <td><b>Employee Name</b></td>
            <td><b>Employee PIN</b></td>
            <td><b>Date</b></td>
            <td><b>Commodity</b></td>
            <td><b>Crop name</b></td>
            <td><b>Pack Type</b></td>
            <td><b>Age</b></td>
            <td><b>Type</b></td>
            <td><b>PW hr (Piece Work Hours)</b></td>
            <td><b>Units</b></td>
            <td><b>Units/HR</b></td>
            <td><b>Rate</b></td>
            <td><b>Total</b></td>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $row)
            <tr>
                {{-- <span>${usuario.pin}: ${usuario.apellido_1}${usuario.apellido_2 ? `-${usuario.apellido_2}` : ""}, ${usuario.nombre_1} </span> --}}
                <td style="border-bottom: 1px solid #c0c0c0">{{ $row->farm }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ $row->nombre_empleado }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ $row->pin }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ $row->fecha_job }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ $row->nombre_categoria }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ $row->nombre_semilla }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ $row->tipo_pack }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ $row->edad }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ $row->abreviatura }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ $row->pwhr }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ $row->units }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ $row->pwhr_rate }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ $row->piece_rate }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ $row->total }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
