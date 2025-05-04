<br><br>
<table cellspacing="1" cellpadding="0" border="0">
    <tbody>
        <tr>
            <td width="35%" style="font-size:8rem;">
                <strong>Fecha de generación: </strong>
                {{
                    isset($records->date) ?
                    date("d/m/Y", strtotime($records->date)) :
                    'Sin fecha asignada'
                }}
            </td>
            <td width="35%" style="font-size:8rem;">
                <strong>Proveedor: </strong>
                {{ $records->purchaseSupplier['name'] }}
            </td>
            <td width="35%" style="font-size:8rem;">
                <strong>Tipo de moneda: </strong>
                {{ $records->currency['name'] }}
            </td>
        </tr>
        <br>
        <tr>
            <td width="35%" style="font-size:8rem;">
                <strong>Año fiscal: </strong>
                {{
                    $records->relatable[0]['purchaseRequirementItem']
                        ['purchaseRequirement']['fiscalYear']['year']
                }}
            </td>
        </tr>
    </tbody>
</table>
<div style="text-align: center; font-size: 10px;">
    <h5><strong>LISTA DE REQUERIMIENTOS</strong></h5>
</div>
<table cellspacing="0" cellpadding="1" border="0.1">
    <tr style="background-color: #BDBDBD;">
        <th width="15%" style="font-size:8rem;" align="center">
            <strong>Fecha de generación</strong>
        </th>
        <th width="20%" style="font-size:8rem;" align="center">
            <strong>Código de requerimiento</strong>
        </th>
        <th width="25%" style="font-size:8rem;" align="center">
            <strong>Descripción</strong>
        </th>
        <th width="20%" style="font-size:8rem;" align="center">
            <strong>Unidad contratante</strong>
        </th>
        <th width="20%" style="font-size:8rem;" align="center">
            <strong>Departamento usuario</strong>
        </th>
    </tr>
    @php
        $q = [];
    @endphp
    @foreach($records->relatable as $requirement)
        @if (!in_array($requirement['purchaseRequirementItem']
            ['purchaseRequirement']['code'], $q))
            <tr>
                <td style="font-size:8rem;" align="center">
                    {{
                        date("d/m/Y", strtotime($requirement['purchaseRequirementItem']
                            ['purchaseRequirement']['date']))
                    }}
                </td>
                <td style="font-size: 8rem;" align="left">
                    {{
                        $requirement['purchaseRequirementItem']
                            ['purchaseRequirement']['code']
                    }}
                </td>
                <td style="font-size:8rem;" align="left">
                    {{
                        $requirement['purchaseRequirementItem']
                            ['purchaseRequirement']['description']
                    }}
                </td>
                <td style="font-size:8rem;">
                    {{
                        $requirement['purchaseRequirementItem']
                            ['purchaseRequirement']['contratingDepartment']
                                ['name']
                    }}
                </td>
                <td style="font-size:8rem;">
                    {{
                        $requirement['purchaseRequirementItem']
                            ['purchaseRequirement']['userDepartment']['name']
                    }}
                </td>
            </tr>
            @php
                $q[] = $requirement['purchaseRequirementItem']
                    ['purchaseRequirement']['code'];
            @endphp
        @endif
    @endforeach
</table>
@php
    $total = 0;
    $subTotal = 0;
    $subTotalFinal = 0;
    $iva = 0;
    $ivaFinal = 0;
    $bases_imponibles = []; // Array para almacenar las bases imponibles
    foreach ($records->relatable as $requirement) {
        $subTotal = ($requirement['purchaseRequirementItem']['Quoted']['quantity'] > 0
        ? $requirement['purchaseRequirementItem']['Quoted']['quantity'] :
        $requirement['purchaseRequirementItem']['quantity'])
        * $requirement['purchaseRequirementItem']['Quoted']['unit_price'];

        $iva = $requirement['purchaseRequirementItem']['history_tax_id'] ?
        ($requirement['purchaseRequirementItem']['Quoted']['quantity'] > 0
        ? $requirement['purchaseRequirementItem']['Quoted']['quantity'] :
        $requirement['purchaseRequirementItem']['quantity'])
        * $requirement['purchaseRequirementItem']['Quoted']['unit_price']
        * ($requirement['purchaseRequirementItem']['historyTax']['percentage'] / 100)
        : 0.00;

        $subTotalFinal += $subTotal;
        $ivaFinal += $iva;
        $total += $subTotal + $iva;

        $historyTax = $requirement['purchaseRequirementItem']
            ? $requirement['purchaseRequirementItem']['historyTax']
            : null;

        $percentage = $historyTax ? $historyTax['percentage'] : "0.00";

        if (isset($bases_imponibles[$percentage])) {
            // Si ya existe una base imponible para este porcentaje
            // de IVA, se suma al valor existente
            $bases_imponibles[$percentage] += $subTotal;
        } else {
            // Si no existe una base imponible para este porcentaje
            // de IVA, se crea una nueva entrada
            $bases_imponibles[$percentage] = $subTotal;
        }
    }
    $decimal = $records->currency ? $records->currency['decimal_places'] : 2;
@endphp
<div style="text-align: center; font-size: 10px;">
    <h5><strong>LISTA DE PRODUCTOS</strong></h5>
