@php
    $startDate = explode('-', $record['start_date']);
    $endDate = explode('-', $record['end_date']);
    $months = [
        ['id' => '01', 'name' => 'Enero'],
        ['id' => '02', 'name' => 'Febrero'],
        ['id' => '03', 'name' => 'Marzo'],
        ['id' => '04', 'name' => 'Abril'],
        ['id' => '05', 'name' => 'Mayo'],
        ['id' => '06', 'name' => 'Junio'],
        ['id' => '07', 'name' => 'Julio'],
        ['id' => '08', 'name' => 'Agosto'],
        ['id' => '09', 'name' => 'Septiembre'],
        ['id' => '10', 'name' => 'Octubre'],
        ['id' => '11', 'name' => 'Noviembre'],
        ['id' => '12', 'name' => 'Diciembre'],
    ];
    $accumulatedMonthsRemunerations = 0;
@endphp
<table width="100%">
    <tr>
        <td width="80%">
            <table width="100%">
                <tr>
                    <td colspan="12">
                        <span style="text-transform: uppercase;"><strong>Beneficiario de las
                                remuneraciones</strong></span>
                    </td>
                </tr>
                <tr>
                    <td colspan="12">
                        <table width="100%" style="border-collapse: collapse;">
                            <thead>
                                <tr>
                                    <th colspan="2"></th>
                                </tr>
                                <tr>
                                    <th colspan="2">
                                        <span style="text-transform: uppercase;">Apellidos y Nombres:
                                        </span>&nbsp;&nbsp;
                                        <strong
                                            style="font-size: x-large; text-transform: uppercase;">{{ $record['payroll_staff']['name'] }}</strong>

                                    </th>
                                </tr>
                                <tr>
                                    <th colspan="2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th width="40%">
                                        <strong style="text-transform: uppercase;">Numero de RIF:</strong>&nbsp;&nbsp;
                                        <span
                                            style="text-transform: uppercase;">{{ $record['payroll_staff']['rif'] }}</span>
                                    </th>
                                    <th>
                                        <strong style="text-transform: uppercase;">Cédula de Identidad
                                            N°:</strong>&nbsp;&nbsp;
                                        <span
                                            style="text-transform: uppercase;">{{ $record['payroll_staff']['id_number'] }}</span>
                                    </th>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
        <td width="20%">
            <table width="100%">
                <thead>
                    <tr>
                        <th>
                            <strong style="text-transform: uppercase;">Periodo</strong>
                        </th>
                        <th></th>
                    </tr>
                    <tr>
                        <th></th>
                    </tr>
                </thead>
            </table>
            <table width="100%" style="border-collapse: collapse;">
                <tr>
                    <th>
                        <span style="text-transform: uppercase;">Desde:</span>&nbsp;&nbsp;
                        <span style="text-transform: uppercase;">{{ $record['start_date'] }}</span>
                    </th>
                </tr>
                <tr>
                    <th></th>
                </tr>
                <tr>
                    <th>
                        <span style="text-transform: uppercase;">Hasta:</span>&nbsp;&nbsp;
                        <span style="text-transform: uppercase;">{{ $record['end_date'] }}</span>
                    </th>
                </tr>
            </table>
        </td>
    </tr>
</table>

<br>
<div style="border-bottom-width: 1;"></div>
<br>

<table width="100%">
    <tr>
        <th style="text-align: center;" colspan="3">
            <span style="text-transform: uppercase; font-size: larger"><strong>Tipo de Agente de
                    Retención</strong></span>
        </th>
    </tr>
    <tr>
        <th colspan="3"></th>
    </tr>
    <tr>
        <th width="70%">
            <strong style="text-transform: uppercase;">Dependencia oficial, organismos y otros:</strong>
            <div class="row" style="margin: 1px 0">
                <span class="col-md-12" style="text-transform: uppercase; font-weight: normal;">
                    {{ $record['institution']['name'] }}
                </span>
            </div>
        </th>
        <th width="30%" style="text-align: right">
            <strong style="text-transform: uppercase;">Numero de RIF:</strong>&nbsp;&nbsp;
            <span style="text-transform: uppercase;">{{ $record['institution']['rif'] }}</span>
        </th>
    </tr>
    <tr>
        <th colspan="3"></th>
    </tr>
    <tr>
        <th width="15%" colspan="2">
            <strong style="text-transform: uppercase;">Dirección:</strong>
        </th>
        <th width="82.5%">
            <span>{{ $record['retention_agent']['address'] }}</span>
            <span>{{ $record['retention_agent']['phone'] }}</span>
            <span>{{ $record['retention_agent']['po_box'] }}</span>
            <span>{{ $record['retention_agent']['postal_code'] }}</span>
            <span>{{ $record['retention_agent']['city'] }}</span>
            <span>{{ $record['retention_agent']['estate'] }}</span>
        </th>
    </tr>
</table>

