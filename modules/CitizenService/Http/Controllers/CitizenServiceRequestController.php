<?php

namespace Modules\CitizenService\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\CitizenService\Models\CitizenServiceRequest;
use Modules\CitizenService\Models\CitizenServiceAddIndicator;
use App\Models\CodeSetting;
use App\Models\FiscalYear;
use App\Models\Phone;
use App\Rules\Rif as RifRule;
use Illuminate\Validation\Rule;
use Nwidart\Modules\Facades\Module;

/**
 * @class CitizenServiceRequestController
 * @brief Controlador para las solicitudes de la oficina de atención al ciudadano
 *
 * Clase que gestiona el controlador para las solicitudes de la OAC
 *
 * @author Ing. Yenifer Ramirez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceRequestController extends Controller
{
    use ValidatesRequests;

    /**
     * Arreglo con las reglas de validación sobre los datos de un formulario
     *
     * @var array $validateRules
     */
    protected $validateRules;
    /**
     * Arreglo con los mensajes para las reglas de validación
     *
     * @var array $messages
     */
    protected $messages;

    /**
     * Define la configuración de la clase
     *
     * @author    Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador

        $this->middleware('permission:citizenservice.requests.list', ['only' => ['index', 'vueList']]);
        $this->middleware('permission:citizenservice.requests.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:citizenservice.requests.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:citizenservice.requests.delete', ['only' => 'destroy']);
        $this->middleware('permission:citizenservice.requests.approved', ['only' => 'approved']);
        $this->middleware('permission:citizenservice.requests.rejected', ['only' => 'rejected']);
        $this->middleware('permission:citizenservice.requests.addindicator', ['only' => 'addIndicator']);
        $this->middleware('permission:citizenservice.requests.info', ['only' => 'vueInfo']);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'date'                  => ['required'],
            'gender_id'             => [Module::has('Payroll') && Module::isEnabled('Payroll') ? 'required' : 'nullable'],
            'gender'                => [Module::has('Payroll') && Module::isEnabled('Payroll') ? 'nullable' : 'required'],
            'nationality_id'        => [Module::has('Payroll') && Module::isEnabled('Payroll') ? 'required' : 'nullable'],
            'nationality'           => [Module::has('Payroll') && Module::isEnabled('Payroll') ? 'nullable' : 'required'],
            'first_name'            => ['required', 'regex:/^[\D][a-zA-ZÁ-ÿ0-9\s]*/u', 'max:100'],
            'last_name'             => ['required', 'regex:/^[\D][a-zA-ZÁ-ÿ0-9\s]*/u', 'max:100'],
            'id_number'             => ['required', 'max:12', 'regex:/^([\d]{7,12})$/u'],
            'email'                 => ['required', 'email'],
            'city_id'               => ['required'],
            'parish_id'             => ['required'],
            'address'               => ['required', 'max:200'],
            'motive_request'        => ['required', 'max:200'],
            'attribute'             => ['required', 'max:200'],
            'citizen_service_request_type_id'  => ['required'],
            'citizen_service_department_id'    => ['required'],
            'birth_date'            => ['nullable', 'after_or_equal:01/01/1900'],
            'director_id'           => [Module::has('Payroll') && Module::isEnabled('Payroll') ? 'required' : 'nullable']
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'date.required'         => 'El campo fecha es obligatorio.',
            'gender_id.required'    => 'El campo género es obligatorio.',
            'gender.required'       => 'El campo género es obligatorio.',
            'nationality_id.required' => 'El campo nacionalidad es obligatorio.',
            'nationality.required'  => 'El campo nacionalidad es obligatorio.',
            'first_name.required'   => 'El campo nombres es obligatorio.',
            'first_name.max'        => 'El campo nombres no debe contener más de 100 caracteres.',
            'first_name.regex'      => 'El campo nombres no debe permitir números ni símbolos.',
            'last_name.required'    => 'El campo apellidos es obligatorio',
            'last_name.max'         => 'El campo apellidos no debe contener más de 100 caracteres.',
            'last_name.regex'       => 'El campo apellidos no debe permitir números ni símbolos.',
            'id_number.required'    => 'El campo cédula de identidad es obligatorio.',
            'id_number.max'         => 'El campo cédula de identidad no debe de contener más de 12 caracteres.',
            'id_number.regex'       => 'El campo cédula de identidad debe tener entre 7 y 12 digitos.',
            'email.required'        => 'El campo correo electrónico es obligatorio. ',
            'email.email'           => 'El campo correo electrónico debe de ingresarse en formato de correo.',
            'city_id.required'           => 'El campo ciudad es obligatorio.',
            'parish_id.required'         => 'El campo parroquia es obligatorio.',
            'address.required'           => 'El campo dirección es obligatorio.',
            'address.max'                => 'El campo dirección no debe contener más de 200 caracteres.',
            'location.required'          => 'El campo ubicación es obligatorio.',
            'location.max'               => 'El campo ubicación no debe contener más de 200 caracteres.',
            'commune.required'           => 'El campo comuna es obligatorio.',
            'commune.max'                => 'El campo comuna no debe contener más de 200 caracteres.',
            'communal_council.required'  => 'El campo consejo comunal es obligatorio.',
            'communal_council.max'       => 'El campo consejo comunal no debe contener más de 200 caracteres.',
            'population_size.required'   => 'El campo cantidad de habitantes es obligatorio.',
            'population_size.integer'    => 'El campo cantidad de habitantes debe ser un número entero.',
            'population_size.min'        => 'El campo cantidad de habitantes debe ser mayor o igual a 1.',
            'motive_request.required'    => 'El campo motivo de la solicitud es obligatorio.',
            'motive_request.max'         => 'El campo motivo de la solicitud no debe de contener más de 200 caracteres.',
            'attribute.required'         => 'El campo atributos es obligatorio.',
            'attribute.max'              => 'El campo atributos no debe de contener más de 200 caracteres.',
            'citizen_service_request_type_id.required'  => 'El campo tipo de solicitud es obligatorio.',
            'citizen_service_department_id.required'    => 'El campo departamento es obligatorio.',
            'director_id.required'       => 'El campo director y/o responsable es obligatorio.',
            'institution_name.required'   => 'El campo nombre de la institución es obligatorio.',
            'institution_name.max'        => 'El campo nombre de la institución no debe de contener más de 200 caracteres.',
            'rif.required'                => 'El campo rif es obligatorio.',
            'rif.unique:citizen_service_requests,rif' => 'El campo rif debe de ser único.',
            'rif.size'                    => 'El campo rif no debe de contener más de 10 caracteres. ',
            'institution_address.required'   => 'El campo dirección de la institución es obligatorio.',
            'institution_address.max'        => 'El campo dirección de la institución no debe de contener más de 200 caracteres.',
            'web.max'                        => 'El campo dirección web no debe de contener más de 200 caracteres.',
            'birth_date.after_or_equal'   => 'El campo fecha de nacimiento debe ser después o igual de la fecha 01/01/1900',
            'birth_date.before_or_equal'  => 'El campo fecha de nacimiento debe ser antes o igual de la fecha 31/12/2100',
        ];
    }
    /**
     * Muestra un listado de las solicitudes de atención al ciudadano
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('citizenservice::requests.list');
    }

    /**
     * Muestra el formulario para registrar una nueva solicitud
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('citizenservice::requests.create');
    }

    /**
     * Valida y registra una nueva solicitud de atención al ciudadano
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validateRules = $this->validateRules;

        $validateRules = array_merge($validateRules, [
            'community'         => ['nullable'],
            'location'          => [$request->community == 'community' ? 'required' : 'nullable', 'max:200'],
            'commune'           => [$request->community == 'community' ? 'required' : 'nullable', 'max:200'],
            'communal_council'  => [$request->community == 'community' ? 'required' : 'nullable', 'max:200'],
            'population_size'   => [$request->community == 'community' ? 'required' : 'nullable', 'integer', 'min:1'],
        ]);

        if ($request->citizen_service_request_type_id == 1) {
            $validateRules = array_merge($validateRules, [
                'inventory_code',
                'type_team',
                'brand',
                'model',
                'serial',
                'color',
                'transfer',
                'entryhour',
                'informationteam',
                'other',
            ]);
        }

        if ($request->type_institution) {
            $validateRules = array_merge($validateRules, [
                'institution_name'              => ['required', 'max:200'],
                'rif' => ['required', 'unique:citizen_service_requests,rif', 'size:10', new RifRule()],
                'institution_address'           => ['required', 'max:200'],
                'web'                           => ['max:200'],
            ]);
        }

        $this->validate($request, $validateRules, $this->messages);

        $i = 0;
        foreach ($request->phones as $phone) {
            $this->validate(
                $request,
                [
                    'phones.' . $i . '.type' => ['required'],
                    'phones.' . $i . '.area_code' => ['required', 'digits:3'],
                    'phones.' . $i . '.number' => ['required', 'digits:7'],
                    'phones.' . $i . '.extension' => ['nullable', 'digits_between:3,6'],
                ],
                [],
                [
                    'phones.' . $i . '.type' => 'tipo #' . ($i + 1),
                    'phones.' . $i . '.area_code' => 'código de area #' . ($i + 1),
                    'phones.' . $i . '.number' => 'número #' . ($i + 1),
                    'phones.' . $i . '.extension' => 'extensión #' . ($i + 1),
                ]
            );
            $i++;
        }

        $codeSetting = CodeSetting::where('table', 'citizen_service_requests')->first();
        if (is_null($codeSetting)) {
            $request->session()->flash('message', [
                'type' => 'other', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'growl-danger',
                'text' => 'Debe configurar previamente el formato para el código a generar'
            ]);
            return response()->json(['result' => false, 'redirect' => route('citizenservice.settings.index')], 200);
        }

        $currentFiscalYear = FiscalYear::select('year')
            ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();

        $code  = generate_registration_code(
            $codeSetting->format_prefix,
            strlen($codeSetting->format_digits),
            (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
                substr($currentFiscalYear->year, 2, 2) : date('y')) : (isset($currentFiscalYear) ?
                $currentFiscalYear->year : date('Y')),
            CitizenServiceRequest::class,
            $codeSetting->field
        );


        //Guardar los registros del formulario en  CitizenServiceRequest
        $citizenServiceRequest = CitizenServiceRequest::create([
            'file_counter'                     => 0,
            'code'                             => $code,
            'date'                             => $request->date,
            'gender_id'                        => $request->gender_id,
            'gender'                           => $request->gender,
            'nationality_id'                   => $request->nationality_id,
            'nationality'                      => $request->nationality,
            'first_name'                       => $request->first_name,
            'last_name'                        => $request->last_name,
            'id_number'                        => $request->id_number,
            'email'                            => $request->email,
            'birth_date'                       => $request->birth_date,
            'age'                              => $request->age,
            'city_id'                          => $request->city_id,
            'parish_id'                        => $request->parish_id,
            'address'                          => $request->address,
            'community'                        => $request->community,
            'location'                         => $request->location,
            'commune'                          => $request->commune,
            'communal_council'                 => $request->communal_council,
            'population_size'                  => $request->population_size,
            'motive_request'                   => $request->motive_request,
            'attribute'                        => $request->attribute,
            'state'                            => 'Pendiente',
            'citizen_service_request_type_id'  => $request->citizen_service_request_type_id,
            'citizen_service_department_id'    => $request->citizen_service_department_id,
            'director_id'                      => $request->director_id,

            'type_institution'                 => $request->type_institution ?? false,
            'institution_name'                 => $request->institution_name,
            'rif'                              => $request->rif,
            'institution_address'              => $request->institution_address,
            'web'                              => $request->web,

            'inventory_code'                   => $request->inventory_code,
            'type_team'                        => $request->type_team,
            'brand'                            => $request->brand,
            'model'                            => $request->model,
            'serial'                           => $request->serial,
            'color'                            => $request->color,
            'transfer'                         => $request->transfer,
            'entryhour'                        => $request->entryhour,
            'exithour'                         => $request->exithour,
            'informationteam'                  => $request->informationteam,
            'other'                            => $request->other,

        ]);


        if ($request->phones && !empty($request->phones)) {
            foreach ($request->phones as $phone) {
                $citizenServiceRequest->phones()->save(new Phone([
                    'type' => $phone['type'],
                    'area_code' => $phone['area_code'],
                    'number' => $phone['number'],
                    'extension' => $phone['extension']
                ]));
            }
        }
        $request->session()->flash('message', ['type' => 'store']);
        return response()->json(['result' => true, 'redirect' => route('citizenservice.request.index')], 200);
    }

    /**
     * Muestra el formulario para ver la información de las solicitudes de atención al ciudadano
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('citizenservice::show');
    }

    /**
     * Muestra el formulario para actualizar la información de las solicitudes de atención al ciudadano
     *
     * @param  integer $id ID de la solicitud
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $request = CitizenServiceRequest::find($id);
        return view('citizenservice::requests.create', compact('request'));
    }

    /**
     * Actualiza la información de las solicitudes de atención al ciudadano
     *
     * @param  Request $request Datos de la petición
     * @param  integer $id ID de la solicitud
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $citizenServiceRequest = CitizenServiceRequest::find($id);
        $validateRules = $this->validateRules;
        if ($request->citizen_service_request_type_id == 1) {
            $validateRules = array_merge($validateRules, [
                'inventory_code',
                'type_team',
                'brand',
                'model',
                'serial',
                'color',
                'transfer',
                'entryhour',
                'informationteam',
                'other',
            ]);
        }

        if ($request->type_institution) {
            $validateRules = array_merge($validateRules, [
                'institution_name'              => ['required', 'max:200'],
                'rif' => ['required', Rule::unique('citizen_service_requests')->ignore($id), 'size:10', new RifRule()],
                'institution_address'           => ['required', 'max:200'],
                'web'                           => ['max:200'],
            ]);

            $citizenServiceRequest->type_institution = $request->type_institution ?? false;
            $citizenServiceRequest->institution_name = $request->institution_name;
            $citizenServiceRequest->rif = $request->rif;
            $citizenServiceRequest->institution_address = $request->institution_address;
            $citizenServiceRequest->web = $request->web;
        } else {
            $citizenServiceRequest->type_institution = false;
            $citizenServiceRequest->institution_name = null;
            $citizenServiceRequest->rif = null;
            $citizenServiceRequest->institution_address = null;
            $citizenServiceRequest->web = null;
        }

        $this->validate($request, $validateRules, $this->messages);

        $i = 0;
        foreach ($request->phones as $phone) {
            $this->validate(
                $request,
                [
                    'phones.' . $i . '.type' => ['required'],
                    'phones.' . $i . '.area_code' => ['required', 'digits:3'],
                    'phones.' . $i . '.number' => ['required', 'digits:7'],
                    'phones.' . $i . '.extension' => ['nullable', 'digits_between:3,6'],
                ],
                [],
                [
                    'phones.' . $i . '.type' => 'tipo #' . ($i + 1),
                    'phones.' . $i . '.area_code' => 'código de area #' . ($i + 1),
                    'phones.' . $i . '.number' => 'número #' . ($i + 1),
                    'phones.' . $i . '.extension' => 'extensión #' . ($i + 1),
                ]
            );
            $i++;
        }

        $citizenServiceRequest->date                             = $request->date;
        $citizenServiceRequest->gender_id                        = $request->gender_id;
        $citizenServiceRequest->gender                           = $request->gender;
        $citizenServiceRequest->nationality_id                   = $request->nationality_id;
        $citizenServiceRequest->nationality                      = $request->nationality;
        $citizenServiceRequest->first_name                       = $request->first_name;
        $citizenServiceRequest->last_name                        = $request->last_name;
        $citizenServiceRequest->id_number                        = $request->id_number;
        $citizenServiceRequest->email                            = $request->email;
        $citizenServiceRequest->birth_date                       = $request->birth_date;
        $citizenServiceRequest->age                              = $request->age;
        $citizenServiceRequest->city_id                          = $request->city_id;
        $citizenServiceRequest->parish_id                        = $request->parish_id;
        $citizenServiceRequest->address                          = $request->address;
        $citizenServiceRequest->community                        = $request->community;
        $citizenServiceRequest->location                         = $request->location;
        $citizenServiceRequest->commune                          = $request->commune;
        $citizenServiceRequest->communal_council                 = $request->communal_council;
        $citizenServiceRequest->population_size                  = $request->population_size;
        $citizenServiceRequest->motive_request                   = $request->motive_request;
        $citizenServiceRequest->attribute                        = $request->attribute;
        $citizenServiceRequest->state                            = 'Pendiente';
        $citizenServiceRequest->citizen_service_request_type_id  = $request->citizen_service_request_type_id;
        $citizenServiceRequest->citizen_service_department_id    = $request->citizen_service_department_id;
        $citizenServiceRequest->director_id                      = $request->director_id;


        $citizenServiceRequest->inventory_code                   = $request->inventory_code;
        $citizenServiceRequest->type_team                        = $request->type_team;
        $citizenServiceRequest->brand                            = $request->brand;
        $citizenServiceRequest->model                            = $request->model;
        $citizenServiceRequest->serial                           = $request->serial;
        $citizenServiceRequest->color                            = $request->color;
        $citizenServiceRequest->transfer                         = $request->transfer;
        $citizenServiceRequest->entryhour                        = $request->entryhour;
        $citizenServiceRequest->exithour                         = $request->exithour;
        $citizenServiceRequest->informationteam                  = $request->informationteam;
        $citizenServiceRequest->other                            = $request->other;
        $citizenServiceRequest->save();

        foreach ($citizenServiceRequest->phones as $phone) {
            $phone->delete();
        }
        if ($request->phones && !empty($request->phones)) {
            foreach ($request->phones as $phone) {
                $citizenServiceRequest->phones()->updateOrCreate(
                    [
                        'type' => $phone['type'], 'area_code' => $phone['area_code'],
                        'number' => $phone['number'], 'extension' => $phone['extension']
                    ],
                    [
                        'type' => $phone['type'], 'area_code' => $phone['area_code'],
                        'number' => $phone['number'], 'extension' => $phone['extension']
                    ]
                );
            }
        }

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('citizenservice.request.index')], 200);
    }

    /**
     * Elimina una solicitud de atención al ciudadano
     *
     * @param CitizenServiceRequest $request Datos de la solicitud
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(CitizenServiceRequest $request)
    {
        $request->delete();
        return response()->json(['message' => 'destroy'], 200);
    }

    /**
     * Obtiene un listado de las solicitudes registradas
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueList()
    {
        return response()->json([
            'records' => CitizenServiceRequest::with([
                'city', 'parish', 'requestGender', 'requestNationality', 'requestDirector'
            ])->get()
        ], 200);
    }

    /**
     * Obtiene información sobre una solicitud
     *
     * @param  integer $id ID de la solicitud
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueInfo($id)
    {
        $citizenServiceRequest = CitizenServiceRequest::where('id', $id)->with([
            'phones', 'citizenServiceDepartment', 'citizenServiceRequestType', 'citizenServiceIndicator.indicator',
            'requestGender', 'requestNationality', 'requestDirector'
        ])->first();
        return response()->json(['record' => $citizenServiceRequest], 200);
    }

    /**
     * Obtiene las solicitudes en estatus pendiente
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueListPending()
    {
        return response()->json(['records' => CitizenServiceRequest::where('state', 'Pendiente')->get()], 200);
    }

    /**
     * Obtiene las solicitudes aceptadas
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueListClosing()
    {
        $citizenServiceRequest = CitizenServiceRequest::where('state', 'Aceptado')->get();
        return response()->json(['records' => $citizenServiceRequest], 200);
    }

    /**
     * Aprueba las solicitudes
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     * @param integer $id identificador de la solicitud
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function approved(Request $request, $id)
    {
        $citizenServiceRequest = CitizenServiceRequest::find($id);
        $citizenServiceRequest->state = 'Aceptado';
        $citizenServiceRequest->observation  = $request->observation;

        $citizenServiceRequest->save();

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('citizenservice.request.index')], 200);
    }


    /**
     * Rechaza las solicitudes
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     * @param integer $id identificador de la solicitud
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function rejected(Request $request, $id)
    {
        $citizenServiceRequest = CitizenServiceRequest::find($id);
        $citizenServiceRequest->state = 'Rechazado';
        $citizenServiceRequest->observation  = $request->observation;


        $citizenServiceRequest->save();

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('citizenservice.request.index')], 200);
    }

    /**
     * Agrega indicadores a las solicitudes
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     * @param integer $id identificador de la solicitud
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addIndicator(Request $request, $id)
    {
        $citizenServiceRequest = CitizenServiceRequest::find($id);

        foreach ($request->indicators as $indicator) {
            CitizenServiceAddIndicator::create([
                'name'         => $indicator['name'],
                'indicator_id' => $indicator['indicator_id'],
                'request_id'   => $citizenServiceRequest->id
            ]);
        }

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('citizenservice.request.index')], 200);
    }

    /**
     * Obtiene la lista de códigos de las solicitudes
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRequestCodes()
    {
        $codeList = CitizenServiceRequest::all();
        $codes = [];
        array_push($codes, [
            'id' => '',
            'text' => 'Seleccione...'
        ]);
        foreach ($codeList->all() as $code) {
            array_push($codes, [
                'id' => $code->id,
                'text' => $code->code
            ]);
        }
        return response()->json($codes);
    }
}
