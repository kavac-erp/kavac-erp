@php
    $internal_code_furniture = [];
    $internal_code_vehicle = [];
    $internal_code_livestock = [];
    $internal_code_property = [];
    foreach ($request['assets'] as $fields) {
        if ($fields['asset']['asset_details']['code']) {
            if (
                str_contains(strtolower($fields['asset']['asset_type']['name']), 'mueble') &&
                !str_contains(strtolower($fields['asset']['asset_category']['name']), 'transporte') &&
                !str_contains(strtolower($fields['asset']['asset_category']['name']), 'semoviente') &&
                !str_contains(strtolower($fields['asset']['asset_type']['name']), 'inmueble')
            ) {
                if (!array_key_exists($fields['asset']['asset_details']['code'], $internal_code_furniture)) {
                    $internal_code_furniture[$fields['asset']['asset_details']['code']] = $fields;
                }
            } elseif (
                str_contains(strtolower($fields['asset']['asset_type']['name']), 'mueble') &&
                str_contains(strtolower($fields['asset']['asset_category']['name']), 'transporte') &&
                !str_contains(strtolower($fields['asset']['asset_category']['name']), 'semoviente') &&
                !str_contains(strtolower($fields['asset']['asset_type']['name']), 'inmueble')
            ) {
                if (!array_key_exists($fields['asset']['asset_details']['code'], $internal_code_vehicle)) {
                    $internal_code_vehicle[$fields['asset']['asset_details']['code']] = $fields;
                }
            } elseif (
                str_contains(strtolower($fields['asset']['asset_type']['name']), 'mueble') &&
                !str_contains(strtolower($fields['asset']['asset_category']['name']), 'transporte') &&
                str_contains(strtolower($fields['asset']['asset_category']['name']), 'semoviente') &&
                !str_contains(strtolower($fields['asset']['asset_type']['name']), 'inmueble')
            ) {
                if (!array_key_exists($fields['asset']['asset_details']['code'], $internal_code_livestock)) {
                    $internal_code_livestock[$fields['asset']['asset_details']['code']] = $fields;
                }
            } else {
                if (!array_key_exists($fields['asset']['asset_details']['code'], $internal_code_property)) {
                    $internal_code_property[$fields['asset']['asset_details']['code']] = $fields;
                }
            }
        }
    }

    $total_depreciation_furniture = 0;
    $total_book_furniture = 0;
    $total_depreciation_vehicle = 0;
    $total_book_vehicle = 0;
    foreach ($request['assets'] as $fields) {
        if (
            str_contains(strtolower($fields['asset']['asset_type']['name']), 'mueble') &&
            !str_contains(strtolower($fields['asset']['asset_category']['name']), 'transporte') &&
            !str_contains(strtolower($fields['asset']['asset_category']['name']), 'semoviente') &&
            !str_contains(strtolower($fields['asset']['asset_type']['name']), 'inmueble')
        ) {
            $total_depreciation_furniture += $fields['asset']['asset_depreciation_asset'][0]['amount'] ?? 0;
            $total_book_furniture += end($fields['asset']['asset_book'])['amount'] ?? 0;
        } elseif (
            str_contains(strtolower($fields['asset']['asset_type']['name']), 'mueble') &&
            str_contains(strtolower($fields['asset']['asset_category']['name']), 'transporte') &&
            !str_contains(strtolower($fields['asset']['asset_category']['name']), 'semoviente') &&
            !str_contains(strtolower($fields['asset']['asset_type']['name']), 'inmueble')
        ) {
            $total_depreciation_vehicle += $fields['asset']['asset_depreciation_asset'][0]['amount'] ?? 0;
            $total_book_vehicle += end($fields['asset']['asset_book'])['amount'] ?? 0;
        }
    }

    $furniture = false;
    $vehicle = false;
    $lifestock = false;
    $property = false;
    foreach ($request['assets'] as $fields) {
        if (
            str_contains(strtolower($fields['asset']['asset_type']['name']), 'mueble') &&
            !str_contains(strtolower($fields['asset']['asset_category']['name']), 'transporte') &&
            !str_contains(strtolower($fields['asset']['asset_category']['name']), 'semoviente') &&
            !str_contains(strtolower($fields['asset']['asset_type']['name']), 'inmueble')
        ) {
            $furniture = true;
        } elseif (
            str_contains(strtolower($fields['asset']['asset_type']['name']), 'mueble') &&
            str_contains(strtolower($fields['asset']['asset_category']['name']), 'transporte') &&
            !str_contains(strtolower($fields['asset']['asset_category']['name']), 'semoviente') &&
            !str_contains(strtolower($fields['asset']['asset_type']['name']), 'inmueble')
        ) {
            $vehicle = true;
        } elseif (
            str_contains(strtolower($fields['asset']['asset_type']['name']), 'mueble') &&
            !str_contains(strtolower($fields['asset']['asset_category']['name']), 'transporte') &&
            str_contains(strtolower($fields['asset']['asset_category']['name']), 'semoviente') &&
            !str_contains(strtolower($fields['asset']['asset_type']['name']), 'inmueble')
        ) {
            $lifestock = true;
        } else {
            $property = true;
        }
    }
    $item_furniture = 1;
    $item_vehicle = 1;
    $item_livestock = 1;
    $item_property = 1;
    $styles = 'font-weight: bold;';
