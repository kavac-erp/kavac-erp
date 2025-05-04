<h2 align="center" style="font-size: 8rem;">Acta de {{$request['action']}} de Bienes </h2>

<table style="font-size: 8rem;"  cellpadding="6" >
    <tr>
        <td colspan="3">&nbsp;</td>
    </tr>
    @if ($request['action'] == 'Asignación' || $request['action'] == 'Entrega')
        <tr>
            <td colspan="3">
                <strong>Fecha de Asignación: </strong>{{$request['created_at']}}
            </td>
        </tr>
    @endif
    @if ($request['action'] == 'Entrega')
        <tr>
            <td colspan="3">
                <strong>Fecha de Entrega: </strong>{{$request['delivered_at']}}
            </td>
        </tr>
    @endif
    @if ($request['action'] == 'Desincorporación')
        <tr>
            <td colspan="3">
                <strong>Fecha de Desincorporación: </strong>{{$request['disincorporation_date']}}
            </td>
        </tr>
    @endif

    <tr>
        <td colspan="3">&nbsp;</td>
    </tr>

    <tr>
        <td colspan="3">
            <strong>Organización: </strong>{{$request['institution']}}
        </td>

    </tr>

    <tr>
        <td colspan="3">
            <strong>Ubicación Geográfica/Física </strong>
        </td>
    </tr>
    <tr>
        <td>
            <strong>Estado: </strong>{{$request['estate']}}
        </td>
        <td>
            <strong>Municipio: </strong>{{$request['municipality']}}
        </td>
        <td>
            <strong>Dirección: </strong>{{$request['address']}}
        </td>
    </tr>
    <tr>
        <td colspan="3">
            <strong>Ejercicio Fiscal: </strong>{{$request['fiscal_year']}}
        </td>
    </tr>
    @if ($request['action'] == 'Asignación' || $request['action'] == 'Entrega')
        <tr>
            <td colspan="3">
                <strong>Responsable por uso</strong>
            </td>
        </tr>
        <tr>
            <td>
                <strong>Apellidos: </strong>{{$request['last_name']}}
            </td>
            <td>
                <strong>Nombres: </strong>{{$request['first_name']}}
            </td>
            <td>
                <strong>Cédula de identidad: </strong>{{$request['id_number']}}
            </td>
        </tr>
        <tr>
            <td>
                <strong>Departamento: </strong>{{$request['department']}}
            </td>
            <td>
                <strong>Cargo: </strong>{{$request['payroll_position']}}
            </td>
            <td>
                <strong>Lugar de Ubicación: </strong>{{$request['location_place']}}
            </td>
        </tr>
    @endif

    @if ($request['action'] == 'Desincorporación')
        <tr>
            <td colspan="3">
                <strong>Motivo de la Desincorporación: </strong>{{$request['disincorporation_motive']}}
            </td>
        </tr>
    @endif

    @if ($request['action'] == 'Entrega' || $request['action'] == 'Desincorporación')
        <tr>
            <td colspan="3">
                <strong>Observaciones: </strong>{{$request['observation']}}
            </td>
        </tr>
    @endif

    <tr>
        <td colspan="3">&nbsp;</td>
    </tr>

    <tr>
        <td colspan="3">
            @if ($request['action'] == 'Asignación')
                <strong>Bienes Asignados: </strong>
            @endif
            @if ($request['action'] == 'Entrega')
                <strong>Bienes Entregados: </strong>
            @endif
            @if ($request['action'] == 'Desincorporación')
                <strong>Bienes Desincorporados: </strong>
            @endif
        </td>
    </tr>

    @foreach($request['assets'] as $asset)
        @if (array_key_exists('serial', $asset['asset']['asset_details']))
            <table cellspacing="0" cellpadding="1" border="0.5" style="font-size: 8rem;">
                <thead>
                    <tr>
                        <th style="background-color: #BDBDBD;" align="center">Código Interno</th>
                        <th style="background-color: #BDBDBD;" align="center">Especificaciones</th>
                        <th style="background-color: #BDBDBD;" align="center">Condición Física</th>
                        <th style="background-color: #BDBDBD;" align="center">Serial</th>
                        <th style="background-color: #BDBDBD;" align="center">Marca</th>
                        <th style="background-color: #BDBDBD;" align="center">Modelo</th>
                        <th style="background-color: #BDBDBD;" align="center">Color</th>
                    </tr>
                </thead>
                <tbody>

                    <tr>
                        <td align="center">
                            {{ $asset['asset']['asset_details']['code'] ?
                                $asset['asset']['asset_details']['code'] :
                                '' }}
                        </td>
                        <td align="center">
                            {{ $asset['asset']['asset_specific_category']['name'] ?
                                $asset['asset']['asset_specific_category']['name'] :
                                '' }}
                        </td>
                        <td align="center">
                            {{ $asset['asset']['asset_condition']['name'] ?
                                $asset['asset']['asset_condition']['name'] :
                                '' }}
                        </td>
                        <td align="center">
                            {{ $asset['asset']['asset_details']['serial'] ?
                                $asset['asset']['asset_details']['serial'] :
                                '' }}
                        </td>
                        <td align="center">
                            {{ $asset['asset']['asset_details']['brand'] ?
                                $asset['asset']['asset_details']['brand'] :
                                '' }}
                        </td>
                        <td align="center">
                            {{ $asset['asset']['asset_details']['model'] ?
                                $asset['asset']['asset_details']['model'] :
                                '' }}
                        </td>
                        <td align="center">
                            {{ $asset['asset']['color'] ?
                                $asset['asset']['color'] :
                                '' }}
                        </td>
                    </tr>

                </tbody>
            </table>
        @endif
        @if (array_key_exists('construction_year', $asset['asset']['asset_details']))
            <table cellspacing="0" cellpadding="1" border="0.5" style="font-size: 8rem;">
                <thead>
                    <tr>
                        <th style="background-color: #BDBDBD;" align="center">Año de Construcción</th>
                        <th style="background-color: #BDBDBD;" align="center">Número de Contrato</th>
                        <th style="background-color: #BDBDBD;" align="center">Estatus de Ocupación</th>
                        <th style="background-color: #BDBDBD;" align="center">Área de Construcción</th>
                        <th style="background-color: #BDBDBD;" align="center">Fecha de Inicio de contrato
                                                                             - Fecha de Fin de contrato</th>
                        <th style="background-color: #BDBDBD;" align="center">Oficina de Registro</th>
                        <th style="background-color: #BDBDBD;" align="center">Fecha de Registro</th>
                        <th style="background-color: #BDBDBD;" align="center">Número de Registro</th>
                        <th style="background-color: #BDBDBD;" align="center">Tomo</th>
                        <th style="background-color: #BDBDBD;" align="center">Folio</th>
                        <th style="background-color: #BDBDBD;" align="center">Localización</th>
                        <th style="background-color: #BDBDBD;" align="center">Linderos</th>
                        <th style="background-color: #BDBDBD;" align="center">Cordenadas</th>
                        <th style="background-color: #BDBDBD;" align="center">Valor de Adquisición</th>
                    </tr>
                </thead>
                <tbody>

                    <tr>
                        <td align="center">
                            {{ $asset['asset']['asset_details']['construction_year'] ?
                                $asset['asset']['asset_details']['construction_year'] :
                                '' }}
                        </td>
                        <td align="center">
                            {{ $asset['asset']['asset_details']['contract_number'] ?
                                $asset['asset']['asset_details']['contract_number'] :
                                ''}}
                        </td>
                        <td align="center">
                            {{ $asset['asset']['occupancy_status'] ?
                                $asset['asset']['occupancy_status'] :
                                '' }}
                        </td>
                        <td align="center">
                            {{ $asset['asset']['asset_details']['construction_area'] ?
                                $asset['asset']['asset_details']['construction_area'] :
                                '' }}
                        </td>
                        <td align="center">
                            {{ $asset['asset']['asset_details']['contract_start_date'] &&
                                $asset['asset']['asset_details']['contract_end_date'] ?
                                $asset['asset']['asset_details']['contract_start_date'] . ' - ' .
                                $asset['asset']['asset_details']['contract_end_date'] :
                                '' }}
                        </td>
                        <td align="center">
                            {{ $asset['asset']['asset_details']['registry_office'] ?
                                $asset['asset']['asset_details']['registry_office'] :
                                '' }}
                        </td>
                        <td align="center">
                            {{ $asset['asset']['asset_details']['registration_date'] ?
                                $asset['asset']['asset_details']['registration_date'] :
                                '' }}
                        </td>
                        <td align="center">
                            {{ $asset['asset']['asset_details']['registration_number'] ?
                                $asset['asset']['asset_details']['registration_number'] :
                                '' }}
                        </td>
                        <td align="center">
                            {{ $asset['asset']['asset_details']['tome'] ?
                                $asset['asset']['asset_details']['tome'] :
                                '' }}
                        </td>
                        <td align="center">
                            {{ $asset['asset']['asset_details']['folio'] ?
                                $asset['asset']['asset_details']['folio'] :
                                '' }}
                        </td>
                        <td align="center">
                            {{ $asset['asset']['asset_details']['location'] ?
                                $asset['asset']['asset_details']['location'] :
                                '' }}
                        </td>
                        <td align="center">
                            {{ $asset['asset']['asset_details']['north_boundaries'] &&
                                $asset['asset']['asset_details']['south_boundaries'] &&
                                $asset['asset']['asset_details']['east_boundaries'] &&
                                $asset['asset']['asset_details']['west_boundaries'] ?
                                $asset['asset']['asset_details']['north_boundaries'] . ', ' .
                                $asset['asset']['asset_details']['south_boundaries'] . ', ' .
                                $asset['asset']['asset_details']['east_boundaries'] . ', ' .
                                $asset['asset']['asset_details']['west_boundaries'] :
                                '' }}
                        </td>
                        <td align="center">
                            {{ $asset['asset']['asset_details']['location_coordinates'] ?
                                $asset['asset']['asset_details']['location_coordinates'] :
                                '' }}
                        </td>
                        <td align="center">
                            {{ $asset['asset']['asset_details']['acquisition_value'] ?
                                $asset['asset']['asset_details']['acquisition_value'] :
                                '' }}
                        </td>
                    </tr>

                </tbody>
            </table>
        @endif
        @if (array_key_exists('license_plate', $asset['asset']['asset_details']))
            <table cellspacing="0" cellpadding="1" border="0.5" style="font-size: 8rem;">
                <thead>
                    <tr>
                        <th style="background-color: #BDBDBD;" align="center">Código Interno</th>
                        <th style="background-color: #BDBDBD;" align="center">Categoría Específica</th>
                        <th style="background-color: #BDBDBD;" align="center">Marca</th>
                        <th style="background-color: #BDBDBD;" align="center">Modelo</th>
                        <th style="background-color: #BDBDBD;" align="center">Color</th>
                        <th style="background-color: #BDBDBD;" align="center">Año de Fabricación</th>
                        <th style="background-color: #BDBDBD;" align="center">Serial de Carrocería</th>
                        <th style="background-color: #BDBDBD;" align="center">Serial del Motor</th>
                        <th style="background-color: #BDBDBD;" align="center">Placa</th>
                        <th style="background-color: #BDBDBD;" align="center">Valor de adquisición</th>
                    </tr>
                </thead>
                <tbody>

                    <tr>
                        <td align="center">
                            {{ $asset['asset']['asset_details']['code'] ?
                                $asset['asset']['asset_details']['code'] :
                                '' }}
                        </td>
                        <td align="center">
                            {{ $asset['asset']['asset_specific_category']['name'] ?
                                $asset['asset']['asset_specific_category']['name'] :
                                '' }}
                        </td>
                        <td align="center">
                            {{ $asset['asset']['asset_details']['brand'] ?
                                $asset['asset']['asset_details']['brand'] :
                                '' }}
                        </td>
                        <td align="center">
                            {{ $asset['asset']['asset_details']['model'] ?
                                $asset['asset']['asset_details']['model'] :
                                '' }}
                        </td>
                        <td align="center">
                            {{ $asset['asset']['color'] ?
                                $asset['asset']['color'] :
                                '' }}
                        </td>
                        <td align="center">
                            {{ $asset['asset']['asset_details']['manufacture_year'] ?
                                $asset['asset']['asset_details']['manufacture_year'] :
                                '' }}
                        </td>
                        <td align="center">
                        {{ $asset['asset']['asset_details']['bodywork_number'] ?
                                $asset['asset']['asset_details']['bodywork_number'] :
                                '' }}
                        </td>
                        <td align="center">
                        {{ $asset['asset']['asset_details']['engine_number'] ?
                                $asset['asset']['asset_details']['engine_number'] :
                                '' }}
                        </td>
                        <td align="center">
                        {{ $asset['asset']['asset_details']['license_plate'] ?
                                $asset['asset']['asset_details']['license_plate'] :
                                '' }}
                        </td>
                        <td align="center">
                        {{ $asset['asset']['asset_details']['acquisition_value'] ?
                                $asset['asset']['asset_details']['acquisition_value'] :
                                '' }}
                        </td>
                    </tr>

                </tbody>
            </table>
        @endif
        @if (array_key_exists('race', $asset['asset']['asset_details']))
            <table cellspacing="0" cellpadding="1" border="0.5" style="font-size: 8rem;">
                <thead>
                    <tr>
                        <th style="background-color: #BDBDBD;" align="center">Código Interno</th>
                        <th style="background-color: #BDBDBD;" align="center">Categoría Específica</th>
                        <th style="background-color: #BDBDBD;" align="center">Raza</th>
                        <th style="background-color: #BDBDBD;" align="center">Tipo</th>
                        <th style="background-color: #BDBDBD;" align="center">Propósito</th>
                        <th style="background-color: #BDBDBD;" align="center">Peso</th>
                        <th style="background-color: #BDBDBD;" align="center">Unidad de Medida</th>
                        <th style="background-color: #BDBDBD;" align="center">Fecha de Nacimiento</th>
                        <th style="background-color: #BDBDBD;" align="center">Género</th>
                        <th style="background-color: #BDBDBD;" align="center">Número de Hierro</th>
                        <th style="background-color: #BDBDBD;" align="center">Valor de Adquisición</th>
                    </tr>
                </thead>
                <tbody>

                    <tr>
                        <td align="center">
                            {{ $asset['asset']['asset_details']['code'] ?
                                $asset['asset']['asset_details']['code'] :
                                '' }}
                        </td>
                        <td align="center">
                            {{ $asset['asset']['asset_specific_category']['name']}}
                        </td>
                        <td align="center">
                            {{ $asset['asset']['asset_details']['race'] ?
                                $asset['asset']['asset_details']['race'] :
                                '' }}
                        </td>
                        <td align="center">
                            {{ $asset['asset']['type'] ?
                                $asset['asset']['type'] :
                                '' }}
                        </td>
                        <td align="center">
                            {{ $asset['asset']['purpose'] ?
                                $asset['asset']['purpose'] :
                                '' }}
                        </td>
                        <td align="center">
                            {{ $asset['asset']['asset_details']['weight'] ?
                                $asset['asset']['asset_details']['weight'] :
                                '' }}
                        </td>
                        <td align="center">
                            {{ $asset['asset']['measurement_unit'] ?
                                $asset['asset']['measurement_unit'] :
                                '' }}
                        </td>
                        <td align="center">
                            {{ $asset['asset']['asset_details']['date_of_birth'] ?
                                $asset['asset']['asset_details']['date_of_birth'] :
                                '' }}
                        </td>
                        <td align="center">
                            {{ $asset['asset']['gender'] ?
                                $asset['asset']['gender'] :
                                '' }}
                        </td>
                        <td align="center">
                            {{ $asset['asset']['asset_details']['iron_number'] ?
                                $asset['asset']['asset_details']['iron_number'] :
                                '' }}
                        </td>
                        <td align="center">
                            {{ $asset['asset']['asset_details']['acquisition_value'] ?
                                $asset['asset']['asset_details']['acquisition_value'] :
                                '' }}
                        </td>
                    </tr>

                </tbody>
            </table>
        @endif
        <table>
            <tr>
                <td colspan="3">
                    <strong></strong>
                </td>
            </tr>
        </table>
    @endforeach

    <tr>
        <td colspan="3">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="3">&nbsp;</td>
    </tr>

    @if ($request['action'] == 'Asignación' || $request['action'] == 'Desincorporación')
        <tr>
            <td colspan="4">
                <strong>Autorizado por:    </strong>{{$request['authorized_by']}}
            </td>
        </tr>
        <tr>
            <td colspan="4">
                <strong>Conformado por:    </strong>{{$request['formed_by']}}
            </td>
        </tr>
    @endif
    @if ($request['action'] == 'Asignación')
        <tr>
            <td colspan="4">
                <strong>Entregado por:    </strong>{{$request['delivered_by']}}
            </td>
        </tr>
    @endif
    @if ($request['action'] == 'Desincorporación')
        <tr>
            <td colspan="4">
                <strong>Elaborado por:    </strong>{{$request['produced_by']}}
            </td>
        </tr>
    @endif
    @if ($request['action'] == 'Entrega')
        <tr>
            <td colspan="4">
                <strong>Aprobado por:    </strong>{{$request['approved_by']}}
            </td>
        </tr>
        <tr>
            <td colspan="4">
                <strong>Recibido por:    </strong>{{$request['received_by']}}
            </td>
        </tr>
    @endif


</table>
