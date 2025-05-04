{{-- Gestión de bienes --}}
<li>
    <a href="javascript:void(0)" title="Gestión de bienes institucionales" data-toggle="tooltip" data-placement="right">
        <i class="ion-ios-pricetags-outline"></i><span>Bienes</span>
    </a>
    <ul class="submenu" style="{!! display_submenu('asset') !!}">

        @role(['admin', 'asset'])
            <li class="{!! set_active_menu('asset.setting.index') !!}">
                <a href="{{ route('asset.setting.index') }}" data-toggle="tooltip" data-placement="right"
                    title="Configuración de bienes">Configuración</a>
            </li>
        @endrole
        @if (!Module::has('Purchase') || !Module::isEnabled('Purchase'))
            <li class="{!! set_active_menu(['asset.suppliers.index', 'asset.suppliers.create', 'asset.suppliers.edit']) !!}">
                <a href="{{ route('asset.suppliers.index') }}" title="Gestión de Proveedores" data-toggle="tooltip"
                    data-placement="right">
                    Proveedores
                </a>
            </li>
        @endif
        <li title="Gestión de registros de bienes institucionales" data-toggle="tooltip" data-placement="right"
            class="{!! set_active_menu(['asset.register.index', 'asset.register.create', 'asset.register.edit']) !!}">
            <a href="{{ route('asset.register.index') }}">Registros</a>
        </li>

        <li title="Gestión de ajustes de bienes institucionales" data-toggle="tooltip" data-placement="right"
            class="{!! set_active_menu(['asset.adjustment.index', 'asset.adjustment.edit']) !!}">
            <a href="{{ route('asset.adjustment.index') }}">Ajustes de bienes</a>
        </li>

        <li title="Gestión de asignaciones de bienes institucionales" data-toggle="tooltip" data-placement="right"
            class="{!! set_active_menu(['asset.asignation.index', 'asset.asignation.create', 'asset.asignation.edit']) !!}">
            <a href="{{ route('asset.asignation.index') }}">Asignaciones</a>
        </li>

        <li title="Gestión de Desincorporaciones de bienes institucionales" data-toggle="tooltip" data-placement="right"
            class="{!! set_active_menu([
                'asset.disincorporation.index',
                'asset.desincorporation.create',
                'asset.desincorporation.edit',
            ]) !!}">
            <a href="{{ route('asset.disincorporation.index') }}">Desincorporaciones</a>
        </li>

        <li title="Gestión de solicitudes de bienes institucionales" data-toggle="tooltip" data-placement="right"
            class="{!! set_active_menu(['asset.request.index', 'asset.request.create', 'asset.request.edit']) !!}">
            <a href="{{ route('asset.request.index') }}">Solicitudes</a>
        </li>

        <li title="Gestión de depreciación de bienes institucionales" data-toggle="tooltip" data-placement="right"
            class="{!! set_active_menu(['asset.depreciation.index', 'asset.depreciation.create', 'asset.depreciation.edit']) !!}">
            <a href="{{ route('asset.depreciation.index') }}">Depreciación</a>
        </li>

        <li class="{!! set_active_menu(['asset.report.index', 'asset.report.depreciation']) !!}">
            <a href="javascript:void(0)" data-toggle="tooltip" data-placement="right"
                title="{{ __('Gestiona la generación de reportes del módulo de Bienes') }}">
                {{ __('Reportes') }}
            </a>
            @php
                $submenuReports = '';
                if (in_array(Route::current()->getName(), ['asset.report.index', 'asset.report.depreciation'])) {
                    $submenuReports = 'style="display:block"';
                }
            @endphp
            <ul class="submenu" {!! $submenuReports !!}>
                <li data-toggle="tooltip" data-placement="right" class="{!! set_active_menu(['asset.inventory-history.index']) !!}"
                    title="Gestiona el almacenamiento y visualización del estado del inventario a lo largo del tiempo">
                    <a href="{{ route('asset.inventory-history.index') }}">Historial de Inventario</a>
                </li>
                <li class="{!! set_active_menu(['asset.report.index']) !!}" data-toggle="tooltip" data-placement="right"
                    title="Gestiona la generación de reportes de bienes institucionales">
                    <a href="{{ route('asset.report.index') }}">
                        {{ __('Reportes de bienes') }}
                    </a>
                </li>
                @permission('asset.depreciation.report')
                    <li class="{!! set_active_menu(['asset.report.depreciation']) !!}">
                        <a href="{{ route('asset.report.depreciation') }}" data-toggle="tooltip" data-placement="right"
                            title="{{ __('Reporte de depreciación') }}">
                            {{ __('Reportes de depreciación') }}
                        </a>
                    </li>
                @endpermission
            </ul>
        </li>
    </ul>
</li>
