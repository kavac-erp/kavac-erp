{{-- Gestión de finanzas --}}
<li>
    <a href="javascript:void(0)" title="Gestión de bancos y finanzas" data-toggle="tooltip" data-placement="right">
        <i class="ion-ios-calculator-outline"></i><span>Finanzas</span>
    </a>
    <ul class="submenu" style="{!! display_submenu('finance') !!}">
        <li class="{!! set_active_menu('finance.setting.index') !!}">
            <a href="{{ route('finance.setting.index') }}">Configuración</a>
        </li>
        @php
            $submenuOrders = '';
            if (in_array(Route::current()->getName(), ['finance.pay-orders.index', 'finance.pay-orders.create', 'finance.pay-orders.edit',
                                                       'finance.payment-execute.index', 'finance.payment-execute.create', 'finance.payment-execute.edit'])) {
                $submenuOrders = 'style="display:block"';
            }
        @endphp
        <li class="{!! set_active_menu(['finance.pay-orders.index', 'finance.payment-execute.index']) !!}">
            <a href="javascript:void('0')">Gestión de Pagos</a>
            <ul class="submenu" {!! $submenuOrders !!}>
                <li class="{!! set_active_menu('finance.pay-orders.index') !!}">
                    <a href="{{ route('finance.pay-orders.index') }}">Órdenes</a>
                </li>
                <li class="{!! set_active_menu('finance.payment-execute.index') !!}">
                    <a href="{{ route('finance.payment-execute.index') }}">Emisiones</a>
                </li>
            </ul>
        </li>
        @php
            $submenuBanks = '';
            if (in_array(Route::current()->getName(), ['finance.movements.index', 'finance.movements.create', 'finance.movements.edit',
                                                       'finance.conciliation.index', 'finance.conciliation.create', 'finance.conciliation.edit'])) {
                $submenuBanks = 'style="display:block"';
            }
        @endphp
        <li class="{!! set_active_menu(['finance.movements.index', 'finance.conciliation.index']) !!}">
            <a href="#">Banco</a>
            <ul class="submenu" {!! $submenuBanks !!}>
                <li class="{!! set_active_menu('finance.movements.index') !!}">
                    <a href="{{ route('finance.movements.index') }}">Movimientos</a>
                </li>
                <li class="{!! set_active_menu('finance.conciliation.index') !!}">
                    <a href="{{ route('finance.conciliation.index') }}">Conciliación</a>
                </li>
            </ul>
        </li>
        <li>
            <a href="{{ route('finance.payment-reports.create') }}">Reportes</a>    
        </li>
    </ul>
</li>
