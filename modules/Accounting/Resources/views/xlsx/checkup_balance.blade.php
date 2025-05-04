<!DOCTYPE html>
<html>

<body>
    @php
        /* total por el debe */
        $totDebit = 0.0;

        /* total por el haber */
        $totAssets = 0.0;

        /* total deudor */
        $totDebitor = 0.0;

        /*  total acreedor */
        $totCreditor = 0.0;

    @endphp

    <br>
    <table>
        <thead>
            <tr>
                <th>
                    <h3 align="center">BALANCE DE COMPROBACIÓN DESDE {{ $initDate }} HASTA {{ $endDate }}</h3>
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
        {{-- Header de la tabla --}}
        <tr>
            <th align="center">CÓDIGO</th>
            <th align="center">CUENTA</th>
            <th align="center">SALDO INICIAL</th>
            <th></th>
            <th align="center">SUMAS</th>
            <th></th>
            <th align="center">SALDO FINAL</th>
            <th></th>
        </tr>
    </table>
    <table cellspacing="0" cellpadding="1" border="0">
        {{-- Header de la tabla --}}
        <tr>
            <td></td>
            <td></td>
            <td align="center">DEUDOR</td>
            <td align="center">ACREEDOR</td>
            <td align="center">DEBE</td>
            <td align="center">HABER</td>
            <td align="center">DEUDOR</td>
            <td align="center">ACREEDOR</td>
        </tr>

        @foreach ($records as $record)
            <tr>
                <td align="left"> {{ $record['code'] }}</td>
                <td align="left"> {{ $record['denomination'] }}</td>
                {{-- Saldo inicial --}}
                <td align="right">
                    @if ($record['beginningBalance'] >= 0)
                        {{ number_format($record['beginningBalance'], (int) $currency['decimal_places'], ',', '.') }}
                    @endif
                </td>
                <td align="right">
                    @if ($record['beginningBalance'] < 0)
                        {{ number_format(-$record['beginningBalance'], (int) $currency['decimal_places'], ',', '.') }}
                    @endif
                </td>

                {{-- Suma de saldos --}}
                <td align="right">
                    @if ($record['sum_debit'])
                        {{ number_format($record['sum_debit'], (int) $currency['decimal_places'], ',', '.') }}
                    @elseif(!$record['sum_assets'])
                        {{ number_format(0, (int) $currency['decimal_places'], ',', '.') }}
                    @endif
                </td>
                <td align="right">
                    @if ($record['sum_assets'])
                        {{ number_format(-$record['sum_assets'], (int) $currency['decimal_places'], ',', '.') }}
                    @elseif(!$record['sum_debit'])
                        {{ number_format(0, (int) $currency['decimal_places'], ',', '.') }}
                    @endif
                </td>

                {{-- Saldo final --}}
                <td align="right">
                    @if ($record['balance_debit'])
                        {{ number_format($record['balance_debit'], (int) $currency['decimal_places'], ',', '.') }}
                    @elseif(!$record['balance_assets'])
                        {{ number_format(0, (int) $currency['decimal_places'], ',', '.') }}
                    @endif
                </td>
                <td align="right">
                    @if ($record['balance_assets'])
                        {{ number_format(-$record['balance_assets'], (int) $currency['decimal_places'], ',', '.') }}
                    @elseif(!$record['balance_debit'])
                        {{ number_format(0, (int) $currency['decimal_places'], ',', '.') }}
                    @endif
                </td>
                @php
                    $totDebit += floatval($record['sum_debit']);
                    $totAssets += floatval($record['sum_assets']);
                    $totDebitor += floatval($record['balance_debit']);
                    $totCreditor += floatval($record['balance_assets']);
                @endphp
            </tr>
        @endforeach

        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td align="center"><strong>TOTALES</strong></td>
            <td></td>
            <td></td>
            <td align="right">
                {{ number_format($totDebit, (int) $currency['decimal_places'], ',', '.') }}
            </td>
            <td align="right">
                {{ number_format(-$totAssets, (int) $currency['decimal_places'], ',', '.') }}
            </td>
            <td align="right">

            </td>
            <td align="right">

            </td>
        </tr>
    </table>
</body>

</html>
