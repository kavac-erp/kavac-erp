<h5 class="card-title text-center mt-4">{{ __('Roles') }}</h5>
<div class="row" id="role">
    @foreach (App\Roles\Models\Role::all() as $role)
        <div class="col-md-2 text-center">
            <div class="form-group">
                <label for="" class="control-label">{{ $role->name }}</label>
                <div class="custom-control custom-switch">
                    {!! Form::checkbox('role[]', $role->id, ($user) ? $user->hasRole($role->id) : null, [
                        'class' => 'custom-control-input switch-roles', 'id' => 'role_'.$role->id,
                        'onchange' => 'relatedPermissions(this)',
                        'data-permissions' => json_encode($role->permissions->pluck('id')->toArray())
                    ]) !!}
                    <label class="custom-control-label" for="role_{{ $role->id }}"></label>
                </div>
            </div>
        </div>
    @endforeach
</div>
<h5 class="card-title text-center mt-4">{{ __('Permisos') }}</h5>
@php
    $module = "";
@endphp
<div class="row" id="permissions">
    @foreach (App\Roles\Models\Permission::orderBy('model_prefix')->get() as $permission)
        @if ($module != $permission->model_prefix)
            @php
                $module = $permission->model_prefix;
            @endphp
            <div class="col-12" style="padding:20px 0">
                <hr>
                <h6 class="card-title text-center">
                    {{ __('MÓDULO [:module]', [
                        'module' => strtoupper((substr($module, 0,1) != '0')?$module:substr($module, 1))
                    ]) }}
                </h6>
                <hr>
            </div>
        @endif
        <div class="col-md-3 text-center">
            <div class="form-group">
                <label for="" class="control-label">{{ $permission->name }}</label>
                @php
                    $userPerm = ($user) ? $permission->users()->where('user_id', $user->id)->get() : null;
                @endphp
                <div class="custom-control custom-switch">
                    {!! Form::checkbox(
                        'permission[]', $permission->id, (!is_null($userPerm) && !$userPerm->isEmpty()) ? true : null, [
                        'class' => 'custom-control-input switch-permission', 'id' => 'perm_'.$permission->id,
                        'data-roles' => json_encode($permission->roles->pluck('id')->toArray())
                    ]) !!}
                    <label class="custom-control-label" for="perm_{{ $permission->id }}"></label>
                </div>
            </div>
        </div>
    @endforeach
</div>

@section('extra-js')
    @parent
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const els = document.getElementsByClassName('switch-roles');
            Array.from(els).forEach((el) => {
                if (el.checked) {
                    relatedPermissions(el);
                }
            });
        });
        /**
        * Establece los permisos asociados a un role
        *
        * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
        */
        const relatedPermissions = (roleEl) => {
            const id = roleEl.getAttribute('id').split("_")[1];
            const perms = JSON.parse(roleEl.dataset.permissions) || [];

            perms.forEach((p) => {
                const permCheckbox = document.getElementById(`perm_${p}`);
                permCheckbox.checked = roleEl.checked;

                /* Cambia la propiedad 'disabled' según el estado del rol, si un
                rol está activado, sus permisos relacionados ahora se activan y
                bloquean en la interfaz */
                permCheckbox.disabled = roleEl.checked;
            });
        }
    </script>
@endsection