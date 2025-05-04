@if (isset($from_date) && isset($to_date))
<table>
    <thead>
        <tr>
            <td width="39%" style="font-weight: bold;">Periodo de consulta:</td>
            <td width="75%" style="font-weight: bold;">
                Desde: {{ date_format(new DateTime($from_date), 'd-m-Y') }}
                Hasta: {{ date_format(new DateTime($to_date), 'd-m-Y') }}
            </td>
        </tr>
        <tr>
            <td>
                &nbsp;
            </td>
        </tr>
        <tr>
            <td>
                &nbsp;
            </td>
        </tr>
    </thead>
</table>
@endif
@foreach ($records as $record)
    @php
        $supervisor = $record->payrollSupervisedGroup->supervisor->id_number . ' - ' .
            $record->payrollSupervisedGroup->supervisor->getFullNameAttribute();
        $approver = $record->payrollSupervisedGroup->approver->id_number . ' - ' .
            $record->payrollSupervisedGroup->approver->getFullNameAttribute();
        $status = $record->documentStatus->name;
        $parametersExist = count($record->time_sheet_columns) > 0;

        if ($record['parameterColumns']) {
            $parametersExist = count($record['parameterColumns']) > 4;
        }
    @endphp
    @if ($parametersExist)
        <table width="35%" cellpadding="4" style="font-size: 8rem">
            <thead style="display: flex;">
                <tr>
                    <td width="25%" style="font-weight: bold; flex: 1;">Desde:</td>
                    <td width="75%">
                        {{ date_format(new DateTime($record['from_date']), 'd-m-Y') }}
                    </td>
                    <td width="25%" style="font-weight: bold; flex: 1;">Hasta:</td>
                    <td width="75%">
                        {{ date_format(new DateTime($record['to_date']), 'd-m-Y') }}
                    </td>
                    <td width="50%" style="font-weight: bold; flex: 1;">Código grupo de supervisados:</td>
                    <td width="75%">
                        {{ $record->payrollSupervisedGroup->code }}
                    </td>
                </tr>
                <tr>
                    <td width="25%" style="font-weight: bold; flex: 1;">Supervisor:</td>
                    <td width="75%">
                        {{ $supervisor }}
                    </td>
                    <td width="25%" style="font-weight: bold; flex: 1;">Aprobador:</td>
                    <td width="75%">
                        {{ $approver }}
                    </td>
                    <td width="25%" style="font-weight: bold; flex: 1;">Estatus:</td>
                    <td width="75%">
                        {{ $status }}
                    </td>
                </tr>
                <tr>
                    <td width="25%" style="font-weight: bold; flex: 1;">Observaciones:</td>
                    <td width="75%">
                        {{ $record['observations'] ?? '' }}
                    </td>
                </tr>
            </thead>
            <tr>
                <td>
                    &nbsp;
                </td>
            </tr>
        </table>
        <table cellspacing="0" cellpadding="4" style="font-size: 7rem;" align="center">
            <thead>
                <tr>
                    @if ($payrollTimeParameters != null)
                        @foreach ($record['parameterColumns'] as $parameterColumn)
                            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">
                                {{ $parameterColumn['name'] }}
                            </th>
                        @endforeach
                    @else
                        @foreach ($record->time_sheet_columns as $time_sheet_column)
                            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">
                                {{ $time_sheet_column['name'] }}
                            </th>
                        @endforeach
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($record['draggableData'] as $key => $keyValue)
                    <tr>
                        @foreach ($keyValue as $keyItem => $data)
                            @if ($payrollTimeParameters != null)
                                @foreach ($record['parameterColumns'] as $value)
                                    @if ($keyItem == $value['name'])
                                        @if (is_array($data))
                                            <td style="border: solid 1px #808080; font-weight: bold;">
                                                {{ $data['name'] ?? '' }}
                                            </td>
                                        @else
                                        <td style="border: solid 1px #808080; font-weight: bold;">
                                            {{ $data }}
                                        </td>
                                        @endif
                                    @endif
                                @endforeach
                            @else
                                @foreach ($record->time_sheet_columns as $value)
                                    @if ($keyItem == $value['name'])
                                        <td style="border: solid 1px #808080; font-weight: bold;">
                                            {{ $data ?? '' }}
                                        </td>
                                    @endif
                                @endforeach
                            @endif
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div>
            <table style="font-size: 8rem; margin-top: 100px" cellpadding="3">
                <tr>
                    <td>
                        &nbsp;
                    </td>
                </tr>
                <tr>
                    <td>
                        &nbsp;
                    </td>
                </tr>
                <tr>
                    <td>
                        &nbsp;
                    </td>
                </tr>
            </table>
        </div>
    @endif
@endforeach