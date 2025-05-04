<h2 style="font-size: 13rem;" align="center">Información de Inventario de Productos
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

        <th width="10%">Código</th>
        <th width="10%">Producto</th>
        <th width="20%">Descripción</th>
        <th width="10%">Almacén</th>
        <th width="10%">Existencia</th>
        <th width="10%">Reservados</th>
        <th width="10%">Solicitados</th>
        <th width="10%">Disponible para solicitar</th>
        <th width="10%">Valor Unitario</th>
    </tr>
    @foreach($fields as $field)
        @php
            $quantity = 0;
            $real = $field->warehouseInventoryProduct
                ? $field->warehouseInventoryProduct->real
                : $field->real;
        @endphp
        <tr>
            <td width="10%"> {{ $field->warehouseInventoryProduct
                ? $field->warehouseInventoryProduct->code
                : $field->code }} </td>
            <td width="10%"> {{ $field->warehouseInventoryProduct
                ? $field->warehouseInventoryProduct->warehouseProduct->name
                : $field->warehouseProduct->name  }} </td>
            <td width="20%"> {{ $field->warehouseInventoryProduct
                ? strip_tags($field->warehouseInventoryProduct->warehouseProduct->description) 
                : strip_tags($field->warehouseProduct->description)   }} </td>
            <td width="10%"> {{ $field->warehouseInventoryProduct
                ? $field->warehouseInventoryProduct->warehouseInstitutionWarehouse->warehouse->name
                : $field->warehouseInstitutionWarehouse->warehouse->name }} </td>
            <td width="10%"> {{ $field->warehouseInventoryProduct
                ? $field->warehouseInventoryProduct->real
                : $field->real }} </td>
            <td width="10%"> {{ $field->warehouseInventoryProduct
                ? $field->warehouseInventoryProduct->reserved
                : $field->reserved }} </td>
            @foreach($productsQuantity as $productQuantity)
                @if($field->code == $productQuantity['code'])
                    @php
                        $quantity = $productQuantity['quantity']
                    @endphp
                @endif
            @endforeach     
                    <td width="10%"> 
                        {{ $productsQuantity
                            ? $quantity
                            : 0 }} 
                    </td>
            <td width="10%"> {{ $real - $quantity }} </td>
            <td width="10%"> {{ $field->warehouseInventoryProduct
                ? ($field->warehouseInventoryProduct->unit_value .' '. $field->warehouseInventoryProduct->currency->symbol)
                : ($field->unit_value .' '. $field->currency->symbol) }} </td>
        </tr>
    @endforeach
</table>
