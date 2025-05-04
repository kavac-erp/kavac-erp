<?php

namespace Modules\Payroll\Http\Controllers;

use App\Models\FiscalYear;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Payroll\Models\PayrollHoliday;

/**
 * @class PayrollHolidayController
 * @brief Gestiona los datos de los días feriados
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollHolidayController extends Controller
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
     * Define la configuración de la clase
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        //$this->middleware('permission:payroll.disabilities.list', ['only' => ['index', 'vueList']]);
        $this->middleware('permission:payroll.disabilities.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:payroll.disabilities.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:payroll.disabilities.delete', ['only' => 'destroy']);

        $this->validateRules = [
            'date' => ['required', 'unique:payroll_holidays,date'],
            'description' => ['required']
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'date.required'        => 'El campo día feriado es obligatorio.',
            'date.unique'          => 'El campo día feriado ya ha sido registrado.',
            'description.required' => 'El campo descripción es obligatorio.',
        ];
    }

    /**
     * Listado de los días feriados
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => payrollHoliday::all()], 200);
    }

    /**
     * Muestra el formulario para registrar un nuevo día feriado
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        return view('payroll::create');
    }

    /**
     * Almacena los datos de un nuevo día feriado
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules, $this->messages);
        $payrollHoliday = PayrollHoliday::create([
            'date' => $request->date,
            'description' => $request->description,
            'permanent_day' => $request->permanent_day
        ]);
        return response()->json(['record' => $payrollHoliday, 'message' => 'Success'], 200);
    }

    /**
     * Muestra información de un día feriado
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function show($id)
    {
        return view('payroll::show');
    }

    /**
     * Muestra el formulario para editar un día feriado
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function edit($id)
    {
        return view('payroll::edit');
    }

    /**
     * Actualiza los datos de un día feriado
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param     Request    $request         Datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $payrollHoliday = PayrollHoliday::find($id);
        $this->validateRules['date'] = ['required', 'unique:payroll_holidays,date,' . $payrollHoliday->id];
        $this->validate($request, $this->validateRules, $this->messages);
        $payrollHoliday->date = $request->date;
        $payrollHoliday->description = $request->description;
        $payrollHoliday->permanent_day = $request->permanent_day;
        $payrollHoliday->save();
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina un día feriado
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $payrollHoliday = PayrollHoliday::find($id);
        $payrollHoliday->delete();
        return response()->json(['record' => $payrollHoliday, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene los registros de días feriados
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHolidays()
    {
        $records = [];
        $payrollHolidays = PayrollHoliday::query()->get();

        foreach ($payrollHolidays as $payrollHoliday) {
            if ($payrollHoliday->permanent_day == true) {
                $date = Carbon::createFromFormat('Y-m-d', $payrollHoliday->date)
                    ->toDateString();
                $date = substr($date, 5, 11);
                $currentFiscalYear = FiscalYear::select('year')
                    ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();
                $payrollHolidayDate = $currentFiscalYear->year . '-' . $date;

                array_push($records, [
                    'id' => $payrollHoliday->id,
                    'text' => $payrollHolidayDate,
                ]);
            } else {
                array_push($records, [
                    'id' => $payrollHoliday->id,
                    'text' => $payrollHoliday->date
                ]);
            }
        }

        return response()->json($records, 200);
    }
}
