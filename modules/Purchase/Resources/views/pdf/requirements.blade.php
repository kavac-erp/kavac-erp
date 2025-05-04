<table cellspacing="0" cellpadding="0" border="0">
    <tbody>
        <tr>
            <td style="font-size:9rem;" width="33%">
                <strong>Fecha de generación</strong>: {{ date("d/m/Y", strtotime($requirement->date)) }}
            </td>
            <td style="font-size:9rem;" width="33%">
                <strong>Institución</strong>: {{ $requirement->institution['name'] }}
            </td>
            <td style="font-size:9rem;" width="33%">
                <strong>Ejercicio económico</strong>: {{ $requirement->fiscalYear['year'] }}
            </td>
        </tr>
        <br>
        <tr>
            <td style="font-size:9rem;" width="33%">
                <strong>Tipo</strong>: {{ $requirement->purchaseSupplierObject['name'] }}
            </td>
            <td style="font-size:9rem;" width="66%">
                <strong> Descripcion</strong>: {{ $requirement->description }}
            </td>
        </tr>
        <br>
        <tr>
            <td style="font-size:9rem;" width="66%">
                <strong>Unidad contratante</strong>: {{ $requirement->contratingDepartment['name']}}
            </td>
        </tr>
        <br>
        <tr>
            <td style="font-size:9rem;" width="66%">
                <strong>Unidad usuaria</strong>: {{ $requirement->userDepartment['name'] }}
            </td>
        </tr>
    </tbody>
</table>
<br>
<br>
<h3 align="center">Listado de Productos</h3>
<br><br>
<table cellspacing="0" cellpadding="1" border="0.1">
    <tr>
        <th width="25%" style="font-size:9rem; background-color: #BDBDBD;" align="center"><strong>Producto</strong></th>
        <th width="45%" style="font-size:9rem; background-color: #BDBDBD;" align="center"><strong>Especificaciones técnicas</strong></th>
        <th width="15%" style="font-size:9rem; background-color: #BDBDBD;" align="center"><strong>Unidad de medida</strong></th>
        <th width="15%" style="font-size:9rem; background-color: #BDBDBD;" align="center"><strong>Cantidad</strong></th>
    </tr>
    @foreach($requirement['purchaseRequirementItems'] as $product)
        <tr>
            <td style="font-size: 8rem;" align="left">
                {{' '.$product['name'] }}
            </td>
            <td style="font-size:9rem;" align="left">
                {!! $product['technical_specifications'] !!}
            </td>
            <td style="font-size:9rem;" align="center">
                {{
                    $product['measurement_unit_id'] ?
                    ' '.$product['measurementUnit']['name'] :
                    'N/A'
                }}
            </td>
            <td style="font-size:9rem;" align="center">
                {{' '.$product['quantity'] }}
            </td>
        </tr>
    @endforeach
</table>
<br><br>

<h3>Firmas autorizadas</h3>

<table cellspacing="1" cellpadding="0" border="0">
    <thead>
        <tr>
            <th width="35%" style="font-size:9rem;">
                <strong>Preparado por</strong>: <br>
                {{
                    $requirement->preparedBy
                        ? $requirement->preparedBy->payrollStaff
                            ? $requirement->preparedBy->payrollStaff->first_name . ' ' . $requirement->preparedBy->payrollStaff->last_name
                            : 'No definido'
                        : 'No definido'
                }}
            </th>
            <th width="35%" style="font-size:9rem;">
                <strong>Revisado por</strong>: <br>
                {{
                    $requirement->reviewedBy
                        ? $requirement->reviewedBy->payrollStaff
                            ? $requirement->reviewedBy->payrollStaff->first_name . ' ' . $requirement->reviewedBy->payrollStaff->last_name
                            : 'No definido'
                        : 'No definido'
                }}
            </th>
            <th width="30%" style="font-size:9rem;">
                <strong>Verificado por</strong>: <br>
                {{
                    $requirement->verifiedBy
                        ? $requirement->verifiedBy->payrollStaff
                            ? $requirement->verifiedBy->payrollStaff->first_name . ' ' . $requirement->verifiedBy->payrollStaff->last_name
                            : 'No definido'
                        : 'No definido'
                }}
            </th>
        </tr>
        <tr> <th width="100%"></th></tr>
        <tr>
            <th width="35%" style="font-size:9rem;">
                <strong>Firmado por</strong>: <br>
                {{
                    $requirement->firstSignature
                        ? $requirement->firstSignature->payrollStaff
                            ? $requirement->firstSignature->payrollStaff->first_name . ' ' . $requirement->firstSignature->payrollStaff->last_name
                            : 'No definido'
                        : 'No definido'
                }}
            </th>
            <th width="35%" style="font-size:9rem;">
                <strong>Firmado por</strong>: <br>
                {{
                    $requirement->secondSignature
                        ? $requirement->secondSignature->payrollStaff
                            ? $requirement->secondSignature->payrollStaff->first_name . ' ' . $requirement->secondSignature->payrollStaff->last_name
                            : 'No definido'
                        : 'No definido'
                }}
            </th>
        </tr>
    </thead>
</table>
