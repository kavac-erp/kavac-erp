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
</style>

<h2 align="center" style="font-size: 12rem;">Depreciación Acumulada de Activo Fijo</h2>
<p></p>
@foreach ($request as $asset)
    <p></p>
    <table class="table-info">
        <tr>
            <td colspan="3">
                <strong>Informacion de bien:</strong>
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
    <p></p>
    <table cellspacing="0" cellpadding="1" border="0.5" class="table">
        <thead>
            <tr class="tabla-fila">
                <th colspan="2">Año</th>
                <th>Descripción</th>
                <th>Depreciación Anual</th>
                <th>Depreciación Acumulada</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($asset['depreciation'] as $item)
                <tr>
                    <td>{{ $item['year'] }}</td>
                    <td>{{ $loop->index + 1 }}/{{ $asset['depresciation_years']['name'] }}</td>
                    <td>Ajuste del Período</td>
                    <td>{{ $item['year_amount'] }} {{ $asset['currency']['symbol'] }}.</td>
                    <td>{{ $item['acumulated_amount'] }} {{ $asset['currency']['symbol'] }}.</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endforeach
