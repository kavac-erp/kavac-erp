{{-- Gestión de proyectos --}}
<li>
    <a href="javascript:void(0)" title="Gestión de proyectos" data-toggle="tooltip" data-placement="right">
        <i class="icofont icofont-circuit"></i><span>Seguimiento</span>
    </a>
    <ul class="submenu" style="{!! display_submenu('projecttracking') !!}">
    
        <li class="{!! set_active_menu(['projecttracking.setting.index']) !!}">
            <a href="{{ route('projecttracking.setting.index') }}" title="Configuración de seguimiento" data-toggle="tooltip" data-placement="right">
                Configuración
            </a>
        </li>

        <li class="{!! set_active_menu(['projecttracking.activity_plans.index']) !!}">
            <a href="{{  route('projecttracking.activity_plans.index') }}" title="Gestión de plan de actividades" data-toggle="tooltip" data-placement="right">
                Plan de actividades
            </a>
        </li>

        <li class="{!! set_active_menu(['projecttracking.tasks.index']) !!}">
            <a href="{{  route('projecttracking.tasks.index') }}" title="Gestión de Tareas" data-toggle="tooltip" data-placement="right">
                Tareas
            </a>
        </li>

        <li>
            <a href="javascript:void(0)">Avances</a>
        </li>

        <li>
            <a href="javascript:void(0)">Reportes</a>
        </li>
    </ul>
</li>