@if (array_key_exists('serial', $assets[0]['asset_details']))
    <table cellspacing="0" cellpadding="1" border="1">
        <tr>
            <th style="background-color: #BDBDBD;" width="9.09090909%" align="center" valign="middle">Código Interno del bien</th>
            <th style="background-color: #BDBDBD;" width="9.09090909%" align="center" valign="middle">Descripción</th>
            <th style="background-color: #BDBDBD;" width="9.09090909%" align="center" valign="middle">Marca</th>
            <th style="background-color: #BDBDBD;" width="9.09090909%" align="center" valign="middle">Modelo</th>
            <th style="background-color: #BDBDBD;" width="9.09090909%" align="center" valign="middle">Color</th>
            <th style="background-color: #BDBDBD;" width="9.09090909%" align="center" valign="middle">Serial</th>
            <th style="background-color: #BDBDBD;" width="9.09090909%" align="center" valign="middle">Estatus de uso</th>
            <th style="background-color: #BDBDBD;" width="9.09090909%" align="center" valign="middle">Dependencia</th>
            <th style="background-color: #BDBDBD;" width="9.09090909%" align="center" valign="middle">Unidad administrativa</th>
            <th style="background-color: #BDBDBD;" width="9.09090909%" align="center" valign="middle">Lugar de ubicación</th>
            <th style="background-color: #BDBDBD;" width="9.09090909%" align="center" valign="middle">Valor de adquisición</th>
        </tr>
        @foreach($assets as $fields)
            <tr align="C">
                <td width="9.09090909%" align="center" valign="middle">
                    {{
                        $fields->asset_details
                            ? $fields->asset_details['code']
                            : 'N/P'
                    }}
                </td>
                <td width="9.09090909%" align="center" valign="middle">
                    {{
                        $fields->assetSpecificCategory
                            ? $fields->assetSpecificCategory['name']
                            : 'N/P'
                    }}
                </td>
                <td width="9.09090909%" align="center" valign="middle">
                    {{
                        $fields->asset_details
                            ? $fields->asset_details['brand']
                            : 'N/P'
                    }}
                </td>
                <td width="9.09090909%" align="center" valign="middle">
                    {{
                        $fields->asset_details
                            ? $fields->asset_details['model']
                            : 'N/P'
                    }}
                </td>
                <td width="9.09090909%" align="center" valign="middle">
                    {{
                        $fields->color
                    }}
                </td>
                <td width="9.09090909%" align="center" valign="middle">
                    {{
                        $fields->asset_details
                            ? $fields->asset_details['serial']
                            : 'N/P'
                    }}
                </td>
                <td width="9.09090909%" align="center" valign="middle">
                    {{
                        $fields->assetStatus
                            ? $fields->assetStatus['name']
                            : 'N/P'
                    }}
                </td>
                <td width="9.09090909%" align="center" valign="middle">
                    {{
                        $fields->assetAsignationAsset
                            && $fields->assetAsignationAsset->assetAsignation
                                && $fields->assetAsignationAsset->assetAsignation->payrollStaff
                                    && $fields->assetAsignationAsset->assetAsignation->payrollStaff->payrollEmployment
                            ? $fields->assetAsignationAsset->assetAsignation->payrollStaff->payrollEmployment->department->name
                            : 'N/P'
                    }}
                </td>
                <td width="9.09090909%" align="center" valign="middle">
                    {{
                        $fields->department
                            ? $fields->department->name
                            : 'N/P'
                    }}
                </td>
                <td width="9.09090909%" align="center" valign="middle">
                    {{
                        $fields->assetAsignationAsset
                            ? $fields->assetAsignationAsset->assetAsignation->location_place
                            : 'N/P'
                    }}
                </td>
                <td width="9.09090909%" align="center" valign="middle">
                    {{
                        $fields->acquisition_value
                            ? $fields->acquisition_value
                            : 'N/P'
                    }}
                </td>
            </tr>
        @endforeach
    </table>
