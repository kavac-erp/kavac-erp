{{-- Reporte de historial de inventario de bienes --}}

<style>
.table {
  width: 100%;
  border-collapse: collapse;
}
.table td {
  padding: 8px;
  text-align: center;
  border: 1px solid #000000;
}
.table th {
    text-align: center;
    background-color: #BDBDBD;
    font-weight: bold;
    border: 1px solid #000000;
    font-size:12rem;
    vertical-align: bottom;
}
</style>
<h3 align="center">Bienes registrados</h3>
<table class="table">
    <thead>
        <tr>
            <th width="8%">Código interno</th>
            <th>Ctg. Específica</th>
            <th>Condición física</th>
            <th>Estatus de uso</th>
            <th width="full">Especificaciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($assets as $registeredAsset)
            <tr>
                <td width="8%">
                    {{ $registeredAsset->asset_institutional_code->name ?? '' }}
                </td>
                <td>
                    {{ $registeredAsset->asset_specific_category->name ?? '' }}
                </td>
                <td>
                    {{ $registeredAsset->asset_condition->name ?? '' }}
                </td>
                <td>
                    {{ $registeredAsset->asset_status->name ?? '' }}
                </td>
                <td width="full">
                <table>
                @foreach($registeredAsset->asset_details as $index => $detail)
                        <tr>
                        <td><strong>{{ $detail->label }} :</strong> {{ $detail->value }}</td>
                        </tr>
                @endforeach
            </table>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@php
    $showAsignedTable = false;

    foreach($assets as $asset) {

            if ($asset->asset_status->id == 1) {
                $showAsignedTable = true;
            }
    }

@endphp

@if ($showAsignedTable)
    <h3 align="center">Bienes asignados</h3>
    <table class="table">
        <thead>
            <tr>

                <th width="8%">Código interno</th>
                <th>Ctg. Específica</th>
                <th>Trabajador</th>
                <th>Condición física</th>
                <th>Estatus de uso</th>
                <th width="full" >Especificaciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($assets as $asset)
                @if($asset->asset_status->id == 1 && isset($asset->asignee_name))

                    <tr>
                    <td width="8%">
                            {{ isset($asset->asset_institutional_code->name) ?
                                $asset->asset_institutional_code->name :
                                '' }}
                        </td>
                        <td>
                            {{ isset($asset->asset_specific_category->name) ?
                                $asset->asset_specific_category->name :
                                '' }}
                        </td>

                        <td>
                            {{ isset($asset->asignee_name) ?
                                $asset->asignee_name :
                                '' }}
                        </td>
                        <td>
                            {{ isset($asset->asset_condition->name) ?
                                $asset->asset_condition->name :
                                '' }}
                        </td>
                        <td>
                            {{ isset($asset->asset_status->name) ?
                                $asset->asset_status->name :
                                '' }}
                        </td>
                        <td width="full">
                        <table>
                        @foreach($asset->asset_details as $index => $detail)
                                <tr>
                                <td><strong>{{ $detail->label }} :</strong> {{ $detail->value }}</td>
                                </tr>
                        @endforeach
                    </table>
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
@endif

@php
    $showDisincorporationTable = false;

    foreach($assets as $asset) {

            if ($asset->asset_status->id == 11) {
                $showDisincorporationTable = true;
            }
    }

@endphp

@if($showDisincorporationTable)

    <h3 align="center">Bienes desincorporados</h3>
    <table class="table">
        <thead>
            <tr>
                <th width="8%">Código interno</th>
                <th>Ctg. Específica</th>
                <th>Condición física</th>
                <th>Estatus de uso</th>
                <th>Motivo de desincorporación</th>
                <th width="full" >Especificaciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($assets as $asset)
                @if($asset->asset_status->id == 11)
                    <tr>

                        <td width="8%">
                            {{ isset($asset->asset_institutional_code->name) ?
                                $asset->asset_institutional_code->name :
                                '' }}
                        </td>
                        <td>
                            {{ isset($asset->asset_specific_category->name) ?
                                $asset->asset_specific_category->name :
                                '' }}
                        </td>
                        <td>
                            {{ isset($asset->asset_condition->name) ?
                                $asset->asset_condition->name :
                                '' }}
                        </td>
                        <td>
                            {{ isset($asset->asset_status->name) ?
                                $asset->asset_status->name :
                                '' }}
                        </td>
                        <td>
                            {{ isset($asset->disincorporation_motive) ?
                                $asset->disincorporation_motive :
                                '' }}
                        </td>
                        <td width="full" >
                        <table>
                        @foreach($asset->asset_details as $index => $detail)
                                <tr>
                                <td><strong>{{ $detail->label }} :</strong> {{ $detail->value }}</td>
                                </tr>
                        @endforeach
                        </table>
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
@endif