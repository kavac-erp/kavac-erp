<style>
    table {
        line-height: 20px;
    }
</style>
<table>
    <tbody>
        <tr>
            <td width="65%">
                <strong>Descripción de contratación:</strong>
                {{ $record->description }}
            </td>
            <td width="35%">
                <strong>Nro. Contratación:</strong>
                <span style="color:red;font-size: 12px;font-weight: bold;">{{ $record->hiring_number }}</span>
            </td>
        </tr>
        <tr>
            <td width="65%">
                <strong>Unidad Contratante:</strong>
                {{ $record->contratingDepartment->name }}
            </td>
            <td width="35%">
                <strong>Fecha de la Orden:</strong>
                {{ date("d/m/Y", strtotime($record->date)) }}
            </td>
        </tr>
        <tr>
            <td width="100%">
                <strong>Unidad Usuaria:</strong>
                {{ $record->userDepartment->name }}
            </td>
        </tr>
    </tbody>
</table>
<hr style="border-top: .5px dotted #000;">
<table>
    <thead>
        <tr>
            <td width="100%" style="text-align: center;">
                <strong style="font-size: 12px;">DATOS DEL PROVEEDOR</strong>
            </td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="65%">
                <strong>Nombre o Razón social:</strong>
                {{ $record->purchaseSupplier->name }}
            </td>
            <td width="35%">
                <strong>RIF:</strong>
                {{ $record->purchaseSupplier->rif }}
            </td>
        </tr>
        <tr>
            <td width="100%" style="display: inline">
                <strong>Dirección fiscal:</strong>
                {{strip_tags($record->purchaseSupplier->direction) }}
            </td>
        </tr>
        <tr>
            <td width="100%">
                <strong>Plazo de entrega:</strong> {{ $record->due_date }} {{ $record->time_frame}}, de no cumplir con los plazos de
                entrega esta orden será anulada
            </td>
        </tr>
        <tr>
            <td width="30%" style="text-align: center;">
                <strong>Forma de pago:</strong>
            </td>
            <td width="30%" style="text-align: center;">
                <strong>Número de Expediente:</strong>
            </td>
            <td width="40%" style="text-align: center;">
                <strong>Número de Certificado (RNC):</strong>
            </td>
        </tr>
        <tr>
            <td width="30%" style="text-align: center;">
                {{-- FORMA DE PAGO --}}
                {{
                    ($record->payment_methods == "pay_order") ? ("Orden de Pago")
                    : ( ($record->payment_methods == "direct") ? ("Directa")
                    : ( ($record->payment_methods == "credit") ? ("Credito")
                    : ( ($record->payment_methods == "advance") ? ("Avance") : ("Otros"))))
                }}
            </td>
            <td width="30%" style="text-align: center;">
                {{-- NUMERO INTERNO / NUMERO DE EXPEDIENTE DEL PROVEEDOR --}}
                {{ $record?->purchaseSupplier->file_number ?? 'NO REGISTRADO' }}
            </td>
            <td width="40%" style="text-align: center;">
                {{--  NUMERO DE CERTIFICADO RNC --}}
                @if ($record?->purchaseSupplier)
                    @if ($record->purchaseSupplier->rnc_status)
                        <span>{{ $record->purchaseSupplier->rnc_status }} - </span>
                    @endif
                    {{ $record->purchaseSupplier->rnc_certificate_number ?? "NO REGISTRADO" }}
                @else
                    <span>NO REGISTRADO</span>
                @endif
            </td>
        </tr>
        <tr>
            <td width="100%">
                <strong>Lugar de entrega:</strong>
                {{ strip_tags($record->institution->legal_address) }}
            </td>
        </tr>
    </tbody>
</table>
<hr style="border-top: .5px dotted #000;">
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
<table>
    <thead>
        <tr><td></td></tr>
        <tr>
            <td width="100%" style="text-align: center;">
                <strong style="font-size: 12px;">LISTA DE PRODUCTOS</strong>
            </td>
        </tr>
        <tr><td></td></tr>
    </thead>
</table>

