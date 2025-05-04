@php
    $total = 0;
    $totalTax = 0;
    $currency_symbol = '';
    foreach ($records->budgetCompromiseDetails as $compromise) {
        if ($compromise['tax_amount'] == 0) {
            $total += $compromise['amount'];
        }
        $totalTax += $compromise['tax_amount'];
        $currency_symbol = $compromise->budgetSubSpecificFormulation
            ? $compromise->budgetSubSpecificFormulation->currency->symbol
            : '';
    }
@endphp
@if ($records->document_status_id == '5')
    <h4 align="center">
        REGISTRO ANULADO
    </h4>
    <br>
@else
    <br>
@endif

<table width="100%" cellpadding="4" style="font-size: 8rem">
    <tbody>
        <tr>
            <td width="25%" style="font-weight: bold;">Institución: </td>
            <td width="75%">{{ $records->institution['name'] }}</td>
        </tr>
        <tr>
            <td width="25%" style="font-weight: bold;">Fecha: </td>
            <td width="75%">{{ date('d-m-Y', strtotime($records['compromised_at'])) }}</td>
        </tr>
        <tr>
            <td width="25%" style="font-weight: bold;">Documento origen: </td>
            <td width="75%">{{ $records['document_number'] }}</td>
        </tr>
        <tr>
            <td width="25%" style="font-weight: bold;">Descripción: </td>
            <td width="75%">{{ preg_replace('/(<([^>]+)>)/', '', $records['description']) }}</td>
        </tr>
    </tbody>
</table>
<br>
@if ($records['receiver'] && $records['receiver']['associateable'])
    <h3 class="text-left"> Beneficiario </h3>
    <table width="100%" cellpadding="4" style="font-size: 8rem">
        <tbody>
            <tr>
                <td width="25%" style="font-weight: bold;">Beneficiario: </td>
                <td width="75%">{{ $records->receiver['description'] }}</td>
            </tr>
            <tr>
                <td width="25%" style="font-weight: bold;">Cuenta contable: </td>
                <td width="75%">
                    {{ $records['receiver']['associateable']['group'] . '.' . $records['receiver']['associateable']['subgroup'] . '.' . $records['receiver']['associateable']['item'] . '.' . $records['receiver']['associateable']['generic'] . '.' . $records['receiver']['associateable']['specific'] . '.' . $records['receiver']['associateable']['subspecific'] . ' - ' . $records['receiver']['associateable']['institutional'] . ' - ' . $records['receiver']['associateable']['denomination'] }}
                </td>
            </tr>
        </tbody>
    </table>
@endif
<br>
<h3 align="center">
    Cuentas presupuestarias de gastos
</h3>
<table cellspacing="0" cellpadding="1" border="1" style="font-size: 7rem;">
    <tr>
        <th style="border: solid 1px #000;" bgcolor="#D3D3D3" align="center">Acción Específica</th>
        <th style="border: solid 1px #000;" bgcolor="#D3D3D3" align="center">Cuenta</th>
        <th style="border: solid 1px #000;" bgcolor="#D3D3D3" align="center">Descripción</th>
        <th style="border: solid 1px #000;" bgcolor="#D3D3D3" align="center">Monto</th>
    </tr>
</table>
<table cellspacing="0" cellpadding="4" style="font-size: 7rem;">
    @foreach ($records->budgetCompromiseDetails as $compromise)
        @if ($compromise['tax_id'] == null)
            <tr>
                <td style="border: solid 1px #808080;" align="center">
                    {{ $compromise['budgetSubSpecificFormulation']['specificAction']['specificable']['code'] . ' - ' . $compromise['budgetSubSpecificFormulation']['specificAction']['code'] . ' | ' . $compromise['budgetSubSpecificFormulation']['specificAction']['name'] }}
                </td>
                <td style="border: solid 1px #808080;" align="center">
                    {{ $compromise['budgetAccount']['code'] }}
                </td>
                <td style="border: solid 1px #808080;" align="center">
                    {{ $compromise['budgetAccount']['denomination'] }}
                </td>
                <td style="border: solid 1px #808080;" align="center">
                    {{
                        number_format(
                            $compromise['amount'], $compromise['budgetSubSpecificFormulation']['currency']['decimal_places'], ",", "."
                        )
                    }}
                </td>
            </tr>
        @endif
    @endforeach
    <tr bgcolor="#D3D3D3" align="center">
        <td style="border-bottom: solid 1px #808080; border-left: solid 1px #808080; " align="center"></td>
        <td style="border-bottom: solid 1px #808080;" align="center"></td>
        <th style="border-bottom: solid 1px #808080;" align="center">
            <b>TOTAL {{ $currency_symbol }}</b>
        </th>
        <td style="border-bottom: solid 1px #808080; border-right: solid 1px #808080; " align="center">
            <b>
                {{ number_format($total, $compromise['budgetSubSpecificFormulation']['currency']['decimal_places'], ",", ".") }}
            </b>
        </td>
    </tr>
</table>
<br>
<h3 align="center">
    Cuentas presupuestarias de impuestos
</h3>
<table cellspacing="0" cellpadding="1" border="1" style="font-size: 7rem;">
    <tr>
        <th style="border: solid 1px #000;" bgcolor="#D3D3D3" align="center">Acción Específica</th>
        <th style="border: solid 1px #000;" bgcolor="#D3D3D3" align="center">Cuenta</th>
        <th style="border: solid 1px #000;" bgcolor="#D3D3D3" align="center">Descripción</th>
        <th style="border: solid 1px #000;" bgcolor="#D3D3D3" align="center">Monto</th>
    </tr>
</table>
<table cellspacing="0" cellpadding="4" style="font-size: 7rem;">
    @foreach ($records->budgetCompromiseDetails as $compromise)
        @if ($compromise['tax_id'])
            <tr>
                <td style="border: solid 1px #808080;" align="center">
                    {{ $compromise['budgetSubSpecificFormulation']['specificAction']['specificable']['code'] . ' - ' . $compromise['budgetSubSpecificFormulation']['specificAction']['code'] . ' | ' . $compromise['budgetSubSpecificFormulation']['specificAction']['name'] }}
                </td>
                <td style="border: solid 1px #808080;" align="center">
                    {{ $compromise['budgetAccount']['code'] }}
                </td>
                <td style="border: solid 1px #808080;" align="center">
                    {{ $compromise['budgetAccount']['denomination'] }}
                </td>
                <td style="border: solid 1px #808080;" align="center">
                    {{
                        number_format(
                            $compromise['amount'], $compromise['budgetSubSpecificFormulation']['currency']['decimal_places'], ",", "."
                        )
                    }}
                </td>
            </tr>
        @endif
    @endforeach
    <tr bgcolor="#D3D3D3" align="center">
        <td style="border-bottom: solid 1px #808080; border-left: solid 1px #808080; " align="center"></td>
        <td style="border-bottom: solid 1px #808080;" align="center"></td>
        <th style="border-bottom: solid 1px #808080;" align="center">
            <b>TOTAL {{ $currency_symbol }}</b>
        </th>
        <td style="border-bottom: solid 1px #808080; border-right: solid 1px #808080; " align="center">
            <b>
                {{
                    number_format(
                        $totalTax, $compromise['budgetSubSpecificFormulation']['currency']['decimal_places'], ",", "."
                    )
                }}
            </b>
        </td>
    </tr>
</table>
