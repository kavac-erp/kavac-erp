<?php

namespace Modules\CitizenService\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\CitizenService\Models\CitizenServiceRegister;
use Modules\CitizenService\Models\CitizenServiceRequest;

/**
 * @class CitizenServiceRegisterController
 * @brief Controlador para los registros de la oficina de atención al ciudadano
 *
 * Clase que gestiona el controlador para los registros de la OAC
 *
 * @author Ing. Yenifer Ramirez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceRegisterController extends Controller
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
     * Lista de atributos personalizados
     *
     * @var array $customAttributes
     */
    protected $customAttributes;

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
        $this->middleware('permission:citizenservice.registers.list', ['only' => ['index', 'vueList']]);
        $this->middleware('permission:citizenservice.registers.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:citizenservice.registers.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:citizenservice.registers.delete', ['only' => 'destroy']);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'date_register'     => ['required'],
            'payroll_staff_id'  => ['required'],
            'code'          => ['required'],
            'team_name'     => ['required', 'max:200'],
            'activities'    => ['required', 'max:100'],
            'start_date'    => ['required','date'],
            'end_date'      => ['required','after_or_equal:start_date'],
            'email'         => ['required', 'email'],
            'percent'       => ['integer', 'min:1', 'max:100']

        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'payroll_staff_id.required'   => 'El campo Nombre del director es obligatorio.',
            'code.required'           => 'El campo Código de la solicitud es obligatorio',
            'team_name.required'      => 'El campo Equipo responsable es obligatorio',
            'team_name.max'           => 'El campo Equipo responsable no debe contener más de 200 caracteres.',
            'activities.required'     => 'El campo Actividades es obligatorio',
            'activities.max'          => 'El campo Actividades no debe contener más de 100 caracteres.',
            'start_date.required'     => 'El campo Fecha de inicio es obligatorio',
            'end_date.required'       => 'El campo Fecha de culminación es obligatorio',
            'end_date.after_or_equal' => 'La fecha de culminación debe ser una fecha posterior o igual a la fecha de inicio',
            'email.required'          => 'El campo Correo electrónico es obligatorio',
            'email.email'             => 'El campo Correo electrónico es de tipo email',
            'percent.max'             => 'El campo Porcentaje de cumplimiento no debe ser un valor mayor a 100.',
            'percent.integer'         => 'El campo Porcentaje de cumpliento debe ser entero',
            'percent.min'             => 'El campo Porcentaje de cumpliento número minimo es 1',
        ];

        $this->customAttributes = [
            'code' => 'código de la solicitud',
            'date_register' => 'Fecha del registro',

        ];
    }

    /**
     * Muestra el listado de cronogramas de actividades
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('citizenservice::registers.list');
    }

    /**
     * Muestra el formulario para registrar un cronograma de actividades
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('citizenservice::registers.create');
    }

    /**
     * Valida y registra un nuevo cronograma de actividades
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules, $this->messages, $this->customAttributes);

        //Guardar los registros del formulario en  CitizenServiceRegister
        CitizenServiceRegister::create([
            'date_register'    => $request->input('date_register'),
            'payroll_staff_id' => $request->input('payroll_staff_id'),
            'code'             => $request->input('code'),
            'team_name'        => $request->input('team_name'),
            'activities'       => $request->input('activities'),
            'start_date'       => $request->input('start_date'),
            'end_date'         => $request->input('end_date'),
            'email'            => $request->input('email'),
            'percent'          => $request->input('percent')
        ]);

        $request->session()->flash('message', ['type' => 'store']);
        return response()->json(['result' => true, 'redirect' => route('citizenservice.register.index')], 200);
    }

    /**
     * Muestra la información de un cronograma de actividades
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('citizenservice::show');
    }

    /**
     * Muestra el formulario para actualizar la información de un cronograma de actividades
     *
     * @param integer $id identificador del cronograma de actividades
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $request = CitizenServiceRegister::find($id);
        return view('citizenservice::registers.create', compact('request'));
    }

    /**
     * Actualiza un registro de actividades
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $citizenServiceRegister = CitizenServiceRegister::find($id);

        $citizenServiceRegister->date_register         = $request->date_register;
        $citizenServiceRegister->payroll_staff_id      = $request->payroll_staff_id;
        $citizenServiceRegister->code                  = $request->code;
        $citizenServiceRegister->team_name             = $request->team_name;
        $citizenServiceRegister->activities            = $request->activities;
        $citizenServiceRegister->start_date            = $request->start_date;
        $citizenServiceRegister->end_date              = $request->end_date;
        $citizenServiceRegister->email                 = $request->email;
        $citizenServiceRegister->percent               = $request->percent;
        $citizenServiceRegister->save();

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('citizenservice.register.index')], 200);
    }


    /**
     * Elimina un registro de actividad
     *
     * @param  integer $id identificador del cronograma de actividades
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $citizenServiceRegister = CitizenServiceRegister::find($id);
        $citizenServiceRegister->delete();
        return response()->json(['record' => $citizenServiceRegister,
            'message' => 'destroy'
        ], 200);
    }


    /**
     * Obtiene un listado de los registros de actividades
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueList()
    {
        $records = CitizenServiceRegister::with(['payrollStaff', 'codeCitizenServiceRequest'])->get()->toArray();
        foreach ($records as $key => $value) {
            $codes = CitizenServiceRequest::where('id', $value['code'])->get()->toArray();
            $records[$key]['request_code'] = $codes[0]['code'];
        }
        return response()->json(['records' => $records], 200);
    }

    /**
     * Obtiene la información de un registro de actividad registrado
     *
     * @param integer $id identificador del cronograma de actividades
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueInfo($id)
    {
        $citizenServiceRegister = CitizenServiceRegister::where('id', $id)
            ->with(['payrollStaff', 'codeCitizenServiceRequest'])->first();
        return response()->json(['record' => $citizenServiceRegister], 200);
    }
}
