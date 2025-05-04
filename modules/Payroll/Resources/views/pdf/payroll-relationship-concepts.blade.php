@foreach ($field as $indexRecord => $record)
    @php
        $indexConcept = count($record['payroll_concepts']) - 1;
    @endphp
    @foreach ($record['payroll_concepts'] as $concept)
        @if (count($concept['payroll_staffs']) > 0)
            <h4 style="font-size: 10rem;">Tipo de pago: {{ $record['name'] }}</h4>
            <h4 style="font-size: 10rem;">Tipo de concepto: {{ $concept['payroll_concept_type'] ?? '' }} </h4>
            <br>

            <h2 style="font-size: 13rem;" align="center"> {{ $concept['name'] ?? '' }} </h2>

            <table cellspacing="0" cellpadding="1" border="1" align="center">
                <thead>
                    <tr style="background-color: #BDBDBD;">
                        <th span="1">Cédula de identidad</th>
                        <th span="1">Nombre del trabajador</th>
                        <th span="1">Monto del concepto</th>
                        <th span="1">Periodo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($concept['payroll_staffs'] ?? [] as $staff)
                        @php
                            $rowspan = count($staff['payroll_payment_periods']);
                        @endphp
                        <tr>
                            <td style="vertical-align: middle;" rowspan="<?php echo $rowspan; ?>"> {{ $staff['id_number'] }}
                            </td>
                            <td style="vertical-align: middle;" rowspan="<?php echo $rowspan; ?>"> {{ $staff['name'] }}
                            </td>
                            @foreach ($staff['payroll_payment_periods'] as $index => $period)
                                @if ($index > 0)
                        <tr>
                    @endif
                    <td> {{ $period['value'] }} </td>
                    <td> {{ $period['period'] }} </td>
                    </tr>
        @endforeach
    @endforeach
    </tbody>
    </table>
    @if (count($field) !== $indexRecord + 1 || (count($field) === $indexRecord + 1 && $indexConcept !== 0))
        <br pagebreak="true" />
    @endif
@endif
@php $indexConcept--; @endphp
@endforeach
@endforeach
