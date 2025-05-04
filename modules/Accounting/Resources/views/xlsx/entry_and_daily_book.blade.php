<!DOCTYPE html>
<html>

<body>
    @php
        /* total por el debe */
        $totDebit = 0.0;

        /* total por el haber */
        $totAssets = 0.0;

        /* contador de operaciones con asientos contables */
        $cont = 1;

    @endphp
    <table>
        <thead>
            <tr>
                <th>
                    <h3 align="center">LIBRO DIARIO DESDE {{ $initDate }} HASTA {{ $endDate }} </h3>
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
    @if ($Entry)
        @php
            $from_date = explode('-', $entry['from_date']);
            $concept = strip_tags($entry['concept']);
            $observations = strip_tags($entry['observations']);
        @endphp
        {{-- Pdf de asiento contable --}}
        <table cellspacing="0" cellpadding="1" border="0">
            <tr>
                {{-- se formatea la fecha de Y-m-d a d-m-Y --}}
                <th> Asiento Contable del {{ $from_date[2] . '-' . $from_date[1] . '-' . $from_date[0] }}</th>
                <th> Ref.: {{ $entry['reference'] }}</th>
            </tr>
            <tr>
                <th> Concepto: {{ $concept }}</th>
                <th> Observaciones: {{ $observations }}</th>
            </tr>
            {{-- Cuentas patrimoniales --}}
            <table cellspacing="1" cellpadding="1" border="0">
                <tr>
                    <th align="center">CÓDIGO</th>
                    <th align="center">CUENTAS</th>
                    <th align="center">DEBE</th>
                    <th align="center">HABER</th>
                </tr>
                @foreach ($entry['accountingAccounts'] as $entryAccount)
                    <tr>
                        <td align="center">
                            {{ $entryAccount['account']->getCodeAttribute() }}
                        </td>
                        <td>
                            {{ ' ' . $entryAccount['account']['denomination'] }}
                            @if (!empty($entryAccount['bank_reference']))
                                Ref: <strong>{{ $entryAccount['bank_reference'] }}</strong>
                            @endif
                        </td>
                        <td align="right">
                            {{ ' ' . number_format($entryAccount['debit'], (int) $currency->decimal_places, ',', '.') }}
                        </td>
                        <td align="right">
                            {{ ' ' . number_format($entryAccount['assets'], (int) $currency->decimal_places, ',', '.') }}
                        </td>
                        @php
                            $totDebit = $totDebit + $entryAccount['debit'];
                            $totAssets = $totAssets + $entryAccount['assets'];
                        @endphp
                    </tr>
                @endforeach
            </table>
            <br><br>
            <table cellspacing="0" cellpadding="1" border="0">
                <tr>
                    <td></td>
                    <td align="center">TOTAL DEBE</td>
                    <td align="center">TOTAL HABER</td>
                </tr>
                <tr>
                    <td></td>
                    <td align="right">
                        {{ ' ' . number_format($totDebit, (int) $currency->decimal_places, ',', '.') }}
                    </td>
                    <td align="right">
                        {{ ' ' . number_format($totAssets, (int) $currency->decimal_places, ',', '.') }}
                    </td>
                </tr>
            </table>
        </table>
    @else
        {{-- reporte de libro diario --}}
        <table cellspacing="0" cellpadding="1" border="1">
            <tr>
                <th align="center">Fecha</th>
                <th align="center">Concepto</th>
                <th align="center">Código</th>
                <th align="center">Cuentas</th>
                <th align="center">Debe</th>
                <th align="center">Haber</th>
            </tr>
        </table>
        @foreach ($entries as $entry)
            <table cellspacing="0" cellpadding="1" border="0">
                <tr>
                    {{-- se formatea la fecha de Y-m-d a d-m-Y --}}
                    <td align="left"> {{ $entry['from_date'] }}</td>
                    <td></td>
                    <td></td>
                    <td align="center">
                        {{ $cont }}
                        @php
                            $cont++;
                        @endphp
                    </td>
                    <td></td>
                    <td></td>
                </tr>
                @foreach ($entry['accountingAccounts'] as $entryAccount)
                    <tr>
                        <td></td>
                        <td>
                            {{ $entryAccount['concept'] }}
                        </td>
                        <td align="center"> {{ $entryAccount['code'] }}</td>
                        <td>
                            {{ ' ' . $entryAccount['denomination'] }}
                            @if (!empty($entryAccount['bank_reference']))
                                Ref: <strong>{{ $entryAccount['bank_reference'] }}</strong>
                            @endif
                        </td>
                        <td align="right">
                            {{ ' ' . number_format($entryAccount['debit'], (int) $currency->decimal_places, ',', '.') }}
                        </td>
                        <td align="right">
                            {{ ' ' . number_format($entryAccount['assets'], (int) $currency->decimal_places, ',', '.') }}
                        </td>
                        @php
                            $totDebit = $totDebit + $entryAccount['debit'];
                            $totAssets = $totAssets + $entryAccount['assets'];
                        @endphp
                    </tr>
                @endforeach
            </table>
        @endforeach
        <table cellspacing="0" cellpadding="1" border="0">
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td align="center">TOTAL DEBE</td>
                <td align="center">TOTAL HABER</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td align="right">
                    {{ number_format($totDebit, (int) $currency->decimal_places, ',', '.') }}
                </td>
                <td align="right">
                    {{ number_format($totAssets, (int) $currency->decimal_places, ',', '.') }}
                </td>
            </tr>
        </table>
    @endif
