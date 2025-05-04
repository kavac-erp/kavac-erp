<table width="100%" cellpadding="4" style="font-size: 8rem">
    <tbody>
        <tr>
            <td width="25%" style="font-weight: bold;">Denominación del ente:</td>
            <td width="75%">{{ $institution->name }}</td>
        </tr>
        <tr>
            <td width="25%" style="font-weight: bold;">RIF:</td>
            <td width="75%">{{ $institution->rif }}</td>
        </tr>
        <tr>
            <td width="25%" style="font-weight: bold;">Nombres y apellidos del trabajador:</td>
            <td width="75%">{{ ucwords($data["payroll_staff"]["first_name"] . " " . $data["payroll_staff"]["last_name"])}}</td>
        </tr>
        <tr>
            <td width="25%" style="font-weight: bold;">Cédula:</td>
            <td width="75%">{{ $data["payroll_staff"]["id_number"] }}</td>
        </tr>
        <tr>
            <td width="25%" style="font-weight: bold;">Cargo:</td>
            <td width="75%">{{ isset($data["payroll_staff"]["payroll_employment"]["payrollPosition"]) ? ucwords($data["payroll_staff"]["payroll_employment"]["payrollPosition"]["name"]) : "" }}</td>
        </tr>
        <tr>
            <td width="25%" style="font-weight: bold;">Fecha de ingreso:</td>
            <td width="75%">{{ date('d-m-Y', strtotime($data["payroll_staff"]["payroll_employment"]["start_date"])) }}</td>
        </tr>
        <tr>
            <td>
                &nbsp;
            </td>
        </tr>
    </tbody>
</table>
<p>
    He recibido de la {{ $institution->name }}, la cantidad de: {{ $total_ }}
    BOLIVARES por concepto de cancelación correspondiente al periodo {{ $period }}, según se especifica a continuación.
</p>
@if ($has_params)
    <table width="100%" cellspacing="0" cellpadding="1" border="1" style="font-size: 9rem; font-weight: bold;">
        <tr>
            <th width="25%" style="border: solid 1px #000;" bgcolor="#D3D3D3" align="center">Conceptos</th>
            <th width="25%" style="border: solid 1px #000;" bgcolor="#D3D3D3" align="center">Parámetros</th>
            <th width="25%" style="border: solid 1px #000;" bgcolor="#D3D3D3" align="center">Cantidad</th>
            <th width="25%" style="border: solid 1px #000;" bgcolor="#D3D3D3" align="center">{{'Montos ' . '(' . $currency["symbol"] . ')'}}</th>
        </tr>
    </table>
    @php
        $total = 0.0;
        $assignations_total = 0.0;
        $deductions_total = 0.0;
    @endphp
    <table width="100%" cellspacing="0" cellpadding="4" border="0" style="font-size: 8rem;">
        @foreach ($data['concept_type'] as $concept_name => $concept_type_arr)
            @php
                $current_total = 0.0;
            @endphp
            @if(isset($concept_type_arr[0]) && ($concept_type_arr[0]["sign"] == '+'))
                <tr style="font-weight: bold;">
                    <th>
                        {{ $concept_name }}
                    </th>
                </tr>
                @foreach ($concept_type_arr as $concept_type)
                    @if (doubleval($concept_type['value']) > 0)
                        @php
                            if($concept_type["sign"] == '+') {
                                $assignations_total += $function($concept_type["value"], $decimals);
                            } else {
                                $deductions_total += $function($concept_type["value"], $decimals);
                            }
                            $current_total += $function($concept_type["value"], $decimals);
                            $total = $function($assignations_total - $deductions_total, $decimals);
                        @endphp
                        <tr>
                            <th width="25%" align="left">
                                {{ $concept_type["name"] }}
                            </th>
                            <td width="30%" align="center">
                                <ul style="list-style-type: none">
                                @if (isset($concept_type["parameters"]))
                                    @foreach ($concept_type["parameters"] as $parameter)
                                        @foreach ($parameter as $param)
                                            <li>
                                                {{ $param["name"] }}
                                            </li>
                                        @endforeach
                                    @endforeach
                                @endif
                                </ul>    
                            </td>
                            <td width="20%" align="left">
                                <ul style="list-style-type: none">
                                @if (isset($concept_type["parameters"]))
                                    @foreach ($concept_type["parameters"] as $parameter)
                                        @foreach ($parameter as $param)
                                            <li>
                                                {{ $param["value"] }}
                                            </li>
                                        @endforeach
                                    @endforeach
                                @endif
                                </ul>    
                            </td>
                            <td width="25%" align="center">
                                {{ $function($concept_type["value"], $decimals) . ' ' . $currency["symbol"] }}
                            </td>
                        </tr>
                    @endif
                @endforeach
                <tr>
                    <th width="25%" style="font-weight: bold;">
                        {{ 'Total ' . $concept_name }}
                    </th>

                    <td width="25%" align="center">
                    </td>

                    <td width="25%" align="center" style="font-weight: bold;">
                    </td>

                    <td width="25%" align="center" style="font-weight: bold;">
                        {{ $current_total . ' ' . $currency["symbol"] }}
                    </td>
                </tr>
            @endif
        @endforeach
        @foreach ($data['concept_type'] as $concept_name => $concept_type_arr)
            @php
                $current_total = 0.0;
            @endphp
            @if(isset($concept_type_arr[0]) && ($concept_type_arr[0]["sign"] == '-'))
                <tr style="font-weight: bold;">
                    <th>
                        {{ $concept_name }}
                    </th>
                </tr>
                @foreach ($concept_type_arr as $concept_type)
                    @if (doubleval($concept_type['value']) > 0)
                        @php
                            if($concept_type["sign"] == '+') {
                                $assignations_total += $function($concept_type["value"], $decimals);
                            } else {
                                $deductions_total += $function($concept_type["value"], $decimals);
                            }
                            $current_total += $function($concept_type["value"], $decimals);
                            $total = $function($assignations_total - $deductions_total, $decimals);
                        @endphp
                        <tr>
                            <th width="25%" align="left">
                                {{ $concept_type["name"] }}
                            </th>
                            <td width="30%" align="center">
                                <ul style="list-style-type: none">
                                @if (isset($concept_type["parameters"]))
                                    @foreach ($concept_type["parameters"] as $parameter)
                                        @foreach ($parameter as $param)
                                            <li>
                                                {{ $param["name"] }}
                                            </li>
                                        @endforeach
                                    @endforeach
                                @endif
                                </ul>    
                            </td>
                            <td width="20%" align="left">
                                <ul style="list-style-type: none">
                                @if (isset($concept_type["parameters"]))
                                    @foreach ($concept_type["parameters"] as $parameter)
                                        @foreach ($parameter as $param)
                                            <li>
                                                {{ $param["value"] }}
                                            </li>
                                        @endforeach
                                    @endforeach
                                @endif
                                </ul>    
                            </td>
                            <td width="25%" align="center">
                                {{ $function($concept_type["value"], $decimals) . ' ' . $currency["symbol"] }}
                            </td>
                        </tr>
                    @endif
                @endforeach
                <tr>
                    <th width="25%" style="font-weight: bold;">
                        {{ 'Total ' . $concept_name }}
                    </th>

                    <td width="25%" align="center">
                    </td>

                    <td width="25%" align="center" style="font-weight: bold;">
                    </td>

                    <td width="25%" align="center" style="font-weight: bold;">
                        {{ $current_total . ' ' . $currency["symbol"] }}
                    </td>
                </tr>
            @endif
        @endforeach
        <tr>
            <th width="25%" style="font-weight: bold;">
                Total a pagar
            </th>

            <td width="25%" align="center" style="font-weight: bold;">
            </td>

            <td width="25%" align="center" style="font-weight: bold;">
            </td>

            <td width="25%" align="center" style="font-weight: bold;">
                {{ $total_ . ' ' . $currency["symbol"] }}
            </td>
        </tr>
    </table>
