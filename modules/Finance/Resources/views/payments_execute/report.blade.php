@if ($financePaymentExecute->status === 'AN')
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
        @endphp
        <tr>
            <td>
                <strong>Institución:</strong> {{ $payOrder[0]->institution->name }}
            </td>
        </tr>
        <tr>
            <td>
                <strong>Fecha de emisión de pago:</strong> {{ $financePaymentExecute->paid_at->format('d/m/Y') }}
            </td>
        </tr>
        <tr>
            <td>
                <strong>Referencia Nº{{ $financePaymentExecute->code }}</strong>
            </td>
        </tr>
        <tr>
            <td width="70%">
                <strong>Proveedor / Beneficiario:</strong> {{ $financePaymentExecute->receiver_name }}
            </td>
        </tr>
        @if (
            $financePaymentExecute->receiver_type != $compromiseClass &&
            $financePaymentExecute->receiver_type != 'App\Models\Institution'
        )
        <tr>
            <td colspan="2">
                <strong>R.I.F. / C.I.:</strong>
                {{
                    $payOrder[0]->nameSourceable->rif ?? $payOrder[0]->nameSourceable->dni ??
                    $payOrder[0]->nameSourceable->payrollStaff->id_number ??
                    $payOrder[0]->nameSourceable->payrollStaff->passport ?? ""
                }}
            </td>
        </tr>
        @endif
        <tr>
            <td colspan="2"></td>
        </tr>
        <tr>
            <td colspan="2" class="text-justify font-weight-bold" style="font-size: 1.2em;font-weight:bold">
                {{
                    convertirNumeros(
                        number_format(
                            $financePaymentExecute->paid_amount,
                            $payOrder[0]->currency->decimal_places,
                            ".",
                            ""
                        ),
                        strtoupper($payOrder[0]->currency->plural_name)
                    )
                }}
            </td>
            <td style="font-size: 1.3em">
                <strong>
                    <span class="mr-2">***</span>
                    <span class="mr-2">
                        <span class="mr-2">{{ $payOrder[0]->currency->symbol }}</span>
                        {{
                            number_format(
                                $financePaymentExecute->paid_amount,
                                $payOrder[0]->currency->decimal_places,
                                ",",
                                "."
                            )
                        }}
                    </span>
                    <span>***</span>
                </strong>
            </td>
        </tr>
        <tr>
            <td colspan="2"></td>
        </tr>
        <tr>
            <td>
                <b>Cod. Orden(s) de pago(s):</b>
                <span class="ml-2">
                    @foreach($payOrder as $payOrd)
                        {{ $payOrd->code }}
                        @if ($loop->last === false)
                            <span class="mr-1">,</span>
                        @endif
                    @endforeach
                </span>
            </td>
        </tr>
        <tr>
            <td>
               <b>Nro.Factura:</b><span class="ml-2">{{ $financePaymentExecute->payment_number }}</span>
            </td>
        </tr>

    </tbody>
</table>
<table>
    <tbody>
        <tr>
            <td>
                <b>Concepto:</b> {{ $payOrder[0]->concept }}
            </td>
        </tr>
    </tbody>
</table>
<br>
<hr>
&#160;
<br>
<table style="font-size: 0.85em;">
    <thead>
        <tr>
            <th style="text-align: center; font-weight:bold;">MÉTODO DE PAGO</th>
            <th style="text-align: center; font-weight:bold;">BANCO DE ORIGEN</th>
            <th style="text-align: center; font-weight:bold;">Nº DE CUENTA</th>
            <th style="text-align: center; font-weight:bold;">MONTO DE ESTA OPERACIÓN</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="text-align: center;">{{ $financePaymentExecute->financePaymentMethod->name }}</td>
            <td style="text-align: center;">{{ $financePaymentExecute->financeBankAccount->financeBankingAgency->financeBank->name }}</td>
            <td style="text-align: center;">{{ $financePaymentExecute->financeBankAccount->formatedCccNumber}}</td>
            <td style="text-align: center;">
                {{ $payOrder[0]->currency->symbol }} {{ number_format($financePaymentExecute->paid_amount, $payOrder[0]->currency->decimal_places, ",", ".") }}
            </td>
        </tr>
        <tr>
            <td colspan="4">&#160;</td>
        </tr>
    </tbody>
</table>

