<table style="font-size: 8rem;" cellpadding="4" width="30%">
    <tbody>
        <tr>
            <td style="font-weight: bold;">Fecha de creación:</td>
            <td width="175%">
                {{ date_format(new DateTime($records['approved_at']), 'd-m-Y') }}
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Institución:</td>
            <td width="175%">
                {{ $institution['name'] }}
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Documento:</td>
            <td width="175%">
                {{ $records['document'] }}
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Moneda:</td>
            <td width="175%">
                {{ $currency['symbol'] . ' - ' . $currency['name'] }}
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Descripción:</td>
            <td width="175%">
                {{ $records['description'] }}
            </td>
        </tr>
    </tbody>
</table>

<table style="font-size: 7rem;" cellpadding="10" cellspacing="10" align="center">
    <tr>
        <th style="font-weight: bold;">
            {{ 'CUENTAS PRESUPUESTARIAS' }}
        </th>
    </tr>
</table>
<table style="font-size: 8rem;" cellpadding="4" cellspacing="0" align="center">
    <thead>
        <tr>
            <th style="border: solid 1px #000; font-weight: bold; background-color: #D3D3D3;">Acción específica</th>
            <th style="border: solid 1px #000; font-weight: bold; background-color: #D3D3D3;">Cuenta</th>
            <th style="border: solid 1px #000; font-weight: bold; background-color: #D3D3D3;">Descripción</th>
            <th style="border: solid 1px #000; font-weight: bold; background-color: #D3D3D3;">Monto</th>
        </tr>
    </thead>
    <tbody>
    @foreach ($records->budgetModificationAccounts as $budgetAccount)
        <tr>
            <td style="border: solid 1px #808080;">
                {{
                    $budgetAccount['budgetSubSpecificFormulation']['specificAction']['code'] . ' | ' .
                    $budgetAccount['budgetSubSpecificFormulation']['specificAction']['name']
                }}
            </td>
            <td style="border: solid 1px #808080;">
                {{ $budgetAccount['budgetAccount']['code'] }}
            </td>
            <td style="border: solid 1px #808080;">
                {{ $budgetAccount['budgetAccount']['denomination'] }}
            </td>
            <td style="border: solid 1px #808080;">
                {{ number_format($budgetAccount['amount'], 2, ',', '.') }}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>