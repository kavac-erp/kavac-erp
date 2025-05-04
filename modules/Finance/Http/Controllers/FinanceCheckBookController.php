<?php

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Finance\Models\FinanceBank;
use Modules\Finance\Models\FinanceCheckBook;
use Modules\Finance\Models\FinanceBankAccount;

/**
 * @class FinanceCheckBookController
 * @brief Controlador para las chequeras
 *
 * Clase que gestiona las chequeras
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FinanceCheckBookController extends Controller
{
    use ValidatesRequests;

    /**
     * Lista de elementos a mostrar en selectores
     *
     * @var array $data
     */
    protected $data = [];

    /**
     * Listado de chequeras
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $banks = FinanceBank::with(['financeAgencies' => function ($queryAgencies) {
            return $queryAgencies->with(['bankAccounts' => function ($queryAccounts) {
                return $queryAccounts->with('financeCheckBooks');
            }]);
        }])->orderBy('name')->get()->filter(function ($bank) {
            foreach ($bank->financeAgencies as $agency) {
                foreach ($agency->bankAccounts as $account) {
                    foreach ($account->financeCheckBooks as $check_book) {
                        return $bank;
                    }
                }
            }
        });

        $financeCheckBooks = [];

        foreach ($banks as $bank) {
            foreach ($bank->financeAgencies as $agency) {
                foreach ($agency->bankAccounts as $account) {
                    $checkBookCode = '';
                    foreach ($account->financeCheckBooks as $check_book) {
                        if ($checkBookCode !== $check_book->code) {
                            $numbers = [];
                            foreach ($account->financeCheckBooks as $check) {
                                array_push($numbers, $check->number);
                            }
                            $checkBookCode = $check_book->code;
                            array_push($financeCheckBooks, [
                                'finance_bank' => $bank->name, 'code' => $checkBookCode, 'id' => $checkBookCode,
                                'checks' => $account->financeCheckBooks->count(),
                                'finance_bank_id' => $bank->id, 'finance_bank_account_id' => $account->id,
                                'numbers' => $numbers,
                                'cant_checks' => $account->financeCheckBooks->first()->number .
                                                 '...' . $account->financeCheckBooks->last()->number,
                            ]);
                        }
                    }
                }
            }
        }

        return response()->json(['records' => $financeCheckBooks], 200);
    }

    /**
     * Muestra el formulario para crear un nuevo registro de chequeras
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('finance::create');
    }

    /**
     * Almacena un nuevo registro de chequeras
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'code' => ['required'],
            'finance_bank_account_id' => ['required'],
            'numbers' => ['required', 'array', 'min:1']
        ], [
            'code.required' => ('El campo serial / código es obligatorio.'),
            'finance_bank_account_id.required' => ('El campo banco es obligatorio.'),
            'numbers.required' => ('El campo número de cheque es obligatorio. Se deben registrar los números de cheques, pulsando el icono +'),
            'numbers.min' => ('Debe ingresar al menos 1 numero de cheque.'),
        ]);

        foreach ($request->numbers as $number) {
            if ($number == "") {
                $error[0] = "El campo número de cheque es obligatorio. Se deben registrar los números de cheques, pulsando el icono +";
                return response()->json(['result' => true, 'errors' => ["code" => $error]], 422);
            }

            //consulta que no exista el campo numero de cheque repetido con el codigo en la base de datos.
            $checksnumber = FinanceCheckBook::where('number', $number)->where('code', $request->code)->first();
            if ($checksnumber) {
                $error[0] = "El campo Serial / Código o el Cheque # ya ha sido registrado en el sistema";
                return response()->json(['result' => true, 'errors' => ["code" => $error]], 422);
            }

            //consulta que no exista el campo numero de cheque esta repetido en el formulario
            if (count($request->numbers) > count(array_unique($request->numbers))) {
                $error[0] = "El campo numero de cheque esta repetido en el formulario";
                return response()->json(['result' => true, 'errors' => ["code" => $error]], 422);
            }
        }

        //consulta que no exista el campo codigo repetido en base de datos.
        $checkscode = FinanceCheckBook::where('code', $request->code)->first();
        if ($checkscode) {
            $error[0] = "El campo código chequera ya ha sido registrado";
            return response()->json(['result' => true, 'errors' => ["code" => $error]], 422);
        }

        foreach ($request->numbers as $number) {
            FinanceCheckBook::create([
                 'code' => $request->code,
                'finance_bank_account_id' => $request->finance_bank_account_id,
               'number' => $number,
              ]);
        }


        return response()->json(['result' => true, 'message' => 'Success'], 200);
    }

    /**
     * Muestra los detalles de un registro de chequeras
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('finance::show');
    }

    /**
     * Muestra el formulario para editar un registro de chequeras
     *
     * @param  integer  $id ID del registro de chequeras
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $record = FinanceCheckBook::find($id);
        return view('finance::create', ['orderid' => $id, 'record' => $record]);
    }

    /**
     * Actualiza un registro de chequeras
     *
     * @param  Request $request Datos de la petición
     * @param  integer $id      ID del registro de chequeras
     *
     * @return void
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Elimina un registro de chequeras
     *
     * @param  integer $id ID del registro de chequeras
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $checksUsed = FinanceCheckBook::where(['code' => $id, 'used' => true])->get();

        if (!$checksUsed->isEmpty()) {
            return response()->json([
                'error' => true, 'message' => 'La chequera posee cheques emitidos y no puede ser eliminada'
            ], 200);
        }

        foreach (FinanceCheckBook::where('code', $id)->get() as $check) {
            $check->delete();
        }
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Obtiene los datos de las cuenta bancarias
     *
     * @author Miguel Narvaez <mnarvaez@cenditel.gob.ve> | <miguelnarvaez31@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBanksAccounts($bank_id)
    {
        $bank_account_id = ($bank_id)
                    ? FinanceBankAccount::where('finance_banking_agency_id', $bank_id)->get()
                    : FinanceBankAccount::all();
        $this->data = [['id' => '', 'text' => 'Seleccione...']];
        foreach ($bank_account_id as $account) {
            $this->data[] = [
                'id' => $account->id,
                'text' => $account->ccc_number
            ];
        }

        return response()->json($this->data);
    }
}
