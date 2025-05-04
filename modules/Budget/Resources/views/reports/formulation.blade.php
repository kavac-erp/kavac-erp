<table
    width="100%"
    cellpadding="4"
    style="font-size: 8px;"
>
    <tbody>
        <tr>
            <td width="20%" style="font-weight: bold;">Fecha de generación:</td>
            <td width="80%">{{ $formulation->date ? date("d/m/Y", strtotime($formulation->date)) : 'Sin fecha asignada' }}</td>
        </tr>
        <tr>
            <td width="20%" style="font-weight: bold;">Presupuesto asignado:</td>
            <td width="80%">{{ ($formulation->assigned || $formulation->assigned==='1')?'Sí':'No' }}</td>
        </tr>
        <tr>
            <td width="20%" style="font-weight: bold;">Institución:</td>
            <td width="80%">{{ $formulation->specificAction->institution }}</td>
        </tr>
        <tr>
            <td width="20%" style="font-weight: bold;">Moneda:</td>
            <td width="80%">{{ $formulation->currency->description }}</td>
        </tr>
        <tr>
            <td width="20%" style="font-weight: bold;">Presupuesto:</td>
            <td width="80%">{{ $formulation->year }}</td>
        </tr>
        <tr>
            <td width="20%" style="font-weight: bold;">{{ $formulation->specificAction->type }}:</td>
            <td width="80%">{{ $formulation->specificAction->specificable->code }} -
                {{ $formulation->specificAction->specificable->name }}
            </td>
        </tr>
        <tr>
            <td width="20%" style="font-weight: bold;">Acción Específica:</td>
            <td width="80%">{{ $formulation->specificAction->code }} - {!! $formulation->specificAction->name !!}</td>
        </tr>
        <tr>
            <td width="20%" style="font-weight: bold;">Fuente de Financiamiento:</td>
            <td width="80%">{{ $formulation->budgetFinancementType->name ? $formulation->budgetFinancementType->name : "Sin dato" }}</td>
        </tr>
        <tr>
            <td width="20%" style="font-weight: bold;">Tipo de Financiamiento:</td>
            <td width="80%">{{ $formulation->budgetFinancementSource->name ? $formulation->budgetFinancementSource->name : "Sin dato" }}</td>
        </tr>
        <tr>
            <td width="20%" style="font-weight: bold;">Monto del Financiamiento:</td>
            <td width="80%">{{ $formulation->currency->symbol }}&#160; {{ number_format($formulation->financement_amount, $formulation->currency->decimal_places, ",", ".") }}</td>
        </tr>
        <tr>
            <td width="20%" style="font-weight: bold;">Total Formulado:</td>
            <td width="80%">{{ $formulation->currency->symbol }}&#160;{{ number_format($formulation->total_formulated, $formulation->currency->decimal_places, ",", ".") }}</td>
        </tr>
    </tbody>
</table>
<div style="margin-bottom: 10px"></div>

<table
    border="1px"
    width="100%"
    cellpadding="4"
    style="font-size: 8px;"
>
    <thead>
        <tr align="center" style="font-weight: bold;">
            <td width="15%">{{ __('Código') }}</td>
            <td width="65%">
                {{ __('Denominación') }}
            </td>
            <td width="20%">{{ __('Total Año') }}</td>
        </tr>
    </thead>
    <tbody>
        @foreach ($formulation->accountOpens as $accountOpen)
            @php
                $finalBorder = '';
                $style = ($accountOpen->budgetAccount->specific==="00") ? 'font-weight:bold;' : '';
            @endphp
            @if ($loop->last)
                @php
                    $finalBorder = "border-bottom: solid 1px #000;";
                @endphp
            @endif
            <tr>
                <td align="center" width="15%">{{ $accountOpen->budgetAccount->code }}</td>
                <td width="65%">
                    {{ $accountOpen->budgetAccount->denomination }}
                </td>
                <td width="20%" align="right">
                    {{ number_format(
                        $accountOpen->total_year_amount,
                        $formulation->currency->decimal_places, ",", "."
                    ) }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
