@php

    /* Balance total inicial */
    $totInitBalance = 0;

    /* Balance total con los valores iniciales sumados */
    $totBalance = 0;

    /* Tipo de cuenta */
    $account = 1;

    /* Variable para determinar si existe la cuenta */
    $countAccount1 = false;
    $countAccount2 = false;
    $countAccount3 = false;
    $countAccount4 = false;
    $countAccount5 = false;
    $countAccount6 = false;

@endphp

<h2 style="font-size: 9rem;" align="center">ESTADO DE MOVIMIENTO DE LAS CUENTAS DE PATRIMONIO DESDE {{ $initDate }}
    HASTA {{ $endDate }}</h2>
<h4 style="font-size: 9rem;">EXPRESADO EN {{ $currency['symbol'] }}</h4>
<br>
<table cellspacing="0" cellpadding="1" border="1">
    {{-- Header de la tabla --}}
    <tr>
        <th style="font-size: 9rem;" width="14%" align="center">CÓDIGO</th>
        <th style="font-size: 9rem;" width="36%" align="center">CUENTA</th>
        <th style="font-size: 9rem;" width="25%" align="center">SALDO INICIAL</th>
        <th style="font-size: 9rem;" width="25%" align="center">SALDO FINAL</th>
    </tr>
</table>
<table cellspacing="0" cellpadding="1" border="0">
    @for ($account = 1; ; $account++)
        @if ($account == 7)
            @php
                break;
            @endphp
        @endif
        <tr style="background-color: #BDBDBD;">
            @if ($account == 1)
                <th style="font-size: 9rem; font-weight: bold;" width="100%" align="center">CAPITAL INSTITUCIONAL</th>
            @endif
            @if ($account == 2)
                <th style="font-size: 9rem; font-weight: bold;" width="100%" align="center">TRANSFERENCIAS DE CAPITAL
                </th>
            @endif
            @if ($account == 3)
                <th style="font-size: 9rem; font-weight: bold;" width="100%" align="center">DONACIONES DE CAPITAL</th>
            @endif
            @if ($account == 4)
                <th style="font-size: 9rem; font-weight: bold;" width="100%" align="center">RESULTADOS DEL EJERCICIO
                </th>
            @endif
            @if ($account == 5)
                <th style="font-size: 9rem; font-weight: bold;" width="100%" align="center">RESULTADOS ACUMULADOS</th>
            @endif
            @if ($account == 6)
                <th style="font-size: 9rem; font-weight: bold;" width="100%" align="center">AJUSTES DE RESULTADOS
                    ACUMULADOS</th>
            @endif
        </tr>
        @foreach ($records as $record)
            @if ($account == 1)
                @if (
                    $countAccount1 == false &&
                        explode('.', $record['code'])[1] == '2' &&
                        explode('.', $record['code'])[2] == '1' &&
                        explode('.', $record['code'])[6] != '000')
                    @php
                        $totInitBalance += $record['beginningBalance'];
                        $totBalance += $record['total_balance'];
                        $countAccount1 = true;
                    @endphp
                    <tr>
                        <td style="font-size: 9rem;" width="14%" align="center"> {{ $record['code'] }}</td>
                        <td style="font-size: 9rem;" width="36%" align="center"> {{ $record['denomination'] }}</td>
                        {{-- Saldo inicial --}}
                        <td style="font-size: 9rem;" width="25%" align="right">
                            {{ number_format($record['beginningBalance'], (int) $currency['decimal_places'], ',', '.') }}
                        </td>
                        {{-- Saldo final --}}
                        <td style="font-size: 9rem;" width="25%" align="right">
                            {{ number_format($record['total_balance'], (int) $currency['decimal_places'], ',', '.') }}
                        </td>
                    </tr>
                @elseif ($countAccount1 == false)
                    <tr>
                        @php
                            $countAccount1 = true;
                        @endphp
                        <td style="font-size: 9rem;" width="14%" align="center"></td>
                        <td style="font-size: 9rem;" width="36%" align="center"></td>
                        {{-- Saldo inicial --}}
                        <td style="font-size: 9rem;" width="25%" align="right">
                            {{ number_format(0, (int) $currency['decimal_places'], ',', '.') }}
                        </td>
                        {{-- Saldo final --}}
                        <td style="font-size: 9rem;" width="25%" align="right">
                            {{ number_format(0, (int) $currency['decimal_places'], ',', '.') }}
                        </td>
                    </tr>
                @endif
            @endif
            @if ($account == 2)
                @if (explode('.', $record['code'])[1] == '2' &&
                        explode('.', $record['code'])[2] == '2' &&
                        explode('.', $record['code'])[3] == '01' &&
                        explode('.', $record['code'])[6] != '000')
                    <tr>
                        @php
                            $totInitBalance += $record['beginningBalance'];
                            $totBalance += $record['total_balance'];
                            $countAccount2 = true;
                        @endphp
                        <td style="font-size: 9rem;" width="14%" align="center"> {{ $record['code'] }}</td>
                        <td style="font-size: 9rem;" width="36%" align="center"> {{ $record['denomination'] }}</td>
                        {{-- Saldo inicial --}}
                        <td style="font-size: 9rem;" width="25%" align="right">
                            {{ number_format($record['beginningBalance'], (int) $currency['decimal_places'], ',', '.') }}
                        </td>
                        {{-- Saldo final --}}
                        <td style="font-size: 9rem;" width="25%" align="right">
                            {{ number_format($record['total_balance'], (int) $currency['decimal_places'], ',', '.') }}
                        </td>
                    </tr>
                @elseif ($countAccount2 == false)
                    <tr>
                        @php
                            $countAccount2 = true;
                        @endphp
                        <td style="font-size: 9rem;" width="14%" align="center"></td>
                        <td style="font-size: 9rem;" width="36%" align="center"></td>
                        {{-- Saldo inicial --}}
                        <td style="font-size: 9rem;" width="25%" align="right">
                            {{ number_format(0, (int) $currency['decimal_places'], ',', '.') }}
                        </td>
                        {{-- Saldo final --}}
                        <td style="font-size: 9rem;" width="25%" align="right">
                            {{ number_format(0, (int) $currency['decimal_places'], ',', '.') }}
                        </td>
                    </tr>
                @endif
            @endif
            @if ($account == 3)
                @if (explode('.', $record['code'])[1] == '2' &&
                        explode('.', $record['code'])[2] == '2' &&
                        explode('.', $record['code'])[3] == '02' &&
                        explode('.', $record['code'])[6] != '000')
                    <tr>
                        @php
                            $totInitBalance += $record['beginningBalance'];
                            $totBalance += $record['total_balance'];
                            $countAccount3 = true;
                        @endphp
                        <td style="font-size: 9rem;" width="14%" align="center"> {{ $record['code'] }}</td>
                        <td style="font-size: 9rem;" width="36%" align="center"> {{ $record['denomination'] }}</td>
                        {{-- Saldo inicial --}}
                        <td style="font-size: 9rem;" width="25%" align="right">
                            {{ number_format($record['beginningBalance'], (int) $currency['decimal_places'], ',', '.') }}
                        </td>
                        {{-- Saldo final --}}
                        <td style="font-size: 9rem;" width="25%" align="right">
                            {{ number_format($record['total_balance'], (int) $currency['decimal_places'], ',', '.') }}
                        </td>
                    </tr>
                @elseif ($countAccount3 == false)
                    <tr>
                        @php
                            $countAccount3 = true;
                        @endphp
                        <td style="font-size: 9rem;" width="14%" align="center"></td>
                        <td style="font-size: 9rem;" width="36%" align="center"></td>
                        {{-- Saldo inicial --}}
                        <td style="font-size: 9rem;" width="25%" align="right">
                            {{ number_format(0, (int) $currency['decimal_places'], ',', '.') }}
                        </td>
                        {{-- Saldo final --}}
                        <td style="font-size: 9rem;" width="25%" align="right">
                            {{ number_format(0, (int) $currency['decimal_places'], ',', '.') }}
                        </td>
                    </tr>
                @endif
            @endif
            @if ($account == 4)
                @if (explode('.', $record['code'])[1] == '2' &&
                        explode('.', $record['code'])[2] == '5' &&
                        explode('.', $record['code'])[3] == '02' &&
                        explode('.', $record['code'])[6] != '000' &&
                        ($record['beginningBalance'] != 0 || $record['total_balance'] != 0))
                    <tr>
                        @php
                            $totInitBalance += $record['beginningBalance'];
                            $totBalance += $record['total_balance'];
                            $countAccount4 = true;
                        @endphp
                        <td style="font-size: 9rem;" width="14%" align="center"> {{ $record['code'] }}</td>
                        <td style="font-size: 9rem;" width="36%" align="center"> {{ $record['denomination'] }}
                        </td>
                        {{-- Saldo inicial --}}
                        <td style="font-size: 9rem;" width="25%" align="right">
                            {{ number_format($record['beginningBalance'], (int) $currency['decimal_places'], ',', '.') }}
                        </td>
                        {{-- Saldo final --}}
                        <td style="font-size: 9rem;" width="25%" align="right">
                            {{ number_format($record['total_balance'], (int) $currency['decimal_places'], ',', '.') }}
                        </td>
                    </tr>
                @elseif ($countAccount4 == false && $record['total_balance'] == 0)
                    <tr>
                        @php
                            $countAccount4 = true;
                        @endphp
                        <td style="font-size: 9rem;" width="14%" align="center"></td>
                        <td style="font-size: 9rem;" width="36%" align="center"></td>
                        {{-- Saldo inicial --}}
                        <td style="font-size: 9rem;" width="25%" align="right">
                            {{ number_format(0, (int) $currency['decimal_places'], ',', '.') }}
                        </td>
                        {{-- Saldo final --}}
                        <td style="font-size: 9rem;" width="25%" align="right">
                            {{ number_format(0, (int) $currency['decimal_places'], ',', '.') }}
                        </td>
                    </tr>
                @endif
            @endif
            @if ($account == 5)
                @if (explode('.', $record['code'])[1] == '2' &&
                        explode('.', $record['code'])[2] == '5' &&
                        explode('.', $record['code'])[3] == '01' &&
                        explode('.', $record['code'])[6] != '000' &&
                        $record['total_balance'] != 0)
                    <tr>
                        @php
                            $totInitBalance += $record['beginningBalance'];
                            $totBalance += $record['total_balance'];
                            $countAccount5 = true;
                        @endphp
                        <td style="font-size: 9rem;" width="14%" align="center"> {{ $record['code'] }}</td>
                        <td style="font-size: 9rem;" width="36%" align="center"> {{ $record['denomination'] }}
                        </td>
                        {{-- Saldo inicial --}}
                        <td style="font-size: 9rem;" width="25%" align="right">
                            {{ number_format($record['beginningBalance'], (int) $currency['decimal_places'], ',', '.') }}
                        </td>
                        {{-- Saldo final --}}
                        <td style="font-size: 9rem;" width="25%" align="right">
                            {{ number_format($record['total_balance'], (int) $currency['decimal_places'], ',', '.') }}
                        </td>
                    </tr>
                @elseif ($countAccount5 == false)
                    <tr>
                        @php
                            $countAccount5 = true;
                        @endphp
                        <td style="font-size: 9rem;" width="14%" align="center"></td>
                        <td style="font-size: 9rem;" width="36%" align="center"></td>
                        {{-- Saldo inicial --}}
                        <td style="font-size: 9rem;" width="25%" align="right">
                            {{ number_format(0, (int) $currency['decimal_places'], ',', '.') }}
                        </td>
                        {{-- Saldo final --}}
                        <td style="font-size: 9rem;" width="25%" align="right">
                            {{ number_format(0, (int) $currency['decimal_places'], ',', '.') }}
                        </td>
                    </tr>
                @endif
            @endif
            @if ($account == 6)
                @if ($record['category'] == 'ARA' && ($record['beginningBalance'] != 0 || $record['total_balance'] != 0))
                    <tr>
                        @php
                            $totInitBalance += $record['beginningBalance'];
                            $totBalance += $record['total_balance'];
                            $countAccount6 = true;
                        @endphp
                        <td style="font-size: 9rem;" width="14%" align="center"> {{ $record['code'] }}</td>
                        <td style="font-size: 9rem;" width="36%" align="center"> {{ $record['denomination'] }}
                        </td>
                        {{-- Saldo inicial --}}
                        <td style="font-size: 9rem;" width="25%" align="right">
                            {{ number_format($record['beginningBalance'], (int) $currency['decimal_places'], ',', '.') }}
                        </td>
                        {{-- Saldo final --}}
                        <td style="font-size: 9rem;" width="25%" align="right">
                            {{ number_format($record['total_balance'], (int) $currency['decimal_places'], ',', '.') }}
                        </td>
                    </tr>
                @elseif ($countAccount6 == false)
                    <tr>
                        @php
                            $countAccount6 = true;
                        @endphp
                        <td style="font-size: 9rem;" width="14%" align="center"></td>
                        <td style="font-size: 9rem;" width="36%" align="center"></td>
                        {{-- Saldo inicial --}}
                        <td style="font-size: 9rem;" width="25%" align="right">
                            {{ number_format(0, (int) $currency['decimal_places'], ',', '.') }}
                        </td>
                        {{-- Saldo final --}}
                        <td style="font-size: 9rem;" width="25%" align="right">
                            {{ number_format(0, (int) $currency['decimal_places'], ',', '.') }}
                        </td>
                    </tr>
                @endif
            @endif
        @endforeach
    @endfor

    <tr style="background-color: #BDBDBD;">
        <td style="font-size: 9rem; font-weight: bold;" align="center" width="50%"><strong>TOTAL PATRIMONIO
                INSTITUCIONAL</strong></td>
        <td style="font-size: 9rem; font-weight: bold;" width="25%" align="right">
            {{ number_format($totInitBalance, (int) $currency['decimal_places'], ',', '.') }}
        </td>
        <td style="font-size: 9rem; font-weight: bold;" width="25%" align="right">
            {{ number_format($totBalance, (int) $currency['decimal_places'], ',', '.') }}
        </td>
    </tr>
</table>
