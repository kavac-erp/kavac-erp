{{-- Gestión de compras --}}
<li>
    <a href="javascript:void(0)" title="Gestión de compras de bienes y servicios" data-toggle="tooltip"
        data-placement="right">
        <i class="ion-social-dropbox-outline"></i><span>Compras</span>
    </a>
    <ul class="submenu" style="{!! display_submenu('purchase') !!}">
        <li class="{!! set_active_menu('purchase.settings.index') !!}">
            <a href="{{ route('purchase.settings.index') }}" data-toggle="tooltip"
                data-placement="right" title="Configuración de compras"
            >
                Configuración
            </a>
        </li>
        <li class="{!! set_active_menu([
            'purchase.suppliers.index', 'purchase.suppliers.create', 'purchase.suppliers.edit'
            ]) !!}">
            <a href="{{ route('purchase.suppliers.index') }}"
                title="Gestión de Proveedores" data-toggle="tooltip" data-placement="right"
            >
                Proveedores
            </a>
        </li>
        <li class="{!! set_active_menu(['purchase.purchase_plans.index']) !!}">
            <a href="{{ route('purchase.purchase_plans.index') }}"
                title="Gestión de plan de compra" data-toggle="tooltip" data-placement="right"
            >
                Planes de compras
            </a>
        </li>
        <li class="{!! set_active_menu(['purchase.requirements.index']) !!}">
            <a href="{{ route('purchase.requirements.index') }}"
                title="Gestión de Requerimientos" data-toggle="tooltip" data-placement="right"
            >
                Requerimientos
            </a>
        </li>
        @if (!Module::has('Budget')  || !Module::isEnabled('Budget') )
        <li class="{!! set_active_menu(['purchase.budgetary_availability.index', 'purchase.budgetary_availability.create', 'purchase.budgetary_availability.edit']) !!}">
            <a href="{{ route('purchase.budgetary_availability.index') }}"
                title="Disponibilidad Presupuestaria" data-toggle="tooltip" data-placement="right"
            >
                Disponibilidad Presupuestaria
            </a>
        </li>
        @endif
        <li class="{!! set_active_menu(['purchase.quotation.index']) !!}">
            <a href="{{ route('purchase.quotation.index') }}"
                title="Gestión de cotizaciones" data-toggle="tooltip" data-placement="right"
            >
                Cotizaciones
            </a>
        </li>
        <li class="{!! set_active_menu(['purchase.purchase_order.index']) !!}">
            <a href="{{ route('purchase.purchase_order.index') }}"
                title="Gestión de ordenes de compra / servicios" data-toggle="tooltip" data-placement="right"
            >
                Órdenes de compras / servicios
            </a>
        </li>
    </ul>
</li>
