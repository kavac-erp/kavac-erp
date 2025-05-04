@php
    function datetime_diff($date1) {
        $datetime1 = new DateTime($date1);
        $datetime2 = new DateTime();
        $difference = $datetime1->diff($datetime2);
        return $difference->y;
    }
@endphp

@php    
    $year = datetime_diff($field->payrollStaff->payrollEmployment->start_date);
    $y_apn = $field->payrollStaff->payrollEmployment->years_apn != null ?
        str_split($field->payrollStaff->payrollEmployment->years_apn)
        : '0';
    $years_service = $field->payrollStaff->payrollEmployment->years_apn != null ?
        $y_apn[7] + $year
        : $y_apn[0] + $year;
@endphp

<table cellspacing="0" cellpadding="1" border="0">
    <tr style="background-color: #BDBDBD;">
        <th width="50%">Código de la solicitud</th>
        <th width="50%">Fecha de la solicitud</th>
    </tr>
    <tr>
        <td width="50%"> {{ $field->code }} </td>
        <td width="50%"> {{ $field->created_at }} </td>
    </tr>
    <tr><th></th></tr>

    <tr style="background-color: #BDBDBD;">
        <th width="50%">Trabajador</th>
        <th width="50%">Cargo</th>
    </tr>
    <tr>
        <td width="50%">
            {{
                $field->payrollStaff
                ? $field->payrollStaff->first_name . ' ' . $field->payrollStaff->last_name
                : 'No definido'
            }}
        </td>
        <td width="50%"> {{ $field->payrollStaff->payrollEmployment->payrollPosition->name }} </td>
    </tr>
    <tr><th></th></tr>
    <tr style="background-color: #BDBDBD;">
        <th width="50%">Departamento</th>
        <th width="50%">Fecha de ingreso</th>
    </tr>
    <tr>
        <td width="50%"> {{ $field->payrollStaff->payrollEmployment->department->name }} </td>
        <td width="50%"> {{ $field->payrollStaff->payrollEmployment->start_date }} </td>
    </tr>
    <tr><th></th></tr>
    <tr style="background-color: #BDBDBD;">
        <th width="50%">Años de servicio</th>
        <th width="50%">Periodos vacacionales solicitados</th>
    </tr>
    <tr>
        <td width="50%">
            {{
                $years_service
            }}
        </td>
        <td width="50%">
            @foreach (json_decode($field->vacation_period_year) as $period)
                {{ $period->text }}
            @endforeach
        </td>
    </tr>
    <tr><th></th></tr>
    <tr style="background-color: #BDBDBD;">
        <th width="50%">Días por antigüedad</th>
        <th width="50%">Días solicitados</th>
    </tr>
    <tr>
        <td width="50%"> {{ $field->days_requested }} </td>
        <td width="50%"> {{ $field->days_requested }} </td>
    </tr>
    <tr><th></th></tr>
    <tr style="background-color: #BDBDBD;">
        <th width="50%">Periodo vacacional solicitado</th>
        <th width="50%">Estatus</th>
    </tr>
    <tr>
        <td width="50%"> {{ $field->start_date . ' - ' . $field->end_date }} </td>
        <td width="50%">
            {{
                ($field->status == 'approved')
                    ? 'Aprobado'
                    : 'Pendiente'
            }}
        </td>
    </tr>
</table>