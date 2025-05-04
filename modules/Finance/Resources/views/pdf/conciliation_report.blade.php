@php
    use Modules\Finance\Models\FinanceConciliation;

	$total_bank = $conciliation->bank_balance;
	$total_system = calculateBalanceSystem($conciliation);

    function calculateBalanceSystem(FinanceConciliation $conciliation) {
        $system_balance = $conciliation->system_balance;

        foreach ($conciliation->financeConciliationBankMovements as $key => $mov) {
            if ($mov->debit != 0) {
                $system_balance += $mov->debit;
            } else {
                $system_balance -= $mov->assets;
            }
        }

        return $system_balance;
    }

@endphp

<h1 align="center" style="font-size: 9rem;">Conciliación Bancaria {!! $conciliation->financeBankAccount->description !!} </h1>
<h4 style="font-size:8rem;">Expresado en {{ $currency->symbol }}</h4>

<p>Periodo de <strong>{{ $conciliation->start_date }}</strong> a <strong>{{ $conciliation->end_date }}</strong></p>
<p>Nro. Cuenta: <strong>{{ $conciliation->financeBankAccount->ccc_number }}</strong></p>
<p>Saldo inicial en sistema: <strong>{{ $conciliation->system_balance }} {{ $currency->symbol }}</strong></p>

<h5 style="text-align: center">LISTADO DE CONCILIACIONES</h5>
    <table style="font-size: 0.85em;padding:1px;">
        <thead>
            <tr style="background-color:#79a2d0;font-size: 1.1em">
                <th style="text-align: center; font-weight:bold;" width="10%">Fecha</th>
                <th style="text-align: center; font-weight:bold;" width="10%">Código de movimiento</th>
                <th style="text-align: center; font-weight:bold;" width="10%">Referencia Bancaria</th>
                <th style="text-align: center; font-weight:bold;" width="22.5%">Concepto en sistema</th>
                <th style="text-align: center; font-weight:bold;" width="22.5%">Concepto Bancario</th>
                <th style="text-align: center; font-weight:bold;" width="12.5%">Débito {{$currency->symbol}}</th>
                <th style="text-align: center; font-weight:bold;" width="12.5%">Crédito {{$currency->symbol}}</th>
            </tr>
        </thead>
        <tbody>
            @php
                $index = 1;
            @endphp
            @foreach ($conciliation->financeConciliationBankMovements as $mov)
                @php
                    $backround = $index % 2 == 0 ? '#adbfd3' : '#ffffff';
                @endphp
                <tr style="background-color:{{ $backround }};">
                    <td style="text-align: center;" width="10%">{{ $mov->accountingEntryAccount->entries->from_date }}</td>
                    <td style="text-align: center;" width="10%">{{ $mov->accountingEntryAccount->entries->reference }}</td>
                    <td width="10%">{{ $mov->accountingEntryAccount->bank_reference }}</td>
                    <td width="22.5%">{{ $mov->accountingEntryAccount->entries->concept }}</td>
                    <td width="22.5%">{{ $mov->concept }}</td>
                    <td style="text-align: center;" width="12.5%">{{ $mov->debit == 0 ? '-' : $mov->debit.' '.$currency->symbol }}</td>
                    <td style="text-align: center;" width="12.5%">{{ $mov->assets == 0 ? '-' : $mov->assets.' '.$currency->symbol }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <br>
    <br>
    <br>
    <table style="font-size: 0.9em;padding:1px;">
        <thead>
            <tr style="font-size: 1.1em">
                <td width="50%"></td>
                <td width="25%" style=" background-color:#79a2d0;text-align: center; font-weight:bold;">Balance en banco</td>
                <td width="25%" style=" background-color:#79a2d0;text-align: center; font-weight:bold;">Balance en sistema</td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="50%"></td>
                <td width="25%" style="text-align: center;">
                    {{ $total_bank }} {{ $currency->symbol }}
                </td>
                <td width="25%" style="text-align: center;">
                    {{ $total_system }} {{ $currency->symbol }}
                </td>
            </tr>
        </tbody>
    </table>
    <br><br>
    <table style="font-size: 0.9em;padding:1px;">
        <thead>
            <tr style="font-size: 1.1em">
                <td width="75%"></td>
                <td width="25%" style="background-color:#79a2d0; text-align: center; font-weight:bold;">Diferencia</td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="75%"></td>
                <td width="25%" style="text-align: center;">
                    {{ abs($total_bank - ($total_system)) }} {{ $currency->symbol }}
                </td>
            </tr>
        </tbody>
    </table>