@endif
@if (array_key_exists('race', $assets[0]['asset_details']))
    <table cellspacing="0" cellpadding="1" border="1">
        <tr>
            <th style="background-color: #BDBDBD;" width="9.09090909%" align="center" valign="middle">Código interno del bien</th>
            <th style="background-color: #BDBDBD;" width="9.09090909%" align="center" valign="middle">Descripción</th>
            <th style="background-color: #BDBDBD;" width="9.09090909%" align="center" valign="middle">Raza</th>
            <th style="background-color: #BDBDBD;" width="9.09090909%" align="center" valign="middle">Tipo</th>
            <th style="background-color: #BDBDBD;" width="9.09090909%" align="center" valign="middle">Propósito</th>
            <th style="background-color: #BDBDBD;" width="9.09090909%" align="center" valign="middle">Peso</th>
            <th style="background-color: #BDBDBD;" width="9.09090909%" align="center" valign="middle">Unidad de medida</th>
            <th style="background-color: #BDBDBD;" width="9.09090909%" align="center" valign="middle">Fecha de nacimiento</th>
            <th style="background-color: #BDBDBD;" width="9.09090909%" align="center" valign="middle">Género</th>
            <th style="background-color: #BDBDBD;" width="9.09090909%" align="center" valign="middle">Número de hierro</th>
            <th style="background-color: #BDBDBD;" width="9.09090909%" align="center" valign="middle">Valor de adquisición</th>
        </tr>
        @foreach($assets as $fields)
            <tr align="C">
                <td width="9.09090909%" align="center" valign="middle">
                    {{
                        $fields->asset_details
                            ? $fields->asset_details['code']
                            : 'N/P'
                    }}
                    </td>
                <td width="9.09090909%" align="center" valign="middle">
                    {{
                        $fields->assetSpecificCategory
                            ? $fields->assetSpecificCategory['name']
                            : 'N/P'
                    }}
                </td>
                <td width="9.09090909%" align="center" valign="middle">
                    {{
                        $fields->asset_details
                            ? $fields->asset_details['race']
                            : 'N/P'
                    }}
                </td>
                <td width="9.09090909%" align="center" valign="middle">
                    {{
                        $fields->type
                            ? $fields->type
                            : 'N/P'
                    }}
                </td>
                <td width="9.09090909%" align="center" valign="middle">
                    {{
                        $fields->purpose
                            ? $fields->purpose
                            : 'N/P'
                    }}
                </td>
                <td width="9.09090909%" align="center" valign="middle">
                    {{
                        $fields->asset_details
                            ? $fields->asset_details['weight']
                            : 'N/P'
                    }}
                </td>
                <td width="9.09090909%" align="center" valign="middle">
                    {{
                        $fields->measurement_unit
                            ? $fields->measurement_unit
                            : 'N/P'
                    }}
                </td>
                <td width="9.09090909%" align="center" valign="middle">
                    {{
                        $fields->asset_details
                            ? $fields->asset_details['date_of_birth']
                            : 'N/P'
                    }}
                </td>
                <td width="9.09090909%" align="center" valign="middle">
                    {{
                        $fields->gender
                            ? $fields->gender
                            : 'N/P'
                    }}
                </td>
                <td width="9.09090909%" align="center" valign="middle">
                    {{
                        $fields->asset_details
                            ? $fields->asset_details['iron_number']
                            : 'N/P'
                    }}
                </td>
                <td width="9.09090909%" align="center" valign="middle">
                    {{
                        $fields->acquisition_value
                            ? $fields->acquisition_value
                            : 'N/P'
                    }}
                </td>
            </tr>
        @endforeach
    </table>
