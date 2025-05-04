<table cellspacing="0" cellpadding="4" border="1" style="font-size: 8rem;">
    <colgroup>
        <col span="1" style="width: 33.3%" />
        <col span="1" style="width: 33.3%" />
        <col span="1" style="width: 33.3%" />
    </colgroup>
    <thead>
        <tr style="border: solid 1px #000; font-weight:bold;" bgcolor="#D3D3D3" align="center">
            <th span="1" style="text-align:center">Numeración</th>
            <th span="1" style="text-align:center">Trabajador</th>
            <th span="1" style="text-align:center">Número de cédula</th>
            <th span="1" style="text-align:center">Cargo</th>
            <th span="1" style="text-align:center">Fecha de ingreso a la institución</th>
            <th span="1" style="text-align:center">Años en la institución</th>
            @if ($columns['payroll_gender'])
                <th span="1" style="text-align:center">Género</th>
            @endif
            @if ($columns['payroll_disability'])
                <th span="1" style="text-align:center">Discapacidad</th>
            @endif
            @if ($columns['has_driver_license'])
                <th span="1" style="text-align:center">Licencia</th>
            @endif
            @if ($columns['payroll_blood_type'])
                <th span="1" style="text-align:center">Tipo de sangre</th>
            @endif
            @if (($columns['payroll_age']))
                <th span="1" style="text-align:center">Edad</th>
            @endif
            @if ($columns['payroll_instruction_degree'])
                <th span="1" style="text-align:center">Grado de instrucción</th>
            @endif
            @if ($columns['payroll_professions'])
                <th span="1" style="text-align:center">Profesión</th>
            @endif
            @if ($columns['payroll_study'])
                <th span="1" style="text-align:center">¿Estudia?</th>
            @endif
            @if ($columns['marital_status'])
                <th span="1" style="text-align:center">Estado civil</th>
            @endif
            @if ($columns['payroll_childs'])
                <th span="1" style="text-align:center">Hijos <br><span>Nombre&nbsp; Edad&nbsp; Escolaridad</span></th>
            @endif
            @if ($columns['payroll_is_active'])
                <th span="1" style="text-align:center">Activo</th>
            @endif
            @if ($columns['payroll_inactivity_types'])
                <th span="1" style="text-align:center">Tipo de Inactividad</th>
            @endif
            @if ($columns['payroll_position_types'])
                <th span="1" style="text-align:center">Tipo de cargo</th>
            @endif
            @if ($columns['payroll_staff_types'])
                <th span="1" style="text-align:center">Tipo de personal</th>
            @endif
            @if ($columns['payroll_contract_types'])
                <th span="1" style="text-align:center">Tipo de contrato</th>
            @endif
            @if ($columns['departments'])
                <th span="1" style="text-align:center">Departamento</th>
            @endif
            @if ($columns['time_service'])
                <th span="1" style="text-align:center">Total años de servicio</th>
            @endif
        </tr>
    </thead>
    <tbody>
    @foreach ($records as $record)
        <tr style="text-align:center">
            <!-- Numeración -->
            <td >{{ $loop->iteration }}</td>
            <!-- Trabajador -->
            <td>{{ $record['first_name'] . " " . $record['last_name'] }}</td>
            <!-- Número de cédula -->
            <td>{{ number_format($record['id_number'], 0, '', '.') }}</td>
            <!-- Cargo -->
            <td>{{ isset($record['payroll_employment_no_appends']['payroll_positions'][0]) ? $record['payroll_employment_no_appends']['payroll_positions'][0]['name'] : 'N/A' }}</td>
            <!-- Fecha de ingreso a la institución -->
            <td>{{ isset($record['payroll_employment_no_appends']['start_date']) ? \Carbon\Carbon::createFromFormat('Y-m-d', $record['payroll_employment_no_appends']['start_date'])->format('d-m-Y') : 'N/A' }}</td>
            <!-- Años en la institución -->
            <td>{{ isset($record['payroll_employment_no_appends']['time_worked']) ? $record['payroll_employment_no_appends']['time_worked'] : 'N/A' }}</td>
            <!-- Género -->
             @if ($columns['payroll_gender'])
                <td>{{ $record['payroll_gender'] ? $record['payroll_gender']['name'] : 'N/A' }}</td>
            @endif
            <!-- Discapacidad -->
            @if ($columns['payroll_disability'])
                <td>{{ (isset($record['payroll_disability']) && $record['payroll_disability']['name']) ? $record['payroll_disability']['name'] : 'N/A'  }}</td>
            @endif
            <!-- Licencia -->
            @if ($columns['has_driver_license'])
                <td>{{ (isset($record['payroll_license_degree']) && $record['payroll_license_degree']['name']) ? $record['payroll_license_degree']['name'] : 'N/A'  }}</td>
            @endif
            <!-- Tipo de sangre -->
            @if ($columns['payroll_blood_type'])
                <td>{{ $record['payroll_blood_type'] ? $record['payroll_blood_type']['name'] : 'N/A' }}</td>
            @endif
            <!-- Edad -->
            @if ($columns['payroll_age'])
                <td>{{ isset($record['age']) ? $record['age'] : 'N/A'  }}</td>
            @endif
            <!-- Grado de instrucción -->
            @if ($columns['payroll_instruction_degree'])
                <td>{{ $record['payroll_professional']['payroll_instruction_degree'] ? $record['payroll_professional']['payroll_instruction_degree']['name'] : 'N/A'  }}</td>
            @endif
            <!-- Profesión -->
            @if ($columns['payroll_professions'])
                <td>{{ isset($record['payroll_professional']['payroll_studies'][0]) ? $record['payroll_professional']['payroll_studies'][0]['professions']['name'] : 'N/A'  }}</td>
            @endif
            <!-- ¿Estudia? -->
            @if ($columns['payroll_study'])
                <td>{{ $record['payroll_professional']['is_student'] ? 'Si' : 'No'  }}</td>
            @endif
            <!-- Estado civil -->
            @if ($columns['marital_status'])
                <td>{{ $record['payroll_socioeconomic']['marital_status'] ? $record['payroll_socioeconomic']['marital_status']['name'] : 'N/A'  }}</td>
            @endif
            <!-- Hijos -->
            @if ($columns['payroll_childs'])
                @if (isset($record['payroll_socioeconomic']['payroll_childrens']) && count($record['payroll_socioeconomic']['payroll_childrens']) > 0)
                    <td style="padding: 0;">
                        <table style="border-collapse: collapse; margin: 0; padding: 0; width: 100%;" cellspacing="0" cellpadding="2" border="0.01" align="left">
                            <tbody>
                                @foreach ($record['payroll_socioeconomic']['payroll_childrens'] as $child)
                                    <tr style="font-size: 6rem;" align="left" width="100%">
                                        <td width="100%">{{ $child['first_name'] . " " . $child['last_name'] . ", " . $child["age"] . ", " . (isset($child["payroll_schooling_level"]) ? $child["payroll_schooling_level"]["name"] : "N/A") }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </td>
                @endif
            @endif
            <!-- Activo -->
            @if ($columns['payroll_is_active'])
                <td>
                    {{ (isset($record['payroll_employment_no_appends']['active']) && $record['payroll_employment_no_appends']['active']) ? 'Si' : 'No' }}
                </td>
            @endif
            <!-- Tipo de inactividad -->
            @if ($columns['payroll_inactivity_types'])
                <td> {{ isset($record['payroll_employment_no_appends']['payroll_inactivity_type']['name']) ? $record['payroll_employment_no_appends']['payroll_inactivity_type']['name'] : 'N/A' }} </td>
            @endif
            <!-- Tipo de cargo -->
            @if ($columns['payroll_position_types'])
                <td> {{ isset($record['payroll_employment_no_appends']['payroll_position_type']['name']) ? $record['payroll_employment_no_appends']['payroll_position_type']['name'] : 'N/A' }}</td>
            @endif
            <!-- Tipo de personal -->
            @if ($columns['payroll_staff_types'])
                <td> {{ isset($record['payroll_employment_no_appends']['payroll_staff_type']) ? $record['payroll_employment_no_appends']['payroll_staff_type']['name'] : 'N/A' }} </td>
            @endif
            <!-- Tipo de contrato -->
            @if ($columns['payroll_contract_types'])
                <td> {{ isset($record['payroll_employment_no_appends']['payroll_contract_type']) ? $record['payroll_employment_no_appends']['payroll_contract_type']['name'] : 'N/A' }} </td>
            @endif
            <!-- Departamento -->
            @if ($columns['departments'])
                <td> {{ isset($record['payroll_employment_no_appends']['department']) ? $record['payroll_employment_no_appends']['department']['name'] : 'N/A' }} </td>
            @endif
            <!-- Tiempo de servicio -->
            @if ($columns['time_service'])
                <td> {{ isset($record['payroll_employment_no_appends']['total']) ? $record['payroll_employment_no_appends']['total'] : 'N/A' }} </td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>
