<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Traits\ModelsTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OwenIt\Auditing\Models\Audit;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

/**
 * @class AppManagementController
 * @brief Gestiona los procesos, registros, etc., de la aplicación, de uso exclusivo para el administrador
 *
 * Controlador para gestionar procesos y registros de la aplicación
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AppManagementController extends Controller
{
    use ModelsTrait;

    /**
     * Método constructor de la clase
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     */
    public function __construct()
    {
        // Restringe el acceso solo a usuarios con el rol admin
        $this->middleware('role:admin');
    }

    /**
     * Método para ordenar por fecha de borrado
     *
     * @author     Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
     */
    public function cmp($a, $b)
    {
        $aDate = strtotime($a['deleted_at']);
        $bDate = strtotime($b['deleted_at']);
        return $aDate < $bDate;
    }

    /**
     * Obtiene un listado de los últimos 20 registros eliminados
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Request $request  Objeto con información de la petición
     *
     * @return    JsonResponse      Objeto Json con datos de respuesta a la petición
     */
    public function getDeletedRecords(Request $request)
    {
        // Arreglo con los registros eliminados
        $trashed = [];

        if (Cache::has('deleted_records')) {
            $deletedRecords = Cache::get('deleted_records');
            if ($request->start_delete_at) {
                $deletedRecords = $deletedRecords->filter(function ($deleted) use ($request) {
                    return $deleted->deleted_at->format('Y-m-d') >= $request->start_delete_at;
                });
            }
            if ($request->end_delete_at) {
                $deletedRecords = $deletedRecords->filter(function ($deleted) use ($request) {
                    return $deleted->deleted_at->format('Y-m-d') <= $request->end_delete_at;
                });
            }
            if ($request->module_delete_at) {
                $deletedRecords = $deletedRecords->filter(function ($deleted) use ($request) {
                    return strpos(get_class($deleted), $request->module_delete_at) !== false;
                });
            }
            if (!$deletedRecords->isEmpty()) {
                $trashed = $this->setDeletedRecords($trashed, $deletedRecords);
                // Si ya dispone de un listado de 20 registros, se detiene y se retorna la consulta
                if ($deletedRecords->count() >= 20) {
                    return response()->json(['result' => true, 'records' => $trashed]);
                }
            }
        } else {
            foreach ($this->getModels() as $model_name) {
                // Nombre del modelo del cual se va a buscar registros eliminados
                $model = (new $model_name());
                try {
                    if ($this->isModelSoftDelete($model)) {
                        if ($request->start_delete_at) {
                            $model = $model->whereDate('deleted_at', '>=', $request->start_delete_at);
                        }
                        if ($request->end_delete_at) {
                            $model = $model->whereDate('deleted_at', '<=', $request->end_delete_at);
                        }
                        if ($request->module_delete_at && strpos($model_name, $request->module_delete_at) === false) {
                            continue;
                        }
                        // Objeto con información de registros eliminados
                        $filtered = $model->onlyTrashed()->orderBy('deleted_at', 'desc');
                        // Objeto con información de los registros eliminados
                        $deleted = $filtered->toBase()->get();
                        if (!$deleted->isEmpty()) {
                            /** Si ya dispone de un listado de 20 registros, se detiene y se retorna la consulta */
                            /*if (count($trashed) >= 20) {
                                break;
                            }*/

                            $trashed = $this->setDeletedRecords($trashed, $deleted, $model_name);
                        }
                    }
                } catch (Exception $e) {
                    Log::error($e->getMessage());
                    continue;
                }
            }
        }

        usort($trashed, array($this, 'cmp'));

        return response()->json(['result' => true, 'records' => $trashed]);
    }

    /**
     * Establece un listado de registros eliminados
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  array          $trashed    Arreglo en el cual agregar los registros eliminados
     * @param  array|object   $deleted    Arreglo de registros eliminados
     * @param  string         $model_name Nombre del modelo
     *
     * @return array  Arreglo con el listado de registros eliminados
     */
    public function setDeletedRecords($trashed, $deleted, $model_name = null)
    {
        foreach ($deleted as $del) {
            // Texto con las etiquetas html que contiene los registros eliminados
            $regs = '<div class="row">';

            foreach ($del as $attr => $value) {
                if (
                    str_contains($attr, 'password') ||
                    str_contains($attr, 'key') ||
                    str_contains($attr, 'token') ||
                    empty($value)
                ) {
                    continue;
                }
                $regs .= "<div class='col-6 break-words'><b>$attr:</b> $value</div>";
            }
            $regs .= '</div>';
            array_push($trashed, [
                'id' => secure_record($del->id),
                'deleted_at' => Carbon::parse($del->deleted_at)->format("d-m-Y"),
                'module' => $model_name ?? get_class($del),
                'registers' => $regs
            ]);
        }
        return $trashed;
    }

    /**
     * Restaura un archivo eliminado
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Request $request  Objeto con datos de la petición
     *
     * @return    JsonResponse      Objeto Json con datos de respuesta a la petición
     */
    public function restoreRecord(Request $request)
    {
        $this->validate($request, [
            'module' => ['required'],
            'id' => ['required']
        ]);

        // Nombre del modelo del cual se van a restaurar los registros
        $model = $request->module;
        // Hash con el identificador del registro a restaurar
        $id = secure_record($request->id, true);

        ($model == "Modules\Payroll\Models\PayrollStaff") ?
            restoreSoftDeletedRelatedModels($model, $id)
            : $model::withTrashed()->find($id)->restore();

        return response()->json(['result' => true], 200);
    }

    /**
     * Obtiene un listado de registros a auditar
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Request $request  Objeto con información de la petición
     *
     * @return    JsonResponse      Objeto Json con datos de respuesta a la petición
     */
    public function getAuditRecords(Request $request)
    {
        // Instancia la clase para buscar datos de auditoria
        $auditables = new Audit();
        $start_date = $request->input('query')['start_date'] ?? '';
        $end_date = $request->input('query')['end_date'] ?? '';
        $user = $request->input('query')['user'] ?? '';
        $module_restore = $request->input('query')['module_restore'] ?? '';

        if ($start_date) {
            $auditables = $auditables->whereDate('created_at', '>=', $start_date);
        }
        if ($end_date) {
            $auditables = $auditables->whereDate('created_at', '<=', $end_date);
        }
        if ($user) {
            // Objeto con información del usuario a consultar
            $users = User::where('name', 'like', "{$user}%")
                ->orWhere('name', 'like', "%{$user}%")
                ->orWhere('name', 'like', "%{$user}")
                ->orWhere('username', 'like', "{$user}%")
                ->orWhere('username', 'like', "%{$user}%")
                ->orWhere('username', 'like', "%{$user}")->get('id');

            if (!$users->isEmpty()) {
                $auditables = $auditables->whereIn('user_id', $users);
            } else {
                return response()->json(['result' => false, 'message' => __('El usuario no está registrado')], 200);
            }
        }
        if ($module_restore) {
            $auditables = $auditables->where('auditable_type', 'like', "{$module_restore}%")
                ->orWhere('auditable_type', 'like', "%{$module_restore}%")
                ->orWhere('auditable_type', 'like', "%{$module_restore}");
        }

        // Arreglo con registros a auditar según la acción ejecutada
        $records = [];
        if ($request->orderBy) {
            switch ($request->orderBy) {
                case "ip":
                    $auditables = $auditables->orderBy("ip_address", ($request->ascending) ? 'asc' : 'desc');
                    break;
                case "date":
                    $auditables = $auditables->orderBy("created_at", ($request->ascending) ? 'asc' : 'desc');
                    break;
                case "users":
                    $auditables = $auditables->whereHas("user", function ($q) use ($request) {
                        $q->orderBy("name", ($request->ascending) ? 'asc' : 'desc');
                    });
                    break;
                default:
                    $auditables = $auditables->orderBy("id", ($request->ascending) ? 'asc' : 'desc');
            }
        }

        $auditables = $auditables->paginate($request->limit);

        foreach ($auditables->items() as $audit) {
            if ($audit->user_id !== null) {
                switch ($audit->event) {
                    case 'created':
                        // texto con la clase text-success
                        $registerClass = 'text-success';
                        break;
                    case 'deleted':
                        // texto con la clase text-danger
                        $registerClass = 'text-danger';
                        break;
                    case 'restored':
                        // texto con la clase text-info
                        $registerClass = 'text-info';
                        break;
                    case 'updated':
                        // texto con la clase text-warning
                        $registerClass = 'text-warning';
                        break;
                    default:
                        // texto con la clase text-default
                        $registerClass = 'text-default';
                        break;
                }

                // Texto con la clase badge a usar
                $badgeClass = str_replace('text', 'badge', $registerClass);
                // Texto con el modelo de usuario a utilizar
                $model_user = ($audit->user_type === "App\User") ? User::class : $audit->user_type;
                // Objeto con información del usuario
                $user = $model_user::find($audit->user_id);

                // Nombre completo del usuario
                $name = ($user) ? $user->name : '';
                // Nombre de usuario con el cual accede a la aplicación
                $username = ($user) ? $user->username : '';

                array_push($records, [
                    'id' => secure_record($audit->id),
                    'status' => '<i class="ion-android-checkbox-blank ' . $registerClass . '"></i>',
                    'date' => $audit->created_at->format('d-m-Y h:i:s A'),
                    'ip' => $audit->ip_address,
                    'module' => $audit->auditable_type,
                    'users' => "<b>Nombre:</b> $name<br><b>Usuario:</b> $username"
                ]);
            }
        }
        return response()->json(
            [
                'result' => true,
                'data'   => $records,
                'count'  => $auditables->total()
            ],
            200
        );
    }

    /**
     * Obtiene detalles de un registro seleccionado
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Request $request  Objeto con datos de la petición
     *
     * @return    JsonResponse      Objeto Json con detalles del registro
     */
    public function getAuditDetails(Request $request)
    {
        $this->validate($request, [
            'id' => ['required']
        ]);
        // Hash con el identificador del registro
        $id = secure_record($request->id, true);

        // Objeto con información de auditoria
        $audit = Audit::find($id);

        return response()->json(['result' => true, 'audit' => $audit], 200);
    }
}
