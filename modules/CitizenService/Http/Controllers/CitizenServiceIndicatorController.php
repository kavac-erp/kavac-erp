<?php

namespace Modules\CitizenService\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\CitizenService\Models\CitizenServiceIndicator;
use Illuminate\Validation\Rule;

/**
 * @class CitizenServiceIndicatorController
 * @brief Controlador para los indicadores de la oficina de información al ciudadano
 *
 * Clase que gestiona el controlador para los indicadores de la OAC
 *
 * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceIndicatorController extends Controller
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
     * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        /* Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:citizenservice.indicators.create', ['only' => ['index', 'create', 'store']]);
        $this->middleware('permission:citizenservice.indicators.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:citizenservice.indicators.delete', ['only' => 'destroy']);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'name'                                  => ['required', 'regex:/^[\D][a-zA-ZÁ-ÿ0-9\s]*/u', 'max:100'],
            'description'                           => ['nullable', 'regex:/^[\D][a-zA-ZÁ-ÿ0-9\s]*/u', 'max:200'],
            'effect_types_id'                       => ['nullable']
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
     * Obtiene un listado de los indicadores
     *
     * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => CitizenServiceIndicator::all()], 200);
    }

    /**
     * Muestra el formulario para registrar un nuevo indicador
     *
     * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
     *
     * @return Renderable
     */
    public function create()
    {
        return view('citizenservice::create');
    }

    /**
     * Registra un nuevo indicador
     *
     * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
     *
     * @param Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
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
     * Muestra información de un indicador

     * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
     *
     * @param integer $id Identificador del registro
     *
     * @return Renderable
     */
    public function show($id)
    {
        return view('citizenservice::show');
    }

    /**
     * Muestra el formulario para editar un indicador
     *
     * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
     *
     * @param integer $id Identificador del registro
     *
     * @return Renderable
     */
    public function edit($id)
    {
        return view('citizenservice::edit');
    }

    /**
     * Actualiza la información del indicador
     *
     * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
     *
     * @param Request $request Datos de la petición
     * @param integer $id      Identificador del registro
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $citizenServiceIndicator = CitizenServiceIndicator::find($id);

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
     * Elimina el indicador
     *
     * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
     *
     * @param integer $id Identificador del registro
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $citizenServiceIndicator = CitizenServiceIndicator::find($id);
        $citizenServiceIndicator->delete();
        return response()->json(['record' => $citizenServiceIndicator, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene los indicadores
     *
     * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
     *
     * @return array
     */
    public function getIndicators()
    {
        return template_choices(
            'Modules\CitizenService\Models\CitizenServiceIndicator',
            'name',
            [],
            true,
            null
        );
    }
}
