@foreach ($orders as $order)
    <h2 style="text-align: center">Pedido: {{ $order->id }} (Estado: {{ $order->status_text }})</h2>
    <h3>I Datos del solicitante:</h3>
    <table cellspacing="1" cellpadding="0" border="0">
        <tbody>
            <tr>
                <td style="font-size:9rem;" width="33%">
                    <strong> Tipo de persona:</strong><br>{{ $order->type_person }}
                </td>
                <td style="font-size:9rem;" width="33%">
                    <strong>
                        @if ($order->type_person === 'Jurídica')
                            Nombre de la Empresa:
                        @else
                            Nombre y Apellido:
                        @endif
                    </strong><br />{{ $order->name }}
                </td>
                <td style="font-size:9rem;" width="33%">
                    <strong>
                        @if ($order->type_person === 'Jurídica')
                            RIF:
                        @else
                            Identificación:
                        @endif
                    </strong><br />{{ $order->id_number }}
                </td>
            </tr>
            <tr>
                <td style="font-size:9rem;" width="33%">
                    <strong>Teléfono de contacto:</strong><br>{{ $order->phone }}
                </td>
                <td style="font-size:9rem;" width="33%">
                    <strong>Correo Electrónico:</strong><br>{{ $order->email }}
                </td>
            </tr>
        </tbody>
    </table>
    <br /><br />
    <h3>II Descripción de productos:</h3>
    {{-- Productos --}}
    @if (isset($order->products) && count($order->products) > 0)
        <table cellspacing="0" cellpadding="1" border="0">
            <tr>
                <th width="20%" style="font-size:9rem; background-color: #BDBDBD;" align="center">Producto</th>
                <th width="10%" style="font-size:9rem; background-color: #BDBDBD;" align="center">Precio unitario
                </th>
                <th width="10%" style="font-size:9rem; background-color: #BDBDBD;" align="center">Cantidad de
                    productos</th>
                <th width="10%" style="font-size:9rem; background-color: #BDBDBD;" align="center">Total sin iva</th>
                <th width="10%" style="font-size:9rem; background-color: #BDBDBD;" align="center">Iva</th>
                <th width="20%" style="font-size:9rem; background-color: #BDBDBD;" align="center">Total</th>
                <th width="20%" style="font-size:9rem; background-color: #BDBDBD;" align="center">Moneda</th>
            </tr>
            @foreach ($order->products as $product)
                <tr>
                    <td style="font-size:9rem; border-bottom-color:#BDBDBD;" align="left">
                        {{ $product['name'] }}
                    </td>
                    <td style="font-size:9rem; border-bottom-color:#BDBDBD;" align="center">
                        {{ number_format($product['price_product'], 2, ',', '.') }}
                    </td>
                    <td style="font-size:9rem; border-bottom-color:#BDBDBD;" align="center">
                        {{ $product['quantity'] }}
                    </td>
                    <td style="font-size:9rem; border-bottom-color:#BDBDBD;" align="center">
                        {{ number_format($product['quantity'] * $product['price_product'], 2, ',', '.') }}
                    </td>
                    <td style="font-size:9rem; border-bottom-color:#BDBDBD;" align="center">
                        {{ number_format($product['iva'], 2, ',', '.') }}
                    </td>
                    <td style="font-size:9rem; border-bottom-color:#BDBDBD;" align="center">
                        {{ number_format($product['total'], 2, ',', '.') }}
                    </td>
                    <td style="font-size:9rem; border-bottom-color:#BDBDBD;" align="left">
                        {{ $product['moneda'] }}
                    </td>
                </tr>
            @endforeach
        </table>
        <br /><br />
        <table cellspacing="1" cellpadding="0" border="0">
            <tbody>
                <tr>
                    <td style="font-size:9rem;" width="33%">
                        <strong>Total sin iva:</strong><br>{{ number_format($order->total_without_tax, 2, ',', '.') }}
                    </td>
                    <td style="font-size:9rem;" width="33%">
                        <strong>IVA:</strong><br>{{ number_format($order->total - $order->total_without_tax, 2, ',', '.') }}
                    </td>
                    <td style="font-size:9rem;" width="33%">
                        <strong>Total a pagar:</strong><br>{{ number_format($order->total, 2, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>
    @else
        No hay productos en el pedido
    @endif
    <br /><br /><br /><br />
@endforeach
