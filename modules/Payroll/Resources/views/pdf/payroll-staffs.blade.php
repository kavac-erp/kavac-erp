@php
    $keys = array_pop($field);
@endphp

<table cellspacing="0" cellpadding="1" border="1">
    <colgroup>
        <col span="1" style="width: 33.3%" />
        <col span="1" style="width: 33.3%" />
        <col span="1" style="width: 33.3%" />
    </colgroup>
    <thead>
        <tr style="background-color: #BDBDBD;">
            <th span="1" style="text-align:center">Trabajador</th>
            <th span="1" style="text-align:center">Fecha de ingreso a la institución</th>
            <th span="1" style="text-align:center">Años en la institución</th>
            @if ($keys['conditions']['payroll_gender'])
                <th span="1" style="text-align:center">Género</th>
            @endif
            @if ($keys['conditions']['payroll_disability'])
                <th span="1" style="text-align:center">Discapacidad</th>
            @endif
            @if ($keys['conditions']['has_driver_license'])
                <th span="1" style="text-align:center">Licencia</th>
            @endif
            @if ($keys['conditions']['payroll_blood_type'])
                <th span="1" style="text-align:center">Tipo de sangre</th>
            @endif
            @if ($keys['conditions']['payroll_age'])
                <th span="1" style="text-align:center">Edad</th>
            @endif
            @if ($keys['conditions']['payroll_instruction_degree'])
                <th span="1" style="text-align:center">Grado de instrucción</th>
            @endif
            @if ($keys['conditions']['payroll_professions'])
                <th span="1" style="text-align:center">Profesión</th>
            @endif
            @if ($keys['conditions']['payroll_study'])
                <th span="1" style="text-align:center">¿Estudia?</th>
            @endif
            @if ($keys['conditions']['marital_status'])
                <th span="1" style="text-align:center">Estado civil</th>
            @endif
            @if ($keys['conditions']['payroll_childs'])
                <th span="1" style="text-align:center">Hijos <br><span>Nombre&nbsp; Edad&nbsp; Escolaridad</span></th>
            @endif
            @if ($keys['conditions']['payroll_is_active'])
                <th span="1" style="text-align:center">Activo</th>
            @endif
            @if ($keys['conditions']['payroll_inactivity_types'])
                <th span="1" style="text-align:center">Tipo de Inactividad</th>
            @endif
            @if ($keys['conditions']['payroll_position_types'])
                <th span="1" style="text-align:center">Tipo de cargo</th>
            @endif
            @if ($keys['conditions']['payroll_positions'])
                <th span="1" style="text-align:center">Cargo</th>
            @endif
            @if ($keys['conditions']['payroll_staff_types'])
                <th span="1" style="text-align:center">Tipo de personal</th>
            @endif
            @if ($keys['conditions']['payroll_contract_types'])
                <th span="1" style="text-align:center">Tipo de contrato</th>
            @endif
            @if ($keys['conditions']['departments'])
                <th span="1" style="text-align:center">Departamento</th>
            @endif
            @if ($keys['conditions']['time_service'])
                <th span="1" style="text-align:center">Total años de servicio</th>
            @endif
            
        </tr>
    </thead>
    <tbody>
        @foreach ($field as $record)
        @php
            if($record['start_date'] != 'N/A') {
                $start_date = date_create($record['start_date']);
                $start_date = date_format($start_date, 'd-m-Y');
            } else {
                $start_date = 'N\A';
            }
        @endphp
        <tr>
            <td>{{ $record['payroll_staff'] }}</td>
            <td>{{ $start_date }}</td>
            <td>{{ $record['time_worked'] }}</td>
            @if ($keys['conditions']['payroll_gender'])
                <td>{{ $record['payroll_gender'] }}</td>
            @endif
            @if ($keys['conditions']['payroll_disability'])
                <td>{{ $record['payroll_disability'] }}</td>
            @endif
            @if ($keys['conditions']['has_driver_license'])
                <td>{{ $record['payroll_license'] }}</td>
            @endif
            @if ($keys['conditions']['payroll_blood_type'])
                <td>{{ $record['payroll_blood_type'] }}</td>
            @endif
            @if ($keys['conditions']['payroll_age'])
                <td>{{ $record['payroll_age'] }}</td>
            @endif
            @if ($keys['conditions']['payroll_instruction_degree'])
                <td>{{ $record['payroll_instruction_degree'] }}</td>
            @endif
            @if ($keys['conditions']['payroll_professions'])
                <td>{{ $record['payroll_profession'] }}</td>
            @endif
            @if ($keys['conditions']['payroll_study'])
                <td>{{ $record['payroll_study'] }}</td>
            @endif
            @if ($keys['conditions']['marital_status'])
                <td>{{ $record['payroll_marital_status'] }}</td>
            @endif
            @if ($keys['conditions']['payroll_childs'])
            <td style="padding-left: 0;">
                <table style="border-collapse: collapse; margin: 0; padding: 0; width: 100%;" cellspacing="0" cellpadding="2" border="0.1" align="left">
                    <tbody>
                        @foreach ($record['payroll_childs_arrays'] as $payroll_child)
                            @if (
                                isset($payroll_child['payroll_relationship']) && 
                                $payroll_child['payroll_relationship'] && 
                                strpos($payroll_child['payroll_relationship']['name'], 'Hijo') !== false 
                            )
                            <tr style="font-size: 6rem;" align="left">
                                <td> {{ $payroll_child['first_name']  . ' ' . $payroll_child['last_name']}} 
                                </td>
                                @php
                                    $nacimiento = new DateTime($payroll_child['birthdate']);
                                    $ahora = new DateTime(date("Y-m-d"));
                                    $diferencia = $ahora->diff($nacimiento);
                                    $age = $diferencia->format("%y");
                                @endphp
                                    <td>
                                        {{ $age }}
                                    </td>
                                    @php
                                        $level = '';
                                        if($payroll_child['payroll_schooling_level_id'] !== null ) {
                                            foreach ($record['schooling_levels'] as $schooling_level) {
                                                if($schooling_level['id'] == $payroll_child['payroll_schooling_level_id']) {
                                                    $level = $schooling_level['text'];
                                                }
                                            }
                                        }
                                    @endphp
                                    <td>
                                        {{ $level }}
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </td>                
            @endif
            @if ($keys['conditions']['payroll_is_active'])
                <td>{{ $record['payroll_is_active'] }}</td>
            @endif
            @if ($keys['conditions']['payroll_inactivity_types'])
                <td>{{ $record['payroll_inactivity_type'] }}</td>
            @endif
            @if ($keys['conditions']['payroll_position_types'])
                <td>{{ $record['payroll_position_type'] }}</td>
            @endif
            @if ($keys['conditions']['payroll_positions'])
                <td>{{ $record['payroll_position'] }}</td>
            @endif
            @if ($keys['conditions']['payroll_staff_types'])
                <td>{{ $record['payroll_staff_type'] }}</td>
            @endif
            @if ($keys['conditions']['payroll_contract_types'])
                <td>{{ $record['payroll_contract_type'] }}</td>
            @endif
            @if ($keys['conditions']['departments'])
                <td>{{ $record['department'] }}</td>
            @endif
            @if ($keys['conditions']['time_service'])
                <td>{{ $record['time_service'] }}</td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>
