@foreach ($field as $record)
    @if(count($record->payrollChildrens) > 0)
        @php
            $startDate = \Carbon\Carbon::parse($record->payrollStaff->payrollEmployment->start_date)->format('d/m/Y');
        @endphp
        <p>
            Nombres y apellidos del trabajador: {{ $record->payrollStaff->full_name }} <br>
            Cédula: {{ $record->payrollStaff->id_number }} <br>
            Cargo: {{
                $record->payrollStaff->payrollEmployment->payroll_position->name ??
                $record->payrollStaff->payrollEmployment->payrollPositions[0]['name'] ??
                ''
            }}
            <br>
            Fecha de ingreso: {{ $startDate ?? '' }} <br>
            Departamento: {{ $record->payrollStaff->payrollEmployment->department->name ?? '' }}
        </p>

        <p style="margin-left: 15px; color: #42a4c1;">
            Carga Familiar:
        </p>
        <br><br>

        <table cellspacing="0" cellpadding="1" border="1">
            <thead>
                <tr style="background-color: #BDBDBD;">
                    <th span="1">Nombres</th>
                    <th span="1">Apellidos</th>
                    <th span="1">Parentesco</th>
                    <th span="1">Fecha de Nacimiento</th>
                    <th span="1">Edad</th>
                    <th span="1">Cédula</th>
                    <th span="1">Género</th>
                    <th span="1">Estudia</th>
                    <th span="1">Nivel de Escolaridad</th>
                    <th span="1">Centro de Estudio</th>
                    <th span="1">Posee Beca</th>
                    <th span="1">Posee Discapacidad</th>
                    <th span="1">Discapacidad</th>
                    <th span="1">Dirección</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($record->payrollChildrens as $child)
                    @php
                        $birthdate = \Carbon\Carbon::parse($child->birthdate)->format('d/m/Y');
                    @endphp
                    <tr>
                        <td>
                            {{ $child->first_name }}
                        </td>
                        <td>
                            {{ $child->last_name }}
                        </td>
                        <td>
                            {{ $child->payrollRelationship->name ?? '' }}
                        </td>
                        <td>
                            {{ $birthdate }}
                        </td>
                        <td>
                            {{ age($child->birthdate) }}
                        </td>
                        <td>
                            {{ $child->id_number }}
                        </td>
                        <td>
                            {{ $child->payrollGender->name ?? '' }}
                        </td>
                        <td>
                            {{ $child->is_student == 1 ? 'Si' : 'No' }}
                        </td>
                        <td>
                            {{ $child->payrollSchoolingLevel->name ?? '' }}
                        </td>
                        <td>
                            {{ $child->study_center }}
                        </td>
                        <td>
                            {{ $child->has_scolarships }}
                        </td>
                        <td>
                            {{ $child->has_disability }}
                        </td>
                        <td>
                            {{ $child->payrollDisability->name ?? '' }}
                        </td>
                        <td>
                            {{ $child->address }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endforeach