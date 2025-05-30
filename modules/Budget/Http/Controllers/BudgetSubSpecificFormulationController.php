<?php

namespace Modules\Budget\Http\Controllers;

use App\Models\Profile;
use App\Models\FiscalYear;
use App\Imports\DataImport;
use App\Models\CodeSetting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Nwidart\Modules\Facades\Module;
use Maatwebsite\Excel\Facades\Excel;
use App\Repositories\ReportRepository;
use Maatwebsite\Excel\HeadingRowImport;
use Modules\Budget\Models\DocumentStatus;
use Illuminate\Contracts\Support\Renderable;
use Modules\Budget\Models\BudgetAccountOpen;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Budget\Models\BudgetSubSpecificFormulation;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Modules\Budget\Exports\BudgetSubSpecificFormulationExport;

/**
 * @class BudgetSubSpecificFormulationController
 * @brief Controlador de formulaciones de presupuesto por sub específicas
 *
 * Clase que gestiona las formulaciones de presupuesto por sub específicas
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetSubSpecificFormulationController extends Controller
{
    use ValidatesRequests;

    /**
     * Define la configuración de la clase
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:budget.formulation.list', ['only' => 'index', 'vueList', 'show']);
        $this->middleware('permission:budget.formulation.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:budget.formulation.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:budget.formulation.delete', ['only' => 'destroy']);
    }

    /**
     * Muestra un listado de formulaciones de presupuesto registradas
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return Renderable
     */
    public function index()
    {
        $records = BudgetSubSpecificFormulation::all();
        return view('budget::formulations.list');
    }

    /**
     * Muestra el formulario para el registro de datos de la formulación de presupuesto
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return Renderable
     */
    public function create()
    {
        $is_admin = auth()->user()->isAdmin();
        $user_profile = Profile::where('user_id', auth()->user()->id)->first();
        $institution_id = isset($user_profile->institution_id)
            ? $user_profile->institution_id
            : null;
        if ($institution_id && !$is_admin) {
            $institutions = template_choices('App\Models\Institution', 'name', ['id' => $institution_id], true);
        } else {
            $institutions = template_choices('App\Models\Institution', 'name', [], true);
        }
        $institutions = json_encode($institutions);
        return view('budget::formulations.create-edit-form', compact('institutions'));
    }

    /**
     * Guarda información para una formulación de presupuesto
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'institution_id' => ['required'],
                'specific_action_id' => ['required'],
                'currency_id' => ['required'],
                'formulated_accounts' => ['required_without:id'],
                'financement_type_id' => ['required'],
                'financement_source_id' => ['nullable'],
                'financement_amount' => ['required'],
                'date' => ['required'],
            ],
            [
                'institution_id.required' => 'El campo institución es obligatorio.',
                'specific_action_id.required' => 'El campo acción específica es obligatorio.',
                'currency_id.required' => 'El campo moneda es obligatorio.',
                'formulated_accounts.required_without' => 'Se deben ingresar los montos en la tabla de distribución financiera.',
                'financement_type_id.required' => 'El campo Fuente de Financiamiento: es obligatorio.',
                'financement_source_id.required' => 'El campo Tipo de financiamiento es obligatorio.',
                'financement_amount.required' => 'El campo monto es obligatorio.',
                'date.required' => 'La fecha de generación es obligatoria.',
            ]
        );

        $year = $request->fiscal_year ?? date("Y");

        $documentStatus = DocumentStatus::where('action', 'EL')->first();
        $codeSetting = CodeSetting::where("model", BudgetSubSpecificFormulation::class)->first();

        if ((float)$request->formulated_accounts[0]['total_year_amount'] != (float)$request->financement_amount) {
            return response()->json(['result' => false, 'message' => [
                'type' => 'custom', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'danger',
                'text' => 'El total anual de la formulación debe ser igual al monto total de financiamiento'
            ]], 200);
        }

        if (!$codeSetting) {
            return response()->json(['result' => false, 'message' => [
                'type' => 'custom', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'danger',
                'text' => 'Debe configurar previamente el formato para el código a generar'
            ]], 200);
        }

        $validateStore = BudgetSubSpecificFormulation::validateStore([
            'budget_specific_action_id' => $request->specific_action_id,
            'currency_id' => $request->currency_id, 'year' => $year,
            'institution_id' => $request->institution_id
        ]);
        if (!$validateStore) {
            return response()->json(['result' => false, 'message' => [
                'type' => 'custom', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'danger',
                'text' => 'Ya existe una formulación con los datos suministrados'
            ]], 200);
        }

        $code = generate_registration_code(
            $codeSetting->format_prefix,
            strlen($codeSetting->format_digits),
            (strlen($codeSetting->format_year) === 2) ? date("y", mktime(0, 0, 0, 1, 1, $year)) : $year,
            BudgetSubSpecificFormulation::class,
            'code'
        );

        DB::transaction(function () use ($request, $code, $year, $documentStatus) {
            $formulation = BudgetSubSpecificFormulation::create([
                'date' => $request->date,
                'code' => $code,
                'year' => $year,
                'total_formulated' => (float)$request->formulated_accounts[0]['total_year_amount'],
                'budget_specific_action_id' => $request->specific_action_id,
                'currency_id' => $request->currency_id,
                'institution_id' => $request->institution_id,
                'document_status_id' => $documentStatus->id,
                'budget_financement_type_id' => $request->financement_type_id,
                'budget_financement_source_id' => $request->financement_source_id,
                'financement_amount' => $request->financement_amount
            ]);

            foreach ($request->formulated_accounts as $formulated_account) {
                $f_acc = (object)$formulated_account;
                BudgetAccountOpen::create([
                    'jan_amount' => (float)$f_acc->jan_amount, 'feb_amount' => (float)$f_acc->feb_amount,
                    'mar_amount' => (float)$f_acc->mar_amount, 'apr_amount' => (float)$f_acc->apr_amount,
                    'may_amount' => (float)$f_acc->may_amount, 'jun_amount' => (float)$f_acc->jun_amount,
                    'jul_amount' => (float)$f_acc->jul_amount, 'aug_amount' => (float)$f_acc->aug_amount,
                    'sep_amount' => (float)$f_acc->sep_amount, 'oct_amount' => (float)$f_acc->oct_amount,
                    'nov_amount' => (float)$f_acc->nov_amount, 'dec_amount' => (float)$f_acc->dec_amount,
                    'total_year_amount' => (float)$f_acc->total_year_amount,
                    'total_year_amount_m' => (float)$f_acc->total_year_amount,
                    'total_real_amount' => (float)$f_acc->total_real_amount,
                    'total_estimated_amount' => (float)$f_acc->total_estimated_amount,
                    'budget_account_id' => $f_acc->id,
                    'budget_sub_specific_formulation_id' => $formulation->id
                ]);
            }
        });

        return response()->json(['result' => true], 200);
    }

    /**
     * Muestra información de una formulación de presupuesto
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  integer $id Identificador de la formulación
     *
     * @return Renderable
     */
    public function show($id)
    {
        $formulation = BudgetSubSpecificFormulation::with(['accountOpens' => function ($query) {
            $query
                ->with(['budgetAccount' => function ($query) {
                    $query
                        ->orderBy('group')
                        ->orderBy('item')
                        ->orderBy('generic')
                        ->orderBy('specific')
                        ->orderBy('subspecific');
                }])
                ->orderBy('id');
        }])->find($id);
        $enable = isModuleEnabled('DigitalSignature');
        return view('budget::formulations.show', compact('formulation', 'enable'));
    }

    /**
     * Muestra el formulario de modificación para una formulación de presupuesto
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  integer $id Identificador de la formulación
     *
     * @return Renderable
     */
    public function edit($id)
    {
        $is_admin = auth()->user()->isAdmin();
        $user_profile = Profile::where('user_id', auth()->user()->id)->first();
        $institution_id = isset($user_profile->institution_id) ? $user_profile->institution_id : null;
        if ($institution_id && !$is_admin) {
            $institutions = template_choices('App\Models\Institution', 'name', ['id' => $institution_id], true);
        } else {
            $institutions = template_choices('App\Models\Institution', 'name', [], true);
        }
        $institutions = json_encode($institutions);
        $formulation = BudgetSubSpecificFormulation::find($id);
        return view('budget::formulations.create-edit-form', compact("formulation", 'institutions'));
    }

    /**
     * Actualiza la información de una formulación presupuestaria
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  Request $request Datos de la petición
     * @param  integer $id Identificador del registro a actualizar
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $formulation = BudgetSubSpecificFormulation::find($id);

        if (isset($request->assigned) && $request->assigned) {
            // Instrucciones para la asignación de presupuesto
            $formulation->assigned = $request->assigned;
            $documentStatus = DocumentStatus::where('action', 'AP')->first();
            $formulation->document_status_id = $documentStatus->id;
            $formulation->save();

            $fiscalYear = FiscalYear::firstOrCreate([
                'year' => $formulation->year, 'institution_id' => $formulation->institution_id
            ]);

            $request->session()->flash('message', [
                'type' => 'other', 'icon' => 'screen-ok',
                'text' => __(
                    'La formulación de presupuesto fue asignada para el ejercicio fiscal ' .
                        ':year y no puede ser modificada',
                    ['year' => $formulation->year]
                )
            ]);
        } elseif ($formulation->assigned) {
            $request->session()->flash('message', [
                'type' => 'other', 'icon' => 'screen-ok',
                'text' => 'La formulación de presupuesto ya se encuentra asignada y no puede ser modificada'
            ]);
        } else {
            $this->validate($request, [
                'institution_id' => ['required'],
                'specific_action_id' => ['required'],
                'currency_id' => ['required'],
                'formulated_accounts.*' => ['required'],
                'financement_amount' => ['required']
            ]);

            if ((float)$request->formulated_accounts[0]['total_year_amount'] != (float)$request->financement_amount) {
                return response()->json(['result' => false, 'message' => [
                    'type' => 'custom', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'danger',
                    'text' => 'El total anual de la formulación debe ser igual al monto total de financiamiento'
                ]], 200);
            }

            DB::transaction(function () use ($request, $formulation) {
                $formulation->total_formulated = (float)$request->formulated_accounts[0]['total_year_amount'];
                $formulation->budget_specific_action_id = $request->specific_action_id;
                $formulation->currency_id = $request->currency_id;
                $formulation->institution_id = $request->institution_id;
                $formulation->financement_amount = $request->financement_amount;
                $formulation->date = $request->date;
                $formulation->budget_financement_type_id = $request->financement_type_id;
                $formulation->budget_financement_source_id = $request->financement_source_id;
                $formulation->save();

                $formulation->accountOpens()->delete();

                foreach ($request->formulated_accounts as $formulated_account) {
                    $f_acc = (object)$formulated_account;
                    BudgetAccountOpen::create([
                        'jan_amount' => (float)$f_acc->jan_amount, 'feb_amount' => (float)$f_acc->feb_amount,
                        'mar_amount' => (float)$f_acc->mar_amount, 'apr_amount' => (float)$f_acc->apr_amount,
                        'may_amount' => (float)$f_acc->may_amount, 'jun_amount' => (float)$f_acc->jun_amount,
                        'jul_amount' => (float)$f_acc->jul_amount, 'aug_amount' => (float)$f_acc->aug_amount,
                        'sep_amount' => (float)$f_acc->sep_amount, 'oct_amount' => (float)$f_acc->oct_amount,
                        'nov_amount' => (float)$f_acc->nov_amount, 'dec_amount' => (float)$f_acc->dec_amount,
                        'total_year_amount' => (float)$f_acc->total_year_amount,
                        'total_year_amount_m' => (float)$f_acc->total_year_amount,
                        'total_real_amount' => (float)$f_acc->total_real_amount,
                        'total_estimated_amount' => (float)$f_acc->total_estimated_amount,
                        'budget_account_id' => $f_acc->id,
                        'budget_sub_specific_formulation_id' => $formulation->id
                    ]);
                }
            });

            return response()->json(['result' => true], 200);
        }

        return redirect()->back();
    }

    /**
     * Elimina un registro en particular
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param integer $id Identificador de la formulación
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $budgetFormulation = BudgetSubSpecificFormulation::find($id);

        if ($budgetFormulation) {
            $budgetFormulation->delete();
        }

        return response()->json(['record' => $budgetFormulation, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene los registros de formulaciones a mostrar en listados de componente Vue
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueList()
    {
        return response()->json([
            'records' => BudgetSubSpecificFormulation::with(['institution', 'specificAction', 'currency' => function ($query) {
                return $query->withTrashed();
            }])->get()
        ], 200);
    }

    /**
     * Obtiene los registros de presupuestos formulados
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  integer $id Identificador de la formulación a consultar
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFormulation($id)
    {
        $formulation = BudgetSubSpecificFormulation::where('id', $id)
            ->with(['currency', 'accountOpens', 'specificAction' => function ($specifiAction) {
                return $specifiAction->with(['specificable' => function ($specificable) {
                    return $specificable->with('department');
                }]);
            }])->first();

        return response()->json(['result' => true, 'formulation' => $formulation], 200);
    }

    /**
     * Obtiene la disponibilidad de las cuentas aperturadas
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  integer $specific_action_id      Identificador de la acción específica
     * @param  integer $account_id              Identificador de la cuenta presupuestaria
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailabilityOpenedAccounts($specific_action_id, $account_id)
    {
        $account_data = ['account_id' => $account_id, 'available' => 'Sin apertura'];

        $formulation = BudgetSubSpecificFormulation::currentFormulation($specific_action_id)
            ->with(['account_opens' => function ($account) use ($account_id) {
                /* Devuelve, si existe, la cuenta formulada */
                return $account->where('budget_account_id', $account_id)->first();
            }, 'modificationAccounts' => function ($account) use ($account_id) {
                /*
                 * Devuelve, si existen, las cuentas agregadas o modificadas
                 * mediante la asignación de créditos adicionales, reducciones
                 * o traspasos
                 */
                return $account->where('budget_account_id', $account_id)->get();
            }])->first();

        $available = 0;
        foreach ($formulation->modificationAccounts as $modified_account) {
            // cálculo de saldo para cada una de las cuentas
        }

        if ($available > 0) {
            $account_data['available'] = $available;
        }

        return response()->json(['result' => true, 'account' => $account_data], 200);
    }

    /**
     * Importa datos de una formulación a partir de un archivo de hoja de cálculo
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function importFormulation()
    {
        $headings = (new HeadingRowImport())->toArray(request()->file('file'));
        $records = Excel::toArray(new DataImport(), request()->file('file'))[0];

        $msg = '';

        if (count($headings) < 1 || $headings[0] < 1) {
            $msg = 'El archivo no contiene las cabeceras de los datos a importar.';
        } elseif (count($headings) === 1 && $headings[0] >= 1) {
            $validHeads = [
                'codigo', 'total_real', 'total_estimado', 'total_anho', 'ene', 'feb', 'mar',
                'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'
            ];
            foreach ($validHeads as $vh) {
                if (!in_array($vh, $headings[0][0])) {
                    $msg = "El archivo no contiene una de las cabeceras requeridas.";
                    break;
                }
            }
        } elseif (count($records) < 1) {
            $msg = "El archivo no contiene registros a ser importados.";
        }
        if (!empty($msg)) {
            return response()->json(['result' => false, 'message' => $msg], 200);
        }

        foreach ($records as $key => $record) {
            $records[$key] = array_map('trim', $record);
        }

        return response()->json(['result' => true, 'records' => $records], 200);
    }

    /**
     * Genera el reporte de presupuesto formulado
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     integer            $id    Identificador del presupuesto formulado a imprimir
     *
     * @return    BinaryFileResponse           Respuesta de la solicitud para descargar el reporte
     */
    public function printFormulated($id)
    {
        $pdf = new ReportRepository();
        $formulation = BudgetSubSpecificFormulation::with([
            'currency',
            'institution'
        ])
            ->where('id', $id)->first();
        $filename = 'formulated-' . $formulation->id . '.pdf';
        $pdf->setConfig(
            [
                'institution' => $formulation->institution,
                'urlVerify'   => url(''),
                'orientation' => 'P',
                'filename'    => $filename
            ]
        );
        $pdf->setHeader("Oficina de Programación y Presupuesto", "Presupuesto de Gastos por Sub Específicas");
        $pdf->setFooter();
        $pdf->setBody('budget::reports.formulation', true, compact('formulation'));
        $file = storage_path() . '/reports/' . $filename;
        return response()->download($file, $filename, [], 'inline');
    }

    /**
     * Genera el reporte de presupuesto formulado como archivo .xlsx
     *
     * @method    export
     *
     * @author     Pedro Contreras <pmcontreras@cenditel.gob.ve>
     *
     * @param     integer            $id    Identificador del presupuesto formulado a imprimir
     *
     * @return    BinaryFileResponse|\Illuminate\Http\RedirectResponse
     */
    public function export($id)
    {
        try {
            $formulation = BudgetSubSpecificFormulation::query()->with([
                'currency',
                'institution'
            ])->where('id', $id)->first();
            $export = new BudgetSubSpecificFormulationExport(BudgetSubSpecificFormulation::class);
            $export->setBudgetFormulationId($formulation->id);
            return Excel::download($export, 'budget_formulation' . $formulation->created_at . '.xlsx');
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            request()->session()->flash('message', [
                'type' => 'other', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'growl-danger',
                'text' => 'No se puede generar el archivo porque se ha presentando un error al momento de su generación.',
            ]);
            return redirect()->route('budget.subspecific-formulations.index');
        }
    }

    /**
     * Genera el reporte de presupuesto formulado
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     integer            $id    Identificador del presupuesto formulado a imprimir
     *
     * @return    BinaryFileResponse|\Illuminate\Http\JsonResponse
     */
    public function printFormulatedSign($id)
    {
        if (!Module::has('DigitalSignature') || !Module::isEnabled('DigitalSignature')) {
            return response()->json([
                'result' => false,
                'message' => 'No se encuentra habilitado el modulo de Firma Digital. Por favor contacte al administrador.'
            ], 200);
        }
        $pdf = new \Modules\DigitalSignature\Repositories\ReportRepositorySign();
        $formulation = BudgetSubSpecificFormulation::with([
            'currency',
            'institution'
        ])
            ->where('id', $id)->first();
        $filename = 'formulated-' . $formulation->id . '.pdf';
        $pdf->setConfig(
            [
                'institution' => $formulation->institution,
                'urlVerify'   => url(''),
                'orientation' => 'P',
                'filename'    => $filename
            ]
        );
        $pdf->setHeader("Oficina de Programación y Presupuesto", "Presupuesto de Gastos por Sub Específicas");
        $pdf->setFooter();
        $sign = $pdf->setBody('budget::reports.formulation', true, compact('formulation'));
        if ($sign['status'] == 'true') {
            return response()->download($sign['file'], $sign['filename'], [], 'inline');
        } else {
            return response()->json([
                'result' => $sign['status'],
                'message' => $sign['message']
            ], 200);
        }
    }

    /**
     * Instrucción que permite descargar un archivo
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     string      $filename    Nombre del archivo a descargar
     *
     * @return    BinaryFileResponse
     */
    public function download($filename)
    {
        $file = storage_path() . '/budget/reports/download/' . $filename;
        return response()->download($file, $filename, [], 'inline');
    }
}
