@if ($financePayOrder->document_status_id == '5')
    <h4 align="center">
        REGISTRO ANULADO
    </h4>
    <br>
@else
    <br>
@endif
<table>
    <tbody>
        @php
            $compromiseClass = 'Modules\Budget\Models\BudgetCompromise';
            $ordered_at = date("d/m/Y", strtotime($financePayOrder->ordered_at))
        @endphp
        <tr>
            <td>
                <strong>Institución:</strong> {{ $financePayOrder->institution->name }}
            </td>
        </tr>
        <tr>
            <td>
                <strong>Fecha de orden de pago:</strong> {{ $ordered_at }}
            </td>
        </tr>
        @if (isset($financePayOrder->documentSourceable))
            <tr>
                <td width="70%">
                    <strong>Nro. Documento de Origen:</strong> {{ $financePayOrder->documentSourceable->reference ?? $financePayOrder->documentSourceable->code ?? $financePayOrder->documentSourceable->name ?? '' }}
                </td>
            </tr>
            @if (isset($financePayOrder->startDate) && isset($financePayOrder->endDate))
                <tr>
                    <td>
                        <strong>Periodo de pago: </strong> Del {{ $financePayOrder->startDate }} al {{ $financePayOrder->endDate }}
                    </td>
                </tr>
            @endif
        @endif
        <tr>
            <td width="70%">
                <strong>Proveedor / Beneficiario:</strong> {{ $financePayOrder->receiver_name }}
            </td>
        </tr>
        @if ($financePayOrder->nameSourceable->receiverable_type != $compromiseClass &&
            $financePayOrder->nameSourceable->receiverable_type != 'App\Models\Institution' &&
            $financePayOrder->name_sourceable_type != 'App\Models\Receiver')
        <tr>
            <td colspan="2">
                <strong>R.I.F. / C.I.:</strong>
                {{
                    $financePayOrder->nameSourceable->rif ?? $financePayOrder->nameSourceable->dni ??
                    $financePayOrder->nameSourceable->payrollStaff->id_number ??
                    $financePayOrder->nameSourceable->payrollStaff->passport
                }}
            </td>
        </tr>
        @endif
        <tr>
            <td colspan="2">&#160;</td>
        </tr>
        <tr>
            <td colspan="2" class="text-justify font-weight-bold" style="font-size: 1.2em;font-weight:bold">
                {{
                    convertirNumeros(
                        number_format(
                            $financePayOrder->amount,
                            $financePayOrder->currency->decimal_places,
                            ".",
                            ""
                        ),
                        strtoupper($financePayOrder->currency->plural_name)
                    )
                }}
            </td>
            <td style="font-size: 1.3em">
                <strong>*** {{ $financePayOrder->currency->symbol  }} {{ number_format($financePayOrder->amount, $financePayOrder->currency->decimal_places, ",", ".") }} ***</strong>
            </td>
        </tr>
        <tr>
            <td colspan="2">&#160;</td>
        </tr>
        <tr>
            <td>
                <strong>Concepto:  </strong> {{ $financePayOrder->concept }}
            </td>
        </tr>
    </tbody>
