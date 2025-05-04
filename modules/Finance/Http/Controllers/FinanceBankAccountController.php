<?php

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Finance\Models\FinanceBank;
use Modules\Finance\Models\FinanceBankAccount;
use App\Rules\DateBeforeFiscalYear;

/**
 * @class FinanceBankAccountController
 * @brief Controlador para las cuentas bancarias
 *
 * Clase que gestiona las cuentas bancarias
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FinanceBankAccountController extends Controller
{
    use ValidatesRequests;

    /**
     * Lista de atributos personalizados
     *
     * @var array $customAttributes
     */
    protected $customAttributes;

    /**
     * Lista de elementos a mostrar en selectores
     *
     * @var array $data
     */
    protected $data = [];

    /**
    * Método constructor de la clase
    *
    * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
    *
    * @return void
    */
    public function __construct()
    {
        $this->customAttributes = [
            'ccc_number' => 'código cuenta cliente ',
            'description' => 'descripción',
            'opened_at' => 'fecha de apertura',
            'finance_banking_agency_id' => 'agencia',
            'finance_account_type_id' => 'tipo de cuenta',
            'accounting_account_id' => 'cuenta contable'
        ];
    }

    /**
     * Listado de Cuentas bancarias
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json([
            'records' => FinanceBankAccount::with(['financeBankingAgency' => function ($query) {
                return $query->with('financeBank');
            }])->orderBy('ccc_number')->get()
        ], 200);
    }

    /**
     * Muestra el formulario para crear una nueva Cuenta bancaria
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('finance::create');
    }

    /**
     * Almacena una nueva Cuenta bancaria
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'ccc_number' => ['required', 'numeric', 'digits_between:16, 20','unique:finance_bank_accounts,ccc_number'],
            'description' => ['required'],
            'opened_at' => ['required', 'date'],
            'finance_banking_agency_id' => ['required'],
            'finance_account_type_id' => ['required'],
            'accounting_account_id' => ['required'],
            'finance_bank_id' => ['required']
        ], [
            'ccc_number.digits_between' => "El campo código cuenta cliente debe tener 20 dígitos, " .
                                           "incluyendo los 4 dígitos del código del banco.",
        ], $this->customAttributes);

        $ccc_number = $request->bank_code . $request->ccc_number;
        $ccc_numbers = FinanceBankAccount::where('ccc_number', $ccc_number)->first();
        if ($ccc_numbers) {
            $error[0] = "El campo código cuenta cliente ya ha sido registrado.";
            return response()->json(['result' => true, 'errors' => ["code" => $error]], 422);
        }

        $financeBankAccount = FinanceBankAccount::create([
            'ccc_number' => $request->bank_code . $request->ccc_number,
            'description' => $request->description,
            'opened_at' => $request->opened_at,
            'finance_banking_agency_id' => $request->finance_banking_agency_id,
            'finance_account_type_id' => $request->finance_account_type_id,
            'accounting_account_id' => $request->accounting_account_id,
            'finance_bank_id' => $request->finance_bank_id
        ]);

        return response()->json(['record' => $financeBankAccount, 'message' => 'Success'], 200);
    }

    /**
     * Muestra detalles de una Cuenta bancaria
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('finance::show');
    }

    /**
     * Muestra el formulario para editar una Cuenta bancaria
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('finance::edit');
    }

    /**
     * Actualiza una Cuenta bancaria
     *
     * @param  Request $request Datos de la petición
     * @param  integer $id      ID de la Cuenta bancaria
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        /* Datos de la cuenta bancaria */
        $bankAccount = FinanceBankAccount::find($id);

        $this->validate($request, [
            'ccc_number' => [
                'required',
                'numeric',
                'digits_between:16, 20',
                'unique:finance_bank_accounts,ccc_number,' . $bankAccount->id
            ],
            'description' => ['required'],
            'opened_at' => ['required', 'date', new DateBeforeFiscalYear('fecha de apertura')],
            'finance_banking_agency_id' => ['required'],
            'finance_account_type_id' => ['required'],
            'accounting_account_id' => ['required']
        ], [
            'ccc_number.digits_between' => "El campo código cuenta cliente debe tener 20 dígitos, " .
                                           "incluyendo los 4 dígitos del código del banco.",
        ], $this->customAttributes);

        $ccc_number = $request->bank_code . $request->ccc_number;
        $ccc_numbers = FinanceBankAccount::where(['id' => $id, 'ccc_number' => $ccc_number])->first();
        if (!$ccc_numbers) {
            $error[0] = "El campo código cuenta cliente ya existe.";
            return response()->json(['result' => true, 'errors' => ["code" => $error]], 422);
        }

        $bankAccount->ccc_number = $request->bank_code . $request->ccc_number;
        $bankAccount->description = $request->description;
        $bankAccount->opened_at = $request->opened_at;
        $bankAccount->finance_banking_agency_id = $request->finance_banking_agency_id;
        $bankAccount->finance_account_type_id = $request->finance_account_type_id;
        $bankAccount->accounting_account_id = $request->accounting_account_id;
        $bankAccount->save();

        return response()->json(['message' => 'Registro actualizado correctamente'], 200);
    }

    /**
     * Elimina una Cuenta bancaria
     *
     * @param  integer $id ID de la Cuenta bancaria
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        /* Datos de la cuenta bancaria */
        $financeBankAccount = FinanceBankAccount::find($id);
        $financeBankAccount->delete();
        return response()->json(['record' => $financeBankAccount, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene todas las cuentas bancarias asociadas a una entidad bancaria
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  integer $bank_id                 Identificador de la entidad bancaria de la que se
     *                                          desean obtener las cuentas
     *
     * @return \Illuminate\Http\JsonResponse    JSON con los datos de las cuentas bancarias asociadas
     *                                          al banco
     */
    public function getBankAccounts($bank_id)
    {
        /* Datos de la entidad bancaria */
        $bank = FinanceBank::where('id', $bank_id)->with(['financeAgencies' => function ($query) {
            return $query->with('bankAccounts');
        }])->first();

        $accounts = [['id' => '', 'text' => 'Seleccione...']];
        foreach ($bank->financeAgencies as $agency) {
            foreach ($agency->bankAccounts as $bank_account) {
                $accounts[] = [
                    'id' => $bank_account->id,
                    'text' => $bank_account->formated_ccc_number
                ];
            }
        }

        return response()->json(['result' => true, 'accounts' => $accounts], 200);
    }

    /**
     * Obtiene los datos de las cuentas bancarias para mostrar en campos select.
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  Request $request              Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse Devuelve un JSON con listado de las entidades bancarias
     */
    public function getFinanceBankAccount(Request $request)
    {
        if ($request->group) {
            $bank_accounts = FinanceBankAccount::with(['financeAccountType', 'financeBankingAgency' => function ($query) {
                $query->with('financeBank');
            }])->get();
            $this->data = [['id' => '', 'text' => 'Seleccione...']];

            $parents = [];

            foreach ($bank_accounts as $bank_account) {
                if (!array_key_exists($bank_account->finance_bank_id, $parents)) {
                    $parents[$bank_account->finance_bank_id] =
                    [
                        'text' => $bank_account->financeBankingAgency && $bank_account->financeBankingAgency->financeBank ?
                                    $bank_account->financeBankingAgency->financeBank->name : '',

                        'children' => [
                            0 => [
                                'id' => $bank_account->id,
                                'text' => $bank_account->formated_ccc_number
                            ]
                        ]
                    ];
                } else {
                    $parents[$bank_account->finance_bank_id]['children'][] =
                    [
                        'id' => $bank_account->id,
                        'text' => $bank_account->formated_ccc_number
                    ];
                }
            }

            foreach ($parents as $parent) {
                array_push($this->data, $parent);
            }
        } else {
            $bank_accounts = FinanceBankAccount::with(['financeAccountType', 'accountingAccount',
                'financeBankingAgency' => function ($query) {
                    $query->with('financeBank');
                }
            ])->get();
            $this->data = [['id' => '', 'text' => 'Seleccione...']];
            foreach ($bank_accounts as $bank_account) {
                $this->data[] = [
                    'id' => $bank_account->id,
                    'text' => $bank_account->formated_ccc_number,
                    'bank_name' => $bank_account->financeBankingAgency && $bank_account->financeBankingAgency->financeBank ?
                                   $bank_account->financeBankingAgency->financeBank->name : '',
                    'bank_account_type' => $bank_account->financeAccountType->name,
                    'accounting_account_id' => $bank_account->accountingAccount->id
                ];
            }
        }

        return response()->json($this->data);
    }
}
