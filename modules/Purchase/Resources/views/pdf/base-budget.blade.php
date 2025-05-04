<table cellspacing="1" cellpadding="0" border="0">
    <thead>
        <tr>
            <th width="35%" style="font-size:9rem;">
                <strong>Código:</strong>
                <br>
                {{  
                    isset($record['purchaseRequirement']) 
                        ? $record['purchaseRequirement']['code'] 
                        : ''
                }}
            </th>
            <th width="40%" style="font-size:9rem;">
                <strong>Descripción:</strong>
                <br>
                {{ 
                    isset($record['purchaseRequirement'])
                        ? $record['purchaseRequirement']['description'] 
                        : ''
                }}
            </th>
            <th width="25%" style="font-size:9rem;">
                <strong>Año fiscal:</strong>
                <br>
                {{ 
                    isset($record['purchaseRequirement']) 
                        ? $record['purchaseRequirement']['fiscalYear']['year'] 
                        : '' 
                }}
            </th>
        </tr>
        <tr>
            <th width="100%"></th>
        </tr>
        <tr>
            <th width="35%" style="font-size:9rem;">
                <strong>Departamento contratante:</strong>
                <br>
                {{ 
                    isset($record['purchaseRequirement']) 
                        ? $record['purchaseRequirement']['contratingDepartment']
                            ['name']
                        : '' 
                }}
            </th>
            <th width="40%" style="font-size:9rem;">
                <strong>Departamento Usuario:</strong>
                <br>
                {{ 
                    isset($record['purchaseRequirement']) 
                        ? $record['purchaseRequirement']['userDepartment']
                            ['name']
                        : '' 
                }}
            </th>
            <th width="25%" style="font-size:9rem;">
                <strong>Tipo:</strong>
                <br>
                {{ 
                    isset($record['purchaseRequirement']) 
                        ? $record['purchaseRequirement']
                            ['purchaseSupplierObject']['name']
                        : '' 
                }}
            </th>
        </tr>
        <tr>
            <th width="100%"></th>
        </tr>
        <tr>
            <th width="35%" style="font-size:9rem;">
                <strong>Fecha de generación:</strong>
                <br>
                {{ 
                    isset($record['purchaseRequirement']) 
                        ? date("d/m/Y", strtotime($record['purchaseRequirement']
                            ['date']))
                        : '' 
                }}
            </th>
        </tr>
    </thead>
</table>
<br>
<br>
@php
    $quantityPrice = 0;
    $ivaSumatoria = 0;
    $subTotal = 0;
    $total = 0;
