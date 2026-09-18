<table>
    <thead>
        <tr>
            <td colspan="5">
                <p><b>Sign Sheet Summary</b></p>
            </td>
            <td colspan="4">
                Created on {{ $today }}
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <p>{{ $initial_date }} - {{ $final_date }}</p>
            </td>
        </tr>
        <tr>
            <td>
            </td>
            <td colspan="8" style="height: 150px">
                <span style="padding-bottom: 10px; font-weight: bold; text-decoration: underline">Reconocimiento del
                    Estado de Lesión o Accidente y Confirmación de Horas:</span><br>
                <span style="padding-bottom: 10px">
                    Al firmar a continuación, confirmo que he revisado los detalles de mi turno/jornada laboral y
                    reconozco lo siguiente:
                </span><br>
                <span>
                    - No he sufrido lesiones ni accidentes durante mi turno/jornada laboral. <br>
                    - Si he sufrido alguna lesión o accidente, lo he reportado al personal correspondiente y he
                    completado la documentación necesaria. <br>
                    - Confirmo que todas las horas registradas en este documento son correctas. <br>
                    - Entiendo que no reportar una lesión o accidente puede causar retrasos en el proceso y afectar los
                    protocolos de seguridad. <br>
                </span>
            </td>
        </tr>
        <tr>
            <td style="width: 100px"><b>Initials</b></td>
            <td><b>Full Name</b></td>
            <td><b>Pin</b></td>
            <td><b>Monday</b></td>
            <td><b>Tuesday</b></td>
            <td><b>Wednesday</b></td>
            <td><b>Thursday</b></td>
            <td><b>Friday</b></td>
            <td><b>Saturday</b></td>
            <td><b>Sunday</b></td>
            <td><b>Grand Total</b></td>
        </tr>
    </thead>
    <tbody>
        <?php
        
        $totales = (object) ['dia1' => 0, 'dia2' => 0, 'dia3' => 0, 'dia4' => 0, 'dia5' => 0, 'dia6' => 0, 'dia7' => 0, 'granTotal' => 0];
        
        ?>
        @foreach ($data as $row)
            <?php
            // $row->dia1 = $row->dia1 ? (float) $row->dia1 : 0;
            // $row->dia2 = $row->dia2 ? (float) $row->dia2 : 0;
            // $row->dia3 = $row->dia3 ? (float) $row->dia3 : 0;
            // $row->dia4 = $row->dia4 ? (float) $row->dia4 : 0;
            // $row->dia5 = $row->dia5 ? (float) $row->dia5 : 0;
            // $row->dia6 = $row->dia6 ? (float) $row->dia6 : 0;
            // $row->dia7 = $row->dia7 ? (float) $row->dia7 : 0;
            // $row->total = $row->dia1 + $row->dia2 + $row->dia3 + $row->dia4 + $row->dia5 + $row->dia6 + $row->dia7;
            ?>
        @endforeach
        @foreach ($data as $row)
            <?php
            $totales->dia1 += $row->dia1 ? (float) $row->dia1 : 0;
            $totales->dia2 += $row->dia2 ? (float) $row->dia2 : 0;
            $totales->dia3 += $row->dia3 ? (float) $row->dia3 : 0;
            $totales->dia4 += $row->dia4 ? (float) $row->dia4 : 0;
            $totales->dia5 += $row->dia5 ? (float) $row->dia5 : 0;
            $totales->dia6 += $row->dia6 ? (float) $row->dia6 : 0;
            $totales->dia7 += $row->dia7 ? (float) $row->dia7 : 0;
            $totales->granTotal += $row->total ? (float) $row->total : 0;
            ?>
            <tr>
                {{-- <span>${usuario.pin}: ${usuario.apellido_1}${usuario.apellido_2 ? `-${usuario.apellido_2}` : ""}, ${usuario.nombre_1} </span> --}}
                <td style="border-bottom: 1px solid #c0c0c0"></td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ $row->full_name }} </td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ $row->pin }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ number_format($row->dia1, 2) }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ number_format($row->dia2, 2) }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ number_format($row->dia3, 2) }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ number_format($row->dia4, 2) }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ number_format($row->dia5, 2) }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ number_format($row->dia6, 2) }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ number_format($row->dia7, 2) }}</td>
                <td style="border-bottom: 1px solid #c0c0c0">{{ number_format($row->total, 2) }}</td>
            </tr>
        @endforeach
        <tr>
            <td style="border-top: 2px solid #000"></td>
            <td style="border-top: 2px solid #000"></td>
            <td style="border-top: 2px solid #000"><b>Total</b></td>
            <td style="border-top: 2px solid #000">{{ number_format($totales->dia1, 2) }}</td>
            <td style="border-top: 2px solid #000">{{ number_format($totales->dia2, 2) }}</td>
            <td style="border-top: 2px solid #000">{{ number_format($totales->dia3, 2) }}</td>
            <td style="border-top: 2px solid #000">{{ number_format($totales->dia4, 2) }}</td>
            <td style="border-top: 2px solid #000">{{ number_format($totales->dia5, 2) }}</td>
            <td style="border-top: 2px solid #000">{{ number_format($totales->dia6, 2) }}</td>
            <td style="border-top: 2px solid #000">{{ number_format($totales->dia7, 2) }}</td>
            <td style="border-top: 2px solid #000">{{ number_format($totales->granTotal, 2) }}</td>
        </tr>
    </tbody>
</table>
