<!DOCTYPE html>
<html>

<body>
    @php
        // @var float suma de el total de los pasivos con los patrimoniales
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


    <table>
        <thead>
            <tr>
                <th>
                    <h3 align="center">ESTADO DE SITUACIÓN FINANCIERA AL
                        {{ explode('-', $endDate)[2] . '-' . explode('-', $endDate)[1] . '-' . explode('-', $endDate)[0] }}</h3>
                </th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <!-- ... otras columnas ... -->
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <h5 align="center">Institución: {{ $institution->name }}</h5>
                </td>
                <td></td>
                <td></td>
                <td></td> <!-- Agregando "Comprobante" en la celda E2 -->
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td> <!-- Texto en la celda G3 -->
                <!-- ... otras celdas ... -->
            </tr>
            <tr>
                <td>
                    <h5 align="center">EXPRESADO EN {{ $currency->symbol }}</h5>
                </td>
                <td></td>
                <td></td>
                <td></td> <!-- Agregando "Comprobante" en la celda E2 -->
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td> <!-- Texto en la celda G3 -->
                <!-- ... otras celdas ... -->
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td> <!-- Agregando "Comprobante" en la celda E2 -->
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td> <!-- Texto en la celda G3 -->
                <!-- ... otras celdas ... -->
            </tr>
        </tbody>
    </table>
    <table>
        <tr>
            <td align="center">CÓDIGO</td>
            <td align="center">DENOMINACIÓN</td>
            <td align="center">ACUMULADO AL {{ $monthBefore }}</td>
            <td align="center">
                {{ count(explode('-', $endDate)) > 1 ? $months[(int) explode('-', $endDate)[1]] : (int) $endDate }}
            </td>
            <td align="center">TOTAL</td>
        </tr>
    </table>
    <table>

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
                        <tr>
                            <td>&nbsp;{{ $p['code'] }}</td>
                            <td>&nbsp;{{ $p['denomination'] }}</td>
                            <td align="right"></td>
                        </tr>
                    @else
                        <tr>
                            <td>&nbsp;{{ $p['code'] }}</td>
                            <td>&nbsp;{{ $p['denomination'] }}</td>
                            <td align="right">
                                &nbsp;{{ number_format($p['lastMonthBalance'], (int) $currency->decimal_places, ',', '.') }}
                            </td>
                            <td align="right">
                                &nbsp;{{ number_format($p['balance'], (int) $currency->decimal_places, ',', '.') }}</td>
                            <td align="right">
                                &nbsp;{{ number_format($p['balance'] + $p['lastMonthBalance'], (int) $currency->decimal_places, ',', '.') }}
                            </td>
                        </tr>
                    @endif
                @endforeach
                <tr>
                    <td></td>
                    <td>
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
                    <td>
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
                    <td>
                        {{ number_format($totAmount, (int) $currency->decimal_places, ',', '.') }}
                        @if ($key == 2 || $key == 3)
                            @php
                                $totPasivePatrimonial += $totAmount;
                                $totPasivePatrimonialLast += $lastTotAmount + $totAmount;
                            @endphp
                        @endif
                    </td>
                    <td>
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
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>

                @if ($key == 3)
                    <br>
                    {{-- Se valida el numero de lineas impresas para llegado el limite realizar el salto de pagina manualmente --}}
                    {{-- Fin de la validación --}}
                    <tr>
                        <td></td>
                        <td>TOTAL PASIVO + PATRIMONIO</td>
                        <td>{{ number_format($totPasivePatrimonialLast, (int) $currency->decimal_places, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endif
                <br><br>
            @endforeach
        @endif
        {{-- Fin Nivel 1 --}}
    </table>
</body>

</html>
