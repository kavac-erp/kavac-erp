@extends('layouts.app')

@section('content')
    @role('dev')
        @include('dev.tools-availables')
    @endrole

    @role('admin')
        @include('dashboard.users-connected')
        <audit-records
            id="helpAudit"
            help-file="{{ json_encode(get_json_resource('ui-guides/audit_list.json')) }}"
            :modules='{!! json_encode(info_modules(true)) !!}'
        >
        </audit-records>
        <restore-records
            id="helpRestore"
            help-file="{{ json_encode(get_json_resource('ui-guides/restore_list.json')) }}"
            :modules='{!! json_encode(info_modules(true)) !!}'
            route_previous="{{ url()->previous() }}"
        >
        </restore-records>
    @endrole
    @yield('dashboard')

    @if (!(bool)env('APP_TESTING', false))
        @foreach(Module::all() as $module)
            @if (Module::isEnabled($module))
                @php
                    $perm = App\Roles\Models\Permission::where('slug', strtolower($module) . '.dashboard')->first();
                    if (!$perm) {
                        continue;
                    }
                @endphp
                @if (auth()->user()->hasPermission($perm->slug))
                    @includeIf(strtolower($module) . '::index')
                @endif
            @endif
        @endforeach
    @endif
@stop

@section('extra-js')
    <script>
        $(document).ready(function() {
            $('.datatable').DataTable();
        });

        var unlockUser = function (userId) {
            axios.get(`${window.app_url}/user-unlock/${userId}`).then(response => {
                if (response.data.result) {
                    location.reload();
                }
            }).catch(error => {
                console.error(error);
            });
        }
    </script>
@stop
