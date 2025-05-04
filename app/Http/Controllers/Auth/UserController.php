<?php

namespace App\Http\Controllers\Auth;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Profile;
use App\Roles\Models\Role;
use Illuminate\Http\Request;
use App\Roles\Models\RoleUser;
use App\Roles\Models\Permission;
use Illuminate\Http\JsonResponse;
use App\Models\NotificationSetting;
use App\Http\Controllers\Controller;
use App\Roles\Models\PermissionUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Notifications\UserRegistered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

/**
 * @class UserController
 * @brief Gestiona información de usuarios
 *
 * Controlador para gestionar usuarios
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UserController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Listado de todos los registros de los usuarios
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin.setting-users');
    }

    /**
     * Muestra el formulario para crear un nuevo registro de usuario
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $header = [
            'route' => 'users.store',
            'method' => 'POST',
            'role' => 'form',
        ];

        // Listado de los perfiles con datos de empleados
        $allPersons = Profile::whereNotNull('employee_id')
            ->whereNull('user_id')
            ->get(['id', 'first_name', 'last_name']);

        $institutions = template_choices('App\Models\Institution', 'name');

        return view('auth.register', compact('header', 'institutions', 'allPersons'));
    }

    /**
     * Registra un nuevo usuario
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  Request  $request
     *
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'first_name' => ['required_without:staff'],
                'institution_id' => ['required'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'username' => ['required', 'string', 'max:25', 'unique:users'],
                'role' => ['required_without:permission', 'array'],
                'permission' => ['required_without:role', 'array'],
            ],
            [
                'first_name.required_without' => __(
                    'El campo nombre es requerido cuando no se ha seleccionado un empleado'
                ),
                'institution_id.required' => __('El campo institución es obligatorio'),
                'role.required_without' => __(
                    'El campo roles es obligatorio cuando no se asignado al menos un permiso.'
                ),
                'permission.required_without' => __(
                    'El campo permisos es obligatorio cuando no se asignado al menos un rol.'
                ),
            ]
        );

        if ($request->staff) {
            // Objeto con información del perfil del usuario
            $profile = Profile::find($request->staff);
        }

        // Hash con una contraseña generada aleatoriamente
        $password = generate_hash();
        // Objeto con información del usuario registrado
        $user = User::create([
            'name' => (!isset($profile)) ? $request->first_name : trim($profile->first_name . ' ' . $profile->last_name ?? ''),
            'email' => $request->email,
            'username' => $request->username,
            'password' => bcrypt($password),
            'level' => 2,
            'email_verified_at' => (config('active-directory.enabled')) ? Carbon::now() : null,
        ]);

        if (!$request->staff) {
            // Instancia al modelo de perfil de usuario
            $profile = new Profile();
            $profile->first_name = $request->first_name;
        }

        $profile->user_id = $user->id;
        $profile->institution_id = $request->institution_id;
        $profile->save();

        if (isset($request->role)) {
            // Asigna los roles al usuario
            $user->syncRoles($request->role);
        }
        if (isset($request->permission)) {
            // Asigna los permisos al usuario
            $user->syncPermissions($request->permission);
        }

        $user->notify(new UserRegistered($user, $password));
        if (!config('active-directory.enabled')) {
            $user->sendEmailVerificationNotification();
        }
        $request->session()->flash('message', ['type' => 'store']);

        return redirect()->route('users.index');
    }

    /**
     * Muestra información acerca del usuario
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  User  $user
     *
     * @return \Illuminate\View\View
     */
    public function show(User $user)
    {
        // Objeto con información del usuario
        $model = $user;
        // Atributos del formulario para la gestión de usuarios
        $header = [
            'route' => ['users.update', $user->id], 'method' => 'PUT', 'role' => 'form', 'class' => 'form',
        ];
        $directory = User::with(['profile' => function ($q) {
            $q->with('image');
        }])->where(
            'id',
            '<>',
            auth()->user()->id
        )->where(
            'username',
            '<>',
            'admin'
        )->get()->toArray();
        return view('auth.profile', compact('model', 'header', 'directory'));
    }

    /**
     * Muestra el formulario para actualizar información de un usuario
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  User  $user
     *
     * @return \Illuminate\View\View
     */
    public function edit(User $user)
    {
        $header = [
            'route' => ['users.update', $user->id],
            'method' => 'PUT',
            'role' => 'form',
        ];

        // Recuperar el modelo con la relación 'profile'
        $model = $user->where('id', $user->id)->with('profile')->first();

        // Recuperar la lista de empleados que cumplen las condiciones
        $allPersons = Profile::where(function ($query) use ($user) {
            $query->whereNull('user_id')->orWhere('user_id', $user->id);
        })->get(['id', 'first_name', 'last_name', 'employee_id']);

        // Construir la lista de nombres completos
        $allPersons = $allPersons->mapWithKeys(function ($person) {
            return [$person->employee_id => $person->first_name . ' ' . $person->last_name];
        });

        $institutions = template_choices('App\Models\Institution', 'name');

        return view(
            'auth.register',
            compact('header', 'model', 'institutions', 'allPersons')
        );
    }

    /**
     * Actualiza la información del usuario
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  Request  $request
     * @param  User  $user
     *
     * @return RedirectResponse
     */
    public function update(Request $request, User $user)
    {
        if (!$request->has('source') || $request->source !== 'profile') {
            $this->validate($request, [
                'first_name' => ['required_without:staff'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
                'username' => ['required', 'string', 'max:25', 'unique:users,username,' . $user->id],
                'role' => ['required_without:permission', 'array'],
                'permission' => ['required_without:role', 'array'],
            ], [
                'first_name.required_without' => __(
                    'El campo nombre es requerido cuando no se ha seleccionado un empleado'
                ),
            ]);

            $user->name = $request->first_name;
            $user->email = $request->email;
            $user->username = $request->username;
            $user->save();

            $profile = Profile::where('user_id', $user->id)->first();

            if (!$request->staff && !$profile) {
                // Crear un nuevo perfil si no hay un empleado seleccionado y no hay perfil existente
                $profile = new Profile();
            } elseif ($request->staff && $profile) {
                // Desasociar el perfil del usuario anterior
                $profile->user_id = null;
                $profile->save();

                // Encontrar o crear el perfil para el nuevo empleado
                $profile = Profile::where('employee_id', $request->staff)->first() ?? new Profile();
            }

            // Asignar los valores al perfil
            $profile->user_id = $user->id;
            $profile->institution_id = $request->institution_id ?? $profile->institution_id ?? null;
            $profile->first_name = $request->first_name;
            $profile->save();

            $roleUser = RoleUser::where('user_id', $user->id)->get();
            if ($roleUser) {
                foreach ($roleUser as $ru) {
                    $ru->delete();
                }
            }

            $permissionUser = PermissionUser::where('user_id', $user->id)->get();
            if ($permissionUser) {
                foreach ($permissionUser as $pu) {
                    $pu->delete();
                }
            }

            if (isset($request->role)) {
                $user->detachAllRoles();
                $user->syncRoles($request->role);
            }

            if (isset($request->permission)) {
                $user->detachAllPermissions();
                $user->syncPermissions($request->permission);
            }
        }

        if ($request->has('password') && $request->password !== null) {
            $this->validate($request, [
                'password' => ['min:6', 'confirmed'],
                'password_confirmation' => ['min:6', 'required_with:password'],
                'complexity-level' => ['numeric', 'min:43', 'max:100'],
            ], [
                'confirmed' => __('La contraseña no coincide con la verificación'),
                'required_with' => __('Debe confirmar la nueva contraseña'),
                'complexity-level.numeric' => __('No es posible determinar la complejidad de la contraseña'),
                'complexity-level.min' => __(
                    'Contraseña muy débil. Intente incorporar símbolos, letras y números, ' .
                        'en combinación con mayúsculas y minúsculas. ' .
                        'Por medidas de seguridad de los datos y de la aplicación, debe indicar una contraseña robusta'
                ),
            ]);

            $user->password = bcrypt($request->input('password'));
            $user->save();
        }

        $request->session()->flash('message', ['type' => 'update']);

        return redirect()->route('users.index');
    }

    /**
     * Elimina un usuario
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  User  $user
     *
     * @return JsonResponse
     */
    public function destroy(User $user)
    {
        if (auth()->user()->id === $user->id) {
            return response()->json([
                'result' => false,
                'message' => __('Usted no puede eliminarse a si mismo')
            ], 200);
        }

        $user->delete();
        session()->flash('message', ['type' => 'destroy']);
        return response()->json(['result' => true, 'record' => $user, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene los roles y permisos disponibles
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    JsonResponse                    JSON con información de los roles y permisos
     */
    public function getRolesAndPermissions()
    {
        // Objeto con información de roles registrados
        $roles = Role::with('permissions')->get();
        // Objeto con información de los permisos registrados
        $permissions = Permission::with('roles')->orderBy('model_prefix')->get();
        return response()->json(['result' => true, 'roles' => $roles, 'permissions' => $permissions], 200);
    }

    /**
     * Configuración de permisos asociados a roles de usuarios
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param Request  $request Datos de la petición
     *
     * @return JsonResponse Retorna la vista que ejecuta la acción junto con el mensaje al usuario
     */
    public function setRolesAndPermissions(Request $request)
    {
        $this->validate($request, [
            'roles_attach_permissions' => 'required|array|min:1',
        ], [
            'roles_attach_permissions.required' => __('Se requiere asignar al menos un permiso a un rol'),
        ]);

        foreach (Role::all() as $r) {
            $r->detachAllPermissions();
        }

        // Arreglo con listado de roles y permisos asociados
        $rolesAndPerms = [];

        // Crea un arreglo de permisos asociados a los diferentes roles seleccionados
        foreach ($request->roles_attach_permissions as $role_perm) {
            list($role_id, $perm_id) = explode("_", $role_perm);
            if (!array_key_exists($role_id, $rolesAndPerms)) {
                $rolesAndPerms[$role_id] = [];
            }
            array_push($rolesAndPerms[$role_id], $perm_id);
        }

        // Asigna los distintos permisos a los roles
        foreach ($rolesAndPerms as $roleId => $roleValues) {
            $role = Role::find($roleId);
            if ($role) {
                $role->syncPermissions($roleValues);
            }
        }

        return response()->json(['result' => true], 200);
    }

    /**
     * Muestra el formulario para la asignación de roles y permisos a usuarios
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  User   $user Modelo de Usuario
     *
     * @return \Illuminate\View\View
     */
    public function assignAccess(User $user)
    {
        if ($user->hasRole('admin') && $user->permissions()->get()->isEmpty()) {
            /** Si el usuario es administrador y no tiene registro de permisos */
            $user->syncPermissions(Permission::select('id')->get()->map(function ($p) {
                return $p->id;
            })->toArray());
        }

        return view('admin.setting-user-access', compact('user'));
    }

    /**
     * Asigna permisos de acceso a los usuarios del sistema
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param Request $request Objeto con los datos de la petición
     */
    public function setAccess(Request $request)
    {
        // Arreglo con reglas de validación
        $rules = [
            'user' => ['required'],
            'role' => ['required_without:permission', 'array'],
            'permission' => ['required_without:role', 'array', 'min:1'],
        ];
        // Arreglo con mensajes de validación
        $messages = [
            'user.required' => __('Se requiere de un usuario para asignar roles y permisos'),
            'role.max' => __('Solo puede asignar un rol al usuario'),
            'permission.min' => __('Se requiere al menos un permiso asignado al usuario'),
        ];

        // Objeto con información de un usuario
        $user = User::find($request->user);
        if (!$user && $request?->user?->id) {
            $user = User::find($request->user->id);
        }

        if (isset($request->role)) {
            foreach ($request->role as $role) {
                if (Role::find($role)->permissions->isEmpty()) {
                    $rules['permission'] = str_replace('required_without:role', 'required', $rules['permission']);
                    if (count($request->role) > 1) {
                        // Mensaje a mostrar en la validación de roles
                        $msg = __(
                            'Uno de los roles seleccionados no tiene permisos asignados, ' .
                                'debe indicar los permisos de acceso'
                        );
                    } else {
                        // Mensaje a mostrar en la validación de roles
                        $msg = __(
                            'El rol seleccionado no tiene permisos asignados, ' .
                            'debe indicar los permisos de acceso'
                        );
                    }
                    $messages['permission.required'] = $msg;
                    break;
                }
            }
        }

        $this->validate($request, $rules, $messages);

        $roleUser = RoleUser::where('user_id', $user->id)->get();
        foreach ($roleUser as $ru) {
            $ru->delete();
        }
        $permissionUser = PermissionUser::where('user_id', $user->id)->get();
        foreach ($permissionUser as $pu) {
            $pu->delete();
        }

        if (isset($request->role)) {
            $user->syncRoles($request->role);
        }
        if (isset($request->permission)) {
            $user->syncPermissions($request->permission);
        }

        $request->session()->flash('message', ['type' => 'store']);

        if ($request->ajax() || $request->api == true) {
            return response()->json(['result' => true], 200);
        }

        return redirect()->route('index');
    }

    /**
     * Muestra información del usuario
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  User $user Objero que abstrae información del usuario
     *
     * @return JsonResponse     Devuelve los datos asociados al usuario
     */
    public function info(User $user)
    {
        // Arreglo con las relaciones a incorporar en la información del usuario
        $with = [];
        if ($user->profile !== null) {
            $with[] = 'profile';
        }
        if ($user->roles !== null) {
            $with[] = 'roles';
        }
        if ($user->permissions !== null) {
            $with[] = 'permissions';
        }

        if (!empty($with)) {
            $user->with($with);
        }

        return response()->json([
            'result' => true, 'user' => $user, 'permissions' => $user->getPermissions(),
        ], 200);
    }

    /**
     * Muestra un listado de roles y permisos de usuario
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return     \Illuminate\View\View   Devuelve la vista correspondiente para mostrar el listado de roles y permisos
     */
    public function indexRolesPermissions()
    {
        return view('admin.settings-access');
    }

    /**
     * Gestiona la configuración de la cuenta de un usuario
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return     \Illuminate\View\View Devuelve la vista para la configuración de la cuenta de usuario
     */
    public function userSettings()
    {
        // Objeto con información del usuario autenticado en la aplicación
        $user = auth()->user();
        // Objeto con información de los permisos asociados a un usuario
        $userPermissions = $user->getPermissions()->where('slug', '<>', '')->pluck('slug')->toArray();
        // Arreglo con información de los permisos asociados a notificaciones de usuarios
        $arr = array_filter($userPermissions, function ($value, $index) {
            return strpos($value, 'notify') !== false;
        }, ARRAY_FILTER_USE_BOTH);
        // Arreglo de permisos para notificaciones
        $userPermissions = $arr;

        // Objeto con información de la configuración de notificaciones
        $notifySettings = NotificationSetting::whereIn(
            'perm_required',
            $userPermissions
        )->orWhereNull('perm_required')->get();

        // Arreglo de notificaciones establecidas por el usuario
        $myNotifications = auth()->user()->notificationSettings()->select('slug')->get();
        $configuredNotify = [];
        foreach ($myNotifications as $myNotify) {
            $configuredNotify[] = $myNotify->slug;
        }

        // Arreglo con los atributos del formulario para el registro de configuraciones del usuario
        $header_general_settings = [
            'route' => 'set.my.settings', 'method' => 'POST', 'role' => 'form', 'class' => 'form',
        ];
        // Arreglo con los atributos del formulario para la configuraciones de notificaciones del usuario
        $header_notify_settings = [
            'route' => 'set.my.notifications', 'method' => 'POST', 'role' => 'form', 'class' => 'form',
        ];
        return view('auth.my-settings', compact(
            'user',
            'notifySettings',
            'header_notify_settings',
            'header_general_settings',
            'configuredNotify'
        ));
    }

    /**
     * Establece la configuración personalizada de un usuario
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param      Request            $request   Objeto con datos de la petición
     *
     * @return     RedirectResponse     redirecciona a la página de configuración del usuario
     */
    public function setUserSettings(Request $request)
    {
        // Objeto con información de un usuario
        $user = User::find(auth()->user()->id);
        $user->lock_screen = (!is_null($request->lock_screen));
        $user->time_lock =  ((bool) $request->lock_screen)
            ? (($request->time_lock > 0) ? $request->time_lock : 10)
            : 0;
        $user->save();
        $request->session()->flash('message', ['type' => 'store']);

        return redirect()->route('my.settings');
    }

    /**
     * Establece la configuración de un usuario desde el panel administrativo
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Request                $request    Datos de la petición
     */
    public function setAdminUserSetting(Request $request)
    {
        $user = User::find($request->user_id);
        $user->blocked_at = ($request->blocked_at === true) ? Carbon::now() : null;
        $user->active = ($request->active === true) ? true : false;
        $user->save();
        return response()->json(['result' => true], 200);
    }

    /**
     * Gestiona la configuración de notificaciones establecida por el usuario
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param      Request               $request    Objeto con información de la petición
     */
    public function setMyNotifications(Request $request)
    {
        // Objeto con información de las configuraciones del usuario a establecer
        $fields = $request->all();

        if (count($fields) > 1) {
            auth()->user()->notificationSettings()->detach();
            // Arreglo con los identificadores de las configuraciones de notificaciones
            $notifications = [];
            foreach ($fields as $keyField => $valueField) {
                if ($keyField === '_token') {
                    continue;
                }
                // Objeto con información de la configuración de notificación
                $notifySetting = NotificationSetting::where('slug', $keyField)->first();

                if ($notifySetting) {
                    array_push($notifications, $notifySetting->id);
                }
            }
            auth()->user()->notificationSettings()->sync($notifications);
        }

        $request->session()->flash('message', ['type' => 'store']);
        return redirect()->route('my.settings');
    }

    /**
     * Obtiene información acerca de la pantalla de bloqueo del sistema
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return     JsonResponse     Devuelve los datos correspondientes a la pantalla de bloqueo
     */
    public function getLockScreenData()
    {
        $user = auth()->user();
        return response()->json(['lock_screen' => $user->lock_screen, 'time_lock' => $user->time_lock], 200);
    }

    /**
     * Actualiza información de la pantalla de bloqueo del sistema
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param      Request          $request    Datos de la petición
     *
     * @return      JsonResponse     Devuelve el resultado de la operación
     */
    public function setLockScreenData(Request $request)
    {
        // Objeto con información de un usuario
        $user = User::find(auth()->user()->id);
        $user->lock_screen = $request->lock_screen;
        $user->save();
        return response()->json(['result' => true], 200);
    }

    /**
     * Realiza las gestiones necesarias para desbloquear la pantalla del sistema
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param      Request          $request    Datos de la petición
     *
     * @return     JsonResponse     Devuelve el resultado de la operación
     */
    public function unlockScreen(Request $request)
    {
        // Objeto con información de un usuario
        $user = User::where('username', $request->username)->first();

        // Verifica si la contraseña es correcta, de lo contrario retorna falso
        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['result' => false], 200);
        }

        if (!auth()->check()) {
            // Objeto con información de las credenciales de acceso de un usuario
            $userCredentials = $request->only('username', 'password');
            if (!Auth::attempt($userCredentials)) {
                return response()->json(['result' => false], 200);
            }
        }

        // Actualiza el campo que determina si la pantalla de bloqueo esta o no activada
        $user->lock_screen = false;
        $user->save();

        return response()->json(['result' => true, 'new_csrf' => csrf_token()], 200);
    }

    /**
     * Desbloquea un usuario
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Request    $request    Datos de la petición
     * @param     User       $user       Usuario a desbloquear
     *
     * @return    JsonResponse$user->blocked_at = \Illuminate\Support\Facades\Date::setNull();$user->blocked_at = \Illuminate\Support\Facades\Date::setNull();
     */
    public function unlock(Request $request, User $user)
    {
        $user->blocked_at = null;
        $user->save();
        $request->session()->flash('message', ['type' => 'other', 'text' => 'Usuario desbloqueado']);
        return response()->json(['result' => true], 200);
    }

    /**
     * Obtiene información de todos los usuarios registrados en el sistema
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    JsonResponse
     */
    public function getAll()
    {
        $users = User::select('id', 'name')->get();
        return response()->json(['result' => true, 'records' => $users]);
    }

    /**
     * Envía nuevas credenciales de acceso al usuario
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return JsonResponse
     */
    public function sendCredentials(Request $request)
    {
        $user = User::find($request->id);
        if (!$user) {
            return response()->json(['result' => false]);
        }
        $password = generate_hash();
        $user->password = Hash::make($password);
        $user->save();
        $user->notify(new UserRegistered($user, $password, true));
        return response()->json(['result' => true]);
    }

    /**
     * Obtiene la información de las sesiones activas e inactivas
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  \Illuminate\Http\Request $request Datos de la petición
     *
     * @return JsonResponse
     */
    public function getExpiredSessions(Request $request)
    {
        if (Auth::check()) {
            // La sesión del usuario está activa
            return response()->json(['result' => true, 'active' => true]);
        }

        // La sesión del usuario no está activa
        return response()->json(['result' => true, 'active' => false], 401);
    }

    /**
     * Restaurar registro de usuario eliminado
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  string|integer $userId Identificador del usuario a restaurar
     *
     * @return JsonResponse
     */
    public function restore($userId)
    {
        $user = User::withTrashed()->find($userId);
        $user->restore();
        $user->profile->restore();
        return response()->json(['result' => true]);
    }
}
