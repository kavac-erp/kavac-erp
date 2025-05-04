<p style="font-size: 9rem; font-weight: bold;">
    {{ $payment_type }}
</p>
<p style="font-size: 9rem; font-weight: bold;">
    1. Afectación Presupuestaria
</p>
<br>
<br>
<table cellspacing="0" cellpadding="4" style="font-size: 7rem;">
    <thead>
        <tr style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3" align="center">
            <th tabindex="0" class="col-8" style="border: 1px solid #dee2e6; position: relative;">
                Partida
            </th>
            <th tabindex="0" class="col-8" style="border: 1px solid #dee2e6; position: relative;">
                Denominación
            </th>
            <th tabindex="0" class="col-2" style="border: 1px solid #dee2e6; position: relative;">
                Monto
            </th>
        </tr>
    </thead>
    <tbody>
        @foreach ($records as $key => $value)
            @if ($value[2] == 'budget')
                <tr>
                    <td style="border: 1px solid #dee2e6;" tabindex="0" class="col-8 text-left" align="center">
                        {{ $key }}
                    </td>
                    <td style="border: 1px solid #dee2e6;" tabindex="0" class="col-8 text-left">
                        {{ currency_format($value[1], 2) }}
                    </td>
                    <td style="border: 1px solid #dee2e6;" tabindex="0" class="col-8 text-right" align="center">
                        {{ currency_format($value[0], 2) }}
                    </td>
                </tr>
            @endif
        @endforeach
        <tr style="border: solid 1px #000; font-weight: bold;" align="center">
            <td style="border: 1px solid #dee2e6;" tabindex="0" class="col-8 text-left">
                Total
            </td>
            <td style="border: 1px solid #dee2e6;" tabindex="0" class="col-8 text-left">
                &nbsp;
            </td>
            <td style="border: 1px solid #dee2e6;" tabindex="0" class="col-2 text-right">
                {{ currency_format($total_array[0], 2) }}
            </td>
        </tr>
    </tbody>
</table>