@endif
@if (array_key_exists('license_plate', $assets[0]['asset_details']))
    <table cellspacing="0" cellpadding="1" border="1">
        <tr>
            <th style="background-color: #BDBDBD;" width="10%" align="center" valign="middle">Código interno del bien</th>
            <th style="background-color: #BDBDBD;" width="10%" align="center" valign="middle">Descripción</th>
            <th style="background-color: #BDBDBD;" width="10%" align="center" valign="middle">Marca</th>
            <th style="background-color: #BDBDBD;" width="10%" align="center" valign="middle">Modelo</th>
            <th style="background-color: #BDBDBD;" width="10%" align="center" valign="middle">Color</th>
            <th style="background-color: #BDBDBD;" width="10%" align="center" valign="middle">Año de fabricación</th>
            <th style="background-color: #BDBDBD;" width="10%" align="center" valign="middle">Serial de carroceria</th>
            <th style="background-color: #BDBDBD;" width="10%" align="center" valign="middle">Serial del motor</th>
            <th style="background-color: #BDBDBD;" width="10%" align="center" valign="middle">Placa</th>
            <th style="background-color: #BDBDBD;" width="10%" align="center" valign="middle">Valor de adquisición</th>
        </tr>
        @foreach($assets as $fields)
            <tr align="C">
                <td width="10%" align="center" valign="middle">
                    {{
                        $fields->asset_details
                            ? $fields->asset_details['code']
                            : 'N/P'
                    }}
                </td>
                <td width="10%" align="center" valign="middle">
                    {{
                        $fields->assetSpecificCategory
                            ? $fields->assetSpecificCategory['name']
                            : 'N/P'
                    }}
                </td>
                <td width="10%" align="center" valign="middle">
                    {{
                        $fields->asset_details
                            ? $fields->asset_details['brand']
                            : 'N/P'
                    }}
                </td>
                <td width="10%" align="center" valign="middle">
                    {{
                        $fields->asset_details
                            ? $fields->asset_details['model']
                            : 'N/P'
                    }}
                </td>
                <td width="10%" align="center" valign="middle">
                    {{
                        $fields->color
                    }}
                </td>
                <td width="10%" align="center" valign="middle">
                    {{
                        $fields->asset_details
                            ? $fields->asset_details['manufacture_year']
                            : 'N/P'
                    }}
                </td>
                <td width="10%" align="center" valign="middle">
                    {{
                        $fields->asset_details
                            ? $fields->asset_details['bodywork_number']
                            : 'N/P'
                    }}
                </td>
                <td width="10%" align="center" valign="middle">
                    {{
                        $fields->asset_details
                            ? $fields->asset_details['engine_number']
                            : 'N/P'
                    }}
                </td>
                <td width="10%" align="center" valign="middle">
                    {{
                        $fields->asset_details
                            ? $fields->asset_details['license_plate']
                            : 'N/P'
                    }}
                </td>
                <td width="10%" align="center" valign="middle">
                    {{
                        $fields->acquisition_value
                            ? $fields->acquisition_value
                            : 'N/P'
                    }}
                </td>
            </tr>
        @endforeach
    </table>
