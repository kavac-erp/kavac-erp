<h2 style="font-size: 13rem;" align="center">Información de Reporte de Mínimo Inventario
</h2>
<h2>
    <br>
</h2>

<h4 style="font-size: 10rem;">Código del ente: {{ $institution['onapre_code'] }}</h4>
<h4 style="font-size: 10rem;">Denominación del ente: {{ $institution['name'] }}</h4>
<h4 style="font-size: 10rem;">Año Fiscal: {{ $fiscal_year }}</h4>
<br>

<table cellspacing="0" cellpadding="1" border="1">
    <tr align="C" style="background-color: #cfcfcf;">
        <th width="25%">Producto</th>
        <th width="15%">Almacén</th>
        <th width="15%">Mínimo</th>
        <th width="15%">Existencia actual</th>
        <th width="30%">Detalle</th>
    </tr>

    @foreach($fields as $field)
        <tr>
            <td width="25%"> {{ $field->warehouseInventoryProduct
                ? $field->warehouseInventoryProduct->warehouseProduct
                    ? $field->warehouseInventoryProduct->warehouseProduct->name
                    : ''
                : ''  }} </td>
            <td width="15%"> {{ $field->warehouseInventoryProduct
                ? $field->warehouseInventoryProduct->warehouseInstitutionWarehouse->warehouse->name
                : $field->warehouseInstitutionWarehouse->warehouse->name }} </td>

            <td width="15%"> {{ $field->minimum }} </td>

            <td width="15%"> {{ $field->warehouseInventoryProduct
                ? $field->warehouseInventoryProduct->real
                : '' }} </td>

            <td width="30%">
                <span>
                    @if ($field->minimum == $field->warehouseInventoryProduct->real)
                            El artículo llegó al mínimo de existencia


                    @elseif ($field->warehouseInventoryProduct->real == 0)

                            No hay existencia en inventario


                    @elseif ($field->minimum > $field->warehouseInventoryProduct->exist)
                            El artículo sobrepasa el mínimo de existencia


                    @elseif ($field->minimum < $field->warehouseInventoryProduct->real)
                            Hay existencia del artículo en inventario
                    @endif

                </span>

            </td>

        </tr>
    @endforeach
</table>
