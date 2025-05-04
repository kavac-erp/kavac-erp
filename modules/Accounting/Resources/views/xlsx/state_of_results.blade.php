<!DOCTYPE html>
<html>

<body>
    @php
        // resultado total de las operaciones
        $result_of_the_excersice = 0;
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
                    <h3 align="center">ESTADO DE RENDIMIENTO FINANCIERO AL {{ $endDate }}</h3>
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
        @php
            if (array_key_exists('5.0.0.00.00.00.000', $records)) {
                $result_of_the_excersice +=
                    $records['5.0.0.00.00.00.000']['beginningBalance'] + $records['5.0.0.00.00.00.000']['balance'];
            }
            if (array_key_exists('6.0.0.00.00.00.000', $records)) {
                $result_of_the_excersice -=
                    $records['6.0.0.00.00.00.000']['beginningBalance'] + $records['6.0.0.00.00.00.000']['balance'];
            }
        @endphp
        @if ($level >= 1 && count($records) > 0)
            @foreach ($records as $key => $parent)
                @php
                    $next = next($records);
                    $styles = $parent['level'] < '2' ? 'font-weight: bold;' : '';
                @endphp

                @if ($next)
                    @if ($records[$key]['code'][0] === '6' && !$last)
                        @php
                            $last = true;
                        @endphp
                        @if (array_key_exists('5.0.0.00.00.00.000', $records))
                            <tr>
                                <td></td>
                                <td align="right">
                                    TOTAL INGRESOS
                                </td>
                                <td align="right">
                                    {{ number_format($records['5.0.0.00.00.00.000']['beginningBalance'], (int) $currency->decimal_places, ',', '.') }}
                                </td>
                                <td align="right">
                                    {{ number_format($records['5.0.0.00.00.00.000']['balance'], (int) $currency->decimal_places, ',', '.') }}
                                </td>
                                <td align="right">
                                    {{ number_format($records['5.0.0.00.00.00.000']['beginningBalance'] + $records['5.0.0.00.00.00.000']['balance'], (int) $currency->decimal_places, ',', '.') }}
                                </td>
                            </tr>
                            <br>
                        @endif
                    @endif
                @elseif(count($records) == 2)
                    @if (array_key_exists('5.0.0.00.00.00.000', $records))
                        <tr>
                            <td></td>
                            <td align="right">
                                TOTAL INGRESOS
                            </td>
                            <td align="right">
                                {{ number_format($records['5.0.0.00.00.00.000']['beginningBalance'], (int) $currency->decimal_places, ',', '.') }}
                            </td>
                            <td align="right">
                                {{ number_format($records['5.0.0.00.00.00.000']['balance'], (int) $currency->decimal_places, ',', '.') }}
                            </td>
                            <td align="right">
                                {{ number_format($records['5.0.0.00.00.00.000']['beginningBalance'] + $records['5.0.0.00.00.00.000']['balance'], (int) $currency->decimal_places, ',', '.') }}
                            </td>
                        </tr>
                        <br>
                    @endif
                @endif
                <tr>
                    <td>&nbsp;{{ $parent['code'] }}</td>
                    <td>&nbsp;{{ $parent['denomination'] }}</td>
                    <td align="right">
                        {{ number_format($parent['beginningBalance'], (int) $currency->decimal_places, ',', '.') }}</td>
                    <td align="right">
                        {{ number_format($parent['balance'], (int) $currency->decimal_places, ',', '.') }}</td>
                    <td align="right">
                        {{ number_format($parent['beginningBalance'] + $parent['balance'], (int) $currency->decimal_places, ',', '.') }}
                    </td>
                </tr>
                @if ($parent['code'][0] == 6 && !$next)
                    @if (array_key_exists('6.0.0.00.00.00.000', $records))
                        <tr style="background-color: #BDBDBD;">
                            <td style="font-size:9rem;"></td>
                            <td align="right">
                                TOTAL GASTOS
                            </td>
                            <td align="right">
                                {{ number_format($records['6.0.0.00.00.00.000']['beginningBalance'], (int) $currency->decimal_places, ',', '.') }}
                            </td>
                            <td align="right">
                                {{ number_format($records['6.0.0.00.00.00.000']['balance'], (int) $currency->decimal_places, ',', '.') }}
                            </td>
                            <td align="right">
                                {{ number_format($records['6.0.0.00.00.00.000']['beginningBalance'] + $records['6.0.0.00.00.00.000']['balance'], (int) $currency->decimal_places, ',', '.') }}
                            </td>
                        </tr>
                    @endif
                    <br>
                    <tr>
                        <td></td>
                        <td align="right">RESULTADO DEL EJERCICIO</td>
                        <td align="right"></td>
                        <td align="right"></td>

                        <td align="right">
                            {{ number_format($result_of_the_excersice, (int) $currency->decimal_places, ',', '.') }}
                        </td>
                    </tr>
                @endif
            @endforeach
        @endif
        {{-- Fin Nivel 1 --}}
    </table>
</body>

</html>
