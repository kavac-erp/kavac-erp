<table cellspacing="0" cellpadding="1" border="0">
    <colgroup>
        <col span="1" style="width: auto" />
        <col span="1" style="width: auto" />
        <col span="1" style="width: auto" />
        <col span="1" style="width: auto" />
        <col span="1" style="width: auto" />
        <col span="1" style="width: auto" />
        <col span="1" style="width: auto" />
    </colgroup>
    <thead>
        <tr style="background-color: #BDBDBD;">
            <th span="1">Nombre</th>
            <th span="1">Código</th>
            <th span="1">Código ONAPRE</th>
            <th span="1" align="center">¿Activo?</th>
            <th span="1">Dependecia</th>
            <th span="1">Responsable</th>
            <th span="1">Cargo</th>
        </tr>
    </thead>
    <!-- <thead>
        <tr style="background-color: #BDBDBD;">
            <th span="1">Dependecia</th>
            <th span="1">Responsable</th>
            <th span="1">Cargo</th>
        </tr>
    </thead> -->
    <tbody>
        @foreach ($records as $record)
        <tr>
            <td>{{ $record->name }}</td>
            <td>{{ $record->code }}</td>
            <td>{{ $record->onapre_code }}</td>
            <td align="center">{{ $record->active ? 'Activo' : 'Inactivo' }}</td>
            <td>{{ $record->department->name }}</td>
            <td>{{ $record->payrollstaff->first_name.' '.$record->payrollstaff->last_name }}</td>
            <td>{{ $record->payrollposition->name }}</td>
        </tr>
        @endforeach
    </tbody>
</table>