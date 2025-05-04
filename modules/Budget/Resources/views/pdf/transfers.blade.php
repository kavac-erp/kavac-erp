<table style="font-size: 8rem;" cellpadding="4" width="30%">
    <tbody>
        <tr>
            <td style="font-weight: bold;">Fecha de creación:</td>
            <td width="175%">{{ date_format(new DateTime($records['approved_at']), 'd-m-Y') }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Institución:</td>
            <td width="175%">{{ $institution['name'] }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Documento:</td>
            <td width="175%">{{ $records['document'] }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Descripción:</td>
            <td width="175%">{{ $records['description'] }}</td>
        </tr>
    </tbody>
</table>

<table style="font-size: 9rem;" cellpadding="10" cellspacing="0" align="center">
    <tr>
        <th style="font-weight: bold;">
            {{ 'CUENTAS PRESUPUESTARIAS' }}
        </th>
    </tr>
</table>

<table style="font-size: 7rem;" cellpadding="4" cellspacing="0" align="center">
    <tr>
        <th style="border: solid 1px #000; font-weight: bold; background-color: #D3D3D3;">
            Datos de Origen
        </th>
        <th style="border: solid 1px #000; font-weight: bold; background-color: #D3D3D3;">
            Datos de Destino
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
            <th style="border: solid 1px #000; font-weight: bold; background-color: #D3D3D3;">Acción específica</th>
            <th style="border: solid 1px #000; font-weight: bold; background-color: #D3D3D3;">Cuenta</th>
            <th style="border: solid 1px #000; font-weight: bold; background-color: #D3D3D3;">Descripción</th>
            <th style="border: solid 1px #000; font-weight: bold; background-color: #D3D3D3;">Monto</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($modification_accounts as $budgetAccount)
            <tr>
                <td style="border: solid 1px #808080;">
                    {{ $budgetAccount['from_spac_description'] }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $budgetAccount['from_code'] }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $budgetAccount['from_description'] }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ number_format($budgetAccount['from_amount'], 2, ',', '.') }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $budgetAccount['to_spac_description'] }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $budgetAccount['to_code'] }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $budgetAccount['to_description'] }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ number_format($budgetAccount['to_amount'], 2, ',', '.') }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>