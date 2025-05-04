{{-- Gestión de firma electrónica --}}
<li>
    <a href="javascript:void(0)" title="Gestión de firma electrónica" data-toggle="tooltip" data-placement="right">
        <i class="icofont icofont-ui-password"></i><span> Firma Electrónica </span>
    </a>
    <ul class="submenu" style="{!! display_submenu('digitalsignature') !!}">
        <li class="{!! set_active_menu(['digitalsignature']) !!}"><a href="{{ route('digitalsignature') }}">Gestión y firmado</a></li>
    </ul>
</li>