@endphp
@if ($furniture == true)
    <h3 align="center">Bienes Muebles Desincorporados</h3>
    <br><br>
    <table cellspacing="0" cellpadding="4" style="font-size: 7rem;" align="center">
        <tr>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Item</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Código interno del bien</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Descripción</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Marca</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Modelo</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Serial</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Ubicación del bien</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Fecha de adquisición</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">N° de factura</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Costo de adquisición</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Depreciación Acumulada</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Valor según libros y/o contables
            </th>
        </tr>
        @foreach ($internal_code_furniture as $fields)
            <tr align="C">
                <td style="border: solid 1px #808080;">
                    {{ $item_furniture }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['asset_details']['code'] ? $fields['asset']['asset_details']['code'] : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['asset_specific_category']['name']
                        ? $fields['asset']['asset_specific_category']['name']
                        : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['asset_details']['brand'] ? $fields['asset']['asset_details']['brand'] : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['asset_details']['model'] ? $fields['asset']['asset_details']['model'] : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['asset_details']['serial'] ? $fields['asset']['asset_details']['serial'] : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['asset_asignation_asset']
                        ? $fields['asset']['asset_asignation_asset']['asset_asignation']['location_place']
                        : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['acquisition_date'] ? $fields['asset']['acquisition_date'] : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['document_num'] ? $fields['asset']['document_num'] : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['acquisition_value'] ? $fields['asset']['acquisition_value'] : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['asset_depreciation_asset']
                        ? currency_format($fields['asset']['asset_depreciation_asset'][0]['amount'])
                        : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['asset_book'] ? currency_format(end($fields['asset']['asset_book'])['amount']) : 'N/P' }}
                </td>
            </tr>
            @php
                $item_furniture += 1;
            @endphp
        @endforeach
        <tr>
            <th width="83.35%" style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">
                Total {{ $request['assets'][0]['asset']['currency']['symbol'] }}
            </th>
            <th style="border: solid 1px #808080; {{ $styles }}">
                {{ currency_format($total_depreciation_furniture) }}</th>
            <th style="border: solid 1px #808080; {{ $styles }}"> {{ currency_format($total_book_furniture) }}
            </th>
        </tr>
    </table>
@endif
@if ($vehicle == true)
    <br>
    <br>
    <h3 align="center">Bienes Vehículos Desincorporados</h3>
    <br><br>
    <table cellspacing="0" cellpadding="4" style="font-size: 7rem;" align="center">
        <tr>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Item</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Código interno del bien</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Descripción</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Marca</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Modelo</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Seriales</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Placa</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Ubicación del bien</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Fecha de adquisición</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">N° de factura</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Costo de adquisición</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Depreciación Acumulada</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Valor según libros y/o contables
            </th>
        </tr>
        @foreach ($internal_code_vehicle as $fields)
            <tr align="C">
                <td style="border: solid 1px #808080;">
                    {{ $item_vehicle }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['asset_details']['code'] ? $fields['asset']['asset_details']['code'] : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['asset_specific_category']['name']
                        ? $fields['asset']['asset_specific_category']['name']
                        : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['asset_details']['brand'] ? $fields['asset']['asset_details']['brand'] : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['asset_details']['model'] ? $fields['asset']['asset_details']['model'] : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    <b>Serial de carrocería: </b>
                    {{ $fields['asset']['asset_details']['bodywork_number']
                        ? $fields['asset']['asset_details']['bodywork_number']
                        : 'N/P' }}
                    <b>Serial del motor: </b>
                    {{ $fields['asset']['asset_details']['engine_number']
                        ? $fields['asset']['asset_details']['engine_number']
                        : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['asset_details']['license_plate']
                        ? $fields['asset']['asset_details']['license_plate']
                        : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['asset_asignation_asset']
                        ? $fields['asset']['asset_asignation_asset']['asset_asignation']['location_place']
                        : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['acquisition_date'] ? $fields['asset']['acquisition_date'] : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['document_num'] ? $fields['asset']['document_num'] : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['acquisition_value'] ? $fields['asset']['acquisition_value'] : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['asset_depreciation_asset']
                        ? currency_format($fields['asset']['asset_depreciation_asset'][0]['amount'])
                        : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['asset_book'] ? currency_format(end($fields['asset']['asset_book'])['amount']) : 'N/P' }}
                </td>
            </tr>
            @php
                $item_vehicle += 1;
            @endphp
        @endforeach
        <tr>
            <th width="84.63%" style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">
                Total {{ $request['assets'][0]['asset']['currency']['symbol'] }}
            </th>
            <th style="border: solid 1px #808080; {{ $styles }}">
                {{ currency_format($total_depreciation_vehicle) }}</th>
            <th style="border: solid 1px #808080; {{ $styles }}"> {{ currency_format($total_book_vehicle) }}
            </th>
        </tr>
    </table>
@endif
@if ($lifestock == true)
    <br>
    <br>
    <h3 align="center">Bienes Semovientes Desincorporados</h3>
    <br><br>
    <table cellspacing="0" cellpadding="4" style="font-size: 7rem;" align="center">
        <tr>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Item</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Código interno del bien</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Descripción</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Raza</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Tipo</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Peso</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Nro de hierro</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Propósito</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Fecha de nacimiento</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Ubicación del bien</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Fecha de adquisición</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">N° de factura</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Costo de adquisición</th>
        </tr>
        @foreach ($internal_code_livestock as $fields)
            <tr align="C">
                <td style="border: solid 1px #808080;">
                    {{ $item_livestock }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['asset_details']['code'] ? $fields['asset']['asset_details']['code'] : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['asset_specific_category']['name']
                        ? $fields['asset']['asset_specific_category']['name']
                        : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['asset_details']['race'] ? $fields['asset']['asset_details']['race'] : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['type'] ? $fields['asset']['type'] : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['asset_details']['weight'] ? $fields['asset']['asset_details']['weight'] : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['asset_details']['iron_number'] ? $fields['asset']['asset_details']['iron_number'] : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['purpose'] ? $fields['asset']['purpose'] : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['asset_details']['date_of_birth']
                        ? $fields['asset']['asset_details']['date_of_birth']
                        : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['asset_asignation_asset']
                        ? $fields['asset']['asset_asignation_asset']['asset_asignation']['location_place']
                        : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['acquisition_date'] ? $fields['asset']['acquisition_date'] : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['document_num'] ? $fields['asset']['document_num'] : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['acquisition_value'] ? $fields['asset']['acquisition_value'] : 'N/P' }}
                </td>
            </tr>
            @php
                $item_livestock += 1;
            @endphp
        @endforeach
    </table>
