<h2 style="font-size: 13rem;" align="center">Información de Solicitudes de Productos
</h2>
<h2>
    <br>
</h2>

<h4 style="font-size: 10rem;">Expresado en: {{ $currencySymbol }}</h4>
<h4 style="font-size: 10rem;">Código del ente: {{ $institution['onapre_code'] }}</h4>
<h4 style="font-size: 10rem;">Denominación del ente: {{ $institution['name'] }}</h4>
<h4 style="font-size: 10rem;">Año Fiscal: {{ $fiscal_year }}</h4>
<br>



<table cellspacing="0" cellpadding="1" border="1">
    <tr align="C" style="background-color: #cfcfcf;">
        <th width="20%">Solicitante</th>
        <th width="15%">Fecha de solicitud</th>
        <th width="15%">Productos solicitados</th>
        <th width="12%">Cantidad solicitada</th>
        <th width="12%">Almacén</th>
        <th width="12%">Valor Unitario</th>
        <th width="12%">Inventario despues de entrega</th>
    </tr>
    @foreach($fields as $field)
        <tr>
            <td width="20%"> {{ $field->warehouseRequest && $field->warehouseRequest->payrollStaff
                ? $field->warehouseRequest->payrollStaff->full_name
                : $field->warehouseRequest->department->name }} </td>
            <td width="15%"> {{ date_format($field->created_at, "d/m/Y") }} </td>
            <td width="15%"> {{ $field->warehouseInventoryProduct
                ? $field->warehouseInventoryProduct->warehouseProduct->name
                : $field->warehouseProduct->name  }} </td>
            <td width="12%"> {{ $field->quantity }} </td>
            <td width="12%"> {{ $field->warehouseInventoryProduct
                ? $field->warehouseInventoryProduct->warehouseInstitutionWarehouse->warehouse->name
                : $field->warehouseInstitutionWarehouse->warehouse->name }} </td>
            <td width="12%"> {{ $field->warehouseInventoryProduct
                ? ($field->warehouseInventoryProduct->unit_value .' '. $field->warehouseInventoryProduct->currency->symbol)
                : ($field->unit_value .' '. $field->currency->symbol) }} </td>
            <td width="12%"> {{ $field->new_exist ? $field->new_exist : '' }} </td>
        </tr>
    @endforeach
</table>