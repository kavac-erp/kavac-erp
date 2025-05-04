<?php

namespace Modules\Payroll\Http\Controllers;

use App\Models\FiscalYear;
use App\Models\CodeSetting;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Modules\Payroll\Models\Institution;
use Illuminate\Contracts\Support\Renderable;
use Modules\Payroll\Models\PayrollVacationRequest;
use Modules\Payroll\Imports\VacationsRequestImport;
use Modules\Payroll\Jobs\PayrollVacationsExportJob;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Payroll\Exports\PayrollVacationRequestExport;

/**
 * @class PayrollVacationRequestController
 * @brief Controlador de solicitudes vacacionales
 *
 * Clase que gestiona las solicitudes vacacionales
 *
 * @author  Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollVacationRequestController extends Controller
{
    use ValidatesRequests;

    /**
     * Arreglo con las reglas de validación sobre los datos de un formulario
     *
     * @var array $validateRules
     */
    protected $validateRules;

    /**
     * Arreglo con los mensajes para las reglas de validación
     *
     * @var array $messages
     */
    protected $messages;

    /**
     * Define la configuración de la clase
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        /* Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:payroll.vacation.requests.list', ['only' => ['index', 'vueList']]);
        $this->middleware('permission:payroll.vacation.requests.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:payroll.vacation.requests.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:payroll.vacation.requests.delete', ['only' => 'destroy']);
        $this->middleware('permission:payroll.vacation.requests.approved', ['only' => 'approved']);
        $this->middleware('permission:payroll.vacation.requests.rejected', ['only' => 'rejected']);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'payroll_staff_id'     => ['required'],
            'vacation_period_year' => ['required'],
            'days_requested'       => ['required'],
            'start_date'           => ['required', 'before:end_date'],
            'end_date'             => ['required', 'after:start_date']
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'payroll_staff_id.required'     => 'El campo trabajador es obligatorio.',
            'vacation_period_year.required' => 'El campo año del período vacacional es obligatorio.',
            'days_requested.required'       => 'El campo días solicitados es obligatorio.',
            'start_date.required'           => 'El campo fecha de inicio de las vacaciones es obligatorio.',
            'start_date.before'           => 'El campo fecha de inicio debe ser menor a la fecha de culminación.',
            'end_date.required'             => 'El campo fecha de culminación de las vacaciones es obligatorio.',
            'end_date.after'             => 'El campo fecha de culminación debe ser mayor a la fecha de inicio.'
        ];
    }

    /**
     * Muestra un listado de las solicitudes vacacionales registradas
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('payroll::requests.vacations.index');
    }

    /**
     * Muestra el formulario para registrar una nueva solicitud vacacional
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin, payroll');
        return view('payroll::requests.vacations.create-edit', compact('isAdmin'));
    }

    /**
     * Importa los datos de solicitudes de vacaciones
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function import(Request $request): JsonResponse
    {
        /* Obtiene el usuario autenticado */
        $user =  User::where('id', auth()->user()->id)->toBase()->get()->first();

        /* Encuentra la institución del usuario */
        $profileUser = $user->profile;

        /* Encuentra la institución del usuario */
        if (($profileUser) && isset($profileUser->institution_id)) {
            $institution = Institution::find($profileUser->institution_id);
        } else {
            $institution = Institution::where('active', true)->where('default', true)->first();
        }

        /* Carga el archivo de Excel a importar y lo almacena temporalmente */
        $excelFilePath = $request->file('file')->store('', 'temporary');

        /* Crea el nombre del archivo de errores para la importación */
        $errorsFilePath = 'import' . uniqid() . '.errors';

        /* Crea el archivo de errores en el disco temporal */
        Storage::disk('temporary')->put($errorsFilePath, '');

        /* Encuentra el año fiscal activo */
        $currentFiscalYear = FiscalYear::select('year')
            ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();

        /* Importa el archivo de Excel */
        Excel::import(
            new VacationsRequestImport(
                $errorsFilePath,
                $currentFiscalYear,
                $user,
                $institution->id
            ),
            $excelFilePath,
            'temporary',
            \Maatwebsite\Excel\Excel::XLSX
        );

        /* Elimina el archivo de Excel temporal */
        Storage::disk('temporary')->delete($excelFilePath);

        return response()->json(['result' => true], 200);
    }

    /**
     * Exporta los datos de solicitudes de vacaciones
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function export()
    {
        /* Obtiene el usuario autenticado */
        $user = auth()->user();

        /* Encuentra la institución del usuario */
        $profileUser = $user->profile;

        /* Encuentra la institución del usuario */
        if (($profileUser) && isset($profileUser->institution_id)) {
            $institution = Institution::find($profileUser->institution_id)->value('acronym');
        } else {
            $institution = Institution::where('active', true)->where('default', true)->value('acronym');
        }

        $institution = Str::lower($institution);

        $pdfPath = 'tmp/solicitudes-de-vacaciones-' . now()->format('d-m-Y') .  '.xlsx';

        (new PayrollVacationRequestExport($institution))
            ->store($pdfPath)
            ->chain(
                [
                new PayrollVacationsExportJob($user, $pdfPath)
                ]
            );

        request()->session()->flash(
            'message',
            [
                'type' => 'other', 'title' => '¡Éxito!',
                'text' => 'Su solicitud esta en proceso, esto puede tardar unos ' .
                    'minutos. Se le notificara al terminar la operación',
                'icon' => 'screen-ok',
                'class' => 'growl-primary'
            ]
        );

        return response()->json(['result' => true], 200);
    }

    /**
     * Valida y registra una nueva solicitud de vacaciones
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse                Objeto con los registros a mostrar
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules, $this->messages);

        $codeSetting = CodeSetting::where('table', 'payroll_vacation_requests')->first();
        if (is_null($codeSetting)) {
            $request->session()->flash(
                'message',
                [
                    'type' => 'other', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'growl-danger',
                    'text' => 'Debe configurar previamente el formato para el código a generar'
                ]
            );
            return response()->json(['result' => false, 'redirect' => route('payroll.settings.index')], 200);
        }

        $currentFiscalYear = FiscalYear::select('year')
            ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();

        $code  = generate_registration_code(
            $codeSetting->format_prefix,
            strlen($codeSetting->format_digits),
            (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
                substr($currentFiscalYear->year, 2, 2) : date('y')) : (isset($currentFiscalYear) ?
                $currentFiscalYear->year : date('Y')),
            PayrollVacationRequest::class,
            $codeSetting->field
        );

        $user = auth()->user();
        $profileUser = $user->profile;
        if (($profileUser) && isset($profileUser->institution_id)) {
            $institution = Institution::find($profileUser->institution_id);
        } else {
            $institution = Institution::where('active', true)->where('default', true)->first();
        }

        /* Objeto asociado al modelo PayrollVacationRequest */
        $payrollVacationRequest = PayrollVacationRequest::create(
            [
            'code'                 => $code,
            'status'               => 'pending',
            'days_requested'       => $request->input('days_requested'),
            'vacation_period_year' => json_encode($request->vacation_period_year),
            'start_date'           => $request->input('start_date'),
            'end_date'             => $request->input('end_date'),
            'payroll_staff_id'     => $request->input('payroll_staff_id'),
            'institution_id'       => $institution->id
            ]
        );

        $request->session()->flash('message', ['type' => 'store']);
        return response()->json(['result' => true, 'redirect' => route('payroll.vacation-requests.index')], 200);
    }

    /**
     * Muestra los datos de la información de la solicitud de vacaciones seleccionada
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param integer $id Identificador único de la solicitud de vacaciones
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $payrollVacationRequest = PayrollVacationRequest::find($id);
        return response()->json(['record' => $payrollVacationRequest], 200);
    }

    /**
     * Muestra el formulario para actualizar la información de una solicitud vacacional
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param integer $id Identificador único del registro de solicitud de vacaciones
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        /* Objeto asociado al modelo PayrollVacationRequest */
        $payrollVacationRequest = PayrollVacationRequest::find($id);
        $roles = [];
        $user = auth()->user();
        foreach ($user->roles()->get()->toArray() ?? [] as $role) {
            array_push($roles, $role["slug"]);
        }
        return view('payroll::requests.vacations.create-edit', compact('payrollVacationRequest', 'roles'));
    }

    /**
     * Actualiza la información de la solicitud de vacaciones
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param integer                  $id      Identificador único asociado a la solicitud de vacaciones
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        /* Objeto asociado al modelo PayrollVacationRequest */
        $payrollVacationRequest = PayrollVacationRequest::find($id);
        $this->validate($request, $this->validateRules, $this->messages);

        // Verificar si se edito el campo de periodos vacacionales
        if (isset($request->old_vacation_period_year)) {
            $old_vacation = serialize($request->old_vacation_period_year);
            $new_vacation = serialize($request->vacation_period_year);

            if (($old_vacation != $new_vacation) || (strcmp($old_vacation, $new_vacation) !== 0)) {
                foreach ($request->old_vacation_period_year as $old_year) {
                    if (strpos($new_vacation, $old_year["text"])) {
                        continue;
                    }

                    // Obtener último registro anterior de solicitud con el trabajador
                    // para remover atributo old (si es que el periodo tiene dias pendientes)
                    $last_vacation_request = PayrollVacationRequest::where('payroll_staff_id', $payrollVacationRequest->payroll_staff_id)
                        ->whereIn('status', ['pending', 'approved'])
                        ->where('vacation_period_year', 'like', '%pending_days%')
                        ->where('vacation_period_year', 'like', '%' . $old_year["text"] . '%')
                        ->where('vacation_period_year', 'like', '%old%')
                        ->orderBy('created_at', 'desc');

                    if (count($last_vacation_request->get())) {
                        if (count($last_vacation_request->get()) > 1) {
                            $last_vacation_request = $last_vacation_request->first();
                        }
                        $last_vacation_year_period = json_decode($last_vacation_request->vacation_period_year);
                        // Modificar periodo vacacional para eliminar atributo viejo
                        if ($last_vacation_request) {
                            foreach ($last_vacation_year_period as $last_year) {
                                if (stripos($old_year->id, $last_year->id) !== false) {
                                    unset($last_year->old);
                                }
                            }
                            $last_vacation_request->vacation_period_year = json_encode($last_vacation_year_period);
                            $last_vacation_request->save();
                        }
                    }
                }
            }
        }

        $profileUser = auth()->user()->profile;
        if (($profileUser) && isset($profileUser->institution_id)) {
            $institution = Institution::find($profileUser->institution_id);
        } else {
            $institution = Institution::where('active', true)->where('default', true)->first();
        }

        $payrollVacationRequest->update(
            [
            'status'               => $request->status ?? $payrollVacationRequest->status,
            'days_requested'       => $request->input('days_requested'),
            'vacation_period_year' => json_encode($request->vacation_period_year),
            'start_date'           => $request->input('start_date'),
            'end_date'             => $request->input('end_date'),
            'payroll_staff_id'     => $request->input('payroll_staff_id'),
            'institution_id'       => $institution->id
            ]
        );

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('payroll.vacation-requests.index')], 200);
    }

    /**
     * Elimina una solicitud de vacaciones
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param integer $id Identificador único de la solicitud de vacaciones a eliminar
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        /* Objeto asociado al modelo PayrollVacationRequest */
        $payrollVacationRequest = PayrollVacationRequest::find($id);

        // Obtener último registro anterior de solicitud con el trabajador
        // para remover atributo old (si es que el periodo tiene dias pendientes)
        $period_years_deleted = json_decode($payrollVacationRequest->vacation_period_year);
        $last_vacation_request = PayrollVacationRequest::where('payroll_staff_id', $payrollVacationRequest->payroll_staff_id)
            ->whereIn('status', ['pending', 'approved'])
            ->where('vacation_period_year', 'like', '%pending_days%')
            ->orderBy('created_at', 'desc')->skip(1)->first();
        // Modificar periodo vacacional para eliminar atributo viejo
        if ($last_vacation_request) {
            $last_vacation_year_period = json_decode($last_vacation_request->vacation_period_year);
            foreach ($period_years_deleted as $deleted_year) {
                foreach ($last_vacation_year_period as $old_year) {
                    if (stripos($deleted_year->id, $old_year->id) !== false) {
                        unset($old_year->old);
                    }
                }
            }
            $last_vacation_request->vacation_period_year = json_encode($last_vacation_year_period);
            $last_vacation_request->save();
        }

        $payrollVacationRequest->delete();

        return response()->json(['record' => $payrollVacationRequest, 'message' => 'Success'], 200);
    }

    /**
     * Muestra un listado de las solicitudes vacacionales registradas
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar
     */
    public function vueList()
    {
        $user = auth()->user();
        $profileUser = $user->profile;
        if (($profileUser) && isset($profileUser->institution_id)) {
            $institution = Institution::find($profileUser->institution_id);
        } else {
            $institution = Institution::where('active', true)->where('default', true)->first();
        }
        if ($user->hasRole('admin, payroll')) {
            $records = PayrollVacationRequest::where('institution_id', $institution->id)
                ->get();
        } else {
            $records = PayrollVacationRequest::where('institution_id', $institution->id)
                ->whereHas(
                    'payrollStaff',
                    function ($query) use ($profileUser) {
                        $query->whereHas(
                            'payrollEmployment',
                            function ($q) use ($profileUser) {
                                $q->where('id', $profileUser->employee_id);
                            }
                        );
                    }
                )
                ->get();
        }
        return response()->json(['records' => $records], 200);
    }

    /**
     * Muestra un listado de las solicitudes vacacionales pendientes registradas
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar
     */
    public function vuePendingList()
    {
        $user = auth()->user();
        $profileUser = $user->profile;
        if (($profileUser) && isset($profileUser->institution_id)) {
            $institution = Institution::find($profileUser->institution_id);
        } else {
            $institution = Institution::where('active', true)->where('default', true)->first();
        }
        if ($user->hasRole('admin, payroll')) {
            $records = PayrollVacationRequest::where('institution_id', $institution->id)
                ->where('status', 'pending')
                ->get();
        } else {
            $records = PayrollVacationRequest::where('institution_id', $institution->id)
                ->where('status', 'pending')->whereHas(
                    'payrollStaff',
                    function ($query) use ($profileUser) {
                        $query->whereHas(
                            'payrollEmployment',
                            function ($q) use ($profileUser) {
                                $q->where('id', $profileUser->employee_id);
                            }
                        );
                    }
                )
                ->get();
        }
        return response()->json(['records' => $records], 200);
    }

    /**
     * Muestra el listado de solicitudes de vacaciones según el trabajador seleccionado
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param integer $id Identificador único del trabajador registrado
     *
     * @return \Illuminate\Http\JsonResponse           Objeto con los registros a mostrar
     */
    public function getVacationRequests($staff_id)
    {
        $payrollVacationRequest = PayrollVacationRequest::where('payroll_staff_id', $staff_id)
            ->whereIn('status', ['pending', 'approved', 'suspended'])->get();
        return response()->json(['records' => $payrollVacationRequest], 200);
    }

    /**
     * Actualiza la información de la solicitud de vacaciones
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     * @param integer                  $id      Identificador único asociado a la solicitud de vacaciones
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function review(Request $request, $id)
    {
        /* Objeto asociado al modelo PayrollVacationRequest */
        $payrollVacationRequest = PayrollVacationRequest::find($id);
        $this->validate($request, $this->validateRules, $this->messages);

        $profileUser = auth()->user()->profile;
        if (($profileUser) && isset($profileUser->institution_id)) {
            $institution = Institution::find($profileUser->institution_id);
        } else {
            $institution = Institution::where('active', true)->where('default', true)->first();
        }
        $payrollVacationRequest->update(
            [
            'status'               => $request->input('status'),
            'status_parameters'    => json_encode($request->input('status_parameters')),
            ]
        );

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('payroll.vacation-requests.index')], 200);
    }

    /**
     * Obtiene la solicitud de vacación aprobada
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     * @param integer $id Identificador de la solicitud de vacación
     * @param boolean $check_permission Determina si el usuario tiene permiso para realizar la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function approved(Request $request, $id, $check_permission = false)
    {
        if (!$check_permission) {
            $payrollVacationRequest = PayrollVacationRequest::find($id);

            //Obtener registros anteriores de solicitud con el trabajador para validar períodos anuales anteriores
            foreach ($request->vacation_period_year as $period_year) {
                $oldPayrollVacationRequest = PayrollVacationRequest::where(
                    'payroll_staff_id',
                    $payrollVacationRequest->payroll_staff_id
                )
                    ->whereIn('status', ['pending', 'approved', 'suspended'])
                    ->where('code', '!=', $payrollVacationRequest->code)
                    ->where('vacation_period_year', 'like', '%' . $period_year['text'] . '%')
                    ->where('vacation_period_year', 'not like', '%old%')->first();

                if ($oldPayrollVacationRequest) {
                    // Modificar periodo vacacional como viejo
                    // (para usar como validacion en la carga de periodos vacacionales)
                    $old_vacation_period_years = json_decode($oldPayrollVacationRequest->vacation_period_year);
                    foreach ($request->vacation_period_year as $request_period_year) {
                        foreach ($old_vacation_period_years as $old_year) {
                            if (stripos($old_year->id, $request_period_year['text']) !== false) {
                                $old_year->old = 1;
                            }
                        }
                    }
                    $oldPayrollVacationRequest->vacation_period_year = json_encode($old_vacation_period_years);
                    $oldPayrollVacationRequest->save();
                }
            }

            $payrollVacationRequest->status = 'approved';
            $payrollVacationRequest->status_parameters = json_encode($request->reincorporation_date);
            $payrollVacationRequest->save();
        }

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('payroll.vacation-requests.index')], 200);
    }

    /**
     * Obtiene la solicitud de vacación rechazada
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     * @param integer $id Identificador de la solicitud de vacación
     * @param boolean $check_permission Determina si el usuario tiene permiso para realizar la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function rejected(Request $request, $id, $check_permission = false)
    {
        if (!$check_permission) {
            $payrollVacationRequest = PayrollVacationRequest::find($id);

            $payrollVacationRequest->status = 'rejected';
            $payrollVacationRequest->status_parameters = json_encode($request->motive);
            $payrollVacationRequest->save();
        }

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('payroll.vacation-requests.index')], 200);
    }
}
