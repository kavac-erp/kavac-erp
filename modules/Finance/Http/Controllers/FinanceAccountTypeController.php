<?php

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Log;
use Modules\Finance\Models\FinanceAccountType;

/**
 * @class FinanceAccountTypeController
 * @brief Controlador para los tipos de cuenta bancaria
 *
 * Clase que gestiona los tipos de cuenta bancaria
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FinanceAccountTypeController extends Controller
{
    use ValidatesRequests;

    /**
     * Lista de elementos a mostrar
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
        $this->data[0] = [
            'id' => '',
            'text' => 'Seleccione...'
        ];
    }

    /**
     * Listado de tipos de cuentas bancarias
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => FinanceAccountType::all()], 200);
    }

    /**
     * Muestra el formulario para crear un nuevo tipo de cuenta bancaria
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('finance::create');
    }

    /**
     * Almacena un nuevo tipo de cuenta bancaria
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'max:100', 'unique:finance_account_types,name'],
            'code' => ['required', 'max:10', 'unique:finance_account_types,code'],
        ], ['code.required' => 'El código asociado al tipo de cuenta es obligatorio']);

        $financeAccountType = FinanceAccountType::create([
            'name' => $request->name,
            'code' => $request->code
        ]);

        return response()->json(['record' => $financeAccountType, 'message' => 'Success'], 200);
    }

    /**
     * Muestra detalles de un tipo de cuenta bancaria
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('finance::show');
    }

    /**
     * Muestra el formulario para editar un tipo de cuenta bancaria
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('finance::edit');
    }

    /**
     * Actualiza un tipo de cuenta bancaria
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            /* Datos del tipo de cuenta bancaria */
            $financeAccountType = FinanceAccountType::find($id);

            $seedRegisters = get_json_resource('Data/FinanceAccountType.json', 'finance');

            $financeAccountTypeRegister = $financeAccountType
                ->makeHidden(['id','created_at','updated_at', 'deleted_at'])
                ->toArray();

            if (!in_array($financeAccountTypeRegister, $seedRegisters)) {
                $financeAccountTypeRegister = (object)$financeAccountTypeRegister;
                $this->validate($request, [
                    'name' => ['required', 'max:100', 'unique:finance_account_types,name,' . $financeAccountType->id],
                    'code' => ['required', 'max:10', 'unique:finance_account_types,code,' . $financeAccountType->id],
                    ], ['code.required' => 'El código asociado al tipo de cuenta es obligatorio']);
                $financeAccountType->name = $request->name;
                $financeAccountType->code = $request->code;
                $financeAccountType->save();
                return response()->json(['message' => 'Registro actualizado correctamente'], 200);
            } else {
                return response()->json(['error' => true, 'message'
                    => 'No se puede Modificar el registro'], 403);
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json(['error' => true, 'message' => __($th->getMessage())], 403);
        }
    }

    /**
     * Elimina un tipo de cuenta bancaria
     *
     * @param  integer $id ID del tipo de cuenta bancaria
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            /* Datos del tipo de cuenta bancaria */
            $financeAccountType = FinanceAccountType::find($id);
            $seedRegisters = get_json_resource('Data/FinanceAccountType.json', 'finance');
            $financeAccountTypeRegister = $financeAccountType
                ->makeHidden(['id','created_at','updated_at', 'deleted_at'])
                ->toArray();

            if (!in_array((object)$financeAccountTypeRegister, $seedRegisters)) {
                $financeAccountType->delete();
                return response()->json(['record' => $financeAccountType, 'message' => 'Success'], 200);
            }
            return response()->json(['error' => true, 'message' => 'No se puede Eliminar el registro'], 403);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => true, 'message' => __($e->getMessage())], 403);
        }
    }

    /**
     * Obtiene los tipos de cuenta bancaria
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAccountTypes()
    {
        foreach (FinanceAccountType::all() as $account_type) {
            $this->data[] = [
                'id' => $account_type->id,
                'text' => $account_type->name
            ];
        }

        return response()->json($this->data);
    }
}
