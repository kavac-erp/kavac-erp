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
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollHolidayController extends Controller
{
    use ValidatesRequests;

    protected $validateRules;
    protected $messages;

    /**
     * Define la configuración de la clase
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     */
    public function __construct()
    {
        /** Establece permisos de acceso para cada método del controlador */
        //$this->middleware('permission:payroll.disabilities.list', ['only' => ['index', 'vueList']]);
        $this->middleware('permission:payroll.disabilities.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:payroll.disabilities.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:payroll.disabilities.delete', ['only' => 'destroy']);

        $this->validateRules = [
            'date' => ['required', 'unique:payroll_holidays,date'],
            'description' => ['required']
        ];

        /** Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'date.required'        => 'El campo día feriado es obligatorio.',
            'date.unique'          => 'El campo día feriado ya ha sido registrado.',
            'description.required' => 'El campo descripción es obligatorio.',
        ];
    }

    /**
     * [descripción del método]
     *
     * @method    index
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @return    Renderable    [description de los datos devueltos]
     */
    public function index()
    {
        return response()->json(['records' => payrollHoliday::all()], 200);
    }

    /**
     * [descripción del método]
     *
     * @method    create
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @return    Renderable    [description de los datos devueltos]
     */
    public function create()
    {
        return view('payroll::create');
    }

    /**
     * [descripción del método]
     *
     * @method    store
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param     object    Request    $request    Objeto con información de la petición
     *
     * @return    Renderable    [description de los datos devueltos]
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
     * [descripción del método]
     *
     * @method    show
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [description de los datos devueltos]
     */
    public function show($id)
    {
        return view('payroll::show');
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
     * @return    Renderable    [description de los datos devueltos]
     */
    public function edit($id)
    {
        return view('payroll::edit');
    }

    /**
     * [descripción del método]
     *
     * @method    update
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param     object    Request    $request         Objeto con datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    Renderable    [description de los datos devueltos]
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
     * [descripción del método]
     *
     * @method    destroy
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [description de los datos devueltos]
     */
    public function destroy($id)
    {
        $payrollHoliday = PayrollHoliday::find($id);
        $payrollHoliday->delete();
        return response()->json(['record' => $payrollHoliday, 'message' => 'Success'], 200);
    }

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
                $payrollHoliday->date = $currentFiscalYear->year . '-' . $date;

                array_push($records, [
                    'id' => $payrollHoliday->id,
                    'text' => $payrollHoliday->date,
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
