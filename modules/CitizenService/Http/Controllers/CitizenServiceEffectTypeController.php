<?php

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
 * @brief Controlador para los tipos de efectos de la oficina de información
 *
 * Clase que gestiona el controlador para los tipos de efectos de la OAC
 *
 * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceEffectTypeController extends Controller
{
    use ValidatesRequests;

    /**
     * Reglas de validación
     *
     * @var array $validateRules
     */
    protected $validateRules;

    /**
     * Mensajes de validación
     *
     * @var array $messages
     */
    protected $messages;

    /**
     * Método constructor de la clase
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:citizenservice.effect.types.create', ['only' => ['index', 'create', 'store']]);
        $this->middleware('permission:citizenservice.effect.types.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:citizenservice.effect.types.delete', ['only' => 'destroy']);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'name'                                  => ['required', 'regex:/^[\D][a-zA-ZÁ-ÿ0-9\s]*/u', 'max:100'],
            'description'                           => ['nullable', 'regex:/^[\D][a-zA-ZÁ-ÿ0-9\s]*/u', 'max:200'],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'name.required'         => 'El campo nombre es obligatorio.',
            'name.max'              => 'El campo nombre no debe contener más de 100 caracteres.',
            'name.regex'            => 'El campo nombre no debe permitir números ni símbolos.',
            'description.max'       => 'El campo descripción no debe contener más de 200 caracteres.',
            'description.regex'     => 'El campo descripción no debe permitir números ni símbolos.',
        ];
    }

    /**
     * Obtiene un listado de los registros de tipos de efectos
     *
     * @author    Pedro Contreras <pmcontreras@cenditel.gob.ve>
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => CitizenServiceEffectType::all()], 200);
    }

    /**
     * Muestra el formulario para crear un nuevo tipo de efecto
     *
     * @author    Pedro Contreras <pmcontreras@cenditel.gob.ve>
     *
     * @return    Renderable
     */
    public function create()
    {
        return view('citizenservice::create');
    }

    /**
     * Almacena un nuevo tipo de efecto
     *
     * @author    Pedro Contreras <pmcontreras@cenditel.gob.ve>
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
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
     * Muestra la información de un tipo de efecto
     *
     * @author    Pedro Contreras <pmcontreras@cenditel.gob.ve>
     *
     * @param     integer    $id    Identificador del tipo de efecto
     *
     * @return    Renderable
     */
    public function show($id)
    {
        return view('citizenservice::show');
    }

    /**
     * Muestra el formulario para editar un tipo de efecto
     *
     * @author    Pedro Contreras <pmcontreras@cenditel.gob.ve>
     *
     * @param     integer    $id    Identificador del tipo de efecto
     *
     * @return    Renderable
     */
    public function edit($id)
    {
        return view('citizenservice::edit');
    }

    /**
     * Actualiza la información de un tipo de efecto
     *
     * @author    Pedro Contreras <pmcontreras@cenditel.gob.ve>
     *
     * @param     Request    $request  Datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $citizenServiceEffectType = CitizenServiceEffectType::find($id);

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
     * Elimina un tipo de efecto
     *
     * @author    Pedro Contreras <pmcontreras@cenditel.gob.ve>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
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
     * @author    Pedro Contreras <pmcontreras@cenditel.gob.ve>
     *
     * @return    \Illuminate\Http\JsonResponse
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
