<?php

$keys = [
    ['value' => 'cod_usuario', 'text' => 'User ID'],
    ['value' => 'nombre_completo', 'text' => 'Full Name'],
    ['value' => 'identidad', 'text' => 'ID'],
    ['value' => 'email', 'text' => 'Email'],
    ['value' => 'telefono', 'text' => 'Phone'],
    ['value' => 'cod_tipo_usuario', 'text' => 'User Type ID'],
    ['value' => 'tipo_usuario', 'text' => 'User Type'],
    ['value' => 'pin', 'text' => 'Pin'],
    ['value' => 'qcpin', 'text' => 'Qcpin'],
    ['value' => 'pay_rate', 'text' => 'User Pay Rate'],
    ['value' => 'categoria', 'text' => 'Category'],
    ['value' => 'cod_location', 'text' => 'Location ID'],
    ['value' => 'location', 'text' => 'Location'],
    ['value' => 'cod_farms', 'text' => 'Farm ID'],
    ['value' => 'farm', 'text' => 'Farm'],
    ['value' => 'horas', 'text' => 'Hours'],
    ['value' => 'escaneos', 'text' => 'Scans'],
];

function getTextByValue(array $data, string $value): ?string
{
    foreach ($data as $item) {
        if (str_contains($value, $item['value'])) {
            return $item['text'];
        }
    }
    return null; // Return null if the value is not found
}

?>
<table>
    <thead>
        <tr>
            <td colspan="2">
                <p><b>Dynamic Report</b></p>
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
            @foreach($data[0] as $key => $value)
                <td><b>{{ getTextByValue($keys, $key) }}</b></td>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($data as $row)
        <tr>
            @foreach($row as $key => $value)
                <td style="border-bottom: 1px solid #c0c0c0">{{ $value }}</td>
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>
