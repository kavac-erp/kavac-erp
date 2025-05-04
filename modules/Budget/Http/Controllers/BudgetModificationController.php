<?php

namespace Modules\Budget\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Models\CodeSetting;
use App\Models\DocumentStatus;
use App\Models\FiscalYear;
use Modules\Budget\Models\BudgetModification;
use Modules\Budget\Models\BudgetModificationAccount;
use Modules\Budget\Models\BudgetSubSpecificFormulation;
use Modules\Budget\Models\BudgetAccountOpen;
use Modules\Budget\Models\BudgetAccount;

/**
 * @class BudgetModificationController
 * @brief Controlador para las modificaciones presupuestarias del módulo de Presupuesto
 *
 * Clase que gestiona información de las modificaciones presupuestarias del módulo de Presupuesto
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 * @license<a href='http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/'>
 *              LICENCIA DE SOFTWARE CENDITEL
 *          </a>
 */
class BudgetModificationController extends Controller
{
    use ValidatesRequests;

    /** @var array Arreglo con los datos a implementar en los atributos del formulario */
    public $header;

    /**
     * Define la configuración de la clase
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     */
    public function __construct()
    {
        /** Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:budget.modifications.list', ['only' => 'index', 'vueList']);
        $this->middleware('permission:budget.modifications.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:budget.modifications.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:budget.modifications.delete', ['only' => 'destroy']);
        $this->middleware('permission:budget.modifications.approve', ['only' => 'changeStatus']);

        /** @var array Arreglo de opciones a implementar en el formulario */
        $this->header = [
            'route' => 'budget.modifications.store',
            'method' => 'POST',
            'role' => 'form',
            'class' => 'form-horizontal',
        ];
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('budget::modifications.list');
    }

    /**
     * Muestra el formulario para crear un crédito adicional
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     * @return Renderable
     */
    public function create($type)
    {
        $viewTemplate = ($type === "AC")
            ? 'aditional_credits'
            : (($type === 'RE')
                ? 'reductions'
                : (($type === "TR")
                    ? 'transfers' : ''));

        return view("budget::$viewTemplate.create-edit-form", compact('type'));
    }

