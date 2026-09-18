<table>
    <thead>
        <tr>
            <td colspan="3">
                <p><b>Estimated Payroll</b></p>
            </td>
            <td colspan="3">
                Created on {{ $today }}
            </td>
        </tr>
        <tr>
            <td colspan="6">
                <p>{{ $initial_date }}-{{ $final_date }}</p>
            </td>
        </tr>
        <tr>
            <td><b>Employee</b></td>
            <td><b>ID</b></td>
            <td><b>Total Hours</b></td>
            <td><b>Hourly Rate</b></td>
            <td><b>Estimated Wage</b></td>
            <td><b>Estimated Total</b></td>
        </tr>
    </thead>
    <tbody>
        @foreach($usuarios as $usuario)
            <tr>
                {{-- <span>${usuario.pin}: ${usuario.apellido_1}${usuario.apellido_2 ? `-${usuario.apellido_2}` : ""}, ${usuario.nombre_1} </span> --}}
                <td style="border-bottom: 1px solid #c0c0c0">{{ $usuario->apellido_1 }}{{ $usuario->apellido_2 ? "-".$usuario->apellido_2 : "" }}, {{ $usuario->nombre_1 }}{{ $usuario->nombre_2 ? "-".$usuario->nombre_2 : "" }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ $usuario->pin }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ $usuario->horas_trabajadas }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">${{ $usuario->pay_rate }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">${{ number_format($usuario->horas_trabajadas * $usuario->pay_rate, 2) }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">${{ number_format($usuario->horas_trabajadas * $usuario->pay_rate, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