</div>
<table cellspacing="0" cellpadding="2" border="0.1">
    <tr style="background-color: #BDBDBD;">
        <th width="20%" style="font-size:8rem;" align="center">
            <strong>Código de requerimiento</strong>
        </th>
        <th width="20%" style="font-size:8rem;" align="center">
            <strong>Nombre</strong>
        </th>
        <th width="20%" style="font-size:8rem;" align="center">
            <strong>Especificaciones técnicas</strong>
        </th>
        <th width="10%" style="font-size:8rem;" align="center">
            <strong>Cantidad</strong>
        </th>
        <th width="10%" style="font-size:8rem;" align="center">
            <strong>Precio unitario sin IVA</strong>
        </th>
        <th width="10%" style="font-size:8rem;" align="center">
            <strong>Cantidad * Precio unitario</strong>
        </th>
        <th width="10%" style="font-size:8rem;" align="center">
            <strong>IVA</strong>
        </th>
    </tr>
    @foreach($records->relatable as $requirement)
        <tr>
            <td style="font-size: 8rem;" align="center">
                {{
                    $requirement['purchaseRequirementItem']
                        ['purchaseRequirement']['code']
                }}
            </td>
            <td style="font-size:8rem;" align="left">
                {{ $requirement['purchaseRequirementItem']['name'] }}
            </td>
            <td style="font-size:8rem;" align="left">
                {{
                    $requirement['purchaseRequirementItem']
                        ? $requirement['purchaseRequirementItem']
                            ['technical_specifications']
                        : ''
                }}
            </td>
            <td style="font-size:8rem;" align="center">
                {{
                    $requirement['purchaseRequirementItem']['Quoted']['quantity']
                    > 0 ? $requirement['purchaseRequirementItem']['Quoted']
                        ['quantity']
                    : $requirement['purchaseRequirementItem']['quantity']
                }}
            </td>
            <td style="font-size:8rem;" align="right">
                {{ $requirement['purchaseRequirementItem']['Quoted']['unit_price'] }}
            </td>
            <td style="font-size:8rem;" align="right">
                {{
                    currency_format(($requirement['purchaseRequirementItem']
                        ['Quoted']['quantity'] > 0
                    ? $requirement['purchaseRequirementItem']['Quoted']['quantity']
                    : $requirement['purchaseRequirementItem']['quantity'])
                    * $requirement['purchaseRequirementItem']['Quoted']
                        ['unit_price'], $decimal)
                }}
            </td>
            <td style="font-size:8rem;" align="center">
                {{
                    $requirement['purchaseRequirementItem']['history_tax_id'] ?
                    ($requirement['purchaseRequirementItem']['historyTax']
                        ['percentage'] / 100 * 100) : 0.00
                }}%
            </td>
        </tr>
    @endforeach
    <!-- Filas de bases imponibles -->
    @foreach ($bases_imponibles as $percentage => $base)
        <tr style="font-size: 8rem;">
            <td width="80%" align="right">
                Base imponible según alícuota {{ $percentage }}%
            </td>
            <td width="20%" align="right">
                {{ $base }}
            </td>
        </tr>
        <tr style="font-size: 8rem;">
            <td width="80%" align="right">
                Monto total del impuesto según alícuota {{ $percentage }}%
            </td>
            <td width="20%" align="right">
                {{
                    round($base * $percentage / 100,
                    $records['currency'] ? $records['currency']
                        ['decimal_places'] : 2)
                }}
            </td>
        </tr>
    @endforeach
</table>

<table border="0.1" cellpadding="4px" cellspacing="0px" style="width:100%">
    <tfoot>
        <tr style="background-color: #BDBDBD;">
            <td width="80%" align="right" style="font-size:8rem;">
                <strong>TOTAL {{ $records->currency['symbol'] }}</strong>
            </td>
            <td width="20%" align="center" style="font-size:8rem;">
                <strong>{{ round($total, $decimal) }}</strong>
            </td>
        </tr>
    </tfoot>
</table>

<div style="text-align: center; font-size: 10px;">
    <h5><strong>CUENTAS PRESUPUESTARIAS DE GASTOS</strong></h5>
</div>

<table cellspacing="0" cellpadding="3" border="0.1">
    <tr style="background-color: #BDBDBD;">
        <th width="33%" style="font-size:8rem;" align="center">
            <strong>Cuenta</strong>
        </th>
        <th width="33%" style="font-size:8rem;" align="center">
            <strong>Nombre</strong>
        </th>
        <th width="33%" style="font-size:8rem;" align="center">
            <strong>Monto</strong>
        </th>
    </tr>
    @if (isset($records->base_budget) && count($records->base_budget) > 0)
        @foreach($records->base_budget as $x)
            @if ($x->relatable->availabilityitem && count($x->relatable->availabilityitem) > 0)
                @foreach($x->relatable->availabilityitem as $availability)
                    <tr>
                        <td style="font-size: 8rem;" align="center">
                            {{ $availability['item_code'] }}
                        </td>
                        <td style="font-size: 8rem;" align="left">
                            {{ $availability['item_name'] }}
                        </td>
                        <td style="font-size: 8rem;" align="center">
                            {{ $availability['amount'] }}
                        </td>
                    </tr>
                @endforeach
            @endif
        @endforeach
    @endif
</table>