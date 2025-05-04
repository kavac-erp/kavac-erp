<?php

/**
 * [descripción del namespace]
 * */

namespace Modules\CitizenService\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\CitizenService\Models\CitizenServiceIndicator;
use Illuminate\Validation\Rule;

/**
 * [descripción corta]
 *
 * @class CitizenServiceIndicatorController
 * @brief [descripción detallada]
 *
 * @author Autor Anonimo <correo@correo.com>
 *
 * @license [LICENCIA DE SOFTWARE CENDITEL]
 * @link    http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/
 */
class CitizenServiceIndicatorController extends Controller
{
    use ValidatesRequests;

    protected $validateRules;
    protected $messages;

    public function __construct()
    {
        /**
         * Establece permisos de acceso para cada método del controlador
         * */
        $this->middleware('permission:citizenservice.indicators.create', ['only' => ['index', 'create', 'store']]);
        $this->middleware('permission:citizenservice.indicators.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:citizenservice.indicators.delete', ['only' => 'destroy']);

        /**
         * Define las reglas de validación para el formulario
         * */
        $this->validateRules = [
            'name'                                  => ['required', 'regex:/^[\D][a-zA-ZÁ-ÿ0-9\s]*/u', 'max:100'],
            'description'                           => ['nullable', 'regex:/^[\D][a-zA-ZÁ-ÿ0-9\s]*/u', 'max:200'],
            'effect_types_id'                       => ['nullable']
        ];

        /**
         * Define los mensajes de validación para las reglas del formulario
         * */
        $this->messages = [
            'name.required'         => 'El campo nombre es obligatorio.',
            'name.max'              => 'El campo nombre no debe contener más de 100 caracteres.',
            'name.regex'            => 'El campo nombre no debe permitir números ni símbolos.',
            'description.max'       => 'El campo descripción no debe contener más de 200 caracteres.',
            'description.regex'     => 'El campo descripción no debe permitir números ni símbolos.',
        ];
    }
    /**
     * [descripción del método]
     *
     * @method index
     *
     * @author [nombre del autor] [correo del autor]
     *
     * @return Renderable [descripción de los datos devueltos]
     */
    public function index()
    {
        return response()->json(['records' => CitizenServiceIndicator::all()], 200);
    }

    /**
     * [descripción del método]
     *
     * @method create
     *
     * @author [nombre del autor] [correo del autor]
     *
     * @return Renderable [descripción de los datos devueltos]
     */
    public function create()
    {
        return view('citizenservice::create');
    }

    /**
     * [descripción del método]
     *
     * @param object Request $request Objeto con información de la petición
     *
     * @method store
     *
     * @author [nombre del autor] [correo del autor]
     *
     * @return Renderable [descripción de los datos devueltos]
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
            'name' => ['required', 'max:100', 'unique:citizen_service_indicators,name'],
            'effect_types_id' => ['required'],
            ],
            [
            'effect_types_id.required' => 'El campo tipo de impacto es obligatorio'
            ]
        );

        $this->validate($request, $this->validateRules, $this->messages);

        //Guardar los registros del formulario en  CitizenServiceEffectType
        $citizenServiceIndicator = CitizenServiceIndicator::create(
            [

            'name'              => $request->input('name'),
            'description'       => $request->input('description'),
            'effect_types_id'   => $request->input('effect_types_id'),
            ]
        );

        return response()->json(['record' => $citizenServiceIndicator, 'message' => 'Success'], 200);
    }

    /**
     * [descripción del método]

     * @param integer $id Identificador del registro
     *
     * @method show
     *
     * @author [nombre del autor] [correo del autor]
     *
     * @return Renderable    [descripción de los datos devueltos]
     */
    public function show($id)
    {
        return view('citizenservice::show');
    }

    /**
     * [descripción del método]
     *
     * @param integer $id Identificador del registro
     *
     * @method edit
     *
     * @author [nombre del autor] [correo del autor]
     *
     * @return Renderable    [descripción de los datos devueltos]
     */
    public function edit($id)
    {
        return view('citizenservice::edit');
    }

    /**
     * [descripción del método]

     * @param object Request $request Objeto con datos de la petición
     * @param integer        $id      Identificador del registro
     *
     * @method update
     *
     * @author [nombre del autor] [correo del autor]
     *
     * @return Renderable [descripción de los datos devueltos]
     */
    public function update(Request $request, $id)
    {
        $citizenServiceIndicator = CitizenServiceIndicator::find($id);
        $validateRules  = $this->validateRules;
        $this->validate(
            $request,
            [
            'name' => [
                'required',
                'max:100',
                'unique:citizen_service_indicators,name,' . $citizenServiceIndicator->id,
            ],
            'description' => [
                'nullable',
                'max:200'
            ],
            'effect_types_id' => [
                'nullable'
            ]
            ]
        );

        $citizenServiceIndicator->name               = $request->name;
        $citizenServiceIndicator->description        = $request->description;
        $citizenServiceIndicator->effect_types_id    = $request->effect_types_id;

        $citizenServiceIndicator->save();

        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * [descripción del método]
     *
     * @param integer $id Identificador del registro
     *
     * @method destroy
     *
     * @author [nombre del autor] [correo del autor]
     *     *
     * @return Renderable    [descripción de los datos devueltos]
     */
    public function destroy($id)
    {
        $citizenServiceIndicator = CitizenServiceIndicator::find($id);
        $citizenServiceIndicator->delete();
        return response()->json(['record' => $citizenServiceIndicator, 'message' => 'Success'], 200);
    }

    /**
     * [descripción del método]
     *
     * @method getIndicators
     *
     * @author [nombre del autor] [correo del autor]
     *     *
     * @return array    [descripción de los datos devueltos]
     */
    public function getIndicators()
    {
        return template_choices('Modules\CitizenService\Models\CitizenServiceIndicator', 'name', [], true, null);
    }
}
