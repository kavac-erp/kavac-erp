<?php

namespace Modules\Budget\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Budget\Models\BudgetAccount;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * @class BudgetAccountController
 * @brief Controlador de Cuentas Presupuestarias
 *
 * Clase que gestiona las Cuentas Presupuestarias
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetAccountController extends Controller
{
    use ValidatesRequests;

    /**
     * Arreglo con información de las cuentas presupuestarias
     *
     * @var array $budget_account_choices
     */
    public $budget_account_choices;

    /**
     * Arreglo con los datos a implementar en los atributos del formulario
     *
     * @var array $header
     */
    public $header;

    /**
     * Arreglo con las reglas de validación sobre los datos de un formulario
     *
     * @var array $validate_rules
     */
    public $validate_rules;

    /**
     * Mensajes de validación
     *
     * @var array $messages
     */
    protected $messages;

    /**
     * Define la configuración de la clase
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:budget.account.list', ['only' => 'index', 'vueList']);
        $this->middleware('permission:budget.account.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:budget.account.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:budget.account.delete', ['only' => 'destroy']);

        /* Arreglo de opciones a implementar en el formulario */
        $this->header = [
            'route' => 'budget.accounts.store',
            'method' => 'POST',
            'role' => 'form',
            'class' => 'form-horizontal',
        ];

        /* Arreglo de opciones a representar en la plantilla para su selección */
        $this->budget_account_choices = template_choices(
            BudgetAccount::class,
            ['code', '-', 'denomination'],
            ['subspecific' => '00']
        );

        /* Define las reglas de validación para el formulario */
        $this->validate_rules = [
            'code' => ['required', 'max:13', 'min:13'],
            'denomination' => ['required'],
            'account_type' => ['required'],
        ];

        /* Define los mensajes de valición para el formulario */
        $this->messages = [
            'code.required' => 'El campo código es obligatorio.',
            'code.max' => 'El campo código debe tener una longitud de 13 caracteres.',
            'code.min' => 'El campo código debe tener una longitud de 13 caracteres.',
            'denomination.required' => 'El campo denominación es obligatorio.',
            'account_type.required' => 'El campo cuenta es obligatorio.',
        ];
    }

    /**
     * Muestra un listado de cuentas presupuestarias
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return Renderable
     */
    public function index()
    {
        /* Objeto que contiene todos los registros de cuentas presupuestarias */
        $records = BudgetAccount::all();
        return view('budget::accounts.list', compact('records'));
    }

    /**
     * Muestra un formulario ara la creación de una cuenta presupuestaria
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
        $budget_accounts = $this->budget_account_choices;

        return view('budget::accounts.create-edit-form', compact('header', 'budget_accounts'));
    }

    /**
     * Crea una nueva cuenta presupuestaria
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  Request $request Datos de la petición realizada
     *
     * @return Renderable
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validate_rules, $this->messages);
        list($group, $item, $generic, $specific, $subspecific) = explode(".", $request->code);

        /* Objeto que contiene los datos de la cuenta ya registrada si existe */
        $budgetAccount = BudgetAccount::where('group', $group)
                                      ->where('item', $item)
                                      ->where('generic', $generic)
                                      ->where('specific', $specific)
                                      ->where('subspecific', $subspecific)
                                      ->where('active', true)->first();

        /*
         * Si la cuenta a registrar ya existe en la base de datos y la nueva cuenta se indica como activa,
         * se desactiva la cuenta anterior
         */
        if ($budgetAccount && $request->active !== null) {
            /* define si la cuenta esta activa */
            $budgetAccount->active = false;
            $budgetAccount->save();
        }

        /* Objeto con información de la cuenta de nivel superior, si existe */
        $parent = BudgetAccount::getParent($group, $item, $generic, $specific, $subspecific);

        /* Registra la nueva cuenta presupuestaria */
        BudgetAccount::create([
            'group' => $group,
            'item' => $item,
            'generic' => $generic,
            'specific' => $specific,
            'subspecific' => $subspecific,
            'denomination' => $request->denomination,
            'resource' => ($request->account_type == "resource"),
            'egress' => ($request->account_type == "egress"),
            'active' => ($request->active !== null),
            'original' => ($request->original !== null),
            'parent_id' => ($parent == false) ? null : $parent->id,
            'disaggregate_tax' => ($request->disaggregate_tax !== null) ? true : false
        ]);

        $request->session()->flash('message', ['type' => 'store']);
        return redirect()->route('budget.accounts.index');
    }

    /**
     * Muestra información de la cuenta presupuestaria
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return void
     */
    public function show()
    {
        //
    }

    /**
     * Muestra el formulario para la edición de una cuenta presupuestaria
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  integer $id Identificador de la cuenta presupuestaria a modificar
     *
     * @return Renderable
     */
    public function edit($id)
    {
        /* Objeto con información de la cuenta presupuestaria a modificar */
        $budgetAccount = BudgetAccount::find($id);

        $this->header['route'] = ['budget.accounts.update', $budgetAccount->id];
        $this->header['method'] = 'PUT';
        /* Arreglo de opciones a implementar en el formulario */
        $header = $this->header;

        /* Arreglo de opciones a representar en la plantilla para su selección */
        $budget_accounts = $this->budget_account_choices;

        /* Objeto con datos del modelo a modificar */
        $model = $budgetAccount;

        return view(
            'budget::accounts.create-edit-form',
            compact('header', 'budget_accounts', 'model')
        );
    }

    /**
     * Actualiza los datos de la cuenta presupuestaria
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  Request $request Datos de la petición realizada
     * @param  integer $id      Identificador de la cuenta presupuestaria a modificar
     *
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, $this->validate_rules, $this->messages);
        list($group, $item, $generic, $specific, $subspecific) = explode(".", $request->code);

        /* Objeto con información de la cuenta presupuestaria a modificar */
        $budgetAccount = BudgetAccount::find($id);
        $budgetAccount->fill($request->all());
        $budgetAccount->group = $group;
        $budgetAccount->item = $item;
        $budgetAccount->generic = $generic;
        $budgetAccount->specific = $specific;
        $budgetAccount->subspecific = $subspecific;
        $budgetAccount->disaggregate_tax = ($request->disaggregate_tax !== null) ? true : false;
        $budgetAccount->save();

        $request->session()->flash('message', ['type' => 'update']);
        return redirect()->route('budget.accounts.index');
    }

    /**
     * Elimina una cuenta presupuestaria
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  integer $id Identificador de la cuenta presupuestaria a eliminar
     *
     * @return Renderable
     */
    public function destroy($id)
    {
        /* Objeto con datos de la cuenta presupuestaria a eliminar */
        $budgetAccount = BudgetAccount::find($id);

        if ($budgetAccount) {
            if ($budgetAccount->restrictDelete()) {
                return response()->json(['error' => true, 'message' => 'El registro no se puede eliminar'], 200);
            }
            $budgetAccount->delete();
        }

        return response()->json(['record' => $budgetAccount, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene listado de registros
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueList()
    {
        /* Arreglo con información de cuentas presupuestarias */
        $budgetAccounts = BudgetAccount::toBase()->get()->map(function ($account) {
            return [
                'code' => "{$account->group}.{$account->item}.{$account->generic}.{$account->specific}.{$account->subspecific}",
                'denomination' => $account->denomination,
                'original' => $account->original ? 'SI' : 'NO',
                'id' => $account->id
            ];
        });
        return response()->json(['records' => $budgetAccounts], 200);
    }

    /**
     * Obtiene un listado de cuentas de egreso
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  boolean $to_formulate Indica si las cuentas a retornar son para formulación,
     *                               en cuyo caso incluye la inicialización de variables para cada cuenta
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function egressAccounts($to_formulate = null)
    {
        /* Objeto que contiene los datos de las cuentas presupuestarias de egreso activas */
        $accounts = BudgetAccount::toBase()->where(['active' => true, 'egress' => true])->get()->map(function ($acc) use ($to_formulate) {
            /* Arreglo con información de la cuenta presupuestaria */
            $data = [
                'id' => $acc->id,
                'code' => "{$acc->group}.{$acc->item}.{$acc->generic}.{$acc->specific}.{$acc->subspecific}",
                'denomination' => $acc->denomination,
                'group' => $acc->group,
                'item' => $acc->item,
                'generic' => $acc->generic,
                'specific' => $acc->specific,
                'subspecific' => $acc->subspecific,
                'tax_id' => $acc->tax_id
            ];
            if (!is_null($to_formulate) && $to_formulate) {
                $data['formulated'] = false;
                $data['locked'] = $acc->specific === '00';
                $data['total_real_amount'] = 0;
                $data['total_estimated_amount'] = 0;
                $data['total_year_amount'] = 0;
                $data['jan_amount'] = 0;
                $data['feb_amount'] = 0;
                $data['mar_amount'] = 0;
                $data['apr_amount'] = 0;
                $data['may_amount'] = 0;
                $data['jun_amount'] = 0;
                $data['jul_amount'] = 0;
                $data['aug_amount'] = 0;
                $data['sep_amount'] = 0;
                $data['oct_amount'] = 0;
                $data['nov_amount'] = 0;
                $data['dec_amount'] = 0;
            }

            return $data;
        });

        return response()->json(['records' => $accounts], 200);
    }

    /**
     * Obtiene detalles de una cuenta presupuestaria
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  integer $id Identificar de la cuenta presupuestaria de la cual se requiere información
     *
     * @return JsonResponse        JSON con los datos de la cuenta presupuestaria
     */
    public function getDetail($id)
    {
        return response()->json([
            'result' => true, 'record' => BudgetAccount::find($id)
        ], 200);
    }

    /**
     * Determina el próximo valor disponible para la cuenta a ser agregada
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param integer $parent_id Identificador de la cuenta padre de la cual se va a generar el nuevo código
     *
     * @return JsonResponse                JSON con los datos del nuevo código generado
     */
    public function setChildrenAccount($parent_id)
    {
        /* Objeto con información de la cuenta presupuestaria de nivel superior */
        $parent = BudgetAccount::find($parent_id);

        /* Contiene el código del ítem */
        $item = $parent->item;
        /* Contiene el código de la genérica */
        $generic = $parent->generic;
        /* Contiene el código de la específica */
        $specific = $parent->specific;
        /* Contiene el código de la sub específica */
        $subspecific = $parent->subspecific;

        if ($parent->item === "00") {
            /* Contiene información de la cuenta presupuestaria por el código del ítem */
            $currentItem = BudgetAccount::where(['group' => $parent->group])->orderBy('item', 'desc')->first();
            /* Contiene el código inmediatamente disponible para su registro */
            $item = (strlen(intval($currentItem->item)) < 2 || intval($currentItem->item) < 99)
                    ? (intval($currentItem->item) + 1) : $currentItem->item;
            /* Determina la longitud de la cadena para agregar un cero a la izquierda en
            caso de requerirlo */
            $item = (strlen($item) === 1) ? "0$item" : $item;
        } elseif ($parent->generic === "00") {
            /* Contiene información de la cuenta presupuestaria por el código de la genérica */
            $currentGeneric = BudgetAccount::where(['group' => $parent->group, 'item' => $parent->item])
                                           ->orderBy('generic', 'desc')->first();
            /* Contiene el código inmediatamente disponible para su registro */
            $generic = (strlen(intval($currentGeneric->generic)) < 2 || intval($currentGeneric->generic) < 99)
                       ? (intval($currentGeneric->generic) + 1) : $currentGeneric->generic;
            /* Determina la longitud de la cadena para agregar un cero a la izquierda en caso
            de requerirlo */
            $generic = (strlen($generic) === 1) ? "0$generic" : $generic;
        } elseif ($parent->specific === "00") {
            /* Contiene información de la cuenta presupuestaria por el código de la específica */
            $currentSpecific = BudgetAccount::where([
                'group' => $parent->group, 'item' => $parent->item, 'generic' => $parent->generic
            ])->orderBy('specific', 'desc')->first();
            /* Contiene el código inmediatamente disponible para su registro */
            $specific = (strlen(intval($currentSpecific->specific)) < 2 || intval($currentSpecific->specific) < 99)
                        ? (intval($currentSpecific->specific) + 1) : $currentSpecific->specific;
            /* Determina la longitud de la cadena para agregar un cero a la izquierda en caso
            de requerirlo */
            $specific = (strlen($specific) === 1) ? "0$specific" : $specific;
        } elseif ($parent->subspecific === "00") {
            /* Contiene información de la cuenta presupuestaria por el código de la sub específica */
            $currentSubSpecific = BudgetAccount::where([
                'group' => $parent->group, 'item' => $parent->item, 'generic' => $parent->generic,
                'specific' => $parent->specific
            ])->orderBy('subspecific', 'desc')->first();
            /* Contiene el código inmediatamente disponible para su registro */
            $subspecific = (
                strlen(intval($currentSubSpecific->subspecific)) < 2 || intval($currentSubSpecific->subspecific) < 99
            ) ? (intval($currentSubSpecific->subspecific) + 1) : $currentSubSpecific->subspecific;
            /* Determina la longitud de la cadena para agregar un cero a la izquierda en caso
            de requerirlo */
            $subspecific = (strlen($subspecific) === 1) ? "0$subspecific" : $subspecific;
        }

        /* Arreglo con información de la nueva cuenta presupuestaria de nivel inferior disponible */
        $newAccount = [
            'group' => $parent->group, 'item' => (string)$item, 'generic' => (string)$generic,
            'specific' => (string)$specific, 'subspecific' => (string)$subspecific,
            'denomination' => $parent->denomination, 'resource' => $parent->resource, 'egress' => $parent->egress
        ];

        return response()->json(['result' => true, 'new_account' => $newAccount], 200);
    }
}
