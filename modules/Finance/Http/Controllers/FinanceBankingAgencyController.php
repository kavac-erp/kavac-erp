<?php

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Finance\Models\FinanceBankingAgency;
use App\Models\Phone;

/**
 * @class FinanceBankingAgencyController
 * @brief Controlador para las agencias bancarias
 *
 * Clase que gestiona las agencias bancarias
 *
 * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FinanceBankingAgencyController extends Controller
{
    use ValidatesRequests;

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
        $this->data[0] = [
            'id' => '',
            'text' => 'Seleccione...'
        ];
    }

    /**
     * Listado de agencias bancarias
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $record = FinanceBankingAgency::with([
            'financeBank', 'city', 'phones'
        ])->get();
        return response()->json(['records' => $record], 200);
    }

    /**
     * Muestra el formulario para crear una nueva agencia bancaria
     *
     * @return void
     */
    public function create()
    {
        //
    }

    /**
     * Almacena una nueva agencia bancaria
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
                'name' => ['required', 'unique:finance_banking_agencies,name'],
                'direction' => ['required'],
                'country_id' => ['required'],
                'estate_id' => ['required'],
                'city_id' => ['required'],
                'finance_bank_id' => ['required']
            ],
            [
                'name.required' => 'El campo nombre de agencia es obligatorio.',
                'name.unique' => 'El nombre de agencia  ya ha sido registrado.',
                'direction.required' => 'El campo dirección es obligatorio.',
                'country_id.required' => 'El campo país es obligatorio.',
                'estate_id.required' => 'El campo estado es obligatorio.',
                'city_id.required' => 'El campo ciudad es obligatorio.',
                'finance_bank_id.required' => 'El campo banco es obligatorio.',
            ]
        );

        $bankingAgency = FinanceBankingAgency::create([
            'name' => $request->name,
            'direction' => $request->direction,
            'finance_bank_id' => $request->finance_bank_id,
            'contact_person' => (!empty($request->contact_person))
                                ? $request->contact_person
                                : null,
            'contact_email' => (!empty($request->contact_email))
                               ? $request->contact_email
                               : null,
            'headquarters' => $request->headquarters,
            'city_id' => $request->city_id,
        ]);


        if ($request->phones && !empty($request->phones)) {
            foreach ($request->phones as $phone) {
                $bankingAgency->phones()->save(new Phone([
                    'type' => $phone['type'],
                    'area_code' => $phone['area_code'],
                    'number' => $phone['number'],
                    'extension' => $phone['extension']
                ]));
            }
        }

        return response()->json(['record' => $bankingAgency, 'message' => 'Success'], 200);
    }

    /**
     * Muestra información de la agencia bancaria
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('finance::show');
    }

    /**
     * Muestra el formulario para editar la agencia bancaria
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('finance::edit');
    }

    /**
     * Actualiza la agencia bancaria
     *
     * @param  Request $request Datos de la petición
     * @param  integer $id      ID de la agencia bancaria
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $this->validate(
            $request,
            [
                'name' => ['required'],
                'direction' => ['required'],
                'city_id' => ['required'],
                'finance_bank_id' => ['required']
            ],
            [
                'name.required' => 'El campo nombre de agencia es obligatorio.',
                'direction.required' => 'El campo dirección es obligatorio.',
                'city_id.required' => 'El campo ciudad es obligatorio.',
                'finance_bank_id.required' => 'El campo banco es obligatorio.',
            ]
        );

        /* Datos de la agencia bancaria */
        $financeBankingAgency = FinanceBankingAgency::find($id);
        $financeBankingAgency->fill($request->all());
        $financeBankingAgency->contact_person = (!empty($request->contact_person))
                                                ? $request->contact_person
                                                : null;
        $financeBankingAgency->contact_email = (!empty($request->contact_email))
                                               ? $request->contact_email
                                               : null;
        $financeBankingAgency->headquarters = $request->headquarters;
        $financeBankingAgency->save();

        if ($request->phones && !empty($request->phones)) {
            foreach ($request->phones as $phone) {
                $financeBankingAgency->phones()->save(new Phone([
                    'type' => $phone['type'],
                    'area_code' => $phone['area_code'],
                    'number' => $phone['number'],
                    'extension' => $phone['extension']
                ]));
            }
        }

        return response()->json(['message' => 'Registro actualizado correctamente'], 200);
    }

    /**
     * Elimina una agencia bancaria
     *
     * @param  integer $id ID de la agencia bancaria
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        /* Datos de la agencia bancaria */
        $financeBankingAgency = FinanceBankingAgency::find($id);
        $financeBankingAgency->delete();
        return response()->json(['record' => $financeBankingAgency, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene las agencias bancarias registradas
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  integer|null $bank_id Identificador del banco
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAgencies($bank_id = null)
    {
        $agencies = ($bank_id)
                    ? FinanceBankingAgency::where('finance_bank_id', $bank_id)->get()
                    : FinanceBankingAgency::all();

        foreach ($agencies as $agency) {
            $this->data[] = [
                'id' => $agency->id,
                'text' => $agency->name
            ];
        }

        return response()->json($this->data);
    }
}
