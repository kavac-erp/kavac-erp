@php
    /* total por el debe de una cuenta */
    $subTotDebit = 0.0;

    /* total por el haber de una cuenta */
    $subTotAssets = 0.0;

    /* saldo de la cuenta */
    $totalDebit = 0.0;

    /* saldo de la cuenta */
    $totalAssets = 0.0;

@endphp
{{-- si la cuenta tiene una de nivel superior la muesta de lo contrario solo muesta la cuenta --}}

<h2 align="center" style="font-size: 9rem;">AUXILIAR DESDE {{ $initDate }} AL {{ $endDate }}</h2>
<h4 style="font-size: 8rem;">EXPRESADO EN {{ $currency->symbol }}</h4>

<table cellspacing="0" cellpadding="1" border="0">
    <thead>
        <tr style="background-color: #BDBDBD;">
            <td style="font-size: 9rem;" width="16%" align="center">CÓDIGO</td>
            <td style="font-size: 9rem;" width="34%" align="center">CUENTA</td>
            <td style="font-size: 9rem;" width="18%" align="right">DEBITO</td>
            <td style="font-size: 9rem;" width="18%" align="right">CREDITO</td>
            <td style="font-size: 9rem;" width="14%" align="right">SALDO</td>
        </tr>
    </thead>
    <tbody>
        @foreach ($record as $rec)
            @php
                $subTotal = 0.0;
                $subTotDebit = 0.0;
                $subTotAssets = 0.0;
                $subPreTotal = 0.0;
            @endphp
            @foreach ($rec['entryAccount'] as $r)
                @if ($r['entries'])
                    @php
                        $subTotDebit += (float) $r['debit'];
                        $subTotAssets += (float) $r['assets'];
                    @endphp
                @endif
            @endforeach
            @if ($rec['entryAccount'] != [])
                <tr>
                    <td style="font-size: 9rem;" width="16%" align="left">{{ $rec['code'] }}</td>
                    <td style="font-size: 9rem;" width="34%" align="left">{{ $rec['denomination'] }}</td>
                    <td style="font-size: 9rem;" width="18%" align="right">

                    </td>
                    <td style="font-size: 9rem;" width="18%" align="right">

                    </td>
                    <td style="font-size: 9rem;" width="14%" align="right">

                    </td>
                </tr>
            @endif

            @foreach ($rec['entryAccount'] as $r)
                @if ($r['entries'])
                    @php
                        switch ($rec['code'][0]) {
                            case 1:
                                $subPreTotal += (float) $r['debit'];
                                $subPreTotal -= (float) $r['assets'];
                                break;
                            case 2:
                                $subPreTotal += (float) $r['assets'];
                                $subPreTotal -= (float) $r['debit'];
                                break;
                            case 3:
                                $subPreTotal += (float) $r['assets'];
                                $subPreTotal -= (float) $r['debit'];
                                break;
                            case 4:
                                $subPreTotal += (float) $r['debit'];
                                $subPreTotal -= (float) $r['assets'];
                                break;
                            case 5:
                                $subPreTotal += (float) $r['assets'];
                                $subPreTotal -= (float) $r['debit'];
                                break;
                            case 6:
                                $subPreTotal += (float) $r['debit'];
                                $subPreTotal -= (float) $r['assets'];
                                break;
                        }

                    @endphp
                    <tr>
                        <td style="font-size: 9rem;" width="16%" align="left"></td>
                        <td style="font-size: 7rem;" width="34%" align="left">
                            {{ $r['entries']['concept'] }}
                        </td>
                        <td style="font-size: 9rem;" width="18%" align="right">
                            {{ number_format($r['debit'], (int) $currency->decimal_places, ',', '.') }}
                        </td>
                        <td style="font-size: 9rem;" width="18%" align="right">
                            {{ number_format($r['assets'], (int) $currency->decimal_places, ',', '.') }}
                        </td>
                        <td style="font-size: 9rem;" width="14%" align="right">
                            {{ number_format($subPreTotal, (int) $currency->decimal_places, ',', '.') }}
                        </td>
                    </tr>
                @endif
            @endforeach
            @php
                $totalDebit += $subTotDebit;
                $totalAssets += $subTotAssets;
            @endphp
        @endforeach

    </tbody>
</table>

<table cellspacing="0" cellpadding="1" border="0">
    <thead>
        <tr style="background-color: #BDBDBD;">
            <td style="font-size: 9rem;" width="50%"></td>
            <td style="font-size: 9rem;" width="18%" align="right">TOTAL DEBITO</td>
            <td style="font-size: 9rem;" width="18%" align="right">TOTAL CREDITO</td>
            <td style="font-size: 9rem;" width="14%" align="right">TOTAL SALDO</td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="font-size: 9rem;" width="46%"></td>
            <td style="font-size: 9rem;" width="20%" align="right">
                {{ $currency->symbol }}
                {{ number_format($totalDebit, (int) $currency->decimal_places, ',', '.') }}
            </td>
            <td style="font-size: 9rem;" width="20%" align="right">
                {{ $currency->symbol }}
                {{ number_format($totalAssets, (int) $currency->decimal_places, ',', '.') }}
            </td>
            <td style="font-size: 9rem;" width="14%" align="right">
                {{ $currency->symbol }}
                @php
                    switch ($rec['code'][0]) {
                        case 1:
                            $preCalculateTotal = $totalDebit - $totalAssets;
                            break;
                        case 2:
                            $preCalculateTotal = $totalAssets - $totalDebit;
                            break;
                        case 3:
                            $preCalculateTotal = $totalAssets - $totalDebit;
                            break;
                        case 4:
                            $preCalculateTotal = $totalDebit - $totalAssets;
                            break;
                        case 5:
                            $preCalculateTotal = $totalAssets - $totalDebit;
                            break;
                        case 6:
                            $preCalculateTotal = $totalDebit - $totalAssets;
                            break;
                    }

                @endphp
                {{ number_format($preCalculateTotal, (int) $currency->decimal_places, ',', '.') }}
            </td>
        </tr>
    </tbody>
</table>
