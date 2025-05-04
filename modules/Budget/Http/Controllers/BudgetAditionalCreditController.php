<?php

namespace Modules\Budget\Http\Controllers;

use App\Models\Document;
use App\Models\CodeSetting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Budget\Models\Institution;
use Modules\Budget\Models\DocumentStatus;
use Illuminate\Contracts\Support\Renderable;
use Modules\Budget\Models\BudgetModification;
use Modules\Budget\Models\BudgetModificationAccount;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Budget\Models\BudgetSubSpecificFormulation;

/**
 * @class BudgetAditionalCreditController
 * @brief Controlador de Créditos Adicionales
 *
 * Clase que gestiona las Créditos Adicionales
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetAditionalCreditController extends Controller
{
    use ValidatesRequests;

    /**
     * Arreglo con los datos a implementar en los atributos del formulario
     *
     * @var array $header
     */
    public $header;

    /**
     * Arreglo con información de las instituciones registradas
     *
     * @var array $institutions
     */
    public $institutions;

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
        $this->middleware('permission:budget.aditionalcredit.list', ['only' => 'index', 'vueList']);
        $this->middleware('permission:budget.aditionalcredit.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:budget.aditionalcredit.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:budget.aditionalcredit.delete', ['only' => 'destroy']);

        /* Arreglo de opciones a implementar en el formulario */
        $this->header = [
            'route' => 'budget.aditional-credits.store',
            'method' => 'POST',
            'role' => 'form',
            'class' => 'form-horizontal',
        ];

        $this->institutions = template_choices(Institution::class, ['acronym', '-', 'name'], ['active' => true]);
    }

    /**
     * Muestra el listado de créditos adicionales
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return Renderable
     */
    public function index()
    {
        /* Objeto con información de las modificaciones presupuestarias del tipo Crédito Adicional */
        $records = BudgetModification::where('type', 'C')->get();
        return view('budget::aditional_credits.list', compact('records'));
    }

    /**
     * Muestra el formulario para crear un crédito adicional
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return Renderable
     */
    public function create()
    {
        /* Arreglo de opciones a implementar en el formulario */
        $header = $this->header;

        /* Arreglo de opciones a representar en la plantilla para su selección */
        $institutions = $this->institutions;

        return view('budget::aditional_credits.create-edit-form', compact('header', 'institutions'));
    }

    /**
     * Registra información del crédito adicional
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  Request $request Datos de la petición realizada
     *
     * @return Renderable
     */
    public function store(Request $request)
    {
        /* Arreglo con las reglas de validación para el registro */
        $rules = [
            'approved_at' => ['required', 'date'],
            'description' => ['required'],
            'document' => ['required'],
            'institution_id' => ['required'],
            'budget_account_id' => ['required', 'array', 'min:1']
        ];

        /* Arreglo con los mensajes para las reglas de validación */
        $messages = [
            'budget_account_id.required' => 'Las cuentas presupestarias son obligatorias.',
        ];

        /* Contiene la configuración del código establecido para el registro */
        $codeSetting = CodeSetting::getSetting(BudgetModification::class, 'budget.aditional-credits');

        if (!$codeSetting) {
            $rules['code'] = 'required';
            $messages['code.required'] = 'Debe configurar previamente el formato para el código a generar';
        }

        $this->validate($request, $rules, $messages);

        /* Obtiene el registro del documento con estatus aprobado */
        $documentStatus = DocumentStatus::getStatus('AP');
        $year = $request->fiscal_year ?? date("Y");

        /* Contiene el código generado para el registro a crear */
        $code = generate_registration_code(
            $codeSetting->format_prefix,
            strlen($codeSetting->format_digits),
            (strlen($codeSetting->format_year) === 2) ? date("y") : $year,
            BudgetModification::class,
            'code'
        );

        DB::transaction(function () use ($request, $code, $documentStatus) {
            /* Objeto que contiene los datos de la modificación presupuestaria creada */
            $budgetModification = BudgetModification::create([
                'type' => 'C',
                'code' => $code,
                'approved_at' => $request->approved_at,
                'description' => $request->description,
                'document' => $request->document,
                'institution_id' => $request->institution_id,
                'document_status_id' => $documentStatus->id
            ]);

            /* Gestiona el índice del elemento budget_account_id */
            $index = 0;

            foreach ($request->budget_account_id as $account) {
                list($budget_specific_action_id, $budget_account_id) = explode("|", $account);

                /* Obtiene la formulación correspondiente a la acción específica seleccionada */
                $formulation = BudgetSubSpecificFormulation::currentFormulation($budget_specific_action_id);

                if ($formulation) {
                    BudgetModificationAccount::create([
                        'amount' => $request->budget_account_amount[$index],
                        'operation' => 'I',
                        'budget_sub_specific_formulation_id' => $formulation->id,
                        'budget_account_id' => $budget_account_id,
                        'budget_modification_id' => $budgetModification->id
                    ]);
                }

                $index++;
            }

            if ($request->documentFiles) {
                //Verifica si tiene documentos para establecer la relación
                foreach ($request->documentFiles as $file) {
                    $doc = Document::find($file);
                    $doc->documentable_id = $budgetModification->id;
                    $doc->documentable_type = BudgetModification::class;
                    $doc->save();
                }
            }
        });

        $request->session()->flash('message', ['type' => 'store']);
        return redirect()->route('budget.aditional-credits.index');
    }

    /**
     * Muestra información del crédito adicional
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  integer $id Identificador del crédito adicional a mostrar
     *
     * @return Renderable
     */
    public function show($id)
    {
        return view('budget::show');
    }

    /**
     * Muestra el formulario para editar datos del crédito adicional
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  integer $id Identificador del crédito adicional a editar
     *
     * @return Renderable
     */
    public function edit($id)
    {
        /* Objeto con información de la modificación presupuestaria a actualizar */
        $budgetModification = BudgetModification::find($id);

        $this->header['route'] = ['budget.aditional-credits.update', $budgetModification->id];
        $this->header['method'] = 'PUT';

        /* Arreglo de opciones a implementar en el formulario */
        $header = $this->header;

        /* Arreglo de opciones a representar en la plantilla para su selección */
        $institutions = $this->institutions;

        /* Objeto con datos del modelo a modificar */
        $model = $budgetModification;

        return view(
            'budget::aditional_credits.create-edit-form',
            compact('header', 'model', 'institutions')
        );
    }

    /**
     * Actualiza información del crédito adicional
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  Request $request Datos de la petición realizada
     *
     * @return void
     */
    public function update(Request $request)
    {
    }

    /**
     * Elimina un crédito adicional específico
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  integer $id Identificador del crédito adicional a elimina
     *
     * @return Renderable
     */
    public function destroy($id)
    {
        /* Objeto con información de la modificación presupuestaria a eliminar */
        $BudgetAditionalCredit = BudgetModification::find($id);

        if ($BudgetAditionalCredit) {
            $BudgetAditionalCredit->delete();
        }

        return response()->json(['record' => $BudgetAditionalCredit, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene los registros a mostrar en listados de componente Vue
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return JsonResponse
     */
    public function vueList()
    {
        return response()->json([
            'records' => BudgetModification::where('type', 'C')->with([
                'institution', 'budgetModificationAccounts'
            ])->get()
        ], 200);
    }
}
