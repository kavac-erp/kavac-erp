<table width="100%" cellpadding="4" style="font-size: 10rem">
    <tbody>
        <tr>
            <td width="25%" style="font-weight: bold;">Institución:</td>
            <td width="75%">{{ $institution->name }}</td>
        </tr>
        <tr>
            <td width="25%" style="font-weight: bold;">Expresado en:</td>
            <td width="75%">{{ $currency->description }}</td>
        </tr>
        <tr>
            <td width="25%" style="font-weight: bold;">Año Fiscal:</td>
            <td width="75%">{{ $fiscal_year }}</td>
        </tr>
        <tr>
            <td colspan="2">&nbsp;</td>
        </tr>
    </tbody>
</table>
<br>
<table cellspacing="0" cellpadding="4" border="1" style="font-size: 7rem;">
    <thead>
        <tr style="border: solid 1px #000;" bgcolor="#D3D3D3" align="center">
            <th style="font-weight:bold;" width="10%">Fecha</th>
            <th style="font-weight:bold;" width="10%">Código</th>
            <th style="font-weight:bold;" width="10%">Referencia</th>
            <th style="font-weight:bold;" width="10%">Tipo de transacción</th>
            <th style="font-weight:bold;" width="12.5%">Banco</th>
            <th style="font-weight:bold;" width="15%">Cuenta</th>
            <th style="font-weight:bold;" width="12.5%">Concepto / Observación</th>
            <th style="font-weight:bold;" width="10%">Estatus</th>
            <th style="font-weight:bold;" width="10%">Monto</th>
        </tr>
    </thead>
    <tbody>
    @foreach ($reportData as $index => $report)
        <tr align="center">
            <td style="border: solid 1px #808080;" width="10%">{{ $report->payment_date->format('d/m/Y') }}</td>
            <td style="border: solid 1px #808080;" width="10%">{{ $report->code }}</td>
            <td style="border: solid 1px #808080;" width="10%"> {{ $report->reference }}</td>
            <td style="border: solid 1px #808080;" width="10%">{{ $report->transaction_type }}</td>
            <td style="border: solid 1px #808080; text-align: justify;" width="12.5%">{{ $report->finance_bank_account_id ? $report->financeBankAccount?->financeBankingAgency?->financeBank?->name : '' }}</td>
            <td style="border: solid 1px #808080; text-align: justify;" width="15%">{{ ($report->finance_bank_account_id ? $report->financeBankAccount?->formated_ccc_number : '' )}}</td>
            <td style="border: solid 1px #808080; text-align: justify;" width="12.5%">{{ strip_tags($report->concept) }}</td>
            <td style="border: solid 1px #808080; color:{{ $report->documentStatus->color }}" width="10%">{{ $report->documentStatus->name }}</td>
            <td style="border: solid 1px #808080;" width="10%">
                {{ number_format($nameDecimalFunction($report->amount, $number_decimals), 2, ',', '.') }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>