@endphp
<h4>Listado de Productos</h4>
<table cellspacing="0" cellpadding="2" border="0.1" style="font-size: 7rem;">
    <thead>
        <tr style="background-color: #BDBDBD;">
            <th width="15%" align="center"><b>Código de requerimiento</b></th>
            <th width="15%" align="center"><b>Nombre</b></th>
            <th width="15%" align="center"><b>Especificaciones técnicas</b></th>
            <th width="10%" align="center"><b>Cantidad - Unidad de medida</b></th>
            <th width="15%" align="center"><b>Precio unitario sin IVA</b></th>
            <th width="15%" align="center"><b>Cantidad * Precio unitario</b></th>
            <th width="15%" align="center"><b>IVA</b></th>
        </tr>
    </thead>
    <tbody>
        @php
            $bases_imponibles = []; // Array para almacenar las bases imponibles
        @endphp
        @foreach($record['relatable'] as $recordRelatable)
        <tr>
            <td width="15%" align="center">
                {{
                    $recordRelatable['purchaseRequirementItem']
                        ? $recordRelatable['purchaseRequirementItem']
                            ['purchaseRequirement']
                            ? $recordRelatable['purchaseRequirementItem']
                                ['purchaseRequirement']['code']
                            : ''
                        : ''
                }}
            </td>
            <td width="15%">
                {{
                    $recordRelatable['purchaseRequirementItem']
                        ? $recordRelatable['purchaseRequirementItem']['name']
                        : ''
                }}
            </td>
            <td width="15%">
                {{
                    $recordRelatable['purchaseRequirementItem']
                        ? $recordRelatable['purchaseRequirementItem']
                            ['technical_specifications']
                        : ''
                }}
            </td>
            <td width="10%" align="center">
                {{
                    $recordRelatable['purchaseRequirementItem']
                        ? $recordRelatable['purchaseRequirementItem']
                            ['quantity']
                        : ''
                }}
                {{
                    $recordRelatable['purchaseRequirementItem']
                        ? $recordRelatable['purchaseRequirementItem']
                            ? $recordRelatable['purchaseRequirementItem']
                                ['measurementUnit']
                                ? $recordRelatable['purchaseRequirementItem']
                                    ['measurementUnit']['name']
                                : ''
                            : ''
                        : ''
                }}
            </td>
            <td width="15%" align="right">
                {{
                    $recordRelatable['purchaseRequirementItem']
                        ? $recordRelatable['purchaseRequirementItem']
                            ['unit_price']
                        : ''
                }}
            </td>
            <td width="15%" align="right">
                {{
                    $recordRelatable['purchaseRequirementItem']
                        ? $record['currency']
                            ? currency_format($recordRelatable['purchaseRequirementItem']
                                ['unit_price']
                            * $recordRelatable['purchaseRequirementItem']
                                ['quantity'], $record['currency']
                                    ['decimal_places'])
                            : $recordRelatable['purchaseRequirementItem']
                                ['unit_price']
                            * $recordRelatable['purchaseRequirementItem']
                                ['quantity']
                        : ''
                }}
            </td>
            <td width="15%" align="center">
                {{
                    $recordRelatable['purchaseRequirementItem']
                        ? $recordRelatable['purchaseRequirementItem']
                            ['historyTax']
                            ? ($recordRelatable['purchaseRequirementItem']
                                ['historyTax']['percentage'] / 100 * 100)
                            : '0'
                        : ''
                }}%
            </td>
            @php
                $quantityPrice = 
                    $recordRelatable['purchaseRequirementItem'] 
                        ? $recordRelatable['purchaseRequirementItem']
                            ['unit_price']
                            ? $recordRelatable['purchaseRequirementItem']
                                ['unit_price']
                            * $recordRelatable['purchaseRequirementItem']
                                ['quantity']
                            : 0
                        : 0;
                $subTotal += $quantityPrice;
                $ivaSumatoria +=
                    $recordRelatable['purchaseRequirementItem']
                        ? $recordRelatable['purchaseRequirementItem']
                            ? $recordRelatable['purchaseRequirementItem']['history_tax_id']
                                ? $recordRelatable['purchaseRequirementItem']['historyTax']
                                    ? $record['currency']
                                        ? $recordRelatable['purchaseRequirementItem']['unit_price']
                                            * $recordRelatable['purchaseRequirementItem']['quantity']
                                            * ($recordRelatable['purchaseRequirementItem']['historyTax']['percentage'] / 100)
                                        : $recordRelatable['purchaseRequirementItem']['unit_price']
                                        * $recordRelatable['purchaseRequirementItem']['quantity']
                                        * ($recordRelatable['purchaseRequirementItem']['historyTax']['percentage'] / 100)
                                    : 0
                                : 0
                            : 0
                        : 0;
                $total = $subTotal + $ivaSumatoria;

                $base_imponible = $quantityPrice; // Valor de la base imponible actual

                $historyTax = $recordRelatable['purchaseRequirementItem']
                    ? $recordRelatable['purchaseRequirementItem']['historyTax']
                    : null;

                $percentage = $historyTax ? $historyTax['percentage'] : "0.00";
                if (isset($bases_imponibles[$percentage])) {
                    // Si ya existe una base imponible para este porcentaje
                    // de IVA, se suma al valor existente
                    $bases_imponibles[$percentage] += $base_imponible;
                } else {
                    // Si no existe una base imponible para este porcentaje
                    // de IVA, se crea una nueva entrada
                    $bases_imponibles[$percentage] = $base_imponible;
                }
            @endphp
        </tr>
        @endforeach

        <!-- Filas de bases imponibles -->
        @foreach ($bases_imponibles as $percentage => $base)
        <tr>
            <td width="70%" align="right">
                Base imponible según alícuota {{ $percentage }}%
            </td>
            <td width="30%" align="right">
                {{ $base }}
            </td>
        </tr>
        <tr>
            <td width="70%" align="right">
            Monto total del impuesto según alícuota {{ $percentage }}%
            </td>
            <td width="30%" align="right">
                {{
                    round($base * $percentage / 100,
                    $record['currency'] ? $record['currency']['decimal_places'] : 2)
                }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<table border="0.1" cellpadding="4px" cellspacing="0px" style="width:100%; font-size: 7rem;">
    <tfoot>
        <tr style="background-color: #BDBDBD;">
            <td width="70%" align="right" style="font-size:8rem;">
                <strong>TOTAL 
                    {{ 
                        $record['currency'] ? $record['currency']['symbol'] : ''
                    }}
                </strong>
            </td>
            <td width="30%" align="center" style="font-size:8rem;">
                <strong>
                    {{
                        $record['currency']
                        ? round($total, $record['currency']['decimal_places'])
                        : 0
                    }}
                </strong>
            </td>
        </tr>
    </tfoot>
</table>

<br>

@if (isset($record['availabilityitem']) && count($record['availabilityitem']) > 0)
    <h4>Cuentas presupuestarias de gastos</h4>
    <table border="0.1" cellspacing="0" cellpadding="2" style="font-size: 8rem;">
        <thead>
            <tr style="background-color: #BDBDBD;">
                <th align="center"><b>Código</b></th>
                <th align="center"><b>Nombre</b></th>
                <th align="center"><b>Descripción</b></th>
                <th align="center"><b>Monto</b></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($record['availabilityitem'] as $item)
                <tr>
                    <td align="center">
                        {{ $item['item_code'] }}
                    </td>
                    <td align="left">
                        {{ $item['item_name'] }}
                    </td>
                    <td align="left">
                        {{ $item['description'] }}
                    </td>
                    <td align="center">
                        {{ $item['amount'] }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

<br>

<h3>Firmas autorizadas</h3>
<table cellspacing="1" cellpadding="0" border="0">
    <thead>
        <tr>
            <th width="35%" style="font-size:9rem;">
                <strong>Preparado por</strong>: <br>
                    {{
                        ( $record['preparedBy']
                        ? $record['preparedBy']['payrollStaff']
                            ? $record['preparedBy']['payrollStaff']['first_name'] . ' ' .
                                $record['preparedBy']['payrollStaff']['last_name']
                            : 'No definido'
                        : $record['purchaseRequirement'] && $record['purchaseRequirement']['preparedBy'] )
                        ? $record['purchaseRequirement']['preparedBy']['payrollStaff']
                            ? $record['purchaseRequirement']['preparedBy']['payrollStaff']['first_name'] . ' ' .
                                $record['purchaseRequirement']['preparedBy']['payrollStaff']['last_name']
                            : 'No definido'
                        : 'No definido'
                    }}
            </th>
            <th width="35%" style="font-size:9rem;">
                <strong>Revisado por</strong>: <br>
                    {{
                        ( $record['reviewedBy']
                        ? $record['reviewedBy']['payrollStaff']
                            ? $record['reviewedBy']['payrollStaff']['first_name'] . ' ' .
                                $record['reviewedBy']['payrollStaff']['last_name']
                            : 'No definido'
                        : $record['purchaseRequirement'] && $record['purchaseRequirement']['reviewedBy'] )
                        ? $record['purchaseRequirement']['reviewedBy']['payrollStaff']
                            ? $record['purchaseRequirement']['reviewedBy']['payrollStaff']['first_name'] . ' ' .
                                $record['purchaseRequirement']['reviewedBy']['payrollStaff']['last_name']
                            : 'No definido'
                        : 'No definido'
                    }}
            </th>
            <th width="30%" style="font-size:9rem;">
                <strong>Verificado por</strong>: <br>
                    {{
                        ( $record['verifiedBy']
                        ? $record['verifiedBy']['payrollStaff']
                            ? $record['verifiedBy']['payrollStaff']['first_name'] . ' ' .
                                $record['verifiedBy']['payrollStaff']['last_name']
                            : 'No definido'
                        : $record['purchaseRequirement'] && $record['purchaseRequirement']['verifiedBy'] )
                        ? $record['purchaseRequirement']['verifiedBy']['payrollStaff']
                            ? $record['purchaseRequirement']['verifiedBy']['payrollStaff']['first_name'] . ' ' .
                                $record['purchaseRequirement']['verifiedBy']['payrollStaff']['last_name']
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
                        ( $record['firstSignature']
                        ? $record['firstSignature']['payrollStaff']
                            ? $record['firstSignature']['payrollStaff']['first_name'] . ' ' .
                                $record['firstSignature']['payrollStaff']['last_name']
                            : 'No definido'
                        : $record['purchaseRequirement'] && $record['purchaseRequirement']['firstSignature'] )
                        ? $record['purchaseRequirement']['firstSignature']['payrollStaff']
                            ? $record['purchaseRequirement']['firstSignature']['payrollStaff']['first_name'] . ' ' .
                                $record['purchaseRequirement']['firstSignature']['payrollStaff']['last_name']
                            : 'No definido'
                        : 'No definido'
                    }}
            </th>
            <th width="35%" style="font-size:9rem;">
                <strong>Firmado por</strong>: <br>
                    {{
                        ( $record['secondSignature']
                        ? $record['secondSignature']['payrollStaff']
                            ? $record['secondSignature']['payrollStaff']['first_name'] . ' ' .
                                $record['secondSignature']['payrollStaff']['last_name']
                            : 'No definido'
                        : $record['purchaseRequirement'] && $record['purchaseRequirement']['secondSignature'] )
                        ? $record['purchaseRequirement']['secondSignature']['payrollStaff']
                            ? $record['purchaseRequirement']['secondSignature']['payrollStaff']['first_name'] . ' ' .
                                $record['purchaseRequirement']['secondSignature']['payrollStaff']['last_name']
                            : 'No definido'
                        : 'No definido'
                    }}
            </th>
        </tr>
    </thead>
</table>
