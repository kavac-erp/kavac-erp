<?php

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Finance\Models\FinanceBank;

/**
 * @class FinanceBankController
 * @brief Controlador para las entidades bancarias
 *
 * Clase que gestiona las entidades bancarias
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FinanceBankController extends Controller
{
    use ValidatesRequests;

    /**
     * Lista de elementos a mostrar
     *
     * @var array $data
     */
    protected $data = [];

    /**
     * Mensajes de validación
     *
     * @var array $messages
     */
    protected $messages;

    /**
     * Lista de atributos personalizados
     *
     * @var  array $customAttributes
     */
    protected $customAttributes;

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

        $this->messages = [
            'code.required' => 'El campo codigo es obligatorio.',
            'code.max' => 'El campo código tiene un longitud maxima de 4 .',
            'code.unique' => 'El campo código ya existe.',
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El campo nombre tiene un longitud maxima de 100 .',
            'name.unique' => 'El campo nombre ya existe.',
            'short_name.required' => 'El Nombre Abreviado es obligatorio.',
            'short_name.max' => 'El campo Nombre Abreviado tiene un longitud maxima de 50 .',
            'short_name.unique' => 'El campo Nombre Abreviado ya existe.',
        ];

        $this->customAttributes = [
            'code' => 'código',
            'name' => 'nombre',
            'short_name' => 'Nombre Abreviado',
        ];
    }

    /**
     * Listado de entidades bancarias
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => FinanceBank::orderBy('code')->get()], 200);
    }

    /**
     * Muestra el formulario para crear una nueva entidad bancaria
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('finance::create');
    }

    /**
     * Almacena una nueva entidad bancaria
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'code' => ['required', 'max:4', 'unique:finance_banks,code'],
            'name' => ['required', 'max:100', 'unique:finance_banks,name'],
            'short_name' => ['required', 'max:50', 'unique:finance_banks,short_name']
        ], $this->messages);

        $financeBank = FinanceBank::create([
            'code' => $request->code,
            'name' => $request->name,
            'short_name' => $request->short_name,
            'website' => (!empty($request->website)) ? $request->website : null,
            'logo_id' => ($request->logo_id) ? $request->logo_id : null
        ]);

        return response()->json(['record' => $financeBank, 'message' => 'Success'], 200);
    }

    /**
     * Muestra detalles de una entidad bancaria
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('finance::show');
    }

    /**
     * Muestra el formulario para editar una entidad bancaria
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('finance::edit');
    }

    /**
     * Actualiza una entidad bancaria
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        /* Datos de la entidad bancaria */
        $financeBank = FinanceBank::find($id);

        $this->validate($request, [
            'code' => ['required', 'max:4', 'unique:finance_banks,code,' . $financeBank->id],
            'name' => ['required', 'max:100', 'unique:finance_banks,name,' . $financeBank->id],
            'short_name' => ['required', 'max:50', 'unique:finance_banks,short_name,' . $financeBank->id]
        ], [], $this->customAttributes);

        $financeBank->code = $request->code;
        $financeBank->name = $request->name;
        $financeBank->short_name = $request->short_name;
        $financeBank->website = (!empty($request->website)) ? $request->website : null;
        $financeBank->logo_id = ($request->logo_id) ? $request->logo_id : null;
        $financeBank->save();

        return response()->json(['message' => 'Registro actualizado correctamente'], 200);
    }

    /**
     * Elimina una entidad bancaria
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        /* Datos de la entidad bancaria */
        $financeBank = FinanceBank::find($id);
        $financeBank->delete();
        return response()->json(['record' => $financeBank, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene los datos de las entidades bancarias
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBanks()
    {
        foreach (FinanceBank::all() as $bank) {
            $this->data[] = [
                'id' => $bank->id,
                'text' => $bank->name
            ];
        }

        return response()->json($this->data);
    }

    /**
     * Obtiene información de una determinada entidad bancaria
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  integer $bank_id                 Identificador de la entidad bancaria
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBankInfo($bank_id)
    {
        return response()->json(['result' => true, 'bank' => FinanceBank::find($bank_id)], 200);
    }
}
