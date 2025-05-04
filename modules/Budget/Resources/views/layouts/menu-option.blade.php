{{-- Gestión de presupuesto --}}
<li>
    <a href="javascript:void(0)" title="{{ __('Formulación y ejecución del presupuesto') }}" data-toggle="tooltip"
        data-placement="right">
        <i class="ion-arrow-graph-up-right"></i><span>{{ __('Presupuesto') }}</span>
    </a>
    <ul class="submenu" style="{!! display_submenu(['budget']) !!}">
        <li class="{!! set_active_menu('budget.settings.index') !!}">
            <a href="{{ route('budget.settings.index') }}" data-toggle="tooltip" data-placement="right"
                title="{{ __('Configuración de presupuesto') }}">{{ __('Configuración') }}</a>
        </li>
        <li class="{!! set_active_menu(['budget.accounts.index', 'budget.accounts.create', 'budget.accounts.edit']) !!}">
            <a href="{{ route('budget.accounts.index') }}" data-toggle="tooltip" data-placement="right"
                title="{{ __('Gestión del clasificador de cuentas presupuestarias') }}">
                {{ __('Clasificador Presupuestario') }}
            </a>
        </li>
        <li class="{!! set_active_menu(['budget.subspecific-formulations.index']) !!}">
            <a href="{{ route('budget.subspecific-formulations.index') }}" data-toggle="tooltip"
                data-placement="right" title="{{ __('Gestión para la formulación de presupesto') }}">
                {{ __('Formulaciones') }}
            </a>
        </li>
        @if (Module::has('Purchase')  && Module::isEnabled('Purchase') )
            <li class="{!! set_active_menu(['purchase.budgetary_availability.index', 'purchase.budgetary_availability.create', 'purchase.budgetary_availability.edit']) !!}">
                <a href="{{ route('purchase.budgetary_availability.index') }}"
                title="Disponibilidad Presupuestaria" data-toggle="tooltip" data-placement="right">
                Disponibilidad Presupuestaria
            </a>
        </li>
        @endif

        <li class="{!! set_active_menu(['budget.aditional-credits.index', 'budget.aditional-credits.create', 'budget.aditional-credits.edit', 'budget.reductions.index', 'budget.reductions.create', 'budget.reductions.edit', 'budget.transfers.index', 'budget.transfers.create', 'budget.transfers.edit', 'budget.modifications.index', 'budget.modifications.create', 'budget.modifications.edit']) !!}">
            <a href="{{ route('budget.modifications.index') }}" data-toggle="tooltip"
                title="{{ __('Gestiona las modificaciones presupuestarias (créditos adicionales, reducciones, traspasos, etc.)') }}">
                {{ __('Modificaciones') }}
            </a>
        </li>
        <li class="{!! set_active_menu(['budget.compromises.index', 'budget.compromises.create', 'budget.compromises.edit']) !!}">
            <a href="javascript:void(0)" data-toggle="tooltip" data-placement="right"
                title="{{ __('Gestión sobre la ejecución de presupuesto') }}">
                {{ __('Ejecución') }}
            </a>
            @php
                $submenuCompromises = '';
                if (in_array(Route::current()->getName(), ['budget.compromises.index', 'budget.compromises.create', 'budget.compromises.edit'])) {
                    $submenuCompromises = 'style="display:block"';
                }
            @endphp
            <ul class="submenu" {!! $submenuCompromises !!}>
                <li class="{!! set_active_menu(['budget.compromises.index', 'budget.compromises.create', 'budget.compromises.edit']) !!}">
                    <a href="{{ route('budget.compromises.index') }}" data-toggle="tooltip" data-placement="right"
                        title="{{ __('Gestiona los compromisos presupuestarios') }}">
                        {{ __('Compromisos') }}
                    </a>
                </li>
            </ul>
        </li>
        <li class="{!! set_active_menu(['budget.report.budgetAnalyticalMajor', 'budget.report.budgetAvailability', 'budget.report.projects', 'budget.report.formulated']) !!}">
            <a href="javascript:void(0)" data-toggle="tooltip" data-placement="right"
                title="{{ __('Gestiona la generación de reportes del módulo de presupuesto') }}">
                {{ __('Reportes') }}
            </a>
            @php
            $submenuReports = '';
            if (in_array(Route::current()->getName(), ['budget.report.budgetAnalyticalMajor', 'budget.report.budgetAvailability', 'budget.report.projects', 'budget.report.formulated'])) {
                $submenuReports = 'style="display:block"';
            }
            @endphp
            <ul class="submenu" {!! $submenuReports !!}>
                <li class="{!! set_active_menu(['budget.report.budgetAnalyticalMajor']) !!}">
                    <a href="{{ route('budget.report.budgetAnalyticalMajor') }}" data-toggle="tooltip"
                        data-placement="right" title="{{ __('Reporte de mayor analítico') }}">
                        {{ __('Mayor Analítico') }}
                    </a>
                </li>
                {{-- <li><a href="javascript:void(0)">{{ __('Consolidado') }}</a></li> --}}
                <li class="{!! set_active_menu(['budget.report.budgetAvailability']) !!}">
                    <a href="{{ route('budget.report.budgetAvailability') }}" data-toggle="tooltip"
                        data-placement="right" title="{{ __('Reporte de disponibilidad presupuestaria') }}">
                        {{ __('Disponibilidad Presupuestaria') }}
                    </a>
                </li>
                {{-- <li><a href="{{ route('budget.report.projects') }}" data-toggle="tooltip"
                        data-placement="right">Proyectos</a></li> --}}
                <li class="{!! set_active_menu(['budget.report.formulated']) !!}">
                    <a href="{{ route('budget.report.formulated') }}" data-toggle="tooltip"
                        data-placement="right" title="{{ __('Reporte de presupuesto formulado') }}">
                        {{ __('Presupuesto Formulado') }}
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</li>