@endif
@if (array_key_exists('construction_year', $assets[0]['asset_details']))
    @foreach($assets as $fields)
        <table>
            <tr align="C">
                <td style="background-color: #BDBDBD;" align="center" valign="middle">
                    <strong>Código interno: </strong>
                        {{
                            $fields->asset_details
                                ? $fields->asset_details['code']
                                : 'N/P'
                        }}
                </td>
            </tr>
        </table>
        <table>
            <tr>
                <td colspan="3">
                    <strong></strong>
                </td>
            </tr>
        </table>
        <table>
            <tr>
                <td colspan="1">
                    <strong>Descripción: </strong>
                        {{
                            $fields->assetSpecificCategory
                                ? $fields->assetSpecificCategory['name']
                                : 'N/P'
                        }}
                </td>
                <td colspan="1">
                    <strong>Uso actual: </strong>
                        {{
                            $fields->asset_use_function
                                ? $fields->asset_use_function
                                : 'N/P'
                        }}
                </td>
                <td colspan="1">
                    <strong>Estatus de ocupación: </strong>
                        {{
                            $fields->occupancy_status
                            ? $fields->occupancy_status
                            : 'N/P'
                        }}
                </td>
            </tr>
        </table>
        <table>
            <tr>
                <td colspan="3">
                    <strong></strong>
                </td>
            </tr>
        </table>
        <table>

            <tr>
                <td colspan="1">
                    <strong>Localización: </strong>
                        {{
                            $fields->asset_details
                            ? $fields->asset_details['location']
                            : 'N/P'
                        }}
                </td>
                <td colspan="1">
                    <strong>Dirección: </strong>
                        {{
                            $fields->asset_details
                                ? $fields->country . ', ' . $fields->estate . ', ' . $fields->municipality . ', ' .
                                    $fields->parish . ', ' . $fields->asset_details['urbanization_sector'] . ', ' .
                                    $fields->asset_details['avenue_street'] . ', ' . $fields->asset_details['house']
                                    . ', Piso: ' . $fields->asset_details['floor']
                                : 'N/P'
                        }}
                </td>
                <td colspan="1">
                    <strong>Valor de adquisición: </strong>
                        {{
                            $fields->acquisition_value
                                ? $fields->acquisition_value
                                : 'N/P'
                        }}
                </td>
            </tr>
        </table>
        <table cellspacing="0" cellpadding="1" border="1">
            <tr>
                <th style="background-color: #BDBDBD;" width="9,090909091%" align="center" valign="middle">Año de contrucción</th>
                <th style="background-color: #BDBDBD;" width="9,090909091%" align="center" valign="middle">Nro de contrato</th>
                <th style="background-color: #BDBDBD;" width="9,090909091%" align="center" valign="middle">Área de construcción</th>
                <th style="background-color: #BDBDBD;" width="9,090909091%" align="center" valign="middle">Fecha de inicio y fin de contrato</th>
                <th style="background-color: #BDBDBD;" width="9,090909091%" align="center" valign="middle">Oficina de registro</th>
                <th style="background-color: #BDBDBD;" width="9,090909091%" align="center" valign="middle">Fecha de registro</th>
                <th style="background-color: #BDBDBD;" width="9,090909091%" align="center" valign="middle">Nro de registro</th>
                <th style="background-color: #BDBDBD;" width="9,090909091%" align="center" valign="middle">Tomo</th>
                <th style="background-color: #BDBDBD;" width="9,090909091%" align="center" valign="middle">Folio</th>
                <th style="background-color: #BDBDBD;" width="9,090909091%" align="center" valign="middle">Linderos</th>
                <th style="background-color: #BDBDBD;" width="9,090909091%" align="center" valign="middle">Cordenadas</th>
            </tr>

            <tr align="C">
                <td width="9,090909091%" align="center" valign="middle">
                    {{
                        $fields->asset_details
                        ? $fields->asset_details['construction_year']
                        : 'N/P'
                    }}
                </td>
                <td width="9,090909091%" align="center" valign="middle">
                    {{
                        $fields->asset_details
                        ? $fields->asset_details['contract_number']
                        : 'N/P'
                    }}
                </td>
                <td width="9,090909091%" align="center" valign="middle">
                    {{
                        $fields->asset_details
                        ? $fields->asset_details['construction_area'] . ' ' . $fields->construction_measurement_unit_acronym
                        : 'N/P'
                    }}
                </td>
                <td width="9,090909091%" align="center" valign="middle">
                    {{
                        $fields->asset_details
                            ? $fields->asset_details['contract_start_date'] . ' - ' .
                                $fields->asset_details['contract_end_date']
                            : 'N/P'
                    }}
                </td>
                <td width="9,090909091%" align="center" valign="middle">
                    {{
                        $fields->asset_details
                        ? $fields->asset_details['registry_office']
                        : 'N/P'
                    }}
                </td>
                <td width="9,090909091%" align="center" valign="middle">
                    {{
                        $fields->asset_details
                        ? $fields->asset_details['registration_date']
                        : 'N/P'
                    }}
                </td>
                <td width="9,090909091%" align="center" valign="middle">
                    {{
                        $fields->asset_details
                        ? $fields->asset_details['registration_number']
                        : 'N/P'
                    }}
                </td>
                <td width="9,090909091%" align="center" valign="middle">
                    {{
                        $fields->asset_details
                        ? $fields->asset_details['tome']
                        : 'N/P'
                    }}
                </td>
                <td width="9,090909091%" align="center" valign="middle">
                    {{
                        $fields->asset_details
                        ? $fields->asset_details['folio']
                        : 'N/P'
                    }}
                </td>
                <td width="9,090909091%" align="center" valign="middle">
                    {{
                        $fields->asset_details
                        ? 'Norte:' . ' ' . $fields->asset_details['north_boundaries'] . ', ' .
                            'Sur:' . ' ' . $fields->asset_details['south_boundaries'] . ', ' .
                            'Este:' . ' ' . $fields->asset_details['east_boundaries'] . ', ' .
                            'Oste:' . ' ' . $fields->asset_details['west_boundaries']
                        : 'N/P'
                    }}
                </td>
                <td width="9,090909091%" align="center" valign="middle">
                    {{
                        $fields->asset_details
                        ? $fields->asset_details['location_coordinates']
                        : 'N/P'
                    }}
                </td>
            </tr>
        </table>
        <table>
            <tr>
                <td colspan="3">
                    <strong></strong>
                </td>
            </tr>
        </table>
    @endforeach
@endif