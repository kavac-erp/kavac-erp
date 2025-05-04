<br>
<table width="100%" cellpadding="4" style="font-size: 8rem">
    <tbody>
        <tr>
            <td width="25%" style="font-weight: bold;">Expresado en:</td>
            <td width="75%">{{ $currencySymbol }}</td>
        </tr>
        <tr>
            <td width="25%" style="font-weight: bold;">Institución:</td>
            <td width="75%">{{ $institution['name'] }}</td>
        </tr>
        <tr>
            <td width="25%" style="font-weight: bold;">Año Fiscal:</td>
            <td width="75%">{{ $fiscal_year }}</td>
        </tr>
        <tr>
            <td width="25%" style="font-weight: bold;">Generado por:</td>
            @php
                $analist_name = $profile ? $profile->first_name . ' ' . $profile->last_name : '';
            @endphp
            <td width="75%">{{ $analist_name }}</td>
        </tr>
        <tr>
            <td>
                &nbsp;
            </td>
        </tr>

    </tbody>
</table>
<table cellspacing="0" cellpadding="1" border="1" style="font-size: 7rem">
    <tr>
        <th style="border: solid 1px #000;" bgcolor="#D3D3D3" align="center">Acción/Proyecto</th>
        <th style="border: solid 1px #000;" bgcolor="#D3D3D3" align="center">Acción Especifica</th>
        <th style="border: solid 1px #000;" bgcolor="#D3D3D3" align="center">Código</th>
        <th style="border: solid 1px #000;" bgcolor="#D3D3D3" align="center">Denominación</th>
        {{-- <th style="font-size: 9rem;" width="14%" align="center">PROGRAMADO </th> --}}
        {{-- <th style="font-size: 9rem;" width="14%" align="center">COMPROMETIDO </th> --}}
        <th style="border: solid 1px #000;" bgcolor="#D3D3D3" width="20%" align="center">Disponibilidad
            Presupuestaria</th>
    </tr>
</table>

<table cellspacing="0" cellpadding="4" border="1" style="font-size: 7rem;">
    @php
        $total_amount_available = 0;
    @endphp
    @foreach ($records as $budgetAccounts)
        @if (count($budgetAccounts[0]) < 0)
            @php
                break;
            @endphp
        @endif
        @php
            usort($budgetAccounts[0], function ($budgetItemOne, $budgetItemTwo) {
                $codeOne = str_replace('.', '', $budgetItemOne->budgetAccount->code ?? $budgetItemOne->code);
                $codeTwo = str_replace('.', '', $budgetItemTwo->budgetAccount->code ?? $budgetItemTwo->code);

                return $codeOne > $codeTwo;
            });
        @endphp
        @foreach ($budgetAccounts[0] as $budgetAccount)
            @php
                $specific = $budgetAccount->specific ?? $budgetAccount->budgetAccount->specific;
                $styles = $specific === '00' ? 'font-weight: bold;' : '';
            @endphp
            @if ($budgetAccount['self_amount'] > 0 || $budgetAccount['self_available'] > 0)
                <tr>
                    <td style="border: solid 1px #808080; {{ $styles }}" align="center">
                        {{ $budgetAccounts['project_code'] }}</td>
                    <td style="border: solid 1px #808080; {{ $styles }}" align="center">
                        {{ $budgetAccounts['specific_action_code'] }}</td>

                    {{-- Informacion de las cuentas --}}
                    <td style="border: solid 1px #808080; {{ $styles }}" align="center">
                        {{ $budgetAccount['code'] ?? $budgetAccount['budgetAccount']['code'] }}</td>
                    <td style="border: solid 1px #808080; {{ $styles }}" align="left">
                        {{ $budgetAccount['denomination'] ?? $budgetAccount['budgetAccount']['denomination'] }}</td>
                    <td style="border: solid 1px #808080; {{ $styles }}" align="center">
                        {{ number_format($budgetAccount['self_available'], 2, ',', '.') }}</td>
                </tr>
            @endif
            @if (isset($budgetAccount['modifications']) && count($budgetAccount['modifications']) > 0)
                @foreach ($budgetAccount['modifications'] as $modification)
                    <tr>
                        <td style="border: solid 1px #808080; {{ $styles }}" align="center">
                            {{ $budgetAccounts['project_code'] }}</td>
                        <td style="border: solid 1px #808080; {{ $styles }}" align="center">
                            {{ $budgetAccounts['specific_action_code'] }}</td>

                        {{-- Informacion de las cuentas --}}
                        <td style="border: solid 1px #808080; {{ $styles }}" align="center">
                            {{ $budgetAccount['code'] ?? $budgetAccount['budgetAccount']['code'] }}</td>
                        <td style="border: solid 1px #808080; {{ $styles }}" align="left">
                            {{ $budgetAccount['denomination'] ?? $budgetAccount['budgetAccount']['denomination'] }}
                        </td>
                        <td style="border: solid 1px #808080; {{ $styles }}" align="center">
                            {{ number_format($modification['self_available'], 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            @endif
            @php
                $item = $budgetAccount->budgetAccount->item ?? $budgetAccount->item;
                if ($item === '00') {
                    $total_amount_available += $budgetAccount['self_available'];
                }
            @endphp
        @endforeach
    @endforeach

</table>

<table cellspacing="0" cellpadding="1" border="1" style="font-weight: bold">
    <tr>
        <td style="font-size: 8rem; border-bottom: 1px solid #999;" align="left" width="80%">
            Total
        </td>
        <td style="font-size: 8rem; border-bottom: 1px solid #999;" align="center" width="20%">
            {{ number_format($total_amount_available, 2, ',', '.') }}
        </td>
    </tr>
</table>

<div>
    <table style="font-size: 8rem; margin-top: 100px" cellpadding="3">
        <tr>
            <td>
                &nbsp;
            </td>
        </tr>
        <tr>
            <td>
                &nbsp;
            </td>
        </tr>
        <tr>
            <td>
                &nbsp;
            </td>
        </tr>
        <tr>
            <td>
                &nbsp;
            </td>
        </tr>
        <tr>
            <td align="center" width="30%">
                Atentamente
            </td>
        </tr>
        <tr>
            <td>
                &nbsp;
            </td>
        </tr>
        <tr>
            <td>
                &nbsp;
            </td>
        </tr>
        <tr>
            <td align="center" width="30%" style="border-top: solid 1px #000;">
                {{ $analist_name }}
            </td>
            <td width="70%">
                &nbsp;
            </td>
        </tr>
    </table>
</div>
