<table border="0" cellspacing="0" cellpadding="0">
    <tbody>
        <tr>
            <td class="col-12">
                <strong>Fecha de generación:</strong>
                {{ date("d/m/Y", strtotime($record->date)) }}
            </td>
        </tr>
        <br>
        <tr>
            <td class="col-12">
                <strong>Número de contratación:</strong>
                <span class="red">{{ $record->code }}</span>
            </td>
        </tr>
        <br>
        <tr>
            <td width="col-12">
                <strong>Descripción de contratación:</strong>
                {{ $record->description }}
            </td>
        </tr>
        <br>
        <tr>
            <td class="col-12">
                <strong>Unidad contratante:</strong>
                {{ $record->contratingDepartment->name }}
            </td>
        </tr>
        <br>
        <tr>
            <td class="col-12">
                <strong>Unidad usuaria:</strong>
                {{ $record->userDepartment->name }}
            </td>
        </tr>
        <tr>
            <th width="100%" style="text-align: center">
                <h5><strong>DATOS DEL PROVEEDOR</strong></h4>
            </th>
        </tr>
        <br>
        <tr>
            <td width="100%">
                <strong>Nombre o Razón social:</strong>
                {{ $record->purchaseSupplier->name }}
            </td>
        </tr>
        <br>
        <tr>
            <td width="100%">
                <strong>RIF:</strong>
                {{ $record->purchaseSupplier->rif }}
            </td>
        </tr>
        <br>
        <tr>
            <td width="100%" style="display: inline">
                <strong>Dirección fiscal:</strong>
                {{strip_tags($record->purchaseSupplier->direction) }}
            </td>
        </tr>
        <br>
        <tr>
            <td width="100%">
                <strong>Plazo de entrega:</strong> {{ $record->due_date }} {{ $record->time_frame}}, de no cumplir con los plazos de
                entrega esta orden será anulada
            </td>
        </tr>
        <br>
        <tr>
            <td width="100%">
                <strong>Forma de pago:</strong>
                {{
                    ($record->payment_methods == "pay_order") ? ("Orden de Pago")
                    : ( ($record->payment_methods == "direct") ? ("Directa")
                    : ( ($record->payment_methods == "credit") ? ("Credito")
                    : ( ($record->payment_methods == "advance") ? ("Avance") : ("Otros"))))
                }}
            </td>
        </tr>
        <br>
        <tr>
            <td width="100%">
                <strong>Número de Certificado (RNC):</strong>
                {{ $record->rnc_certificate_number ? $record->rnc_certificate_number : "No definido" }}
            </td>
        </tr>
        <br>
        <tr>
            <td width="100%">
                <strong>Lugar de entrega:</strong>
                {{ strip_tags($record->institution->legal_address) }}
            </td>
        </tr>
    </tbody>
</table>

