<style>
    .tabla-fila {
        background-color: #BDBDBD;
        font-weight: bold;
        text-align: center;
    }

    .table {
        font-size: 8rem;
        text-align: center;
    }

    .table td,
    .table th {
        text-align: center;
    }

    .table-info {
        font-size: 8rem;
    }

    .div {
        height: 100000px;
    }
</style>

<h2 align="center" style="font-size: 12rem;">Tabla de Depreciación</h2>

@foreach ($request as $asset)
    <table class="table-info">
        <tr>
            <td colspan="3">
                <strong>INFORMACIÓN DEL BIEN:</strong>
            </td>
        </tr>
        <tr>
            <td>
                &nbsp;
            </td>
        </tr>
        <tr>
            <td>
                <strong>{{ $asset['asset_institutional_code']['label'] }}:
                </strong>{{ $asset['asset_institutional_code']['name'] }}
            </td>
            <td>
                <strong>{{ $asset['asset_subcategory']['label'] }}: </strong>{{ $asset['asset_subcategory']['name'] }}
            </td>
            <td>
                <strong>{{ $asset['asset_specific_category']['label'] }}:
                </strong>{{ $asset['asset_specific_category']['name'] }}
            </td>
        </tr>
    </table>
    <table cellspacing="0" cellpadding="1" border="0.5" class="table">
        <thead>
            <tr class="tabla-fila">
                <th>Año</th>
                <th>Descripción</th>
                <th>Depreciación Anual</th>
                <th>Depreciación Acumulada</th>
                <th>Valor Según Libros</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td></td>
                <td>Compra</td>
                <td></td>
                <td></td>
                <td>{{ $asset['acquisition_value']['name'] }} {{ $asset['currency']['symbol'] }}.</td>
            </tr>
            @foreach ($asset['depreciation'] as $item)
                <tr>
                    <td>Año {{ $loop->index + 1 }}</td>
                    <td>Ajuste del Período</td>
                    <td>{{ $item['year_amount'] }} {{ $asset['currency']['symbol'] }}.</td>
                    <td>{{ $item['acumulated_amount'] }} {{ $asset['currency']['symbol'] }}.</td>
                    <td>{{ $item['asset_book_value'] }} {{ $asset['currency']['symbol'] }}.</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="div"></div>
    <hr>
@endforeach