</table>
<br>
<hr>
&#160;
<br>
@if ($deductions)
    <h5 style="text-align: center">LISTA DE RETENCIONES A SER PAGADAS</h5>
    <table style="font-size: 0.85em;padding:1px;">
        <thead>
            <tr style="background-color:#5a7fa9;font-size: 1.1em">
                <th style="text-align: center; font-weight:bold;">N°</th>
                <th style="text-align: center; font-weight:bold;">Tipo</th>
                <th style="text-align: center; font-weight:bold;">Monto en {{$financePayOrder->currency->symbol}}</th>
                <th style="text-align: center; font-weight:bold;">Fecha</th>
            </tr>
        </thead>
        <tbody>
            @php
                $index = 1;
            @endphp
            @foreach ($deductions as $deduction)
                @php
                    $backround = $index % 2 == 0 ? '#adbfd3' : '#ffffff';
                @endphp
                <tr style="background-color:{{ $backround }};">
                    <td style="text-align: center;">{{ $index++ }}</td>
                    <td style="text-align: center;">{{ $deduction['name'] }}</td>
                    <td style="text-align: center;">
                        {{ number_format($deduction['amount'], $financePayOrder->currency->decimal_places, ",", ".") }}
                    </td>
                    <td style="text-align: center;">{{ $deduction['deducted_at'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
<table style="font-size: 0.85em;">
    <tbody>
        <tr>
            <td colspan="4">&#160;</td>
        </tr>
        @if ($specificAction!==null)
            <tr>
                <td colspan="4" style="text-align: center">PROYECTO O ACCIÓN CENTRALIZADA</td>
            </tr>
            <tr>
                <td colspan="4" style="text-align: center">{{ $specificAction['type'] }}: {{ $specificAction['code'] }}</td>
            </tr>
        @endif
    </tbody>
</table>
@if ($accountable)
<br>
&#160;
<br>
    <h5 style="text-align: center">PARTIDAS PRESUPUESTARIAS</h5>
    <table style="border:solid 1px #000;font-size: 0.85em;background-color:#adbfd3; padding:10px;">
        <thead>
            <tr>
                <th style="text-align: center; font-weight:bold;">CÓDIGO  DE PROYECTO O <br>ACCIÓN CENTRALIZADA</th>
                <th style="text-align: center; font-weight:bold;">NOMBRE DE LA PARTIDA</th>
                <th style="text-align: center; font-weight:bold;">PARTIDA PRESUPUESTARIA</th>
                <th style="text-align: center; font-weight:bold;">MONTO</th>
            </tr>
        </thead>
        @php
            $accountAmount = 0;
        @endphp
        <tbody>
            @foreach ($accountable as $accountableAccount)
                @if (count($accountableAccount) > 0)
                    @foreach ($accountableAccount as $account)
                        <tr>
                            <td style="text-align: center;">
                                {{ $financePayOrder->budgetSpecificAction->specificable->code . ' - ' . $financePayOrder->budgetSpecificAction->code }}
                            </td>
                            <td style="text-align: center;">
                                {{ $account['accountable']['denomination'] }}
                            </td>
                            <td style="text-align: center;">
                                {{ $account['accountable']['code'] }}
                            </td>
                            <td style="text-align: center;">
                                {{ number_format($account['amount'], $financePayOrder->currency->decimal_places, ",", ".") }}
                            </td>
                            <td style="text-align: right;">
                                {{ number_format($account['amount'], $financePayOrder->currency->decimal_places, ",", ".") }}
                            </td>
                        </tr>
                        @php
                            $accountAmount += $account['amount'];
                        @endphp
                    @endforeach
                @endif
            @endforeach
            <tr>
                <td colspan="3" style="font-weight:bold;text-align: right">TOTAL {{$financePayOrder->currency->symbol}}</td>
                <td style="font-weight:bold;text-align: center;border-top:solid 1px #000;">
                    {{ number_format($accountAmount, $financePayOrder->currency->decimal_places, ",", ".") }}
                </td>
            </tr>
        </tbody>
    </table>
@endif
@if ($accountingEntry)
<br>
&#160;
<br>
    <h5 style="text-align: center">ASIENTO CONTABLE</h5>
    <table style="border:solid 1px #000;font-size: 0.85em;background-color:#adbfd3; padding:10px;">
        <thead>
            <tr>
                <th style="text-align: center; font-weight:bold;">CÓDIGO CUENTA</th>
                <th style="text-align: center; font-weight:bold;">NOMBRE CUENTA</th>
                <th style="text-align: right; font-weight:bold;">DEBE</th>
                <th style="text-align: right; font-weight:bold;">HABER</th>
            </tr>
        </thead>
        <tbody>

            @foreach ($accountingEntry->accountingAccounts as $accountEntry)
                <tr>
                    <td style="text-align: center;">{{ $accountEntry->account->code }}</td>
                    <td style="text-align: center;">{{ $accountEntry->account->denomination }}</td>
                    <td style="text-align: right;">
                        {{ number_format($accountEntry->debit, $financePayOrder->currency->decimal_places, ",", ".") }}
                    </td>
                    <td style="text-align: right;">
                        {{ number_format($accountEntry->assets, $financePayOrder->currency->decimal_places, ",", ".") }}
                    </td>
                </tr>
            @endforeach
            <tr>
                <td colspan="2" style="font-weight:bold;text-align: right">TOTAL {{$financePayOrder->currency->symbol}}</td>
                <td style="font-weight:bold;text-align: right;border-top:solid 1px #000;">
                    {{ number_format($accountingEntry->tot_debit, $financePayOrder->currency->decimal_places, ",", ".") }}
                </td>
                <td style="font-weight:bold;text-align: right;border-top:solid 1px #000;">
                    {{ number_format($accountingEntry->tot_assets, $financePayOrder->currency->decimal_places, ",", ".") }}
                </td>
            </tr>
        </tbody>
    </table>
@endif
<br>
&#160;
<br>
<table>
    <tbody>
        <tr>
            <td colspan="2">
                <b>Observaciones:</b> {{ strip_tags($financePayOrder->observations) }}
            </td>
        </tr>
    </tbody>
</table>
<br>
<h5 style="text-align: center">DATOS DEL RESPONSABLE DE LA ORDEN</h5>
&#160;
<br>
<table>
    <tbody>
        <tr>
            <td>Preparado por: _____________________________________</td>
            <td>Autorizado por: _____________________________________</td>
        </tr>
        <br><br>
        <tr>
            <td>Revisado por: _____________________________________</td>
            <td>Presidencia: _____________________________________</td>
        </tr>
    </tbody>
</table>