<br>
<div style="border-bottom-width: 1;"></div>
<br>
<table width="100%" style="border-collapse: collapse;">
    <tr>
        <th width="2.5%"></th>
        <th width="95%">
            <table border="1" width="100%" style="border-collapse: collapse;">
                <thead>
                    <tr style="color: white; background-color: #53a0e4;">
                        <th style="text-align: center;" height="20px" width="13%">
                            <br>
                            <br>
                            <strong style="text-transform: uppercase;">Mes</strong>
                        </th>
                        <th style="text-align: center;" height="20px" width="21%">
                            <br>
                            <br>
                            <strong style="text-transform: uppercase;">Remuneraciones Pagadas En Cuenta</strong>
                        </th>
                        <th style="text-align: center;" height="20px" width="15%">
                            <br>
                            <br>
                            <strong style="text-transform: uppercase;">Porcentaje de Retención</strong>
                        </th>
                        <th style="text-align: center;" height="20px" width="15%">
                            <br>
                            <br>
                            <strong style="text-transform: uppercase;">Impuesto Retenido</strong>
                        </th>
                        <th style="text-align: center;" height="20px" width="21%">
                            <br>
                            <br>
                            <strong style="text-transform: uppercase;">Remuneraciones Pagadas Acumuladas</strong>
                            <br>
                        </th>
                        <th style="text-align: center;" height="20px" width="15%">
                            <br>
                            <br>
                            <strong style="text-transform: uppercase;">Impuesto Retenido Acumulado</strong>
                            <br>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($months as $key => $month)
                        @php
                            $color = $key % 2 == 0 ? '#DAD8D8' : '';
                            $accumulatedMonthsRemunerations += isset($record['months_remunerations'][$month['id']])
                                ? $record['months_remunerations'][$month['id']]
                                : 0;
                        @endphp
                        <tr style="{!! 'background-color: ' . $color !!}">
                            <td style="padding: 0.5rem; text-align: center;" width="13%">
                                <strong>{{ $month['name'] }}</strong>
                            </td>
                            <td style="text-align: right;" width="21%">
                                <span>{{ $record['symbol'] . ' ' . round(isset($record['months_remunerations'][$month['id']]) ? $record['months_remunerations'][$month['id']] : 0, 2) }}&nbsp;&nbsp;</span>
                            </td>
                            <td style="text-align: right;" width="15%">
                                <span>0,00&nbsp;&nbsp;</span>
                            </td>
                            <td style="text-align: right;" width="15%">
                                <span>0,00&nbsp;&nbsp;</span>
                            </td>
                            <td style="text-align: right;" width="21%">
                                <span>{{ $record['symbol'] . ' ' . round($accumulatedMonthsRemunerations, 2) }}&nbsp;&nbsp;</span>
                            </td>
                            <td style="text-align: right;" width="15%">
                                <span>0,00&nbsp;&nbsp;</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </th>
        <th width="2.5%"></th>
    </tr>
</table>
<!--<table border="1" width="100%" style="border-collapse: collapse;">
    <thead>
        <tr>
            <th style="text-align: center;" colspan="2">
                <strong style="text-transform: uppercase;">Retenido Por:</strong>
            </th>
        </tr>
    </thead>
    <tbody>
        @foreach (['I.V.S.S.', 'F.A.O.V.', 'R.P.E.', 'Cja. Aho.', 'Fondo Jub.', 'Otros'] as $agenteR)
<tr style="font-size: small">
            <td style="text-align: center;" width="40%">
                <strong style="text-transform: uppercase;">{{ $agenteR }}</strong>
            </td>
            <td style="text-align: right;" width="60%"><span>0,00&nbsp;&nbsp;</span></td>
        </tr>
@endforeach
    </tbody>
</table>-->
<div style="border-bottom-width: 1;"></div>

<table width="100%">
    <tr>
        <th></th>
    </tr>
    <tr style="font-size: x-large">
        <th width="60%">
            <strong style="text-transform: uppercase;">Total Remuneraciones Pagadas:</strong>
        </th>
        <th width="40%">
            <strong
                style="text-transform: uppercase;"><span>{{ round($accumulatedMonthsRemunerations, 2) . ' ' . $record['symbol'] }}</span></strong>
        </th>
    </tr>
</table>

<tr>
    <th></th>
</tr>
<tr>
    <th></th>
</tr>
<tr>
    <th></th>
</tr>
<tr>
    <th></th>
</tr>

<table width="100%" style="border-collapse: collapse;">
    <tr>
        <th width="25%"></th>
        <th width="50%">
            <table style="border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="border-bottom-width: 1;"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="text-align: center;">
                            <span>{{ $record['arc_responsible']['name'] }}</span>
                        </td>
                    </tr>
                    @if ($record['arc_responsible']['rif'])
                        <tr>
                            <td style="text-align: center;">
                                <span>{{ $record['arc_responsible']['rif'] }}</span>
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td style="text-align: center;">
                            <strong style="text-transform: uppercase;">Responsable de ARC</strong>
                        </td>
                    </tr>
                </tbody>
            </table>
        </th>
        <th width="25%"></th>
    </tr>
</table>
