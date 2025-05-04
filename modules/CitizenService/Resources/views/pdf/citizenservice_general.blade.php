<h2 style="font-size: 13rem;" align="center">Información de Solicitudes 
</h2>
<h2>
    <br>
</h2>


<h4 style="font-size: 10rem;">Código del ente: {{ $institution['onapre_code'] }}</h4>
<h4 style="font-size: 10rem;">Denominación del ente: {{ $institution['name'] }}</h4>
<h4 style="font-size: 10rem;">Año Fiscal: {{ $fiscal_year }}</h4>
<br>

<table cellspacing="0" cellpadding="1" border="0">
    <tbody> 
        @foreach ($field as $data)
            <tr>
                <th width="100%"  style="text-align: left;">
                    <h4><strong>Datos del solicitante</strong></h4>
                </th>
            </tr>
            <hr class="one"/>
            <br>
            <tr>
                <td width="50%">
                    <strong>Fecha de solicitud:</strong> {{ $data->date }}
                </td>
                <td width="50%">
                    <strong>Nombre del solicitante:</strong> {{  $data->first_name
                            ? $data->first_name . ' ' . $data->last_name
                            : 'No definido' }}
                </td>
            </tr>
            <tr>
                <td width="50%">
                    <strong>Cédula de identidad:</strong> {{ $data->id_number }} 
                </td>
                <td width="50%">
                    <strong>Correo electrónico:</strong> {{ $data->email }}  
                </td>
            </tr>
            <tr>
                <td width="50%">
                    <strong>Fecha de nacimiento:</strong> {{ $data->birth_date }}
                </td>
                <td width="50%">
                    <strong>Número de contacto:</strong> 
                        @foreach ($data->phones as $phone)
                            {{ $phone->area_code . ' ' . $phone->number }}
                        @endforeach
                </td>
            </tr>
            <tr>
                <th width="100%"  style="text-align: left;">
                    <h4><strong>Ubicación de la solicitud</strong></h4>
                </th>
            </tr>
            <hr class="one"/>
            <br>
            <tr>
                <td width="50%">
                    <strong>País:</strong> {{ $data->parish->municipality->estate->country->name }} 
                </td>
                <td width="50%">
                    <strong>Estado:</strong> {{ $data->parish->municipality->estate->name }}  
                </td>
            </tr>
            <tr>
                <td width="50%">
                    <strong>Municipio:</strong> {{ $data->parish->municipality->name }} 
                </td>
                <td width="50%">
                    <strong>Parroquia:</strong> {{ $data->parish->name }}  
                </td>
            </tr>
            <tr>
                <td width="100%">
                    <strong>Dirección:</strong> {{ $data->address }} 
                </td>
            </tr>
            <tr>
                <th width="100%"  style="text-align: left;">
                    <h4><strong>Datos de la solicitud</strong></h4>
                </th>
            </tr>
            <hr class="one"/>
            <br>
            <tr>
                <td width="100%">
                    <strong>Motivo de la solicitud:</strong> {{ $data->motive_request }} 
                </td>   
            </tr>
            <tr>
                <td width="50%">
                    <strong>Tipo de solicitud:</strong> {{ $data->citizenServiceRequestType->name }}  
                </td>
                <td width="50%">
                    <strong>Departamento:</strong> {{ $data->citizenServiceDepartment->name }}  
                </td>
            </tr>
            @if (isset($data->institution_name))
                <tr>
                    <th width="100%"  style="text-align: left;">
                        <h4><strong>Datos de la institución</strong></h4>
                    </th>
                </tr>
                <hr class="one"/>
                <br>
                <tr>
                    <td width="50%">
                        <strong>Nombre de la institución:</strong> {{ $data->institution_name }}  
                    </td>
                    <td width="50%">
                        <strong>RIF:</strong> {{ $data->rif }}  
                    </td>
                </tr>
                <tr>
                    <td width="100%">
                        <strong>Dirección de la institución:</strong> {{ $data->institution_address }} 
                    </td>   
                </tr>
            @endif
            <tr>
                <th width="100%"  style="text-align: left;">
                    <h4><strong>Indicadores</strong></h4>
                </th>
            </tr>
            <hr class="one"/>
            <tr>
                <td width="20%">
                    <ul>
                        @if(isset($data->citizenServiceIndicator) && count($data->citizenServiceIndicator) > 0)
                            @foreach($data->citizenServiceIndicator->toArray() as $indicator)
                                    <li>{{ $indicator['indicator']['name'] }}</li>
                            @endforeach
                        @endif
                    </ul>
                </td>
                <td width="80">
                    <ul>
                        @if(isset($data->citizenServiceIndicator) && count($data->citizenServiceIndicator) > 0)
                            @foreach($data->citizenServiceIndicator->toArray() as $indicator)
                                    <li>{{ $indicator['name'] }}</li>
                            @endforeach
                        @endif
                    </ul>
                </td>
            </tr>
            <tr class="page_break">
                <td></td>
            </tr>
            <br>
        @endforeach
    </tbody>
</table>

<style>
    .page_break {
      page-break-before: always;
    }
</style>