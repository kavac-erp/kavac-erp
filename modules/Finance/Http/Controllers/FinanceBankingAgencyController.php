<?php

/** Controladores del módulo de finanzas */

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Finance\Models\FinanceBankingAgency;
use Models\Estate;
use App\Models\Phone;

/**
 * @class FinanceBankingAgencyController
 * @brief Controlador para las agencias bancarias
 *
 * Clase que gestiona las agencias bancarias
 *
 * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 * @license<a href='http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/'>
 *              LICENCIA DE SOFTWARE CENDITEL
 *          </a>
 */
class FinanceBankingAgencyController extends Controller
{
    use ValidatesRequests;

    /** @var array Lista de elementos a mostrar */
    protected $data = [];

    /**
     * Método constructor de la clase
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     */
    public function __construct()
    {
        $this->data[0] = [
            'id' => '',
            'text' => 'Seleccione...'
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $record = FinanceBankingAgency::with([
            'financeBank', 'city', 'phones'
        ])->get();
        //Log::emergency($record);

        // return response()->json(['records' =>
        // FinanceBankingAgency::with([
        //     'financeBank', 'city', 'phones'
        // ])->get()], 200);
        return response()->json(['records' => $record], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Renderable
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
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
     * Show the specified resource.
     *
     * @return Renderable
     */
    public function show()
    {
        return view('finance::show');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Renderable
     */
    public function edit()
    {
        return view('finance::edit');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
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

        /** @var object Datos de la agencia bancaria */
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
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        /** @var object Datos de la agencia bancaria */
        $financeBankingAgency = FinanceBankingAgency::find($id);
        $financeBankingAgency->delete();
        return response()->json(['record' => $financeBankingAgency, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene las agencias bancarias registradas
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     * @param  integer $bank_id                 Identificador del banco
     * @return \Illuminate\Http\JsonResponse    JSON con el listado de las agencias bancarias
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
