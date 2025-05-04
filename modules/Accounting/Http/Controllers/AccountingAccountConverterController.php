<?php

namespace Modules\Accounting\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Nwidart\Modules\Facades\Module;
use Modules\Accounting\Models\Accountable;
use Illuminate\Contracts\Support\Renderable;
use Modules\Accounting\Models\BudgetAccount;
use Modules\Accounting\Models\AccountingAccount;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * @class AccountingAccountConverterController
 * @brief Controlador para la conversión de cuentas presupuestarias y patrimoniales
 *
 * Clase que gestiona la conversión entre cuentas presupuestarias y patrimoniales
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AccountingAccountConverterController extends Controller
{
    use ValidatesRequests;

    /**
     * Define la configuración de la clase
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     */
    public function __construct()
    {
        /* Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:accounting.converter.index', ['only' => 'index']);
        $this->middleware('permission:accounting.converter.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:accounting.converter.edit', ['only' => ['update']]);
        $this->middleware('permission:accounting.converter.delete', ['only' => 'destroy']);
    }

    /**
     * Muestra la vista principal para mostrar las conversiones
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return Renderable
     */
    public function index()
    {
        /* determina si esta instalado el modulo Budget */
        $has_budget = (Module::has('Budget') && Module::isEnabled('Budget'));
        return view('accounting::account_converters.index', compact('has_budget'));
    }

    /**
     * Registros de las cuentas patrimoniales al componente
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return JsonResponse
     */
    public function getAllRecordsAccountingVuejs()
    {
        return response()->json(['records' => $this->getRecordsAccounting()]);
    }

    /**
     * Registros de las cuentas presupuestarias al componente
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return JsonResponse
     */
    public function getAllRecordsBudgetVuejs()
    {
        return response()->json(['records' => $this->getRecordsBudget()]);
    }

    /**
     * Muestra un formulario para crear conversiones de cuentas
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return Renderable
     */
    public function create()
    {
        /* determina si esta instalado el modulo Budget */
        $has_budget = (Module::has('Budget') && Module::isEnabled('Budget'));

        if (!Module::has('Budget') || !Module::isEnabled('Budget')) {
            return view('accounting::account_converters.create', compact('has_budget'));
        }

        /* contiene las cuentas patrimoniales */
        $accountingList = $this->getGroupAccountingAccount();

        /* contiene las cuentas presupuestarias */
        $budgetList = $this->getBudgetAccount();

        return view('accounting::account_converters.create', compact('has_budget', 'accountingList', 'budgetList'));
    }

    /**
     * Crea una nuevas conversiones
     * ejemplo de datos que recibe la función
     * limitacion una cuenta presupuestaria solo puede estar asociada a una cuenta contable
     * limitacion una cuenta contable puede estar asociada a varias cuentas presupuestarias
     * {
     * 'module'                : 'Budget',     Nombre del modulo hacia el cual se relacionara el registro
     * 'model'                 : Modules\\Accounting\\Models\\BudgetAccount',  Clase a la que se hara la relacion
     * 'accountable_id'        : id, identificador del registro a relacionar
     * 'accounting_account_id' : id, identificador de la cuenta patrimonial
     * }
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  Request $request
     *
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'module' => ['required'],
                'model' => ['required'],
                'pair' => [
                    'required',
                    Rule::unique('accountables', 'accountable_id')
                        ->where('accountable_type', $request->model)
                ],
            ],
            [
                'pair.unique' => '¡Atención! Una de estas cuentas ya se encuentran vinculadas.'
            ]
        );
        $duplicates = [];
        $nonDuplicates = [];

        /* Crea el registro de conversiones */
        if (Module::has($request->module) && Module::isEnabled($request->module)) {
            foreach ($request->pair as $value) {
                $accountables = Accountable::where('accountable_id', $value["budgetSelect"])

                ->where('accountable_type', $request->model)

                ->where('active', true)

                ->orderBy('id', 'ASC')

                ->get();


                if ($accountables->count() > 0) {
                    foreach ($accountables as $accountable) {
                        $duplicates[] = "La relacion " . $accountable->accountable->code . " Y " . $accountable->accountingAccount->code . 'No fue vinculada con exito. La cuenta ' . $accountable->accountable->code . ' ya se encuentra vinculadas.';
                    }
                } else {
                    $budgetAccount = BudgetAccount::where('active', true)->find($value["budgetSelect"]);
                    $accountingAccount = AccountingAccount::where('active', true)->find($value["accountingSelect"]);
                    $nonDuplicates[] =  "La relacion " . $budgetAccount->code . " y " . $accountingAccount->code . ' fue vinculada con exito.';
                    Accountable::updateOrCreate([

                    'accounting_account_id' => $value["accountingSelect"],

                    'accountable_type' => $request->model,

                    'accountable_id' => $value["budgetSelect"],

                    ], ['active' => true]);
                }
            }
            return response()->json([
            'message' => 'Success', 'duplicates' => $duplicates,
            'non_duplicates' => $nonDuplicates
            ], 200);
        }
        return response()->json(['message' => 'No se pudo crear la relacion entre registros.'], 200);
    }

    /**
     * Muestra el formulario para la edición de conversión de cuentas
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  [integer] $id [Identificador de la conversión a modificar]
     *
     * @return Renderable
     */
    public function edit($id)
    {
        /* determina si esta instalado el modulo Budget */
        $has_budget = (Module::has('Budget') && Module::isEnabled('Budget'));

        if (!$has_budget) {
            return view('accounting::account_converters.edit', compact('has_budget'));
        }

        /* contine el registro de conversión a editar */
        $account = Accountable::find($id);

        /* contiene las cuentas patrimoniales disponibles */
        $accountingAccounts = $this->getGroupAccountingAccount();

        /* contiene las cuentas presupuestarias disponibles */
        $budgetAccounts = $this->getBudgetAccount();

        return view(
            'accounting::account_converters.edit',
            compact('has_budget', 'account', 'accountingAccounts', 'budgetAccounts')
        );
    }

    /**
     * Actualiza los datos de la conversión
     * ejemplo de datos que recibe la función en $request
     * {
     * 'module'                : 'Budget',     Nombre del modulo hacia el cual se relacionara el registro
     * 'model'                 : Modules\\Accounting\\Models\\BudgetAccount',  Clase a la que se hara la relacion
     * 'accountable_id'        : id, identificador del registro a relacionar
     * 'accounting_account_id' : id, identificador de la cuenta patrimonial
     * }
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  Request $request Datos de la petición a realizar
     * @param  integer $id      Identificador de la conversión a modificar
     *
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $this->validate(
            $request,
            [
                'module' => ['required'],
                'model' => ['required'],
                'accounting_account_id' => ['required'],
                'accountable_id' => [
                    'required',
                    Rule::unique('accountables', 'accountable_id')
                        ->where('accountable_type', $request->model)
                        ->whereNull('deleted_at')
                        ->whereNotIn('id', [$id])
                ],
            ],
            [
                'accountable_id.unique' => '¡Atención! Estas cuentas ya se encuentran vinculadas.'
            ]
        );

        if (Module::has($request->module) && Module::isEnabled($request->module)) {
            /* Actualiza el registro de conversión a editar */
            $record = Accountable::find($id);

            $record->accounting_account_id = $request->accounting_account_id;
            $record->accountable_id = $request->accountable_id;
            $record->accountable_type = $request->model;
            $record->active = true;
            $record->save();

            return response()->json(['message' => 'Success'], 200);
        }

        return response()->json(['message' => 'No se pudo crear la relacion entre registros.'], 200);
    }

    /**
     * Elimina un conversión
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  integer $id Identificador de la conversión a eliminar
     *
     * @return JsonResponse
     */
    public function destroy($id)
    {
        /* registro de conversión a eliminar */
        $convertion = Accountable::with('accountingAccount', 'accountable')->find($id);
        $compromiseDetails = (
            Module::has('Budget') && Module::isEnabled('Budget')
        ) ? \Modules\Budget\Models\BudgetCompromiseDetail::where('budget_account_id', $convertion->accountable->id)->first() : null;

        if (!$compromiseDetails) {
            $convertion->delete();

            return response()->json(['records' => [], 'message' => 'Success'], 200);
        }

        return response()->json([
            'error' => true,
            'message' => 'El registro no se puede eliminar, debido a que esta siendo usado por otro(s) parámetro(s).',
        ], 200);
    }

    /**
     * Registros en un rango de ids dado
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  Request $request parametros de busqueda
     *
     * @return JsonResponse
     */
    public function getRecords(Request $request)
    {
        /* contendra registros */
        $records = [];

        /* id de rango inicial de busqueda */
        $init_id = 0;

        /* id de rango final de busqueda */
        $end_id = 0;

        if (!$request->all()) {
            $init_id = $request->merge([
                'init_id' => ($request->init_id > $request->end_id) ? $request->end_id : $request->init_id,
            ])->init_id;
            $end_id = $request->merge([
                'end_id' => ($request->init_id > $request->end_id) ? $request->init_id : $request->end_id,
            ])->end_id;
        }

        if ($request->type == 'budget') {
            if ($request->all()) {
                /* Se obtienen el primer y ultimo id de las cuentas presupuestarias */
                $init_id = \Modules\Budget\Models\BudgetAccount::orderBy('created_at', 'ASC')
                    ->where('parent_id', null)->first()->id;
                $end_id = \Modules\Budget\Models\BudgetAccount::orderBy('created_at', 'DESC')->first()->id;
            }

            /* contine los registros de conversión a en un rango de ids */
            $query = Accountable::with('accountable', 'accountingAccount')
                ->where('accountable_id', '>=', $init_id)
                ->where('accountable_id', '<=', $end_id)
                ->orderBy('id', 'ASC')->get();
        } elseif ($request->type == 'accounting') {
            if ($request->all) {
                /* Se obtienen el primer y ultimo id de las cuentas patrimoniales */
                $init_id = AccountingAccount::orderBy('created_at', 'ASC')->where('parent_id', null)->first()->id;
                $end_id = AccountingAccount::orderBy('created_at', 'DESC')->first()->id;
            }

            /* contine los registros de conversión a en un rango de ids */
            $query = Accountable::with('accountingAccount', 'accountable')->where('accounting_account_id', '>=', $init_id)
                ->where('accounting_account_id', '<=', $end_id)
                ->orderBy('id', 'ASC')->get();
        }

        $records = [];
        $cont = 0;
        foreach ($query as $r) {
            $records[$cont] = [
                'id' => $r['id'],
                'codeAccounting' => $r['accountingAccount'] ? $r['accountingAccount']->getCodeAttribute() : "",
                'accounting_account' => $r['accountingAccount'] ? $r['accountingAccount']['denomination'] : "",
                'codeBudget' => $r['accountable'] ? $r['accountable']->getCodeAttribute() : "",
                'budget_account' => $r['accountable'] ? $r['accountable']['denomination'] : "",
                'active' => $r['accountable'] ? $r['active'] : "",
            ];
            $cont++;
        }
        return response()->json(['records' => $records, 'message' => 'Success', 200]);
    }

    /**
     * Consulta los registros del modelo AccountingAccount que posean conversión
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  Request $request [array con listado de cuentas a convertir]
     *                             true= todo, false=solo sin conversiones
     *
     * @return array
     */
    public function getRecordsAccounting()
    {
        /* contendra registros */
        $records = [];
        $index = 0;
        array_push($records, [
            'id' => '',
            'text' => "Seleccione...",
        ]);

        /* ciclo para almacenar en array cuentas patrimoniales disponibles para conversiones */
        foreach (
            AccountingAccount::with('accountable')
            ->where('active', true)
            ->orderBy('group', 'ASC')
            ->orderBy('subgroup', 'ASC')
            ->orderBy('item', 'ASC')
            ->orderBy('generic', 'ASC')
            ->orderBy('specific', 'ASC')
            ->orderBy('subspecific', 'ASC')
            ->orderBy('denomination', 'ASC')
            ->cursor() as $AccountingAccount
        ) {
            array_push($records, [
                'id' => $AccountingAccount->id,
                'text' => "{$AccountingAccount->getCodeAttribute()} - {$AccountingAccount->denomination}",
            ]);
            $index++;
        }

        $records[0]['index'] = $index;

        /* se convierte array a JSON */
        return $records;
    }

    /**
     * Consulta los registros del modelo BudgetAccount que posean conversión
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return JsonResponse|array|null
     */
    public function getRecordsBudget()
    {
        /* contendra registros */
        $records = null;
        $index = 0;

        if (Module::has('Budget') && Module::isEnabled('Budget')) {
            $records = [];
            array_push($records, [
                'id' => '',
                'text' => "Seleccione...",
            ]);

            /* ciclo para almacenar en array cuentas presupuestarias disponibles para conversiones */
            foreach (
                BudgetAccount::with('accountingAccounts')
                ->where('active', true)
                ->orderBy('group', 'ASC')
                ->orderBy('item', 'ASC')
                ->orderBy('generic', 'ASC')
                ->orderBy('specific', 'ASC')
                ->orderBy('subspecific', 'ASC')
                ->orderBy('denomination', 'ASC')
                ->cursor() as $AccountingAccount
            ) {
                array_push($records, [
                    'id' => $AccountingAccount->id,
                    'text' => "{$AccountingAccount->getCodeAttribute()} - {$AccountingAccount->denomination}",
                ]);
            }
            $index++;
        }

        $records[0]['index'] = $index;
        /* se convierte array a JSON */
        return $records;
    }

    /**
     * Cuenta patrimonial correspondiente a la presupuestaria
     *
     * @author    Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  integer $id id de cuenta patrimonial
     *
     * @return JsonResponse     cuenta patrimonial correspondiente a la presupuestaria
     */
    public function budgetToAccount($id)
    {
        /* registros relacionados */
        $convertion = Accountable::with('accountingAccount')->where('accountable_id', $id)->first();
        return response()->json(['record' => $convertion->accounting_account, 'message' => 'Success'], 200);
    }

    /**
     * Cuenta presupuestaria correspondiente a la patrimonial
     *
     * @author    Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  integer $id id de cuenta presupuestaria
     *
     * @return JsonResponse     cuenta presupuestaria correspondiente a la patrimonial
     */
    public function accountToBudget($id)
    {
        /* registros relacionados */
        $convertion = Accountable::with('accountable')->where('accounting_account_id', $id)->first();
        return response()->json(['record' => $convertion->accountable, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene los grupos de cuentas contables
     *
     * @author    Francisco Escala <fescala@cenditel.gob.ve>
     *
     * @param  boolean $first Buscar desde la primera cuenta patrimonial
     * @param  integer|null $parent_id Identificador de la cuenta de nivel superior
     *
     * @return JsonResponse|boolean|string     cuenta presupuestaria correspondiente a la patrimonial
     */
    public function getGroupAccountingAccount($first = true, $parent_id = null)
    {
        /** @todo Crear actions y mover todas las coincidencias getGroupAccountingAccount */
        /* Colección con información de las cuentas contables regsitradas en el cátalogo de cuentas patrimoniales  */
        $accountings = AccountingAccount::where('active', true)
            ->orderBy('group')
            ->orderBy('subgroup')
            ->orderBy('item')
            ->orderBy('generic')
            ->orderBy('specific')
            ->orderBy('subspecific')
            ->orderBy('institutional')
            ->toBase()->get();
        /* Arreglo con el listado de opciones de cuentas patrimoniales a seleccionar */
        $records = $accountings->map(function ($a) {
            return [
                'id' => $a->id,
                'text' => "{$a->group}.{$a->subgroup}.{$a->item}.{$a->generic}.{$a->specific}.{$a->subspecific}.{$a->institutional} - {$a->denomination}",
                'disabled' => ($a->original) ?: false,
            ];
        })->toArray();
        array_unshift($records, [
            'id' => '',
            'text' => 'Seleccione...',
        ]);

        return json_encode($records);
    }

    /**
     * Obtener cuenta presupuestaria
     *
     * @author    Francisco Escala <fescala@cenditel.gob.ve>
     *
     * @param  boolean $first Buscar desde la primera cuenta patrimonial
     * @param  integer|null $parent_id Identificador de la cuenta de nivel superior
     *
     * @return JsonResponse|boolean|string
     */
    public function getBudgetAccount($first = true, $parent_id = null)
    {
        /** @todo Crear actions y mover todas las coicidencias getBudgetAccount */
        /* contendra registros */
        $records = null;
        $index = 0;

        if (Module::has('Budget') && Module::isEnabled('Budget')) {
            $records = [];
            array_push($records, [
                'id' => '',
                'text' => "Seleccione...",
            ]);

            /* ciclo para almacenar en array cuentas presupuestarias disponibles para conversiones */
            foreach (
                BudgetAccount::query()
                ->where('active', true)
                ->orderBy('group', 'ASC')
                ->orderBy('item', 'ASC')
                ->orderBy('generic', 'ASC')
                ->orderBy('specific', 'ASC')
                ->orderBy('subspecific', 'ASC')
                ->orderBy('denomination', 'ASC')
                ->cursor() as $AccountingAccount
            ) {
                array_push($records, [
                    'id' => $AccountingAccount->id,
                    'text' => "{$AccountingAccount->getCodeAttribute()} - {$AccountingAccount->denomination}",
                ]);
            }
            $index++;
        }

        $records[0]['index'] = $index;
        /* se convierte array a JSON */
        return json_encode($records);
    }
}
