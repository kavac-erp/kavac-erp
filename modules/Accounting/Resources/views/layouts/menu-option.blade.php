{{-- Gestión de contabilidad --}}
<li>
    <a href="javascript:void(0)" title="Gestión de asientos contables" data-toggle="tooltip" data-placement="right">
        <i class="ion-social-buffer-outline"></i><span>Contabilidad</span>
    </a>
    <ul class="submenu" style="{!! display_submenu('accounting') !!}">
        <li class="{!! set_active_menu(['accounting.settings.index']) !!}">
            <a href="{{ route('accounting.settings.index') }}">
                <i></i>Configuración
            </a>
        </li>
        <li class="{!! set_active_menu(['accounting.converter.index']) !!}">
            <a href="{{ route('accounting.converter.index') }}">Convertidor de cuentas</a>
        </li>
        <li class="{!! set_active_menu(['accounting.entries.index']) !!}">
            <a href="{{ route('accounting.entries.index') }}">Asientos contables</a>
        </li>
        <li class="{!! set_active_menu(['accounting.report.accountingBooks', 'accounting.report.financeStatements']) !!}">
            <a href="javascript:void(0)">Reportes</a>
            @php
                $submenuReports = '';
                if (
                    in_array(Route::current()->getName(), [
                        'accounting.report.accountingBooks',
                        'accounting.report.financeStatements',
                    ])
                ) {
                    $submenuReports = 'style="display:block"';
                }
            @endphp
            <ul class="submenu" {!! $submenuReports !!}>
                <li class="{!! set_active_menu(['accounting.report.accountingBooks']) !!}">
                    <a href="{{ route('accounting.report.accountingBooks') }}" data-toggle="tooltip"
                        data-placement="right">Libros contables</a>
                </li>
                <li class="{!! set_active_menu(['accounting.report.financeStatements']) !!}">
                    <a href="{{ route('accounting.report.financeStatements') }}" data-toggle="tooltip"
                        data-placement="right">Estados financieros</a>
                </li>
            </ul>
        </li>
        <!--li>
            <a href="{{ route('accounting.dashboard.test') }}">Panel de control</a>
        </li-->
    </ul>
</li>
