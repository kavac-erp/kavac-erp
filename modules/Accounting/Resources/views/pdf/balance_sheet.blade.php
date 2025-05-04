@php
    // suma de el total de los pasivos con los patrimoniales
    $totPasivePatrimonial = 0;
    $totPasivePatrimonialLast = 0;
    $lastTotAmount = 0;
    $months = [
        1 => 'ENERO',
        2 => 'FEBRERO',
        3 => 'MARZO',
        4 => 'ABRIL',
        5 => 'MAYO',
        6 => 'JUNIO',
        7 => 'JULIO',
        8 => 'AGOSTO',
        9 => 'SEPTIEMBRE',
        10 => 'OCTUBRE',
        11 => 'NOVIEMBRE',
        12 => 'DICIEMBRE',
    ];
    $last = false;
@endphp

<h3 align="center" style="font-size: 9rem;">ESTADO DE SITUACIÓN FINANCIERA AL
    {{ explode('-', $endDate)[2] . '-' . explode('-', $endDate)[1] . '-' . explode('-', $endDate)[0] }}</h3>
<h5 align="center" style="font-size: 9rem;">Institución: {{ $institution->name }}</h5>
<h5 align="center" style="font-size: 8rem;">EXPRESADO EN {{ $currency->symbol }}</h5>
<table cellspacing="0" cellpadding="1" border="1">
    <tr style="background-color: #BDBDBD;">
        <td style="font-size: 9rem;" width="18%" align="center">CÓDIGO</td>
        <td style="font-size: 9rem;" width="32%" align="center">DENOMINACIÓN</td>
        <td style="font-size:9rem;" width="15%" align="center">ACUMULADO AL {{ $monthBefore }}</td>
        <td style="font-size:9rem;" width="15%" align="center">
            {{ count(explode('-', $endDate)) > 1 ? $months[(int) explode('-', $endDate)[1]] : (int) $endDate }}
        </td>
        <td style="font-size:9rem;" width="20%" align="center">TOTAL</td>
    </tr>
