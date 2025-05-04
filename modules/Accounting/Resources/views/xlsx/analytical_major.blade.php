<!DOCTYPE html>
<html>

<body>
    <table>
        <thead>
            <tr>
                <th>
                    <h3 align="center"> MAYOR ANALÍTICO DESDE {{ $initDate }} HASTA {{ $endDate }} </h3>
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
                    <h5 align="center"> EXPRESADO EN {{ $currency->symbol }}</h5>
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
    @foreach ($records as $record)
        @php
            /* saldo acumulado en cada iteración por cuenta */
            $previousBalance = 0.0;
            /* inializamos con el saldo de la cuenta */
            $previousBalance = $record['balance'];
            /* total del saldo de la cuenta */
            $totBalance = 0.0;

            /* total por el debe de una cuenta */
            $totDebit = 0.0;

            /* total por el haber de una cuenta */
            $totAssets = 0.0;

        @endphp

        {{-- HEADER de tablas --}}
        <div style="text-align: center;">
            <h4 style="font-size: 9rem;">MAYOR ANALÍTICO {{ $record['denomination'] }} ( {{ $record['code'] }} )</h4>
        </div>
        <br>
        <!-- @if ($typeBalance === 'fiscal')
<div style="text-align: right;padding-top:0;padding-bottom: 0rem;">
   <h5 style="font-size: 9rem;">SALDO ANTERIOR {{ $record['balance'] != 0 ? number_format($record['balance'], (int) $currency->decimal_places, ',', '.') : '0,00' }} </h5>
  </div>
@endif
  @if ($typeBalance === 'month')
<div style="text-align: right;padding-top:0;padding-bottom: 0rem;">
   <h5 style="font-size: 9rem;">SALDO MES ANTERIOR {{ $record['balance'] != 0 ? number_format($record['balance'], (int) $currency->decimal_places, ',', '.') : '0,00' }} </h5>
  </div>
@endif -->
        <table cellspacing="0" cellpadding="1" border="0">
            <tr style="background-color: #BDBDBD;">
                <th width="9%" align="center" style="font-size: 9rem;">FECHA</th>
                <th width="15%" align="center" style="font-size: 9rem;">REFERENCIA</th>
                <th width="28%" align="center" style="font-size: 9rem;">DESCRIPCIÓN DE LA OPERACIÓN</th>
                <th width="16%" align="center" style="font-size: 9rem;">DEBE</th>
                <th width="16%" align="center" style="font-size: 9rem;">HABER</th>
                <th width="16%" align="center" style="font-size: 9rem;">SALDO FINAL</th>
            </tr>
            <tr>
                <td style="font-size: 9rem;"></td>
                <td style="font-size: 9rem;"></td>
                <td style="font-size: 9rem;">Saldo Inicial</td>
                <td style="font-size: 9rem;" align="right">
                </td>
                <td style="font-size: 9rem;" align="right">
                </td>
                <td style="font-size: 9rem;" align="right">
                    @if ($typeBalance === 'fiscal')
                        {{ $record['balance'] != 0 ? number_format($record['balance'], (int) $currency->decimal_places, ',', '.') : '0,00' }}
                    @endif
                    @if ($typeBalance === 'month')
                        {{ $record['balance'] != 0 ? number_format($record['balance'], (int) $currency->decimal_places, ',', '.') : '0,00' }}
                    @endif
                </td>
            </tr>
            @foreach ($record['entryAccount'] as $r)
                @if ($r['entries'])
                    <tr>
                        <td style="font-size: 9rem;"> {{ $r['entries']['created_at'] }}</td>
                        <td style="font-size: 9rem;"> {{ $r['entries']['reference'] }}</td>
                        <td style="font-size: 9rem;"> {{ strip_tags($r['entries']['concept']) }}</td>
                        <td style="font-size: 9rem;" align="right">
                            {{ number_format($r['debit'], (int) $currency->decimal_places, ',', '.') }}
                            @php
                                // se realizan los calculos para el saldo total por el debe
                                $totDebit += (float) $r['debit'];
                            @endphp
                        </td>
                        <td style="font-size: 9rem;" align="right">
                            {{ number_format($r['assets'], (int) $currency->decimal_places, ',', '.') }}
                            @php
                                // se realizan los calculos para el saldo total por el haber
                                $totAssets += (float) $r['assets'];
                            @endphp
                        </td>
                        <td style="font-size: 9rem;" align="right">
                            @php
                                switch ($record['code'][0]) {
                                    case 1:
                                        $totBalance =
                                            (float) $previousBalance + (float) $r['debit'] - (float) $r['assets'];
                                        break;
                                    case 2:
                                        $totBalance =
                                            (float) $previousBalance + (float) $r['assets'] - (float) $r['debit'];
                                        break;
                                    case 3:
                                        $totBalance =
                                            (float) $previousBalance + (float) $r['assets'] - (float) $r['debit'];
                                        break;
                                    case 4:
                                        $totBalance =
                                            (float) $previousBalance + (float) $r['debit'] - (float) $r['assets'];
                                        break;
                                    case 5:
                                        $totBalance =
                                            (float) $previousBalance + (float) $r['assets'] - (float) $r['debit'];
                                        break;
                                    case 6:
                                        $totBalance =
                                            (float) $previousBalance + (float) $r['debit'] - (float) $r['assets'];
                                        break;
                                }
                                $previousBalance = $totBalance;
                            @endphp
                            {{ number_format($totBalance, (int) $currency->decimal_places, ',', '.') }}
                        </td>
                    </tr>
                @endif
            @endforeach
            <tr>
                <td></td>
                <td style="font-size: 9rem; background-color: #BDBDBD;"></td>
                <td style="font-size: 9rem; background-color: #BDBDBD;" align="right"> TOTAL CUENTA</td>
                <td style="font-size: 9rem; background-color: #BDBDBD;" align="right">
                    {{ number_format($totDebit, (int) $currency->decimal_places, ',', '.') }} </td>
                <td style="font-size: 9rem; background-color: #BDBDBD;" align="right">
                    {{ number_format($totAssets, (int) $currency->decimal_places, ',', '.') }} </td>
                <td style="font-size: 9rem; background-color: #BDBDBD;" align="right">
                    {{ number_format($totBalance, (int) $currency->decimal_places, ',', '.') }} </td>
            </tr>
        </table>
        <br>
    @endforeach
