<table cellspacing="0" cellpadding="1" border="1">
    
    <thead>
        <tr style="background-color: #BDBDBD;">
            <th span="1">Concepto</th>
            <th span="1">Cuenta contable</th>
            <th span="1">Cuenta presupuestaria</th>
            <th span="1">Formula</th>
            <th span="1">Tipo de concepto</th>
            <th span="1">Tipo de pago</th>
            <th span="1">Beneficiario</th>
            <th span="1">Cuenta contable del beneficiario</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($field as $records)
        <tr>
            <td>
                 {{ $records->name }}
            </td>
            <td>
                 {{ $records->accountingAccount ? $records->accountingAccount->code . ' - ' . $records->accountingAccount->denomination : 'No definido' }}
            </td>
            <td>
                {{ $records->budgetAccount ? $records->budgetAccount->code . ' - ' . $records->budgetAccount->denomination : 'No definido' }}
           </td>
            <td>
                 {{ $records->translate_formula }}
            </td>
            <td>
                 {{ $records->payrollConceptType ? $records->payrollConceptType->name : 'No definido' }}
            </td>
            <td>
                @foreach($records->payrollPaymentTypes as $payrollPaymentType)
                    <span>
                        {{ $payrollPaymentType->name }}
                    </span>
                @endforeach
            </td>
            <td>
                {{  $records->receiver ? $records->receiver['text'] : 'No definido' }}
           </td>
           <td>
            {{ $records->receiver ? $records->receiver['accounting_account'] . '-' . $records->receiver['denomination'] : 'No definido' }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>