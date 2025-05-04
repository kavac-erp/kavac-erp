<table cellspacing="0" cellpadding="1" border="1">
    <thead>
        <tr style="background-color: #BDBDBD;">
            <th span="1">Concepto</th>
            <th span="1">Cuenta contable</th>
            <th span="1">Cuenta presupuestaria</th>
            <th span="1">Formula</th>
            <th span="1">Tipo de concepto</th>
            <th span="1">Tipo de nómina</th>
            <th span="1">Beneficiario</th>
            <th span="1">Cuenta contable del beneficiario</th>
            <th span="1">Genera orden de pago</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($field as $records)
        @php
            $payrollPaymentTypeNames = '';

            $totalNames = count($records->payrollPaymentTypes);
            foreach ($records->payrollPaymentTypes as $key => $payrollPaymentType) {
                $payrollPaymentTypeName = $payrollPaymentType->name;
                $payrollPaymentTypeNames .= $payrollPaymentTypeName;

                if ($key < $totalNames - 1) {
                    $payrollPaymentTypeNames .= ', ';
                } else {
                    $payrollPaymentTypeNames .= '.';
                }
            }
        @endphp
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
                {{ $payrollPaymentTypeNames == '' ? 'No definido' : $payrollPaymentTypeNames }}
            </td>
            <td>
                {{  $records->receiver ? $records->receiver['text'] : 'No definido' }}
           </td>
           <td>
                {{ $records->receiver ? $records->receiver['accounting_account'] . '-' . $records->receiver['denomination'] : 'No definido' }}
            </td>
            <td>
                {{ $records->pay_order == true ? 'Si' : 'No' }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>