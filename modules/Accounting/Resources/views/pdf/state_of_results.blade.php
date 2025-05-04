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

<h3 align="center" style="font-size: 9rem;">ESTADO DE RENDIMIENTO FINANCIERO AL {{ $endDate }}</h3>
<h5 align="center" style="font-size: 9rem;">Institución: {{ $institution->name }}</h5>
<h5 align="center" style="font-size: 8rem;">EXPRESADO EN {{ $currency->symbol }}</h5>
<table cellspacing="0" cellpadding="1" border="1">
    <tr style="background-color: #BDBDBD;">
        <td style="font-size:9rem;" width="18%" align="center">CÓDIGO</td>
        <td style="font-size:9rem;" width="32%" align="center">DENOMINACIÓN</td>
        <td style="font-size:9rem;" width="15%" align="center">ACUMULADO AL {{ $monthBefore }}</td>
        <td style="font-size:9rem;" width="15%" align="center">
            {{ count(explode('-', $endDate)) > 1 ? $months[(int) explode('-', $endDate)[1]] : (int) $endDate }}
        </td>
        <td style="font-size:9rem;" width="20%" align="center">TOTAL</td>
    </tr>
</table>
<table cellspacing="0" cellpadding="1" border="0">

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
                        <tr style="background-color: #BDBDBD;">
                            <td style="font-size:9rem;"></td>
                            <td style="font-size:9rem;font-weight: bold;" align="right">
                                TOTAL INGRESOS
                            </td>
                            <td style="font-size:9rem;font-weight: bold;" align="right" width="15%">
                                {{ number_format($records['5.0.0.00.00.00.000']['beginningBalance'], (int) $currency->decimal_places, ',', '.') }}
                            </td>
                            <td style="font-size:9rem;font-weight: bold;" align="right" width="15%">
                                {{ number_format($records['5.0.0.00.00.00.000']['balance'], (int) $currency->decimal_places, ',', '.') }}
                            </td>
                            <td style="font-size:9rem;font-weight: bold;" align="right">
                                {{ number_format($records['5.0.0.00.00.00.000']['beginningBalance'] + $records['5.0.0.00.00.00.000']['balance'], (int) $currency->decimal_places, ',', '.') }}
                            </td>
                        </tr>
                        <br>
                    @endif
                @endif
            @elseif(count($records) == 2)
                @if (array_key_exists('5.0.0.00.00.00.000', $records))
                    <tr style="background-color: #BDBDBD;">
                        <td style="font-size:9rem;"></td>
                        <td style="font-size:9rem;font-weight: bold;" align="right">
                            TOTAL INGRESOS
                        </td>
                        <td style="font-size:9rem;font-weight: bold;" align="right" width="15%">
                            {{ number_format($records['5.0.0.00.00.00.000']['beginningBalance'], (int) $currency->decimal_places, ',', '.') }}
                        </td>
                        <td style="font-size:9rem;font-weight: bold;" align="right" width="15%">
                            {{ number_format($records['5.0.0.00.00.00.000']['balance'], (int) $currency->decimal_places, ',', '.') }}
                        </td>
                        <td style="font-size:9rem;font-weight: bold;" align="right">
                            {{ number_format($records['5.0.0.00.00.00.000']['beginningBalance'] + $records['5.0.0.00.00.00.000']['balance'], (int) $currency->decimal_places, ',', '.') }}
                        </td>
                    </tr>
                    <br>
                @endif
            @endif

            <tr style="background-color: #BDBDBD;">
                <td style="font-size:9rem; {{ $styles }}" width="18%">&nbsp;{{ $parent['code'] }}</td>
                <td style="font-size:9rem; {{ $styles }}" width="32%">&nbsp;{{ $parent['denomination'] }}
                </td>
                <td style="font-size:9rem; {{ $styles }}" align="right" width="15%">
                    {{ number_format($parent['beginningBalance'], (int) $currency->decimal_places, ',', '.') }}</td>
                <td style="font-size:9rem; {{ $styles }}" align="right" width="15%">
                    {{ number_format($parent['balance'], (int) $currency->decimal_places, ',', '.') }}</td>
                <td style="font-size:9rem; {{ $styles }}" align="right" width="20%">
                    {{ number_format($parent['beginningBalance'] + $parent['balance'], (int) $currency->decimal_places, ',', '.') }}
                </td>
            </tr>
            @if ($parent['code'][0] == 6 && !$next)
                @if (array_key_exists('6.0.0.00.00.00.000', $records))
                    <tr style="background-color: #BDBDBD;">
                        <td style="font-size:9rem;"></td>
                        <td style="font-size:9rem;font-weight: bold;" align="right">
                            TOTAL GASTOS
                        </td>
                        <td style="font-size:9rem;font-weight: bold;" align="right" width="15%">
                            {{ number_format($records['6.0.0.00.00.00.000']['beginningBalance'], (int) $currency->decimal_places, ',', '.') }}
                        </td>
                        <td style="font-size:9rem;font-weight: bold;" align="right" width="15%">
                            {{ number_format($records['6.0.0.00.00.00.000']['balance'], (int) $currency->decimal_places, ',', '.') }}
                        </td>
                        <td style="font-size:9rem;font-weight: bold;" align="right">
                            {{ number_format($records['6.0.0.00.00.00.000']['beginningBalance'] + $records['6.0.0.00.00.00.000']['balance'], (int) $currency->decimal_places, ',', '.') }}
                        </td>
                    </tr>
                @endif
                <br>
                <tr style="background-color: #BDBDBD;">
                    <td style="font-size:9rem;" width="12%"></td>
                    <td style="font-size:9rem;" align="right" width="38%">RESULTADO DEL EJERCICIO</td>
                    <td style="font-size:9rem;" align="right" width="15%"></td>
                    <td style="font-size:9rem;" align="right" width="15%"></td>

                    <td style="font-size:9rem;" align="right" width="20%">
                        {{ number_format($result_of_the_excersice, (int) $currency->decimal_places, ',', '.') }}</td>
                </tr>
            @endif
        @endforeach
    @endif
    {{-- Fin Nivel 1 --}}
</table>
