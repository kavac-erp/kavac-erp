<?php

/** [descripción del namespace] */

namespace Modules\CitizenService\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\CitizenService\Models\CitizenServiceEffectType;
use Illuminate\Validation\Rule;
use Nwidart\Modules\Facades\Module;

/**
 * @class CitizenServiceEffectTypeController
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceEffectTypeController extends Controller
{
    use ValidatesRequests;

    protected $validateRules;
    protected $messages;

    public function __construct()
    {
        /** Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:citizenservice.effect-types.create', ['only' => ['index', 'create', 'store']]);
        $this->middleware('permission:citizenservice.effect-types.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:citizenservice.effect-types.delete', ['only' => 'destroy']);

        /** Define las reglas de validación para el formulario */
        $this->validateRules = [
            'name'                                  => ['required', 'regex:/^[\D][a-zA-ZÁ-ÿ0-9\s]*/u', 'max:100'],
            'description'                           => ['nullable', 'regex:/^[\D][a-zA-ZÁ-ÿ0-9\s]*/u', 'max:200'],
        ];

        /** Define los mensajes de validación para las reglas del formulario */
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
     * @method    index
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function index()
    {
        return response()->json(['records' => CitizenServiceEffectType::all()], 200);
    }

    /**
     * [descripción del método]
     *
     * @method    create
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function create()
    {
        return view('citizenservice::create');
    }

    /**
     * [descripción del método]
     *
     * @method    store
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     object    Request    $request    Objeto con información de la petición
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => [
                'required',
                'max:100',
                'unique:citizen_service_effect_types,name'
            ]
        ]);

        $this->validate($request, $this->validateRules, $this->messages);

        //Guardar los registros del formulario en  CitizenServiceEffectType
        $citizenServiceEffectType = CitizenServiceEffectType::create([

            'name'          => $request->input('name'),
            'description'   => $request->input('description'),
        ]);

        return response()->json(['record' => $citizenServiceEffectType, 'message' => 'Success'], 200);
    }

    /**
     * [descripción del método]
     *
     * @method    show
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function show($id)
    {
        return view('citizenservice::show');
    }

    /**
     * [descripción del método]
     *
     * @method    edit
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function edit($id)
    {
        return view('citizenservice::edit');
    }

    /**
     * [descripción del método]
     *
     * @method    update
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     object    Request    $request         Objeto con datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function update(Request $request, $id)
    {
        $citizenServiceEffectType = CitizenServiceEffectType::find($id);
        $validateRules  = $this->validateRules;
        $this->validate($request, [
            'name' => [
                'required',
                'max:100',
                'unique:citizen_service_effect_types,name,' . $citizenServiceEffectType->id,
            ],
            'description' => [
                'nullable',
                'max:200'
            ]
        ]);

        $citizenServiceEffectType->name          = $request->name;
        $citizenServiceEffectType->description   = $request->description;

        $citizenServiceEffectType->save();

        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * [descripción del método]
     *
     * @method    destroy
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function destroy($id)
    {
        $citizenServiceEffectType = CitizenServiceEffectType::find($id);
        $citizenServiceEffectType->delete();
        return response()->json(['record' => $citizenServiceEffectType, 'message' => 'Success'], 200);
    }

    /**
     * Retorna un json con todos los tipos de impacto para ser usado en un componente <select2>
     *
     * @method    getEffectType
     *
     * @author    Pedro Contreras <pmcontreras@cenditel.gob.ve>
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function getEffectType()
    {
        $effectTypeList = CitizenServiceEffectType::all();
        $effectTypes = [];
        array_push($effectTypes, [
            'id' => '',
            'text' => 'Seleccione...'
        ]);
        foreach ($effectTypeList->all() as $effectType) {
            array_push($effectTypes, [
                'id' => $effectType->id,
                'text' => $effectType->name
            ]);
        }
        return response()->json($effectTypes);
    }
}
