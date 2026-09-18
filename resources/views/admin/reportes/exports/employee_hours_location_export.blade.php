<table>
    <thead>
        <tr>
            <td colspan="2">
                <p><b>Employee By Location</b></p>
            </td>
            <td>
                Created on {{ $today }}
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <p>{{ $initial_date }}-{{ $final_date }}</p>
            </td>
        </tr>
        <tr>
            <td><b>Employee</b></td>
            <td><b>Location Name</b></td>
            <td><b>Total</b></td>
        </tr>
    </thead>
    <tbody>
        @foreach($usuarios as $usuario)
            @if($usuario->horas_trabajadas > 0)
                <tr>
                    {{-- <span>${usuario.pin}: ${usuario.apellido_1}${usuario.apellido_2 ? `-${usuario.apellido_2}` : ""}, ${usuario.nombre_1} </span> --}}
                    <td style="border-bottom: 1px solid #c0c0c0">{{ $usuario->pin }}: {{ $usuario->apellido_1 }} {{ $usuario->apellido_2 ? $usuario->apellido_2 : "" }}, {{ $usuario->nombre_1 }} - {{ $usuario->es_veterano == 0 ? "Regular" : ($usuario->es_veterano == 1 ? "Veteran" : "H2A") }}</td>
                    <td style="border-bottom: 1px solid #c0c0c0">
                        @foreach($usuario->granjas as $granja)
                            @if($granja->horas_trabajadas != "00:00")
                                {{-- <p>{{ $granja->farm }}: {{ round($granja->horas_trabajadas * 2) / 2 }}</p> --}}
                                @foreach($granja->locations as $location)
                                    @if($location->horas_trabajadas != "00:00")
                                        <p>{{ $granja->farm }} - {{ $location->location }}: {{ $location->horas_trabajadas_decimales }}</p>
                                    @endif
                                @endforeach
                            @endif
                        @endforeach
                    </td>
                    <td style="border-bottom: 1px solid #c0c0c0">{{ $usuario->horas_trabajadas_decimales }}</td>
                </tr>
            @endif
        @endforeach
    </tbody>
</table>