</table>
<table cellspacing="0" cellpadding="1" border="0">

    {{-- Se recorren las cuentas de Inicio Nivel 1 --}}
    @if ($level >= 1 && count($records) > 0)
        {{-- $records es un array con las cuenta de nivel 1 --}}
        @foreach ($records as $key => $parent)
            @php
                $totAmount = 0;
            @endphp
            @foreach ($parent as $p)
                @php

                    $styles = $p['level'] < '5' ? 'font-weight: bold;' : '';
                    if ($p['code'] == '2.0.0.00.00.00.000') {
                        $totAmount = 0;
                        $lastTotAmount = 0;
                    }
                    if ($p['code'] == '3.0.0.00.00.00.000') {
                        $totAmount = 0;
                        $lastTotAmount = 0;
                    }

                    if ($p['code'][2] == 0) {
                        $totAmount += $p['balance'];
                        $lastTotAmount += $p['lastMonthBalance'];
                    }
                @endphp
                {{-- Fin de la validación --}}
                @if ($p['parent'] === null)
                    <tr style="background-color: #BDBDBD;">
                        <td style="font-size:9rem; {{ $styles }}" width="16%">&nbsp;{{ $p['code'] }}</td>
                        <td style="font-size:9rem; {{ $styles }}" width="60%">&nbsp;{{ $p['denomination'] }}
                        </td>
                        <td style="font-size:9rem; {{ $styles }}" align="right" width="24%"></td>
                    </tr>
                @else
                    <tr>
                        <td style="font-size:9rem; {{ $styles }}" width="16%">&nbsp;{{ $p['code'] }}</td>
                        <td style="font-size:9rem; {{ $styles }}" width="32%">&nbsp;{{ $p['denomination'] }}
                        </td>
                        <td style="font-size:9rem; {{ $styles }}" align="right" width="15%">
                            &nbsp;{{ number_format($p['lastMonthBalance'], (int) $currency->decimal_places, ',', '.') }}
                        </td>
                        <td style="font-size:9rem; {{ $styles }}" align="right" width="15%">
                            &nbsp;{{ number_format($p['balance'], (int) $currency->decimal_places, ',', '.') }}</td>
                        <td style="font-size:9rem; {{ $styles }}" align="right" width="20%">
                            &nbsp;{{ number_format($p['balance'] + $p['lastMonthBalance'], (int) $currency->decimal_places, ',', '.') }}
                        </td>
                    </tr>
                @endif
            @endforeach
            <tr style="background-color: #BDBDBD;">
                <td style="font-size: 9rem;" width="16%"></td>
                <td style="font-size: 9rem; font-weight: bold;" align="right" width="32%">
                    @if ($key == 1)
                        TOTAL ACTIVO
                    @elseif($key == 2)
                        TOTAL PASIVO
                    @elseif($key == 3)
                        TOTAL PATRIMONIO
                    @elseif($key == 4)
                        TOTAL CUENTAS DE ORDEN
                    @endif
                </td>
                <td style="font-size: 9rem; font-weight: bold;" align="right" width="15%">
                    @if ($key == 1)
                        {{ number_format($lastTotAmount, (int) $currency->decimal_places, ',', '.') }}
                    @elseif($key == 2)
                        {{ number_format($lastTotAmount, (int) $currency->decimal_places, ',', '.') }}
                    @elseif($key == 3)
                        {{ number_format($lastTotAmount, (int) $currency->decimal_places, ',', '.') }}
                    @elseif($key == 4)
                        {{ number_format($lastTotAmount, (int) $currency->decimal_places, ',', '.') }}
                    @endif
                </td>
                <td style="font-size: 9rem; font-weight: bold;" align="right" width="15%">
                    {{ number_format($totAmount, (int) $currency->decimal_places, ',', '.') }}
                    @if ($key == 2 || $key == 3)
                        @php
                            $totPasivePatrimonial += $totAmount;
                            $totPasivePatrimonialLast += $lastTotAmount + $totAmount;
                        @endphp
                    @endif
                </td>
                <td style="font-size: 9rem; font-weight: bold;" align="right" width="20%">
                    @if ($key == 1)
                        {{ number_format($lastTotAmount + $totAmount, (int) $currency->decimal_places, ',', '.') }}
                    @elseif($key == 2)
                        {{ number_format($lastTotAmount + $totPasivePatrimonial, (int) $currency->decimal_places, ',', '.') }}
                    @elseif($key == 3)
                        {{ number_format($lastTotAmount + $totAmount, (int) $currency->decimal_places, ',', '.') }}
                    @elseif($key == 4)
                        {{ number_format($lastTotAmount + $totAmount, (int) $currency->decimal_places, ',', '.') }}
                    @endif
                </td>
            </tr>

            <tr>
                <td style="font-size: 9rem;"></td>
                <td style="font-size: 9rem;"></td>
                <td style="font-size: 9rem;"></td>
            </tr>

            @if ($key == 3)
                <br>
                {{-- Se valida el numero de lineas impresas para llegado el limite realizar el salto de pagina manualmente --}}
                {{-- Fin de la validación --}}
                <tr style="background-color: #BDBDBD;">
                    <td style="font-size: 9rem;"></td>
                    <td style="font-size: 9rem; font-weight: bold;" align="right">TOTAL PASIVO + PATRIMONIO</td>
                    <td style="font-size: 9rem; font-weight: bold;" align="right">
                        {{ number_format($totPasivePatrimonialLast, (int) $currency->decimal_places, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="font-size: 9rem;"></td>
                    <td style="font-size: 9rem;"></td>
                    <td style="font-size: 9rem;"></td>
                </tr>
            @endif
            <br><br>
        @endforeach
    @endif
    {{-- Fin Nivel 1 --}}
</table>
