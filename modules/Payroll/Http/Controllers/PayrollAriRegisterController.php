<?php

namespace Modules\Payroll\Http\Controllers;

use DateTime;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Payroll\Models\PayrollStaff;
use Illuminate\Contracts\Support\Renderable;
use Modules\Payroll\Models\PayrollAriRegister;
use Modules\Payroll\Exports\PayrollAriRegisterExport;
use Modules\Payroll\Jobs\PayrollAriRegisterImportJob;

/**
 * @class PayrollAriRegisterController
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollAriRegisterController extends Controller
{
    protected $rules;

    protected $messages;


    public function __construct()
    {
        /** Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:payroll.ari_register.list', ['only' => ['index', 'getAriRegisters']]);
        $this->middleware('permission:payroll.ari_register.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:payroll.ari_register.edit', ['only' => ['edit']]);
        $this->middleware('permission:payroll.ari_register.delete', ['only' => ['destroy']]);
        $this->middleware('permission:payroll.ari_register.import', ['only' => 'import']);
        $this->middleware('permission:payroll.ari_register.export', ['only' => 'export']);

        $this->rules = [
            "id"                            => 'nullable',
            "percetage"                     => 'required',
            "startDate"                     => ['required'],
            "endDate"                       => 'nullable|after:startDate',
            "payroll_staff_id"              => "required",
        ];
        $this->messages = [
            "percetage.required"            => 'El porcentaje de pago es requerido',
            "startDate.required"            => 'La fecha de inicio es requerida',
            "startDate.before"              => 'La fecha de inicio debe ser anterior a la fecha final',
            "endDate.after"                 => 'La fecha final debe ser posterior a la fecha de inicio',
            "payroll_staff_id.required"     => 'El trabajador es requerido',
            "payroll_staff_id.unique"       => 'El registro ya existe para este trabajador'
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
        return view('payroll::ari_register.index');
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
        return view('payroll::ari_register.create-edit');
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
        if ($request->endDate) {
            array_push($this->rules["startDate"], "before:endDate");
        }
    
        $request->validate($this->rules, $this->messages);

        $allAriRegisterForPayrollStaff =
            PayrollAriRegister::query()
                ->where('payroll_staff_id', $request->payroll_staff_id)
                ->when(isset($request->id), function ($query) use ($request) {
                    $query->where('id', '!=', $request->id);
                })
                ->get();

        $from_date = null;
        $to_date = null;

        /** Fechas del formulario ARI*/
        $startDate = new DateTime($request->startDate);

        if (count($allAriRegisterForPayrollStaff) > 0) {
            $lastestAriRegisterForPayrollStaff = $allAriRegisterForPayrollStaff->last();
            /** Fechas del ultimo registro ARI */
            $from_date = new DateTime($lastestAriRegisterForPayrollStaff?->from_date);
            $to_date = $lastestAriRegisterForPayrollStaff->to_date ? new DateTime($lastestAriRegisterForPayrollStaff->to_date) : null;

            if (new DateTime($request->startDate) <= $from_date && !$request->id) {
                return response()->json(['errors' => ['error' => ['La fecha de inicio debe ser posterior a la fecha de inicio anterior. Periodo: ' . $from_date->format('d-m-Y')]]], 500);
            }

            if ($to_date) {
                if ($startDate <= $to_date && $startDate >= $from_date && (count($allAriRegisterForPayrollStaff) > 1 || $request->id && count($allAriRegisterForPayrollStaff) >= 1)) {
                    return response()->json(['errors' => ['error' => ['La fecha de inicio se encuentra dentro del periodo de un registro anterior. Periodo: ' . $from_date->format('d-m-Y') . ' - ' . $to_date->format('d-m-Y')]]], 500);
                } else {
                    $lastestAriRegisterForPayrollStaff->to_date = $startDate->modify('-1 day')->format('Y-m-d');
                    $lastestAriRegisterForPayrollStaff->save();
                }
            } else {
                if ($startDate <= $from_date && count($allAriRegisterForPayrollStaff) > 1) {
                    return response()->json(['errors' => ['error' => ['La fecha de inicio se encuentra dentro del periodo de un registro anterior. Periodo: ' . $from_date->format('d-m-Y')]]], 500);
                }

                $lastestAriRegisterForPayrollStaff->to_date = $startDate->modify('-1 day')->format('Y-m-d');
                $lastestAriRegisterForPayrollStaff->save();
            }
        }

        if (!$request->id) {
            PayrollAriRegister::query()->create([
                "percetage" => $request->percetage / 100,
                "from_date" => $request->startDate,
                "to_date" => $request->endDate ?? null,
                "payroll_staff_id" => $request->payroll_staff_id
            ]);
        } else {
            $register = PayrollAriRegister::query()->find($request->id);
            $register->percetage = $request->percetage / 100;
            $register->from_date = $request->startDate;
            $register->to_date = $request->endDate ?? null;
            $register->save();
        }

        return response()->json(['success' => true, 'redirect_back' => 'payroll/ari-register'], 200);
    }

    /**
     * undocumented function summary
     *
     * Undocumented function long description
     *
     * @param Type $var Description
     *
     * @return type
     *
     * @throws conditon
     **/
    public function getAriRegisters()
    {
        return response()->json([
            'records' => PayrollStaff::query()
                ->whereHas('payrollAriRegisters')
                ->with('payrollAriRegisters')
                ->get()
        ], 200);
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
        $payrollAriRegister = PayrollAriRegister::query()->where('payroll_staff_id', $id)->latest()->first();

        return view('payroll::ari_register.create-edit', ['payrollAriRegister' => json_encode($payrollAriRegister ?? '')]);
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
        $payrollAriRegister = PayrollAriRegister::where('payroll_staff_id', $id)->get();

        foreach ($payrollAriRegister as $payrollAriRegister) {
            $payrollAriRegister->delete();
        }

        return response()->json(['message' => 'Success'], 200);
    }
    public function import(Request $request)
    {
        request()->validate([
            'file' => 'required|mimes:xlx,xls,xlsx'
        ]);

        $data['filePath'] = $request->file('file')->store('/tmp');

        dispatch(new PayrollAriRegisterImportJob($data));
    }

    public function export()
    {
        return Excel::download(new PayrollAriRegisterExport(), 'registros_ari.xlsx');
    }
}
