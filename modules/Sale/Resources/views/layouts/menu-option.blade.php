{{-- Gestión de comercialización --}}
{{-- <li>
    <a href="javascript:void(0)" title="Gestión de comercialización" data-toggle="tooltip" data-placement="right">
        <i class="ion ion-briefcase"></i><span>Comercialización</span>
    </a>
    <ul class="submenu" style="{!! display_submenu('sale') !!}">
         <li class="{!! set_active_menu('sale.settings.index') !!}">
            <a href="{{ route('sale.settings.index') }}" data-toggle="tooltip" data-placement="right" 
               title="Configuración de Comercialización">Configuración</a>
        </li>
        <li class="{!! set_active_menu('sale.services.index') !!}">
            <a href="{{ route('sale.services.index') }}" data-toggle="tooltip" data-placement="right" 
               title="Solicitud de servicios">Solicitud de servicios</a>
        </li>
        <li class="{!! set_active_menu('sale.bills.index') !!}">
            <a href="{{ route('sale.bills.index') }}" data-toggle="tooltip" data-placement="right" 
               title="Facturas">Facturas</a>
        </li>
        <li class="{!! set_active_menu('sale.reception.index') !!}">
            <a href="{{ route('sale.reception.index') }}" data-toggle="tooltip" data-placement="right"
               title="Recepciones de almacén">Recepciones de almacén</a>
        </li>
        <li class="{!! set_active_menu('sale.payment.index') !!}">
            <a href="{{ route('sale.payment.index') }}" data-toggle="tooltip" data-placement="right"
               title="Registro y aprobación de pagos">Pagos</a>
        </li>
        <li class="{!! set_active_menu('sale.order.index') !!}">
            <a href="{{ route('sale.order.index') }}" data-toggle="tooltip" data-placement="right"
               title="Pedidos">Pedidos</a>
        </li>
        <li class="{!! set_active_menu('sale.quotes.index') !!}">
            <a href="{{ route('sale.quotes.index') }}" data-toggle="tooltip" data-placement="right"
               title="Registro y aprobación de cotizaciones">Cotizaciones</a>
        </li>
        <li>
            <a href="javascript:void(0)"
               data-toggle="tooltip" data-placement="right"
               title="Gestiona la generación de reportes del módulo de comercialización">
                    Reportes
            </a>
            <ul class="submenu" style="{!! display_submenu('report') !!}">
                <li class="{!! set_active_menu(['sale.report.inventory-products']) !!}" title="Reporte de inventario de productos de almacén"
                    data-toggle="tooltip" data-placement="right">
                    <a href="{{ route('sale.report.inventory-products') }}">Inventario de productos</a>
                </li>
                <li class="{!! set_active_menu(['sale.report.orders']) !!}" title="Reporte de pedidos"
                    data-toggle="tooltip" data-placement="right">
                    <a href="{{ route('sale.report.orders') }}">Pedidos</a>
                </li>
                <li class="{!! set_active_menu(['sale.report.payment']) !!}" title="Reporte de pagos"
                    data-toggle="tooltip" data-placement="right">
                    <a href="{{ route('sale.report.payment' )}}">Pagos</a>
                </li>
                <li class="{!! set_active_menu(['sale.report.service-requests']) !!}" title="Reporte de servicios"
                    data-toggle="tooltip" data-placement="right">
                    <a href="{{ route('sale.report.service-requests') }}">Solicitudes de servicios</a>
                </li>
                <li class="{!! set_active_menu(['sale.report.bill']) !!}" title="Reporte de facturas"
                    data-toggle="tooltip" data-placement="right">
                    <a href="{{ route('sale.report.bill') }}">Facturas</a>
                </li>
                <li class="{!! set_active_menu(['sale.report.quote']) !!}" title="Reporte de cotizaciones"
                    data-toggle="tooltip" data-placement="right">
                    <a href="{{ route('sale.report.quote') }}">Cotizaciones</a>
                </li>
            </ul>
        </li>
    </ul>
</li> --}}