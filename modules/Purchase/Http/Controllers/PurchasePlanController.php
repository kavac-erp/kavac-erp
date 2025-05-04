<?php

namespace Modules\Purchase\Http\Controllers;

use App\Repositories\UploadDocRepository;
use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Purchase\Models\PurchasePlan;
use Modules\Purchase\Models\PurchaseType;
use Modules\Purchase\Models\Document;
use Nwidart\Modules\Facades\Module;

class PurchasePlanController extends Controller
{
    use ValidatesRequests;

    public function __construct()
    {
        /** Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:purchase.purchase_plans.list', ['only' => 'index', 'vueList']);
        $this->middleware('permission:purchase.purchase_plans.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:purchase.purchase_plans.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:purchase.purchase_plans.delete', ['only' => 'destroy']);
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $record_list = PurchasePlan::with('purchaseProcess', 'purchaseType', 'document')->orderBy('id')->get();
        return view('purchase::purchase_plans.index', compact('record_list'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $purchase_process = template_choices('Modules\Purchase\Models\PurchaseProcess', 'name', [], true);

        $users = (Module::has('Payroll')) ?
            template_choices(
                'Modules\Payroll\Models\PayrollStaff',
                ['id_number', '-', 'full_name'],
                ['relationship' => 'PayrollEmployment', 'where' => ['active' => true]],
                true
            ) : [];

        $purchase_types = [];

        array_push($purchase_types, [
            'id'                    =>  '',
            'text'                  =>  'Seleccione...',
        ]);
        foreach (PurchaseType::with('purchaseProcess')->orderBy('id')->get() as $record) {
            array_push($purchase_types, [
                'id'                    =>  $record->id,
                'text'                  =>  $record->name,
            ]);
        }

        return view('purchase::purchase_plans.form', [
            'purchase_process' => json_encode($purchase_process),
            'purchase_types'   => json_encode($purchase_types),
            'users'            => json_encode($users),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'purchase_type_id'      => 'required|int',
            'payroll_staff_id'      => 'required|int',
            'init_date'             => 'required|date',
            'end_date'              => 'required|date'//,
        ], [
            'purchase_type_id.required'      => 'El campo tipo de compra es obligatorio.',
            'purchase_type_id.int'           => 'El campo tipo de compra no tiene el formato adecuado.',
            'payroll_staff_id.required'      => 'El campo responsable es obligatorio.',
            'payroll_staff_id.int'           => 'El campo responsable no tiene el formato adecuado.',
            'init_date.required'             => 'El campo fecha inicial es obligatorio.',
            'init_date.date'                 => 'El campo fecha inicial no tiene el formato adecuado.',
            'end_date.required'              => 'El campo fecha de culminación es obligatorio.',
            'end_date.date'                  => 'El campo fecha de culminación no tiene el formato adecuado.',
        ]);

        $purchase_plan = PurchasePlan::create([
            'purchase_type_id'      => $request->purchase_type_id,
            'payroll_staff_id'      => $request->payroll_staff_id,
            'init_date'             => $request->init_date,
            'end_date'              => $request->end_date,
        ]);

        return response()->json(['message' => 'success'], 200);
    }

    /**
     * Show the specified resource.
     * @return JsonResponse
     */
    public function show($id)
    {
        return response()->json([
            'records' => PurchasePlan::with(
                'purchaseType',
                'purchaseProcess',
                'document',
                'PayrollStaff'
            )->find($id)
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     * @return Renderable
     */
    public function edit($id)
    {
        $record_edit = PurchasePlan::find($id);

        $purchase_process = template_choices('Modules\Purchase\Models\PurchaseProcess', 'name', [], true);

        $users = [];

        $purchase_types = [];

        array_push($purchase_types, [
            'id'                    =>  '',
            'text'                  =>  'Seleccione...',
        ]);
        foreach (PurchaseType::with('purchaseProcess')->orderBy('id')->get() as $record) {
            array_push($purchase_types, [
                'id'                    =>  $record->id,
                'text'                  =>  $record->name,
            ]);
        }

        $users = (Module::has('Payroll')) ?
            template_choices(
                'Modules\Payroll\Models\PayrollStaff',
                ['id_number', '-', 'full_name'],
                ['relationship' => 'PayrollEmployment', 'where' => ['active' => true]],
                true
            ) : [];

        return view('purchase::purchase_plans.form', [
            'purchase_process' => json_encode($purchase_process),
            'purchase_types'   => json_encode($purchase_types),
            'users'            => json_encode($users),
            'record_edit'      => $record_edit,
        ]);
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $record                        = PurchasePlan::find($id);
        $record->init_date             = $request->init_date;
        $record->end_date              = $request->end_date;
        $record->purchase_type_id      = $request->purchase_type_id;
        $record->save();
        return response()->json(['message' => 'success'], 200);
    }

    /**
     * Remove the specified resource from storage.
     * @return JsonResponse
     */
    public function destroy($id)
    {
        PurchasePlan::find($id)->delete();
        return response()->json(['message' => 'Success'], 200);
    }

    public function uploadFile(Request $request)
    {
        $this->validate($request, [
            'file'            => 'required|mimes:pdf',
            'purchase_plan_id' => 'required|integer',
        ], [
            'file.required'             => 'El archivo es obligatorio.',
            'file.mimes'                => 'El archivo debe ser de tipo pdf.',
            'purchase_plan_id.required' => 'El campo plan de compra es obligatorio.',
            'purchase_plan_id.integer'  => 'El campo plan de compra debe ser numerico.',
        ]);

        // Se guarda el archivo
        $document = new UploadDocRepository();

        $name = $request['file']->getClientOriginalName();
        $docs = Document::where('file', ($name))->get()->count();

        $document->uploadDoc(
            $request['file'],
            'documents',
            'Modules\Purchase\Models\PurchasePlan',
            $request->purchase_plan_id,
            null
        );
        $purchase_plan = PurchasePlan::find($request->purchase_plan_id);
        $purchase_plan->active = true;
        $purchase_plan->save();
    }

    public function getDownload($code)
    {
        $doc = Document::where('code', $code)->first();

        $filename = $doc->file;

        $path = explode('storage', storage_path())[0];

        return response()->download($path . '/' . $doc->url, $doc->file);
    }
}