@endif
@if ($property == true)
    <br>
    <br>
    <h3 align="center">Bienes Inmuebles Desincorporados</h3>
    <br><br>
    <table cellspacing="0" cellpadding="4" style="font-size: 7rem;" align="center">
        <tr>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Item</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Código interno del bien</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Descripción</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Año de construcción</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Fecha de adquisición</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Área de construcción</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Área del terreno</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Nro de contrato del inmueble</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">RIF del comodatario</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Ubicación del bien</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">N° de factura</th>
            <th style="border: solid 1px #000; font-weight: bold;" bgcolor="#D3D3D3">Costo de adquisición</th>
        </tr>
        @foreach ($internal_code_property as $fields)
            <tr align="C">
                <td style="border: solid 1px #808080;">
                    {{ $item_property }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['asset_details']['code'] ? $fields['asset']['asset_details']['code'] : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['asset_specific_category']['name']
                        ? $fields['asset']['asset_specific_category']['name']
                        : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['asset_details']['construction_year']
                        ? $fields['asset']['asset_details']['construction_year']
                        : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['acquisition_date'] ? $fields['asset']['acquisition_date'] : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['asset_details']['construction_area']
                        ? $fields['asset']['asset_details']['construction_area']
                        : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['asset_details']['land_area'] ? $fields['asset']['asset_details']['land_area'] : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['asset_details']['contract_number']
                        ? $fields['asset']['asset_details']['contract_number']
                        : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['asset_details']['rif'] ? $fields['asset']['asset_details']['rif'] : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['asset_asignation_asset']
                        ? $fields['asset']['asset_asignation_asset']['asset_asignation']['location_place']
                        : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['document_num'] ? $fields['asset']['document_num'] : 'N/P' }}
                </td>
                <td style="border: solid 1px #808080;">
                    {{ $fields['asset']['acquisition_value'] ? $fields['asset']['acquisition_value'] : 'N/P' }}
                </td>
            </tr>
            @php
                $item_property += 1;
            @endphp
        @endforeach
    </table>
@endif
<div>
    <table style="font-size: 8rem; margin-top: 100px" cellpadding="3">
        <tr>
            <td>
                &nbsp;
            </td>
        </tr>
        <tr>
            <td>
                &nbsp;
            </td>
        </tr>
        <tr>
            <td align="center" width="30%" style="{{ $styles }}">
                Área de Bienes Públicos
            </td>
            <td align="center" width="106%" style="{{ $styles }}">
                Área de Contabilidad
            </td>
        </tr>
        <tr>
            <td>
                &nbsp;
            </td>
        </tr>
        <tr>
            <td>
                &nbsp;
            </td>
        </tr>
        <tr>
            <td align="center" width="30%" style="border-top: solid 1px #000; {{ $styles }}">
                {{ 'Firma y Sello' }}
            </td>
            <td width="38%">
                &nbsp;
            </td>
            <td align="center" width="30%" style="border-top: solid 1px #000; {{ $styles }}">
                {{ 'Firma y Sello' }}
            </td>
            <td width="10%">
                &nbsp;
            </td>
        </tr>
    </table>
</div>