    /**
     * Registra información de la modificación presupuestaria
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     * @param  Request $request Objeto con datos de la petición realizada
     * @return Renderable
     */
    public function store(Request $request)
    {
        /** @var array Arreglo con las reglas de validación para el registro */
        $rules = [
            'approved_at' => ['required', 'date'],
            'description' => ['required'],
            'document' => ['required', 'max:20'],
            'institution_id' => ['required'],
            'budget_account_id' => ['required', 'array', 'min:1']
        ];

        /** @var array Arreglo con los mensajes para las reglas de validación */
        $messages = [
            'budget_account_id.required' => 'Las cuentas presupuestarias son obligatorias.',
            'document.max' => 'El campo Documento sólo debe tener 20 carácteres o menos',
        ];

        $attributes = [
            'approved_at' => 'Fecha de creación',
            'document' => 'Documento',
            'institution_id' => 'Institución',
        ];

        /** @var object Contiene la configuración del código establecido para el registro */
        if (!is_null($request->type)) {
            switch ($request->type) {
                case 'AC':
                    $codeFilter = 'budget.aditional-credits';
                    $codeSetting = CodeSetting::where(['table' => 'budget_modifications', 'type' => $codeFilter])->first();
                    break;
                case 'RE':
                    $codeFilter = 'budget.reductions';
                    $codeSetting = CodeSetting::where(['table' => 'budget_modifications', 'type' => $codeFilter])->first();
                    break;
                case 'TR':
                    $codeFilter = 'budget.transfers';
                    $codeSetting = CodeSetting::where(['table' => 'budget_modifications', 'type' => $codeFilter])->first();
                    break;
                default:
                    $codeFilter = '';
                    $codeSetting = '';
                    break;
            }
        }

        if (!isset($codeSetting) || !$codeSetting) {
            $rules['code'] = 'required';
            $message['code.required'] = 'Debe configurar previamente el formato para el código a generar';
        }

        $this->validate($request, $rules, $messages, $attributes);

        /** @var object Obtiene el registro del documento con estatus aprobado */
        $documentStatus = DocumentStatus::getStatus('AP');

        $currentFiscalYear = FiscalYear::select('year')
        ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();

        /** @var string Contiene el código generado para el registro a crear */
        $code  = generate_registration_code(
            $codeSetting->format_prefix,
            strlen($codeSetting->format_digits),
            (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
                substr($currentFiscalYear->year, 2, 2) : date('y')) : (isset($currentFiscalYear) ?
                $currentFiscalYear->year : date('Y')),
            BudgetModification::class,
            $codeSetting->field
        );

        DB::transaction(function () use ($request, $code, $documentStatus) {
            $type = ($request->type === "AC") ? 'C' : (($request->type === "RE") ? 'R' : 'T');

            /** @var object Objeto que contiene los datos de la modificación presupuestaria creada */
            $budgetModification = BudgetModification::create([
                'type' => $type,
                'code' => $code,
                'approved_at' => $request->approved_at,
                'description' => $request->description,
                'document' => $request->document,
                'institution_id' => $request->institution_id,
                'document_status_id' => $documentStatus->id
            ]);

            foreach ($request->budget_account_id as $account) {

                /** @var object Obtiene la formulación correspondiente a la acción específica seleccionada */
                $formulation = BudgetSubSpecificFormulation::where('budget_specific_action_id', $account['from_specific_action_id'])
                    ->where('document_status_id', $documentStatus->id)
                    ->where('assigned', true)
                    ->orderBy('year', 'desc')->first();

                if ($formulation) {
                    $budgetAccountOpen = BudgetAccountOpen::where('budget_sub_specific_formulation_id', $formulation->id)
                        ->where('budget_account_id', $account['from_account_id'])
                        ->first();
                    if ($budgetAccountOpen) {
                        $modificationType = ($type === "C") ? 'I' : 'D';

                        if ($modificationType == 'D') {
                            $budgetAccountOpen->total_year_amount_m = $budgetAccountOpen->total_year_amount_m - $account['from_amount'];
                            $budgetAccountOpen->save();
                        }

                        if ($modificationType == 'I') {
                            $budgetAccountOpen->total_year_amount_m = $budgetAccountOpen->total_year_amount_m + $account['from_amount'];
                            $budgetAccountOpen->save();
                        }
                    }

                    BudgetModificationAccount::create([
                        'amount' => $account['from_amount'],
                        'operation' => ($type === "C") ? 'I' : 'D',
                        'budget_sub_specific_formulation_id' => $formulation->id,
                        'budget_account_id' => $account['from_account_id'],
                        'budget_modification_id' => $budgetModification->id
                    ]);
                }

                if (isset($account['to_account_id'])) {
                    /** @var object Obtiene la formulación correspondiente a la acción específica a donde transferir
                    los recursos */
                    $formulation_transfer = BudgetSubSpecificFormulation::currentFormulation(
                        $account['to_specific_action_id']
                    );
                    $budgetAccountOpen = BudgetAccountOpen::where('budget_sub_specific_formulation_id', $formulation_transfer->id)
                        ->where('budget_account_id', $account['to_account_id'])
                        ->first();
                    if ($budgetAccountOpen) {
                        $modificationType = ($type === "C") ? 'I' : 'D';
                        if ($modificationType == 'D') {
                            $budgetAccountOpen->total_year_amount_m = $budgetAccountOpen->total_year_amount_m + $account['to_amount'];
                            $budgetAccountOpen->save();
                        }
                    }

                    if ($formulation_transfer) {
                        BudgetModificationAccount::create([
                            'amount' => $account['to_amount'],
                            'operation' => 'I',
                            'budget_sub_specific_formulation_id' => $formulation_transfer->id,
                            'budget_account_id' => $account['to_account_id'],
                            'budget_modification_id' => $budgetModification->id
                        ]);
                    }
                }
            }
        });

        $request->session()->flash('message', ['type' => 'store']);
        return response()->json([
            'result' => true, 'redirect' => route('budget.modifications.index')
        ], 200);
    }

