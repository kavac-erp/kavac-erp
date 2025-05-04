<?php

namespace App\Http\Controllers;

use App\Models\Deduction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @class DeductionController
 * @brief Gestiona información de las deducciones y/o retenciones
 *
 * Controlador para gestionar las deducciones y/o retenciones
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class DeductionController extends Controller
{
    /**
     * Listado de todas las deducciones registradas
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    JsonResponse    JSON con el listado de deducciones
     */
    public function index()
    {
        return response()->json(['records' => Deduction::with(['accountingAccount'])->get()], 200);
    }

    /**
     * Registra una nueva deducción
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Request    $request    Objeto con información de la petición
     *
     * @return    JsonResponse     JSON con información de respuesta a la petición
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'unique:deductions,name'],
            'formula' => ['required']
        ]);

        $request->merge(['active' => (!is_null($request->active) && $request->active !== false)]);
        // Objeto con información de la deducción registrada
        $deduction = Deduction::create($request->all());

        return response()->json(['record' => $deduction, 'message' => 'Success'], 200);
    }

    /**
     * Actualiza los datos de una deducción
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Request      $request      Objeto con información de la petición
     * @param     Deduction    $deduction    Objeto con información de la deducción a modificar
     *
     * @return    JsonResponse     JSON con información de respuesta a la petición
     */
    public function update(Request $request, Deduction $deduction)
    {
        $this->validate($request, [
            'name' => ['required', 'unique:deductions,name,' . $deduction->id],
            'formula' => ['required']
        ]);

        $deduction->name = $request->name;
        $deduction->description = $request->description ?? null;
        $deduction->accounting_account_id = $request->accounting_account_id ?? null;
        $deduction->formula = $request->formula;
        $deduction->active = (!is_null($request->active) && $request->active !== false);
        $deduction->save();

        return response()->json(['message' => __('Registro actualizado correctamente')], 200);
    }

    /**
     * Elimina una deducción
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Deduction    $deduction    Objeto con datos de la deducción a eliminar
     *
     * @return    JsonResponse     JSON con información de respuesta a la petición
     */
    public function destroy(Deduction $deduction)
    {
        $deduction->delete();
        return response()->json(['record' => $deduction, 'message' => 'Success'], 200);
    }

    /**
     * Listado de deducciones
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return JsonResponse JSON con el listado de deducciones
     */
    public function list()
    {
        $deductions = Deduction::with('accountingAccount')->where('active', true)->get();
        $options = [['id' => '', 'text' => 'Seleccione...']];
        foreach ($deductions as $deduction) {
            array_push($options, [
                'id' => $deduction->id,
                'text' => $deduction->name,
                'formula' => $deduction->formula,
                'accounting_account' => $deduction->accountingAccount
            ]);
        }
        return response()->json(['records' => $options], 200);
    }
}
