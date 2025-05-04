<table cellspacing="0" cellpadding="1" border="1">
    <tr align="C">
        <th width="25%">Trabajador</th>
        <th width="25%">Asignaciones</th>
        <th width="25%">Deducciones</th>
        <th width="25%">Total</th>
    </tr>
    <!-- Sección para consultar los parametros de configuración de los reporte -->
     @php
        $PayrollReportConfigurations = (Modules\Payroll\Models\Parameter::where(['active' => true, 'required_by' => 'payroll'])->orderBy('id')->get());
        $numberDecimals = '';
        $round = '';
        $zeroConcept = '';
        $raiz = 10;
        $multiplicador = '';
    @endphp
    @foreach($PayrollReportConfigurations as $PayrollReportConfiguration)
       @if($PayrollReportConfiguration['p_key'] === 'number_decimals')
            @php
                $numberDecimals = $PayrollReportConfiguration['p_value'];
                $raiz = 10;
                $multiplicador = pow($raiz,$numberDecimals);
            @endphp
         @elseif($PayrollReportConfiguration['p_key'] == 'round')
            @php
                $round = $PayrollReportConfiguration['p_value'];
            @endphp
        @elseif($PayrollReportConfiguration['p_key'] == 'zero_concept')
            @php
                $zeroConcept = $PayrollReportConfiguration['p_value'];
            @endphp
       @endif
    @endforeach
    <!--/Sección para consultar los parametros de configuración de los reporte -->

    @foreach($field as $record)
    @php
        $total = 0;
    @endphp
            <tr>
                <td width="25%"> {{ ($record->payrollStaff)? $record->payrollStaff->first_name . ' ' . $record->payrollStaff->last_name: ''}} </td>
                <td width="25%">
                    <span>
                        @foreach(json_decode($record->assignments) as $assignment)
                            @if($zeroConcept === 'true')
                                <p><strong> Concepto: </strong> {{ $assignment->name }} </p>
                                @if($round === 'true')
                                    <p><strong> Valor: </strong>
                                    {{ round($assignment->value, $numberDecimals, PHP_ROUND_HALF_EVEN) }}
                                    </p>
                                @elseif($round !== 'true')
                                    <p><strong> Valor: </strong>
                                    {{ number_format( ((int)($assignment->value*$multiplicador))/$multiplicador, $numberDecimals, ',', '.') }}
                                    </p>
                                @endif
                            @elseif($assignment->value !== 0 and $zeroConcept === 'false')
                                <p><strong> Concepto: </strong> {{ $assignment->name }} </p>
                                @if($round === 'true')
                                    <p><strong> Valor: </strong>
                                    {{ round($assignment->value, $numberDecimals, PHP_ROUND_HALF_EVEN) }}
                                    </p>
                                @elseif($round !== 'true')
                                    <p><strong> Valor: </strong>
                                    {{ number_format( ((int)($assignment->value*$multiplicador))/$multiplicador, $numberDecimals, ',', '.') }}
                                    </p>
                                @endif
                            @endif
                        @endforeach
                    </span>
                </td>
                <td width="25%">
                    <span>
                        @foreach(json_decode($record->deductions) as $deduction)
                            @if($zeroConcept === 'true')
                                <p><strong> Concepto: </strong> {{ $deduction->name }} </p>
                                @if($round === 'true')
                                    <p><strong> Valor: </strong>
                                    {{ round($deduction->value, $numberDecimals, PHP_ROUND_HALF_EVEN) }}
                                    </p>
                                @elseif($round !== 'true')
                                    <p><strong> Valor: </strong>
                                    {{ number_format( ((int)($deduction->value*$multiplicador))/$multiplicador, $numberDecimals, ',', '.') }}
                                    </p>
                                @endif
                            @elseif($deduction->value !== 0 and $zeroConcept === 'false')
                                <p><strong> Concepto: </strong> {{ $deduction->name }} </p>
                                @if($round === 'true')
                                    <p><strong> Valor: </strong>
                                    {{ round($deduction->value, $numberDecimals, PHP_ROUND_HALF_EVEN) }}
                                    </p>
                                @elseif($round !== 'true')
                                    <p><strong> Valor: </strong>
                                    {{ number_format( ((int)($deduction->value*$multiplicador))/$multiplicador, $numberDecimals, ',', '.') }}
                                    </p>
                                @endif
                            @endif
                        @endforeach
                    </span>
                </td>
                <td width="25%">
                    @foreach(json_decode($record->assignments) as $assignment)
                        @php
                            $total += $assignment->value;
                        @endphp
                    @endforeach
                    @foreach(json_decode($record->deductions) as $deduction)
                        @php
                            $total -= $deduction->value;
                        @endphp
                    @endforeach
                    @if($round === 'true')
                       <span>
                        {{ round($total, $numberDecimals, PHP_ROUND_HALF_EVEN) }} </span>

                    @elseif($round !== 'true')
                        <span>
                        {{ number_format( ((int)($total*$multiplicador))/$multiplicador, $numberDecimals, ',', '.') }} </span>
                    @endif
                </td>
            </tr>

    @endforeach
    <br pagebreak="true" />
</table>