<table cellspacing="0" cellpadding="3" border="0.1" style="font-size: 6px;">
    <thead>
        <tr style="background-color: #f0f0f0;">
            <td width="15%" align="center"><b>Código de requerimiento</b></td>
            <td width="20%" align="center"><b>Nombre</b></td>
            <th width="20%" align="center">
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
                        {{ number_format($items->unit_price, $record?->currency?->decimal_places ?? 2, ',', '.') }}
                    </td>
                    <td width="15%" align="right">
                        {{
                            number_format(
                                (
                                    $items->quantity > 0
                                    ? $items->quantity
                                    : $items->purchaseRequirementItem->quantity
                                ) * $items->unit_price,
                                $record?->currency?->decimal_places ?? 2,
                                ',', '.'
                            )
                        }}
                    </td>
                    <td width="10%" align="right">
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
    <tfoot>
        {{-- Filas de bases imponibles --}}
        @foreach ($bases_imponibles as $percentage => $base)
            <tr>
                <td width="75%" align="right">
                    Base imponible según alícuota {{ $percentage }}%
                </td>
                <td width="25%" align="right">
                    {{ number_format($base, $record?->currency?->decimal_places ?? 2, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td width="75%" align="right">
                    Monto total del impuesto según alícuota {{ $percentage }}%
                </td>
                <td width="25%" align="right">
                    {{ number_format($base * $percentage / 100, $record?->currency?->decimal_places ?? 2, ',', '.') }}
                </td>
            </tr>
        @endforeach
        <tr style="background-color: #f0f0f0;font-size:8rem;">
            <td width="75%" align="right">
                <strong>
                    TOTAL {{ $record?->currency?->symbol ?? '' }}
                </strong>
            </td>
            <td width="25%" align="right">
                <strong>
                    {{ number_format($total, $record->currency->decimal_places, ',', '.') }}
                </strong>
            </td>
        </tr>
    </tfoot>
</table>
<div style="page-break-after: always;"></div>
<h5 style="text-align: center">CUENTAS PRESUPUESTARIAS DE GASTOS</h5>

<table style="border:solid 1px #000;font-size: 0.85em;background-color:#adbfd3; padding:10px;">
    <thead>
        <tr>
            <th width="33%" align="center">
                <strong>Cuenta</strong>
            </th>
            <th width="33%" align="center">
                <strong>Nombre</strong>
            </th>
            <th width="33%" align="center">
                <strong>Monto</strong>
            </th>
        </tr>
    </thead>

    @if (isset($record->base_budget) && count($record->base_budget) > 0)
        @foreach($record->base_budget as $x)
            @if ($x->relatable->availabilityitem && count($x->relatable->availabilityitem) > 0)
                @foreach($x->relatable->availabilityitem as $availability)
                    <tr>
                        <td align="center">
                            {{ $availability['item_code'] }}
                        </td>
                        <td align="left">
                            {{ $availability['item_name'] }}
                        </td>
                        <td align="center">
                            {{ $availability['amount'] }}
                        </td>
                    </tr>
                @endforeach
            @endif
        @endforeach
    @endif
</table>

<br>
&#160;
<br>
<h5 style="text-align: center">FIRMAS AUTORIZADAS</h5>

<table style="border:solid 1px #000;font-size: 0.85em;padding:10px;" cellpadding="5px">
    <tbody>
        <tr>
            <td style="border-bottom: solid 1px #000;border-right: solid 1px #000;" align="center" width="20%"><b>ELABORADO POR:</b></td>
            <td style="border-bottom: solid 1px #000;border-right: solid 1px #000;" align="center" width="20%"><b>REVISADO POR:</b></td>
            <td style="border-bottom: solid 1px #000;border-right: solid 1px #000;" align="center" width="20%"><b>VERIFICADO POR</b></td>
            <td style="border-bottom: solid 1px #000;border-right: solid 1px #000;" align="center" width="20%"><b>AUTORIZADO POR:</b></td>
            <td style="border-bottom: solid 1px #000;" align="center" width="20%"><b>AUTORIZADO POR:</b></td>
        </tr>
        <tr>
            <td style="border-bottom: solid 1px #000;border-right: solid 1px #000;"></td>
            <td style="border-bottom: solid 1px #000;border-right: solid 1px #000;"></td>
            <td style="border-bottom: solid 1px #000;border-right: solid 1px #000;"></td>
            <td style="border-bottom: solid 1px #000;border-right: solid 1px #000;"></td>
            <td style="border-bottom: solid 1px #000;"></td>
        </tr>
        <tr>
            <td style="border-right: solid 1px #000;" align="center" width="20%">
                {{ $record->preparedBy->payrollStaff->first_name }}
                {{ $record->preparedBy->payrollStaff->last_name }}
            </td>
            <td style="border-right: solid 1px #000;" align="center" width="20%">
                {{ $record->reviewedBy?->payrollStaff?->first_name }}
                {{$record->reviewedBy?->payrollStaff?->last_name }}
            </td>
            <td style="border-right: solid 1px #000;" align="center" width="20%">
                {{ $record->verifiedBy?->payrollStaff?->first_name }}
                {{$record->verifiedBy?->payrollStaff?->last_name }}
            </td>
            <td style="border-right: solid 1px #000;" align="center" width="20%">
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

<br>
&#160;
<br>
<h5 style="text-align: center">ACUSE DE RECIBIDO</h5>

<table>
    <tbody>
        <tr>
            <td width="60%">
                <span style="display: table-cell; width: 100px;"><b>Nombre y Apellido:</b></span>
                <span>__________________________________</span>
            </td>
        </tr>
        <tr>
            <td width="60%">
                <span style="display: table-cell; width: 100px;"><b>Nº de Cédula de identidad:</b></span>
                <span>____________________________</span>
            </td>
            <td width="40%" style="border-bottom: solid 1px #000;"></td>
        </tr>
        <tr>
            <td width="60%">
                <span style="display: table-cell; width: 100px;"><b>En representación de:</b></span>
                <span>________________________________</span>
            </td>
            <td width="40%" align="center">Firma</td>
        </tr>
        <tr>
            <td width="60%">
                <span style="display: table-cell; width: 100px;"><b>Fecha y Hora de recepción:</b></span>
                <span>___________________________</span>
            </td>
        </tr>
    </tbody>
</table>
<div style="page-break-after: always;"></div>
<h3 style="text-align: center; color:red;font-weight: normal">
    CONDICIONES GENERALES<br>{{ str_replace('</h4>', '', str_replace('<h4>', '', $orderTitle))  }}
</h3>
@if ($generalCondition && $generalCondition->p_value)
    <div style="text-align: justify">
        {!! $generalCondition->p_value !!}
    </div>
@endif