    /**
     * Muestra el formulario de actualización de datos según el tipo de modificación presupuestaria
     *
     * @method     edit
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param      string                $type            Define el tipo de modificación presupuestaria a mostrar
     * @param      BudgetModification    $modification    Objeto con información de la modificación presupuestaria a
     *                                                    actualizar
     *
     * @return     View                Devuelve la vista a mostrar para la respectiva modificación presupuestaria
     */
    public function edit($type, BudgetModification $modification)
    {
        $viewTemplate = ($type === "AC")
            ? 'aditional_credits'
            : (($type === 'RE')
                ? 'reductions'
                : (($type === "TR")
                    ? 'transfers' : ''));
        $model = $modification;

        return view("budget::$viewTemplate.create-edit-form", compact('type', 'model'));
    }

    public function update(Request $request)
    {
        /** @var array Arreglo con las reglas de validación para el registro */
        $rules = [
            'approved_at' => ['required', 'date'],
            'description' => ['required'],
            'document' => ['required', 'max:20'],
            'institution_id' => ['required'],
            'budget_account_id' => ['required', 'array', 'min:1']
        ];

        /** @var array Arreglo con los mensajes para las reglas de validación */
        $messages = [
            'budget_account_id.required' => 'Las cuentas presupuestarias son obligatorias.',
            'document.max' => 'El campo Documento sólo debe tener 20 carácteres o menos',
        ];

        $attributes = [
            'approved_at' => 'Fecha de creación',
            'document' => 'Documento',
            'institution_id' => 'Institución',
        ];

        $this->validate($request, $rules, $messages, $attributes);

        $documentStatus = DocumentStatus::getStatus('AP');

        DB::transaction(function () use ($request, $documentStatus) {
            $budgetModification = BudgetModification::find($request->id);
            $type = ($request->type === "AC") ? 'C' : (($request->type === "RE") ? 'R' : 'T');

            /** @var object Objeto que contiene los datos de la modificación presupuestaria creada */
            $budgetModification->type = $type;
            $budgetModification->approved_at = $request->approved_at;
            $budgetModification->description = $request->description;
            $budgetModification->document = $request->document;
            $budgetModification->institution_id = $request->institution_id;
            $budgetModification->document_status_id = $documentStatus->id;
            $budgetModification->save();

            $deleted = BudgetModificationAccount::where('budget_modification_id', $budgetModification->id)->delete();

            foreach ($request->budget_account_id as $account) {

                /** @var object Obtiene la formulación correspondiente a la acción específica seleccionada */
                $formulation = BudgetSubSpecificFormulation::where('budget_specific_action_id', $account['from_specific_action_id'])
                    ->where('document_status_id', $documentStatus->id)
                    ->where('assigned', true)
                    ->orderBy('year', 'desc')->first();

                if ($formulation) {
                    $budgetAccountOpen = BudgetAccountOpen::where('budget_sub_specific_formulation_id', $formulation->id)
                        ->where('budget_account_id', $account['from_account_id'])
                        ->first();

                    if (isset($account['from_account_original'])) {
                        $budgetAccountOriginal = BudgetAccountOpen::where('budget_sub_specific_formulation_id', $formulation->id)
                            ->where('budget_account_id', $account['from_account_original'])
                            ->first();
                    }

                    if ($budgetAccountOpen) {
                        $modificationType = ($type === "C") ? 'I' : 'D';

                        if ($modificationType == 'D') {
                            if ($request->type === "TR") {
                                if ($account['from_operation'] == 'I') {
                                    $budgetAccountOpen->update([
                                        'total_year_amount_m' => $budgetAccountOpen->total_year_amount_m,
                                    ]);
                                } elseif ($account['from_operation'] == 'C') {
                                    $budgetAccountOpen->update([
                                        'total_year_amount_m' => $budgetAccountOpen->total_year_amount_m - ($account['from_amount'] < 0 ? $account['from_amount'] * -1 : $account['from_amount']),
                                    ]);

                                    if (isset($budgetAccountOriginal) && $budgetAccountOriginal != null && $account['from_equal'] == 'N') {
                                        $budgetAccountOriginal->update([
                                            'total_year_amount_m' => $budgetAccountOriginal->total_year_amount_m + ($account['from_amount_original'] < 0 ? $account['from_amount_original'] * -1 : $account['from_amount_original']),
                                        ]);
                                    }
                                } elseif ($account['from_operation'] == 'S') {
                                    if ($account['from_equal'] == 'S') {
                                        $budgetAccountOpen->update([
                                            'total_year_amount_m' => $budgetAccountOpen->total_year_amount_m + ($account['from_amount_edit'] < 0 ? $account['from_amount_edit'] * -1 : $account['from_amount_edit']),
                                        ]);
                                    } else {
                                        $budgetAccountOpen->update([
                                            'total_year_amount_m' => $budgetAccountOpen->total_year_amount_m - ($account['from_amount_edit'] < 0 ? $account['from_amount_edit'] * -1 : $account['from_amount_edit']),
                                        ]);

                                        if (isset($budgetAccountOriginal) && $budgetAccountOriginal != null) {
                                            $budgetAccountOriginal->update([
                                                'total_year_amount_m' => $budgetAccountOriginal->total_year_amount_m + ($account['from_amount_original'] < 0 ? $account['from_amount_original'] * -1 : $account['from_amount_original']),
                                            ]);
                                        }
                                    }
                                } elseif ($account['from_operation'] == 'R') {
                                    if ($account['from_equal'] == 'S') {
                                        $budgetAccountOpen->update([
                                            'total_year_amount_m' => $budgetAccountOpen->total_year_amount_m - ($account['from_amount_edit'] < 0 ? $account['from_amount_edit'] * -1 : $account['from_amount_edit']),
                                        ]);
                                    } else {
                                        $budgetAccountOpen->update([
                                            'total_year_amount_m' => $budgetAccountOpen->total_year_amount_m - ($account['from_amount'] < 0 ? $account['from_amount'] * -1 : $account['from_amount']),
                                        ]);

                                        if (isset($budgetAccountOriginal) && $budgetAccountOriginal != null) {
                                            $budgetAccountOriginal->update([
                                                'total_year_amount_m' => $budgetAccountOriginal->total_year_amount_m + ($account['from_amount_original'] < 0 ? $account['from_amount_original'] * -1 : $account['from_amount_original']),
                                            ]);
                                        }
                                    }
                                } else {
                                    $budgetAccountOpen->total_year_amount_m = $budgetAccountOpen->total_year_amount_m - $account['from_amount'];
                                    $budgetAccountOpen->save();
                                }
                            } else {
                                if ($account['operation'] == 'I') {
                                    $budgetAccountOpen->update([
                                        'total_year_amount_m' => $budgetAccountOpen->total_year_amount_m,
                                    ]);
                                } elseif ($account['operation'] == 'C') {
                                    $budgetAccountOpen->update([
                                        'total_year_amount_m' => $budgetAccountOpen->total_year_amount_m - ($account['from_amount_edit'] < 0 ? $account['from_amount_edit'] * -1 : $account['from_amount_edit']),
                                    ]);

                                    if (isset($budgetAccountOriginal) && $budgetAccountOriginal != null && $account['equal'] == 'N') {
                                        $budgetAccountOriginal->update([
                                            'total_year_amount_m' => $budgetAccountOriginal->total_year_amount_m + ($account['from_amount_original'] < 0 ? $account['from_amount_original'] * -1 : $account['from_amount_original']),
                                        ]);
                                    }
                                } elseif ($account['operation'] == 'S') {
                                    if ($account['equal'] == 'S') {
                                        $budgetAccountOpen->update([
                                            'total_year_amount_m' => $budgetAccountOpen->total_year_amount_m + ($account['from_amount_edit'] < 0 ? $account['from_amount_edit'] * -1 : $account['from_amount_edit']),
                                        ]);
                                    } else {
                                        $budgetAccountOpen->update([
                                            'total_year_amount_m' => $budgetAccountOpen->total_year_amount_m - ($account['from_amount_edit'] < 0 ? $account['from_amount_edit'] * -1 : $account['from_amount_edit']),
                                        ]);

                                        if (isset($budgetAccountOriginal) && $budgetAccountOriginal != null) {
                                            $budgetAccountOriginal->update([
                                                'total_year_amount_m' => $budgetAccountOriginal->total_year_amount_m + ($account['from_amount_original'] < 0 ? $account['from_amount_original'] * -1 : $account['from_amount_original']),
                                            ]);
                                        }
                                    }
                                } elseif ($account['operation'] == 'R') {
                                    $budgetAccountOpen->update([
                                        'total_year_amount_m' => $budgetAccountOpen->total_year_amount_m - ($account['from_amount_edit'] < 0 ? $account['from_amount_edit'] * -1 : $account['from_amount_edit']),
                                    ]);

                                    if (isset($budgetAccountOriginal) && $budgetAccountOriginal != null && $account['equal'] == 'N') {
                                        $budgetAccountOriginal->update([
                                            'total_year_amount_m' => $budgetAccountOriginal->total_year_amount_m + ($account['from_amount_original'] < 0 ? $account['from_amount_original'] * -1 : $account['from_amount_original']),
                                        ]);
                                    }
                                } else {
                                    $budgetAccountOpen->total_year_amount_m = $budgetAccountOpen->total_year_amount_m - $account['from_amount'];
                                    $budgetAccountOpen->save();
                                }
                            }
                        }

                        if ($modificationType == 'I') {
                            if ($account['operation'] == 'I') {
                                $budgetAccountOpen->update([
                                    'total_year_amount_m' => $budgetAccountOpen->total_year_amount_m,
                                ]);
                            } elseif ($account['operation'] == 'C') {
                                $budgetAccountOpen->update([
                                    'total_year_amount_m' => $budgetAccountOpen->total_year_amount_m + ($account['from_amount_edit'] < 0 ? $account['from_amount_edit'] * -1 : $account['from_amount_edit']),
                                ]);

                                if (isset($budgetAccountOriginal) && $budgetAccountOriginal != null && $account['equal'] == 'N') {
                                    $budgetAccountOriginal->update([
                                        'total_year_amount_m' => $budgetAccountOriginal->total_year_amount_m - ($account['from_amount_original'] < 0 ? $account['from_amount_original'] * -1 : $account['from_amount_original']),
                                    ]);
                                }
                            } elseif ($account['operation'] == 'S') {
                                $budgetAccountOpen->update([
                                    'total_year_amount_m' => $budgetAccountOpen->total_year_amount_m + ($account['from_amount_edit'] < 0 ? $account['from_amount_edit'] * -1 : $account['from_amount_edit']),
                                ]);

                                if (isset($budgetAccountOriginal) && $budgetAccountOriginal != null && $account['equal'] == 'N') {
                                    $budgetAccountOriginal->update([
                                        'total_year_amount_m' => $budgetAccountOriginal->total_year_amount_m - ($account['from_amount_original'] < 0 ? $account['from_amount_original'] * -1 : $account['from_amount_original']),
                                    ]);
                                }
                            } elseif ($account['operation'] == 'R') {
                                if ($account['equal'] == 'S') {
                                    $budgetAccountOpen->update([
                                        'total_year_amount_m' => $budgetAccountOpen->total_year_amount_m - ($account['from_amount_edit'] < 0 ? $account['from_amount_edit'] * -1 : $account['from_amount_edit']),
                                    ]);
                                } else {
                                    $budgetAccountOpen->update([
                                        'total_year_amount_m' => $budgetAccountOpen->total_year_amount_m + ($account['from_amount_edit'] < 0 ? $account['from_amount_edit'] * -1 : $account['from_amount_edit']),
                                    ]);

                                    if (isset($budgetAccountOriginal) && $budgetAccountOriginal != null) {
                                        $budgetAccountOriginal->update([
                                            'total_year_amount_m' => $budgetAccountOriginal->total_year_amount_m - ($account['from_amount_original'] < 0 ? $account['from_amount_original'] * -1 : $account['from_amount_original']),
                                        ]);
                                    }
                                }
                            } else {
                                $budgetAccountOpen->total_year_amount_m = $budgetAccountOpen->total_year_amount_m + $account['from_amount'];
                                $budgetAccountOpen->save();
                            }
                        }
                    }

                    BudgetModificationAccount::create([
                        'amount' => $account['from_amount'],
                        'operation' => ($type === "C") ? 'I' : 'D',
                        'budget_sub_specific_formulation_id' => $formulation->id,
                        'budget_account_id' => $account['from_account_id'],
                        'budget_modification_id' => $budgetModification->id
                    ]);
                }

                if (isset($account['to_account_id'])) {
                    /** @var object Obtiene la formulación correspondiente a la acción específica a donde transferir
                    los recursos */
                    $formulation_transfer = BudgetSubSpecificFormulation::currentFormulation(
                        $account['to_specific_action_id']
                    );
                    $budgetAccountOpen = BudgetAccountOpen::where('budget_sub_specific_formulation_id', $formulation_transfer->id)
                        ->where('budget_account_id', $account['to_account_id'])
                        ->first();

                    $budgetAccountOriginal = BudgetAccountOpen::where('budget_sub_specific_formulation_id', $formulation->id)
                        ->where('budget_account_id', $account['to_account_original'])
                        ->first();

                    if ($budgetAccountOpen) {
                        $modificationType = ($type === "C") ? 'I' : 'D';
                        if ($modificationType == 'D') {
                            if ($account['to_operation'] == 'I') {
                                $budgetAccountOpen->update([
                                    'total_year_amount_m' => $budgetAccountOpen->total_year_amount_m,
                                ]);
                            } elseif ($account['to_operation'] == 'C') {
                                $budgetAccountOpen->update([
                                    'total_year_amount_m' => $budgetAccountOpen->total_year_amount_m + ($account['to_amount'] < 0 ? $account['to_amount'] * -1 : $account['to_amount']),
                                ]);

                                if (isset($budgetAccountOriginal) && $budgetAccountOriginal != null && $account['to_equal'] == 'N') {
                                    $budgetAccountOriginal->update([
                                        'total_year_amount_m' => $budgetAccountOriginal->total_year_amount_m - ($account['to_amount_original'] < 0 ? $account['to_amount_original'] * -1 : $account['to_amount_original']),
                                    ]);
                                }
                            } elseif ($account['to_operation'] == 'S') {
                                if ($account['to_equal'] == 'S') {
                                    $budgetAccountOpen->update([
                                        'total_year_amount_m' => $budgetAccountOpen->total_year_amount_m - ($account['to_amount_edit'] < 0 ?
                                            $account['to_amount_edit'] * -1 : $account['to_amount_edit']),
                                    ]);
                                } else {
                                    $budgetAccountOpen->update([
                                        'total_year_amount_m' => $budgetAccountOpen->total_year_amount_m + ($account['to_amount'] < 0 ?
                                            $account['to_amount'] * -1 : $account['to_amount']),
                                    ]);

                                    if (isset($budgetAccountOriginal) && $budgetAccountOriginal != null) {
                                        $budgetAccountOriginal->update([
                                            'total_year_amount_m' => $budgetAccountOriginal->total_year_amount_m - ($account['to_amount_original'] < 0 ? $account['to_amount_original'] * -1 : $account['to_amount_original']),
                                        ]);
                                    }
                                }
                            } elseif ($account['to_operation'] == 'R') {
                                if ($account['to_equal'] == 'S') {
                                    $budgetAccountOpen->update([
                                        'total_year_amount_m' => $budgetAccountOpen->total_year_amount_m + ($account['to_amount_edit'] < 0 ?
                                            $account['to_amount_edit'] * -1 : $account['to_amount_edit']),
                                    ]);
                                } else {
                                    $budgetAccountOpen->update([
                                        'total_year_amount_m' => $budgetAccountOpen->total_year_amount_m + ($account['to_amount'] < 0 ?
                                            $account['to_amount'] * -1 : $account['to_amount']),
                                    ]);

                                    if (isset($budgetAccountOriginal) && $budgetAccountOriginal != null) {
                                        $budgetAccountOriginal->update([
                                            'total_year_amount_m' => $budgetAccountOriginal->total_year_amount_m - ($account['to_amount_original'] < 0 ? $account['to_amount_original'] * -1 : $account['to_amount_original']),
                                        ]);
                                    }
                                }
                            } else {
                                $budgetAccountOpen->total_year_amount_m = $budgetAccountOpen->total_year_amount_m + $account['from_amount'];
                                $budgetAccountOpen->save();
                            }
                        }
                    }

                    if ($formulation_transfer) {
                        BudgetModificationAccount::create([
                            'amount' => $account['to_amount'],
                            'operation' => 'I',
                            'budget_sub_specific_formulation_id' => $formulation_transfer->id,
                            'budget_account_id' => $account['to_account_id'],
                            'budget_modification_id' => $budgetModification->id
                        ]);
                    }
                }
            }
        });

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json([
            'result' => true, 'redirect' => route('budget.modifications.index')
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     * @return Renderable
     */
    public function destroy($id)
    {
        /** @var object Objeto con información de la modificación presupuestaria a eliminar */
        $budgetModification = BudgetModification::find($id);

        if ($budgetModification) {
            $BudgetModificationAccounts = BudgetModificationAccount::where('budget_modification_id', $budgetModification->id)->get();
            $documentStatus = DocumentStatus::getStatus('AP');

            foreach ($BudgetModificationAccounts as $account) {
                /** @var object Obtiene la formulación correspondiente a la acción específica seleccionada */
                $formulation = BudgetSubSpecificFormulation::where('id', $account['budget_sub_specific_formulation_id'])
                    ->where('document_status_id', $documentStatus->id)
                    ->where('assigned', true)
                    ->orderBy('year', 'desc')->first();

                if ($formulation) {
                    $budgetAccountOpen = BudgetAccountOpen::with('budgetAccount')->where('budget_sub_specific_formulation_id', $formulation->id)
                        ->where('budget_account_id', $account['budget_account_id'])
                        ->first();
                    if ($budgetAccountOpen) {
                        $modificationType = ($account['operation'] === "I") ? 'I' : 'D';

                        if ($modificationType == 'D') {
                            if ($budgetAccountOpen->budgetAccount->specific != 00) {
                                $budgetAccountOpen->total_year_amount_m = $budgetAccountOpen->total_year_amount_m + $account['amount'];
                                $budgetAccountOpen->save();
                            }
                        }

                        if ($modificationType == 'I') {
                            if ($budgetAccountOpen->budgetAccount->specific != 00) {
                                $budgetAccountOpen->total_year_amount_m = $budgetAccountOpen->total_year_amount_m - $account['amount'];
                                $budgetAccountOpen->save();
                            }
                        }
                    }
                }
            }

            $budgetModification->delete();
            $BudgetModificationAccountsDelete = BudgetModificationAccount::where('budget_modification_id', $budgetModification->id)->delete();
        }

        return response()->json(['record' => $budgetModification, 'message' => 'Success'], 200);
    }

    /**
     * Actualiza el estatus del registro.
     *
     * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function changeStatus(Request $request, $id)
    {
        $query = BudgetModification::find($id);

        if ($query) {
            // Actualiza el estatus del registro con el valor status en la solicitud.
            $query->status = $request->status;
            $query->save();

            return response()->json([
                'result' => true,
                'message' => 'Registro aprobado',
            ], 200);
        } else {
            return response()->json([
                'result' => false,
                'message' => 'Registro no encontrado'
            ], 404);
        }
    }

    /**
     * Obtiene los registros a mostrar en listados de componente Vue
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     * @return json Json con datos de la perición realizada
     */
    public function vueList($type)
    {
        switch ($type) {
            case 'AC':
                $tp = 'C';
                break;
            case 'RE':
                $tp = 'R';
                break;
            case 'TR':
                $tp = 'T';
                break;
            default:
                $tp = '';
                break;
        }

        $records = ($tp) ? BudgetModification::where('type', $tp)->get() : [];
        return response()->json([
            'records' => $records
        ], 200);
    }
}
