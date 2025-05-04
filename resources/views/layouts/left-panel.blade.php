<div class="left-panel h-100" style="z-index:1005;">
    <div class="media profile-left">
        @php
            $prf = auth()->user()->profile;
            $img_profile = $prf && $prf->image_id ? $prf->image->url : null;
        @endphp
        <a class="float-left profile-thumb" href="{{ url('users') . '/' . Auth::user()->id }}">
            @php
                $avatar =
                    $img_profile !== null && file_exists(base_path($img_profile))
                        ? $img_profile
                        : 'images/default-avatar.png';
            @endphp
            <img class="img-circle img-profile-mini" src="{{ asset($avatar, Request::secure()) }}"
                alt="{{ auth()->user()->name }}" title="{{ __('Imagen de perfil') }}" data-toggle="tooltip">
        </a>
        <div class="media-body">
            <h4 class="media-heading">{{ Auth::user()->name }}</h4>
            <small class="text-muted">{{-- Cargo --}}</small>
        </div>
    </div>
    @if (Auth::user()->hasVerifiedEmail())
        @if (!App\Models\Institution::all()->isEmpty())
            <h5 class="navigation-panel-title text-center">{{ __('AÑO FISCAL:') }}
                <span class="fiscal-year"></span>
            </h5>
            <hr>
        @endif
        <h5 class="navigation-panel-title text-center">{{ __('MENU') }}</h5>
        <div id="jquery-accordion-menu" class="jquery-accordion-menu white">
            {{-- <div class="jquery-accordion-menu-header">Header </div> --}}
            <ul class="submenu" style="{!! display_submenu(['']) !!}">
                {{-- Acceso al panel de control del usuario --}}
                <li class="{!! set_active_menu('index') !!}">
                    <a href="{{ route('index') }}" title="{{ __('Panel de control del usuario') }}"
                        data-toggle="tooltip" data-placement="right">
                        <i class="ion-ios-speedometer-outline"></i>
                        <span>{{ __('Panel de control') }}</span>
                    </a>
                </li>
                @role('admin')
                    {{-- Acceso a la configuración de la aplicación --}}
                    <li>
                        <a href="javascript:void(0)" title="{{ __('Gestión de configuración') }}" data-toggle="tooltip"
                            data-placement="right">
                            <i class="ion-settings"></i>
                            <span>{{ __('Configuración') }}</span>
                        </a>
                        @php
                            $submenu = '';
                            if (
                                in_array(Route::current()->getName(), [
                                    'settings.index',
                                    'access.settings',
                                    'access.settings.users',
                                    'module.list',
                                ])
                            ) {
                                $submenu = 'style="display:block"';
                            }
                        @endphp
                        <ul class="submenu" {!! $submenu !!}>
                            <li class="{!! set_active_menu('settings.index') !!}">
                                <a href="{{ route('settings.index') }}" data-toggle="tooltip" data-placement="right"
                                    title="{{ __('Configuración general de la aplicación') }}">
                                    {{ __('General') }}
                                </a>
                            </li>
                            <li class="{!! set_active_menu(['access.settings', 'access.settings.users']) !!}">
                                <a href="javascript:void(0)" title="{{ __('Gestión de usuarios, roles y permisos') }}"
                                    data-toggle="tooltip" data-placement="right">
                                    {{ __('Acceso') }}
                                </a>
                                <ul class="submenu" {!! $submenu !!}>
                                    <li class="{!! set_active_menu('access.settings') !!}" data-toggle="tooltip"
                                        title="{{ __('Gestión de roles y permisos del sistema') }}" data-placement="right">
                                        <a href="{{ route('access.settings') }}">
                                            {{ __('Roles') }} / {{ __('Permisos') }}
                                        </a>
                                    </li>
                                    <li class="{!! set_active_menu('access.settings.users') !!}" data-toggle="tooltip"
                                        title="{{ __('Gestión de usuarios del sistema') }}" data-placement="right">
                                        <a href="{{ route('access.settings.users') }}">
                                            {{ __('Usuarios') }}
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="{!! set_active_menu('module.list') !!}">
                                <a href="{{ route('module.list') }}" title="{{ __('Gestión de módulos del sistema') }}"
                                    data-toggle="tooltip" data-placement="right">
                                    {{ __('Módulos') }}
                                </a>
                            </li>
                        </ul>
                    </li>
                @endrole

                @if (count(App\Models\Institution::where('active', true)->get()) > 0)
                    @foreach (\Module::getOrdered(1) as $module)
                        {{-- Menú de opciones de módulos instalados y habilitados --}}
                        @includeIf(strtolower($module->getName()) . '::layouts.menu-option')
                    @endforeach

                    {{-- Cierre de ejercicio económico --}}
                    <li>
                        @if (Module::has('Accounting') && Module::isEnabled('Accounting'))
                            <a href="javascript:void()" title="{{ __('Cierre de ejercicio actual') }}"
                                data-toggle="tooltip" data-placement="right">
                                <i class="ion-lock-combination"></i>
                                <span>{{ __('Cierre de Ejercicio') }}</span>
                            </a>
                            <ul class="submenu" style="{!! display_submenu('close-fiscal-year') !!}">
                                @permission('closefiscalyear.setting')
                                    <li class="{!! set_active_menu('close-fiscal-year.settings.index') !!}">
                                        <a href="{{ route('close-fiscal-year.settings.index') }}" data-toggle="tooltip"
                                            data-placement="right"
                                            title="{{ __('Configuración para el cierre de ejercicio') }}">
                                            {{ __('Configuración') }}
                                        </a>
                                    </li>
                                @endpermission
                                @permission('closefiscalyear.entries')
                                    <li class="{!! set_active_menu('close-fiscal-year.entries.create') !!}">
                                        <a href="{{ route('close-fiscal-year.entries.create') }}" data-toggle="tooltip"
                                            data-placement="right" title="{{ __('Asientos de ajustes') }}">
                                            {{ __('Asientos de ajustes') }}
                                        </a>
                                    </li>
                                @endpermission
                                @permission('closefiscalyear.create')
                                    <li class="{!! set_active_menu('close-fiscal-year.registers.index') !!}">
                                        <a href="{{ route('close-fiscal-year.registers.index') }}" data-toggle="tooltip"
                                            data-placement="right"
                                            title="{{ __('Gestión de los cierres de ejercicio económico') }}">
                                            {{ __('Cierre') }}
                                        </a>
                                    </li>
                                @endpermission
                            </ul>
                        @else
                            <a href="{{ route('close-fiscal-year.registers.index') }}"
                                title="{{ __('Cierre de ejercicio actual') }}" data-toggle="tooltip"
                                data-placement="right">
                                <i class="ion-lock-combination"></i>
                                <span>{{ __('Cierre de Ejercicio') }}</span>
                            </a>
                        @endif
                    </li>
                @endif
            </ul>
        </div>
    @endif
</div>
