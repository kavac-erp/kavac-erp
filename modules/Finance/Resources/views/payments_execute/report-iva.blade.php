<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Comprobante de Retención</title>
    <style>
        table {
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid black;
            padding: 8px;
            /* Añadido para dar espacio entre el contenido y el borde */
            text-align: left;
            /* Ajusta según tus necesidades */
        }
    </style>
</head>

<body>
    @php
        $total = 0;
    @endphp
    <table>
        <thead>
            <tr>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <!-- ... otras columnas ... -->
            </tr>
        </thead>
        <tbody>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td> <!-- Agregando "Comprobante" en la celda E2 -->
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td> <!-- Texto en la celda G3 -->
                <!-- ... otras celdas ... -->
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td> <!-- Agregando "Comprobante" en la celda E2 -->
                <td></td>
                <td>Comprobante de Retención</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td> <!-- Texto en la celda G3 -->
                <!-- ... otras celdas ... -->
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td> <!-- Agregando "Comprobante" en la celda E2 -->
                <td></td>
                <td>(Providencia Nº SNTA / 2002 / 1.454)</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td> <!-- Texto en la celda G3 -->
                <!-- ... otras celdas ... -->
            </tr>
            <tr>
                <td></td><!-- celda A-->
                <td></td><!-- celda B-->
                <td></td><!-- celda C-->
                <td></td><!-- celda D-->
                <td></td> <!-- celda E-->
                <td>(Ley IVA-Art.11 "Serán responsables del pago del impuesto en calidad de agentes de")</td>
                <!-- celda F-->
                <td></td><!-- celda G-->
                <td></td><!-- celda H-->
                <td></td><!-- celda I-->
                <td></td><!-- celda J-->
                <td></td><!-- celda K-->
                <td></td><!-- celda L-->
                <td>Nº Comprobante</td><!-- celda M-->
                <td></td><!-- celda N-->
                <td></td> <!-- Texto en la celda O -->
                <td>Fecha</td> <!-- Texto en la celda P -->
                <!-- ... otras celdas ... -->
            </tr>
            <tr>
                <td></td><!-- celda A-->
                <td></td><!-- celda B-->
                <td></td><!-- celda C-->
                <td></td><!-- celda D-->
                <td></td> <!-- celda E-->
                <td>retención, los compradores o adquirientes de determinados bienes muebles y los </td><!-- celda F-->
                <td></td><!-- celda G-->
                <td></td><!-- celda H-->
                <td></td><!-- celda I-->
                <td></td><!-- celda J-->
                <td></td><!-- celda K-->
                <td></td><!-- celda L-->
                <td> </td><!-- celda M-->
                <td></td><!-- celda N-->
                <td></td> <!-- Texto en la celda O -->
                <td> </td> <!-- Texto en la celda P -->
                <!-- ... otras celdas ... -->
            </tr>
            <tr>
                <td></td><!-- celda A-->
                <td></td><!-- celda B-->
                <td></td><!-- celda C-->
                <td></td><!-- celda D-->
                <td></td> <!-- celda E-->
                <td>receptores de ciertos servicios, a quienes la Administración Tributaria designe como tal </td>
                <!-- celda F-->
                <td></td><!-- celda G-->
                <td></td><!-- celda H-->
                <td></td><!-- celda I-->
                <td></td><!-- celda J-->
                <td></td><!-- celda K-->
                <td></td><!-- celda L-->
                <td>{{ $deductions[0]['code'] }}</td><!-- celda M-->
                <td></td><!-- celda N-->
                <td></td> <!-- Texto en la celda O -->
                <td>{{ $dateShow }}</td> <!-- Texto en la celda P -->
                <!-- ... otras celdas ... -->
            </tr>
            <tr> <!-- ...celdas 8 ... -->
                <td></td><!-- celda A-->
                <td></td><!-- celda B-->
                <td></td><!-- celda C-->
                <td></td><!-- celda D-->
                <td></td> <!-- celda E-->
                <td></td><!-- celda F-->
                <td></td><!-- celda G-->
                <td></td><!-- celda H-->
                <td></td><!-- celda I-->
                <td></td><!-- celda J-->
                <td></td><!-- celda K-->
                <td></td><!-- celda L-->
                <td></td><!-- celda M-->
                <td></td><!-- celda N-->
                <td></td> <!-- Texto en la celda O -->
                <td></td> <!-- Texto en la celda P -->
                <!-- ... otras celdas ... -->
            </tr>
            <tr> <!-- ...celdas 9 ... -->
                <td></td><!-- celda A-->
                <td></td><!-- celda B-->
                <td></td><!-- celda C-->
                <td></td><!-- celda D-->
                <td>NOMBRE O RAZON SOCIAL DEL AGENTE DE RETENCION</td> <!-- celda E-->
                <td></td><!-- celda F-->
                <td></td><!-- celda G-->
                <td></td><!-- celda H-->
                <td></td><!-- celda I-->
                <td></td><!-- celda J-->
                <td>R.I.F AGENTE DE RETENCION</td><!-- celda K-->
                <td></td><!-- celda L-->
                <td></td><!-- celda M-->
                <td>PERIODO FISCAL</td><!-- celda N-->
                <td></td> <!-- Texto en la celda O -->
                <td></td> <!-- Texto en la celda P -->
                <!-- ... otras celdas ... -->
            </tr>
            <tr> <!-- ...celdas 10 ... -->
                <td></td><!-- celda A-->
                <td></td><!-- celda B-->
                <td></td><!-- celda C-->
                <td></td><!-- celda D-->
                <td>{{ $institution->name }}</td> <!-- celda E-->
                <td></td><!-- celda F-->
                <td></td><!-- celda G-->
                <td></td><!-- celda H-->
                <td></td><!-- celda I-->
                <td></td><!-- celda J-->
                <td>{{ $institution->rif }}</td><!-- celda K-->
                <td></td><!-- celda L-->
                <td></td><!-- celda M-->
                <td>AÑO: {{ $currentFiscalYear->year }}</td><!-- celda N-->
                <td></td> <!-- Texto en la celda O -->
                <td>MES: {{ $month }}</td> <!-- Texto en la celda P -->
                <!-- ... otras celdas ... -->
            </tr>
            <tr> <!-- ...celdas 11 ... -->
                <td></td><!-- celda A-->
                <td></td><!-- celda B-->
                <td></td><!-- celda C-->
                <td></td><!-- celda D-->
                <td></td> <!-- celda E-->
                <td></td><!-- celda F-->
                <td></td><!-- celda G-->
                <td></td><!-- celda H-->
                <td></td><!-- celda I-->
                <td></td><!-- celda J-->
                <td></td><!-- celda K-->
                <td></td><!-- celda L-->
                <td></td><!-- celda M-->
                <td></td><!-- celda N-->
                <td></td> <!-- Texto en la celda O -->
                <td></td> <!-- Texto en la celda P -->
                <!-- ... otras celdas ... -->
            </tr>
            <tr> <!-- ...celdas 12 ... -->
                <td></td><!-- celda A-->
                <td></td><!-- celda B-->
                <td></td><!-- celda C-->
                <td></td><!-- celda D-->
                <td></td> <!-- celda E-->
                <td></td><!-- celda F-->
                <td></td><!-- celda G-->
                <td></td><!-- celda H-->
                <td></td><!-- celda I-->
                <td></td><!-- celda J-->
                <td></td><!-- celda K-->
                <td></td><!-- celda L-->
                <td></td><!-- celda M-->
                <td></td><!-- celda N-->
                <td></td> <!-- Texto en la celda O -->
                <td></td> <!-- Texto en la celda P -->
                <!-- ... otras celdas ... -->
            </tr>
            <tr> <!-- ...celdas 13 ... -->
                <td></td><!-- celda A-->
                <td></td><!-- celda B-->
                <td></td><!-- celda C-->
                <td></td><!-- celda D-->
                <td>DIRECCION FISCAL DEL AGENTE DE RETENCION</td> <!-- celda E-->
                <td></td><!-- celda F-->
                <td></td><!-- celda G-->
                <td></td><!-- celda H-->
                <td></td><!-- celda I-->
                <td></td><!-- celda J-->
                <td></td><!-- celda K-->
                <td></td><!-- celda L-->
                <td></td><!-- celda M-->
                <td></td><!-- celda N-->
                <td></td> <!-- Texto en la celda O -->
                <td></td> <!-- Texto en la celda P -->
                <!-- ... otras celdas ... -->
            </tr>
            <tr> <!-- ...celdas 14 ... -->
                <td></td><!-- celda A-->
                <td></td><!-- celda B-->
                <td></td><!-- celda C-->
                <td></td><!-- celda D-->
                <td>{{ $institution->addressParse[0] }}</td> <!-- celda E-->
                <td></td><!-- celda F-->
                <td></td><!-- celda G-->
                <td></td><!-- celda H-->
                <td></td><!-- celda I-->
                <td></td><!-- celda J-->
                <td></td><!-- celda K-->
                <td></td><!-- celda L-->
                <td></td><!-- celda M-->
                <td></td><!-- celda N-->
                <td></td> <!-- Texto en la celda O -->
                <td></td> <!-- Texto en la celda P -->
                <!-- ... otras celdas ... -->
            </tr>
            <tr> <!-- ...celdas 15 ... -->
                <td></td><!-- celda A-->
                <td></td><!-- celda B-->
                <td></td><!-- celda C-->
                <td></td><!-- celda D-->
                <td>{{ $institution->addressParse[1] }} </td> <!-- celda E-->
                <td></td><!-- celda F-->
                <td></td><!-- celda G-->
                <td></td><!-- celda H-->
                <td></td><!-- celda I-->
                <td></td><!-- celda J-->
                <td></td><!-- celda K-->
                <td></td><!-- celda L-->
                <td></td><!-- celda M-->
                <td></td><!-- celda N-->
                <td></td> <!-- Texto en la celda O -->
                <td></td> <!-- Texto en la celda P -->
                <!-- ... otras celdas ... -->
            </tr>
            <tr> <!-- ...celdas 16 ... -->
                <td></td><!-- celda A-->
                <td></td><!-- celda B-->
                <td></td><!-- celda C-->
                <td></td><!-- celda D-->
                <td>{{ $institution->addressParse[2] }}</td> <!-- celda E-->
                <td></td><!-- celda F-->
                <td></td><!-- celda G-->
                <td></td><!-- celda H-->
                <td></td><!-- celda I-->
                <td></td><!-- celda J-->
                <td></td><!-- celda K-->
                <td></td><!-- celda L-->
                <td></td><!-- celda M-->
                <td></td><!-- celda N-->
                <td></td> <!-- Texto en la celda O -->
                <td></td> <!-- Texto en la celda P -->
                <!-- ... otras celdas ... -->
            </tr>
            <tr> <!-- ...celdas 17 ... -->
                <td></td><!-- celda A-->
                <td></td><!-- celda B-->
                <td></td><!-- celda C-->
                <td></td><!-- celda D-->
                <td></td> <!-- celda E-->
                <td></td><!-- celda F-->
                <td></td><!-- celda G-->
                <td></td><!-- celda H-->
                <td></td><!-- celda I-->
                <td></td><!-- celda J-->
                <td></td><!-- celda K-->
                <td></td><!-- celda L-->
                <td></td><!-- celda M-->
                <td></td><!-- celda N-->
                <td></td> <!-- Texto en la celda O -->
                <td></td> <!-- Texto en la celda P -->
            <tr> <!-- ...celdas 18 ... -->
                <td></td><!-- celda A-->
                <td></td><!-- celda B-->
                <td></td><!-- celda C-->
                <td></td><!-- celda D-->
                <td>NOMBRE O RAZON SOCIAL DEL SUJETO RETENIDO</td> <!-- celda E-->
                <td></td><!-- celda F-->
                <td></td><!-- celda G-->
                <td></td><!-- celda H-->
                <td></td><!-- celda I-->
                <td></td><!-- celda J-->
                <td>R.I.F DEL SUJETO RETENIDO</td><!-- celda K-->
                <td></td><!-- celda L-->
                <td></td><!-- celda M-->
                <td></td><!-- celda N-->
                <td></td> <!-- Texto en la celda O -->
                <td></td> <!-- Texto en la celda P -->
                <!-- ... otras celdas ... -->
            </tr>
            <tr> <!-- ...celdas 19 ... -->
                <td></td><!-- celda A-->
                <td></td><!-- celda B-->
                <td></td><!-- celda C-->
                <td></td><!-- celda D-->
                <td>{{ $finance->name_provider }}</td> <!-- celda E-->
                <td></td><!-- celda F-->
                <td></td><!-- celda G-->
                <td></td><!-- celda H-->
                <td></td><!-- celda I-->
                <td></td><!-- celda J-->
                <td>{{ $finance->rif_provider }}</td><!-- celda K-->
                <td></td><!-- celda L-->
                <td></td><!-- celda M-->
                <td></td><!-- celda N-->
                <td></td> <!-- Texto en la celda O -->
                <td></td> <!-- Texto en la celda P -->
                <!-- ... otras celdas ... -->
            </tr>
            <tr> <!-- ...celdas 20 ... -->
                <td></td><!-- celda A-->
                <td></td><!-- celda B-->
                <td></td><!-- celda C-->
                <td></td><!-- celda D-->
                <td></td> <!-- celda E-->
                <td></td><!-- celda F-->
                <td></td><!-- celda G-->
                <td></td><!-- celda H-->
                <td></td><!-- celda I-->
                <td></td><!-- celda J-->
                <td></td><!-- celda K-->
                <td></td><!-- celda L-->
                <td></td><!-- celda M-->
                <td></td><!-- celda N-->
                <td></td> <!-- Texto en la celda O -->
                <td></td> <!-- Texto en la celda P -->
                <!-- ... otras celdas ... -->
            </tr>
            <tr> <!-- ...celdas 21 ... -->
                <td></td><!-- celda A-->
                <td></td><!-- celda B-->
                <td></td><!-- celda C-->
                <td></td><!-- celda D-->
                <td></td> <!-- celda E-->
                <td></td><!-- celda F-->
                <td></td><!-- celda G-->
                <td></td><!-- celda H-->
                <td></td><!-- celda I-->
                <td></td><!-- celda J-->
                <td></td><!-- celda K-->
                <td>COMPRAS INTERNAS</td><!-- celda L-->
                <td></td><!-- celda M-->
                <td></td><!-- celda N-->
                <td></td> <!-- Texto en la celda O -->
                <td></td> <!-- Texto en la celda P -->
                <!-- ... otras celdas ... -->
            </tr>
            <tr> <!-- ...celdas 22 ... -->
                <td></td><!-- celda A-->
                <td></td><!-- celda B-->
                <td></td><!-- celda C-->
                <td></td><!-- celda D-->
                <td></td> <!-- celda E-->
                <td></td><!-- celda F-->
                <td></td><!-- celda G-->
                <td></td><!-- celda H-->
                <td></td><!-- celda I-->
                <td></td><!-- celda J-->
                <td></td><!-- celda K-->
                <td>o IMPORTACIONES</td><!-- celda L-->
                <td></td><!-- celda M-->
                <td></td><!-- celda N-->
                <td></td> <!-- Texto en la celda O -->
                <td></td> <!-- Texto en la celda P -->
                <!-- ... otras celdas ... -->
            </tr>
            <tr> <!-- ...celdas 23 ... -->
                <td></td><!-- celda A-->
                <td></td><!-- celda B-->
                <td></td><!-- celda C-->
                <td></td><!-- celda D-->
                <td></td> <!-- celda E-->
                <td></td><!-- celda F-->
                <td>Número</td><!-- celda G-->
                <td>Número</td><!-- celda H-->
                <td></td><!-- celda I-->
                <td></td><!-- celda J-->
                <td>Compras sin</td><!-- celda K-->
                <td></td><!-- celda L-->
                <td></td><!-- celda M-->
                <td></td><!-- celda N-->
                <td></td> <!-- Texto en la celda O -->
                <td></td> <!-- Texto en la celda P -->
                <!-- ... otras celdas ... -->
            </tr>
            <tr> <!-- ...celdas 24 ... -->
                <td></td><!-- celda A-->
                <td></td><!-- celda B-->
                <td></td><!-- celda C-->
                <td></td><!-- celda D-->
                <td>Oper.</td> <!-- celda E-->
                <td>Fecha de </td><!-- celda F-->
                <td>de la</td><!-- celda G-->
                <td>Control</td><!-- celda H-->
                <td>Tipo de</td><!-- celda I-->
                <td>Total Compras</td><!-- celda J-->
                <td>Derecho a</td><!-- celda K-->
                <td>Base</td><!-- celda L-->
                <td>%</td><!-- celda M-->
                <td>Impuesto</td><!-- celda N-->
                <td></td> <!-- Texto en la celda O -->
                <td>IVA</td> <!-- Texto en la celda P -->
                <!-- ... otras celdas ... -->
            </tr>
            <tr> <!-- ...celdas 25 ... -->
                <td></td><!-- celda A-->
                <td></td><!-- celda B-->
                <td></td><!-- celda C-->
                <td></td><!-- celda D-->
                <td>Nro</td> <!-- celda E-->
                <td>la Factura</td><!-- celda F-->
                <td>Factura</td><!-- celda G-->
                <td></td><!-- celda H-->
                <td>Transacción</td><!-- celda I-->
                <td>Con IVA</td><!-- celda J-->
                <td>Credito IVA</td><!-- celda K-->
                <td>Imponible</td><!-- celda L-->
                <td>Alicuota</td><!-- celda M-->
                <td>IVA</td><!-- celda N-->
                <td></td> <!-- Texto en la celda O -->
                <td>Retenido</td> <!-- Texto en la celda P -->
                <!-- ... otras celdas ... -->
            </tr>
            @foreach ($deductions as $key => $deduction)
                @php
                    $total += $deduction['financePaymentDeductions']['amount'];
                @endphp
                <tr>
                    <td></td><!-- celda A-->
                    <td></td><!-- celda B-->
                    <td></td><!-- celda C-->
                    <td></td><!-- celda D-->
                    <td>{{ $key + 1 }}</td> <!-- celda E-->
                    <td>{{ substr($finance->paid_at, 0, 10) }}</td><!-- celda F-->
                    <td>{{ $finance->code }}</td><!-- celda G-->
                    <td></td><!-- celda H-->
                    <td></td><!-- celda I-->
                    <td>{{ $deduction['total_purchases_iva'] + $deduction['percentage_retained'] }}</td><!-- celda J-->
                    <td>{{ $deduction['total_purchases_without_iva'] }}</td><!-- celda K-->
                    <td>{{ $deduction['total_purchases_iva'] }}</td><!-- celda L-->
                    <td>{{ $deduction['percentage'] }}</td><!-- celda M-->
                    <td>{{ $deduction['percentage_retained'] }}</td><!-- celda N-->
                    <td></td> <!-- Texto en la celda O -->
                    <td>{{ $deduction['financePaymentDeductions']['amount'] }}</td> <!-- Texto en la celda P -->
                </tr>
            @endforeach
            <tr> <!-- ...celdas 26 de repitión ... -->
                <td></td><!-- celda A-->
                <td></td><!-- celda B-->
                <td></td><!-- celda C-->
                <td></td><!-- celda D-->
                <td></td> <!-- celda E-->
                <td></td><!-- celda F-->
                <td></td><!-- celda G-->
                <td></td><!-- celda H-->
                <td></td><!-- celda I-->
                <td></td><!-- celda J-->
                <td></td><!-- celda K-->
                <td></td><!-- celda L-->
                <td></td><!-- celda M-->
                <td></td><!-- celda N-->
                <td></td> <!-- Texto en la celda O -->
                <td>{{ $total }}</td> <!-- Texto en la celda P -->
                <!-- ... otras celdas ... -->
            </tr>
            <tr> <!-- ...celdas 26 de repitión ... -->
                <td></td><!-- celda A-->
                <td></td><!-- celda B-->
                <td></td><!-- celda C-->
                <td></td><!-- celda D-->
                <td></td> <!-- celda E-->
                <td></td><!-- celda F-->
                <td></td><!-- celda G-->
                <td></td><!-- celda H-->
                <td></td><!-- celda I-->
                <td></td><!-- celda J-->
                <td></td><!-- celda K-->
                <td></td><!-- celda L-->
                <td></td><!-- celda M-->
                <td></td><!-- celda N-->
                <td></td> <!-- Texto en la celda O -->
                <td></td> <!-- Texto en la celda P -->
                <!-- ... otras celdas ... -->
            </tr>
            <tr> <!-- ...celdas 26 de repitión ... -->
                <td></td><!-- celda A-->
                <td></td><!-- celda B-->
                <td></td><!-- celda C-->
                <td></td><!-- celda D-->
                <td></td> <!-- celda E-->
                <td></td><!-- celda F-->
                <td></td><!-- celda G-->
                <td></td><!-- celda H-->
                <td></td><!-- celda I-->
                <td></td><!-- celda J-->
                <td></td><!-- celda K-->
                <td></td><!-- celda L-->
                <td></td><!-- celda M-->
                <td></td><!-- celda N-->
                <td></td> <!-- Texto en la celda O -->
                <td></td> <!-- Texto en la celda P -->
                <!-- ... otras celdas ... -->
            </tr>
            <tr> <!-- ...celdas + Total ... -->
                <td></td><!-- celda A-->
                <td></td><!-- celda B-->
                <td></td><!-- celda C-->
                <td></td><!-- celda D-->
                <td>AGENTE DE RETENCION (SELLO, FECHA Y FIRMA)</td> <!-- celda E-->
                <td></td><!-- celda F-->
                <td></td><!-- celda G-->
                <td></td><!-- celda H-->
                <td></td><!-- celda I-->
                <td></td><!-- celda J-->
                <td></td><!-- celda K-->
                <td></td><!-- celda L-->
                <td></td><!-- celda M-->
                <td></td><!-- celda N-->
                <td></td> <!-- Texto en la celda O -->
                <td></td> <!-- Texto en la celda P -->
                <!-- ... otras celdas ... -->
            </tr>
            <tr> <!-- ...celdas + Total ... -->
                <td></td><!-- celda A-->
                <td></td><!-- celda B-->
                <td></td><!-- celda C-->
                <td></td><!-- celda D-->
                <td></td> <!-- celda E-->
                <td></td><!-- celda F-->
                <td></td><!-- celda G-->
                <td></td><!-- celda H-->
                <td></td><!-- celda I-->
                <td></td><!-- celda J-->
                <td></td><!-- celda K-->
                <td></td><!-- celda L-->
                <td></td><!-- celda M-->
                <td></td><!-- celda N-->
                <td></td> <!-- Texto en la celda O -->
                <td></td> <!-- Texto en la celda P -->
                <!-- ... otras celdas ... -->
            </tr>
            <tr> <!-- ...celdas + Total ... -->
                <td></td><!-- celda A-->
                <td></td><!-- celda B-->
                <td></td><!-- celda C-->
                <td></td><!-- celda D-->
                <td></td> <!-- celda E-->
                <td></td><!-- celda F-->
                <td></td><!-- celda G-->
                <td></td><!-- celda H-->
                <td></td><!-- celda I-->
                <td></td><!-- celda J-->
                <td></td><!-- celda K-->
                <td></td><!-- celda L-->
                <td></td><!-- celda M-->
                <td></td><!-- celda N-->
                <td></td> <!-- Texto en la celda O -->
                <td></td> <!-- Texto en la celda P -->
                <!-- ... otras celdas ... -->
            </tr>
        </tbody>
    </table>
</body>

</html>