@php
    $total = 0;
    $iva = 0;
    $ivaFinal = 0;
    $subTotalFinal = 0;
    $subTotal = 0;
    $bases_imponibles = []; // Array para almacenar las bases imponibles
    foreach ($record->quatations as $quatation) {
        foreach ($quatation->relatable as $requirement) {
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
    }
@endphp

<div style="text-align: center; font-size: 10px;">
    <h5><strong>LISTA DE PRODUCTOS</strong></h5>
</div>

<table cellspacing="0" cellpadding="3" border="0.1" style="font-size: 8rem;">
    <thead>
        <tr style="background-color: #BDBDBD;">
            <td width="15%" align="center"><b>Código de requerimiento</b></td>
            <td width="20%" align="center"><b>Nombre</b></td>
            <th width="20%" style="font-size:8rem;" align="center">
                <strong>Especificaciones técnicas</strong>
            </th>
            <td width="10%" align="center"><b>Cantidad</b></td>
            <td width="10%" align="center"><b>Precio unitario sin IVA</b></td>
            <td width="15%" align="center"><b>Cantidad * Precio unitario</b></td>
            <td width="10%" align="center"><b>IVA</b></td>
        </tr>
    </thead>
    <tbody>
        @foreach ($record->quatations as $quatations)
            @foreach ($quatations->relatable as $items)
                <tr>
                    <td width="15%" align="center">
                        {{ $items->purchaseRequirementItem->purchaseRequirement->code }}
                    </td>
                    <td width="20%" align="left">
                        {{ $items->purchaseRequirementItem->name }}
                    </td>
                    <td width="20%" align="left">
                        {{
                            $items->purchaseRequirementItem
                                ? $items->purchaseRequirementItem->
                                    technical_specifications
                                : ''
                        }}
                    </td>
                    <td width="10%" align="center">
                        {{ $items->quantity > 0 ?
                            $items->quantity:
                            $items->purchaseRequirementItem->quantity }}
                    </td>
                    <td width="10%" align="right">
                        {{ $items->unit_price }}
                    </td>
                    <td width="15%" align="right">
                        {{ round(($items->quantity > 0 ?
                            $items->quantity:
                            $items->purchaseRequirementItem->quantity)
                            * $items->unit_price, $record->currency->decimal_places)
                        }}
                    </td>
                    <td width="10%" align="center">
                        {{
                            $items->purchaseRequirementItem->history_tax_id ?
                            $items->purchaseRequirementItem->historyTax->percentage
                            : 0
                        }}%
                    </td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>

<table border="0.1" cellpadding="4px" cellspacing="0px" style="width:100%; font-size: 7rem;">
    <tfoot>
        <!-- Filas de bases imponibles -->
        @foreach ($bases_imponibles as $percentage => $base)
            <tr style="font-size: 8rem;">
                <td width="75%" align="right">
                    Base imponible según alícuota {{ $percentage }}%
                </td>
                <td width="25%" align="right">
                    {{ $base }}
                </td>
            </tr>
            <tr style="font-size: 8rem;">
                <td width="75%" align="right">
                    Monto total del impuesto según alícuota {{ $percentage }}%
                </td>
                <td width="25%" align="right">
                    {{
                        round($base * $percentage / 100,
                        $record->currency ? $record->currency->decimal_places
                        : 2)
                    }}
                </td>
            </tr>
        @endforeach
        <tr style="background-color: #BDBDBD;">
            <td width="75%" align="right" style="font-size:8rem;">
                <strong>
                    TOTAL
                    {{
                        $record['currency'] ? $record['currency']['symbol'] : ''
                    }}
                </strong></td>
            <td width="25%" align="center" style="font-size:8rem;"><b>
                {{ round($total, $record->currency->decimal_places) }}</b>
            </td>
        </tr>
    </tfoot>
</table>

<br>
<br>
<br>
<br>

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

    @if (isset($record->base_budget) && count($record->base_budget) > 0)
        @foreach($record->base_budget as $x)
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

<table>
    <tbody>
        <tr>
            <th width="100%" style="text-align: center">
                <h5><strong>FIRMAS AUTORIZADAS</strong></h5>
            </th>
        </tr>
        <br>
        <tr>
            <th>
                <table border="0" cellpadding="5px">
                    <tbody>
                        <tr>
                            <td align="center" width="20%"><b>Preparado por:</b></td>
                            <td align="center" width="20%"><b>Revisado por:</b></td>
                            <td align="center" width="20%"><b>Verificado por</b></td>
                            <td align="center" width="20%"><b>Firmado por:</b></td>
                            <td align="center" width="20%"><b>Firmado por:</b></td>
                        </tr>
                        <tr>
                            <td align="center" width="20%">
                                {{ $record->preparedBy->payrollStaff->first_name }}
                                {{ $record->preparedBy->payrollStaff->last_name }}
                            </td>
                            <td align="center" width="20%">
                                {{ $record->reviewedBy?->payrollStaff?->first_name }}
                                {{$record->reviewedBy?->payrollStaff?->last_name }}
                            </td>
                            <td align="center" width="20%">
                                {{ $record->verifiedBy?->payrollStaff?->first_name }}
                                {{$record->verifiedBy?->payrollStaff?->last_name }}
                            </td>
                            <td align="center" width="20%">
                                {{ $record->firstSignature?->payrollStaff?->first_name }}
                                {{ $record->firstSignature?->payrollStaff?->last_name }}
                            </td>
                            <td align="center" width="20%">
                                {{ $record->secondSignature?->payrollStaff?->first_name }}
                                {{$record->secondSignature?->payrollStaff?->last_name }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </th>
        </tr>
    </tbody>
</table>

<table>
    <tbody>
        <tr>
            <th width="100%" style="text-align: center">
                <h5><strong>ACUSE DE RECIBIDO</strong></h5>
            </th>
        </tr>
        <br>
        <tr>
            <td>
                <span style="display: table-cell; width: 100px;"><b>Nombre y Apellido:</b></span>
                <span>__________________________________</span>
            </td>
        </tr>
        <br>
        <tr>
            <td>
                <span style="display: table-cell; width: 100px;"><b>Nº de Cédula de identidad:</b></span>
                <span>____________________________</span>
            </td>
        </tr>
        <br>
        <tr>
            <td>
                <span style="display: table-cell; width: 100px;"><b>En representación de:</b></span>
                <span>________________________________</span>
            </td>
        </tr>
        <br>
        <tr>
            <td>
                <span style="display: table-cell; width: 100px;"><b>Fecha y Hora de recepción:</b></span>
                <span>___________________________</span>
            </td>
        </tr>
    </tbody>
</table>