@if ($financePaymentExecute->has('financePaymentDeductions') &&
    count($financePaymentExecute->financePaymentDeductions) > 0)
    <br>
    <h5 style="text-align: center">RETENCIONES</h5>
    <table style="border:solid 1px #000;font-size: 0.85em;background-color:#adbfd3; padding:10px;">
        <thead>
            <tr>
                <th style="text-align: center; font-weight:bold;">TIPO DE RETENCIÓN</th>
                <th style="text-align: center; font-weight:bold;">MONTO</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($financePaymentExecute->financePaymentDeductions as $paymentDeduction)
                <tr>
                    <td>{{ $paymentDeduction->deduction ? $paymentDeduction->deduction->name : $paymentDeduction->deductionable->name }}</td>
                    <td style="text-align: right">{{ $paymentDeduction->amount }}</td>
                </tr>
            @endforeach
            <tr>
                <td style="text-align: right;"><b>TOTAL RETENCIONES:</b></td>
                <td style="text-align: right"><b>{{ $payOrder[0]->currency->symbol }} {{ $financePaymentExecute->deduction_amount }}</b></td>
            </tr>
        </tbody>
    </table>
    <br>
    &#160;
    <br>
@endif
@if ($accountingEntry)
    <br>
    <h5 style="text-align: center">ASIENTO CONTABLE</h5>
    <table style="border:solid 1px #000;font-size: 0.85em;background-color:#adbfd3; padding:10px;">
        <thead>
            <tr>
                <th style="text-align: center; font-weight:bold;">CÓDIGO CUENTA</th>
                <th style="text-align: center; font-weight:bold;">NOMBRE CUENTA</th>
                <th style="text-align: center; font-weight:bold;">DEBE</th>
                <th style="text-align: center; font-weight:bold;">HABER</th>
            </tr>
        </thead>
        <tbody>

            @foreach ($accountingEntry->accountingAccounts as $accountEntry)
                <tr>
                    <td style="text-align: center;">{{ $accountEntry->account ? $accountEntry->account->code : 'No definido' }}</td>
                    <td style="text-align: center;">{{ $accountEntry->account ? $accountEntry->account->denomination : 'No definido' }}</td>
                    <td style="text-align: right;">{{ number_format($accountEntry->debit, $payOrder[0]->currency->decimal_places, ",", ".") }}</td>
                    <td style="text-align: right;">{{ number_format($accountEntry->assets, $payOrder[0]->currency->decimal_places, ",", ".") }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="2" style="font-weight:bold;text-align: right">TOTAL {{ $payOrder[0]->currency->symbol }}</td>
                <td style="font-weight:bold;text-align: right;border-top:solid 1px #000;">
                    {{ number_format($accountingEntry->tot_debit, $payOrder[0]->currency->decimal_places, ",", ".") }}
                </td>
                <td style="font-weight:bold;text-align: right;border-top:solid 1px #000;">
                    {{ number_format($accountingEntry->tot_assets, $payOrder[0]->currency->decimal_places, ",", ".") }}
                </td>
            </tr>
        </tbody>
    </table>
@endif
<br>
<br>
@if ($financePaymentExecute->description)
    <table>
        <tbody>
            <tr>
                <td colspan="2">
                    <b>Observaciones:</b> {{ strip_tags($financePaymentExecute->description) }}
                </td>
            </tr>
        </tbody>
    </table>
@endif
<br>
<h5 style="text-align: center">DATOS DEL RESPONSABLE DE LA EMISIÓN</h5>
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
            <td>Revisado por: ______________________________________</td>
            <td>Presidencia: _______________________________________</td>
        </tr>
    </tbody>
</table>
<br>&#160;<br>
<table>
    <tbody>
        <tr>
            <td colspan="2" style="font-size: 0.85em;">
                <b>Por medio de la presente declaro haber recibido a mi entera conformidad el  pago identificado en el presente documento por el concepto descrito en el mismo.</b>
            </td>
        </tr>
        <tr><td colspan="2">&#160;</td></tr>
        <tr>
            <td style="width:350px;font-size: 0.85em;">
                Nombre y Apellido: _____________________________________<br>&#160;<br>
                Cédula de identidad Nº: _________________________________<br>&#160;<br>
                En representación de: ___________________________________
            </td>
            <td style="width:220px;text-align: center;">
                _____________________________________<br>&#160;<br>
                Firma
            </td>
        </tr>
    </tbody>
</table>