@else
    <table width="100%" cellspacing="0" cellpadding="1" border="1" style="font-size: 9rem; font-weight: bold;">
        <tr>
            <th width="50%" style="border: solid 1px #000;" bgcolor="#D3D3D3" align="center">Conceptos</th>
            <th width="50%" style="border: solid 1px #000;" bgcolor="#D3D3D3" align="center">{{'Montos ' . '(' . $currency["symbol"] . ')'}}</th>
        </tr>
    </table>
    @php
        $total = 0.0;
        $assignations_total = 0.0;
        $deductions_total = 0.0;
    @endphp
    <table width="100%" cellspacing="0" cellpadding="4" border="0" style="font-size: 8rem;">
        @foreach ($data['concept_type'] as $concept_name => $concept_type_arr)
            @php
                $current_total = 0.0;
                $allowed_concept_names = ["asignaciones", "deducciones"]
            @endphp
            @if(in_array(strtolower($concept_name), $allowed_concept_names ))
                <tr style="font-weight: bold;">
                    <th>
                        {{ $concept_name }}
                    </th>
                </tr>
                @foreach ($concept_type_arr as $concept_type)
                    @php
                        if($concept_type["sign"] == '+') {
                            $assignations_total += $function($concept_type["value"], $decimals);
                        } else {
                            $deductions_total += $function($concept_type["value"], $decimals);
                        }
                        $current_total += $function($concept_type["value"], $decimals);
                        $total = $function($assignations_total - $deductions_total, $decimals);
                    @endphp
                    <tr>
                        <th width="50%" align="left">
                            {{ $concept_type["name"] }}
                        </th>
                        <td width="50%" align="center">
                            {{ $function($concept_type["value"], $decimals) . ' ' . $currency["symbol"] }}
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <th width="50%" style="font-weight: bold;">
                        {{ 'Total ' . $concept_name }}
                    </th>
                    <td width="50%" align="center" style="font-weight: bold;">
                        {{ $current_total . ' ' . $currency["symbol"] }}
                    </td>
                </tr>
            @endif
        @endforeach
        <tr>
            <th width="50%" style="font-weight: bold;">
                Total a pagar
            </th>
            <td width="50%" align="center" style="font-weight: bold;">
                {{ $total_ . ' ' . $currency["symbol"] }}
            </td>
        </tr>
    </table>
@endif