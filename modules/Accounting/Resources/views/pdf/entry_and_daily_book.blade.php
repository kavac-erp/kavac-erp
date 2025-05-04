@php
    /* total por el debe */
    $totDebit = 0.0;

    /* total por el haber */
    $totAssets = 0.0;

    /* contador de operaciones con asientos contables */
    $cont = 1;

@endphp

@if (isset($initDate) || isset($endDate))
    <h2 align="center" style="font-size: 8rem;">LIBRO DIARIO DESDE {{ $initDate }} HASTA {{ $endDate }} </h2>
@else
    <h2 align="center" style="font-size: 8rem;">ASIENTO CONTABLE </h2>
@endif
<h4 style="font-size:8rem;">Expresado en {{ $currency->symbol }}</h4>

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
            <th style="font-size:8rem;" width="50%"> Asiento Contable del
                {{ $from_date[2] . '-' . $from_date[1] . '-' . $from_date[0] }}</th>
            <th style="font-size:8rem;" width="50%"> Ref.: {{ $entry['reference'] }}</th>
        </tr>
        <tr>
            <th style="font-size:8rem;" width="50%"> Concepto: {{ $concept }}</th>
            <th style="font-size:8rem;" width="50%"> Observaciones: {{ $observations }}</th>
        </tr>
        {{-- Cuentas patrimoniales --}}
        <table cellspacing="1" cellpadding="1" border="0">
            <tr>
                <th width="14%" style="font-size:8rem; background-color: #BDBDBD;" align="center">CÓDIGO</th>
                <th width="51%" style="font-size:8rem; background-color: #BDBDBD;" align="center">CUENTAS</th>
                <th width="17%" style="font-size:8rem; background-color: #BDBDBD;" align="center">DEBE</th>
                <th width="17%" style="font-size:8rem; background-color: #BDBDBD;" align="center">HABER</th>
            </tr>
            @foreach ($entry['accountingAccounts'] as $entryAccount)
                <tr>
                    <td style="font-size: 8rem;" align="center">
                        {{ $entryAccount['account']->getCodeAttribute() }}
                    </td>
                    <td style="font-size:8rem;">
                        {{ ' ' . $entryAccount['account']['denomination'] }}
                        @if (!empty($entryAccount['bank_reference']))
                            Ref: <strong>{{ $entryAccount['bank_reference'] }}</strong>
                        @endif
                    </td>
                    <td style="font-size:8rem;" align="right">
                        {{ ' ' . number_format($entryAccount['debit'], (int) $currency->decimal_places, ',', '.') }}
                    </td>
                    <td style="font-size:8rem;" align="right">
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
            <tr style="background-color: #BDBDBD;">
                <td style="font-size:8rem;" width="65%"></td>
                <td style="font-size:8rem;" width="17%" align="center">TOTAL DEBE</td>
                <td style="font-size:8rem;" width="17%" align="center">TOTAL HABER</td>
            </tr>
            <tr>
                <td style="font-size:8rem;"></td>
                <td style="font-size:8rem;" align="right">
                    {{ ' ' . number_format($totDebit, (int) $currency->decimal_places, ',', '.') }}
                </td>
                <td style="font-size:8rem;" align="right">
                    {{ ' ' . number_format($totAssets, (int) $currency->decimal_places, ',', '.') }}
                </td>
            </tr>
        </table>
    </table>
@else
    {{-- reporte de libro diario --}}
    <table cellspacing="0" cellpadding="1" border="1">
        <tr>
            <th style="font-size:8rem;" width="10%" align="center">Fecha</th>
            <th width="14%" style="font-size:8rem; " align="center">Concepto</th>
            <th style="font-size:8rem;" width="15%" align="center">Código</th>
            <th style="font-size:8rem;" width="35%" align="center">Cuentas</th>
            <th style="font-size:8rem;" width="13%" align="center">Debe</th>
            <th style="font-size:8rem;" width="13%" align="center">Haber</th>
        </tr>
    </table>
    @foreach ($entries as $entry)
        <table cellspacing="0" cellpadding="1" border="0">
            <tr>
                {{-- se formatea la fecha de Y-m-d a d-m-Y --}}
                <td width="10%" style="font-size:8rem; background-color: #BDBDBD;" align="left">
                    {{ $entry['from_date'] }}</td>
                <td width="14%" style="font-size:8rem; background-color: #BDBDBD;"></td>
                <td width="15%" style="font-size:8rem; background-color: #BDBDBD;"></td>
                <td width="35%" style="font-size:8rem; background-color: #BDBDBD;" align="center">
                    {{ $cont }}
                    @php
                        $cont++;
                    @endphp
                </td>
                <td width="13%" style="font-size:8rem; background-color: #BDBDBD;"></td>
                <td width="13%" style="font-size:8rem; background-color: #BDBDBD;"></td>
            </tr>
            @foreach ($entry['accountingAccounts'] as $entryAccount)
                <tr>
                    <td style="font-size:8rem;"></td>
                    <td style="font-size:8rem;">
                        {{ $entryAccount['concept'] }}
                    </td>
                    <td style="font-size:8rem;" align="center"> {{ $entryAccount['code'] }}</td>
                    <td style="font-size:8rem;">
                        {{ ' ' . $entryAccount['denomination'] }}
                        @if (!empty($entryAccount['bank_reference']))
                            Ref: <strong>{{ $entryAccount['bank_reference'] }}</strong>
                        @endif
                    </td>
                    <td style="font-size:8rem;" align="right">
                        {{ ' ' . number_format($entryAccount['debit'], (int) $currency->decimal_places, ',', '.') }}
                    </td>
                    <td style="font-size:8rem;" align="right">
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
        <tr style="background-color: #BDBDBD;">
            <td style="font-size:8rem;" width="70%"></td>
            <td style="font-size:8rem;" width="15%" align="center">TOTAL DEBE</td>
            <td style="font-size:8rem;" width="15%" align="center">TOTAL HABER</td>
        </tr>
        <tr>
            <td style="font-size:8rem;"></td>
            <td style="font-size:8rem;" align="right">
                {{ number_format($totDebit, (int) $currency->decimal_places, ',', '.') }}
            </td>
            <td style="font-size:8rem;" align="right">
                {{ number_format($totAssets, (int) $currency->decimal_places, ',', '.') }}
            </td>
        </tr>
    </table>
@endif
