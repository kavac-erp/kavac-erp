{{-- Gestión de atención al ciudadano --}}
<li>
    <a href="javascript:void(0)" title="Gestión de atención al ciudadano" data-toggle="tooltip" 
       data-placement="right">
        <i class="icofont icofont-users-social"></i><span>Atención / Ciudadano</span>
    </a>
    <ul class="submenu" style="{!! display_submenu('citizenservice') !!}">
        <li class="{!! set_active_menu(['citizenservice.settings.index']) !!}">
             <a href="{{ route('citizenservice.settings.index') }}">Configuración</a>
        </li>
        <li class="{!! set_active_menu(['citizenservice.request.index']) !!}">
             <a href="{{ route('citizenservice.request.index') }}">Solicitudes</a>
        </li>
         <li class="{!! set_active_menu(['citizenservice.report.index']) !!}">
             <a href="{{ route('citizenservice.report.index') }}">Reportes</a>
        </li>
        <li class="{!! set_active_menu(['citizenservice.register.index']) !!}">
             <a href="{{ route('citizenservice.register.index') }}">Ingresar Cronograma</a>
        </li>
    </ul>
</li>
