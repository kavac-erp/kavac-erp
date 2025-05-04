<?php

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Support\Renderable;
use Modules\Finance\Rules\ConciliationBankExist;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Finance\Models\FinanceSettingBankReconciliationFiles;

/**
 * @class FinanceSettingBankReconciliationFilesController
 *
 * @brief Configuraciones de los archivos de conciliación bancaria.
 *
 * Clase que gestiona las configuraciones de archivos de conciliación bancarias.
 *
 * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FinanceSettingBankReconciliationFilesController extends Controller
{
    use ValidatesRequests;

    /**
     * Define la configuración inicial de la clase.
     *
     * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        /* Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:finance.settingbankreconciliationfiles.index', ['only' => 'index']);
        $this->middleware('permission:finance.settingbankreconciliationfiles.store', ['only' => 'store']);
        $this->middleware('permission:finance.settingbankreconciliationfiles.update', ['only' => 'update']);
        $this->middleware('permission:finance.settingbankreconciliationfiles.destroy', ['only' => 'destroy']);
    }

    /**
     * Obtiene un listado de los registros almacenados.
     *
     * @author Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => FinanceSettingBankReconciliationFiles::orderBy('bank_id')->get()], 200);
    }

    /**
     * Almacena un registro recién creado en la base de datos.
     *
     * @author Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'bank_id' => ['required', new ConciliationBankExist()],
            'balance_according_bank' => ['required'],
            'position_reference_column' => [
                'required',
                'different:different:position_date_column',
                'different:position_description_column',
                'different:position_balance_according_bank',
                'different:position_debit_amount_column',
                'different:position_credit_amount_column'
            ],
            'position_date_column' => [
                'required',
                'different:position_reference_column',
                'different:position_description_column',
                'different:position_balance_according_bank',
                'different:position_debit_amount_column',
                'different:position_credit_amount_column'
            ],
            'position_debit_amount_column' => [
                'required',
                'different:position_reference_column',
                'different:position_description_column',
                'different:position_balance_according_bank',
            ],
            'position_credit_amount_column' => [
                'required',
                'different:position_reference_column',
                'different:position_description_column',
                'different:position_balance_according_bank',
            ],
            'position_balance_according_bank' => ['required'],
            'separated_by' => ['required'],
            'date_format' => ['required'],
            'thousands_separator' => ['required', 'different:decimal_separator'],
            'decimal_separator' => ['required'],
        ], [
            'bank_id.required' => 'El campo banco es obligatorio.',
            'balance_according_bank.required' => 'El campo saldo según banco es obligatorio.',
            'position_reference_column.required' => 'El campo referencia es obligatorio.',
            'position_date_column.required' => 'El campo fecha es obligatorio.',
            'position_debit_amount_column.required' => 'El campo monto débito es obligatorio.',
            'position_credit_amount_column.required' => 'El campo monto crédito es obligatorio.',
            'position_description_column.required' => 'El campo descripción es obligatorio.',
            'position_balance_according_bank.required' => 'El campo saldo según banco es obligatorio.',
            'separated_by.required' => 'El campo columnas separadas por es obligatorio.',
            'date_format.required' => 'El campo formato de fecha es obligatorio.',
            'thousands_separator.required' => 'El campo separador de miles es obligatorio.',
            'decimal_separator.required' => 'El campo separador de decimales es obligatorio.',
        ]);

        $data = DB::transaction(function () use ($request) {
            $data = FinanceSettingBankReconciliationFiles::create([
                'bank_id' => $request->bank_id,
                'read_start_line' => $request->read_start_line,
                'read_end_line' => $request->read_end_line,
                'balance_according_bank' => $request->balance_according_bank,
                'position_reference_column' => $request->position_reference_column,
                'position_date_column' => $request->position_date_column,
                'position_debit_amount_column' => $request->position_debit_amount_column,
                'position_credit_amount_column' => $request->position_credit_amount_column,
                'position_description_column' => $request->position_description_column,
                'position_balance_according_bank' => $request->position_balance_according_bank,
                'separated_by' => $request->separated_by,
                'date_format' => $request->date_format,
                'thousands_separator' => $request->thousands_separator,
                'decimal_separator' => $request->decimal_separator,
            ]);
            return $data;
        });
        return response()->json(['record' => $data, 'message' => 'Success'], 200);
    }

    /**
     * Muestra información de la configuración de conciliación bancaria
     *
     * @return void
     */
    public function show()
    {
        //
    }

    /**
     * Actualiza un registro específico de la base de datos.
     *
     * @author Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $data = FinanceSettingBankReconciliationFiles::find($id);
        $data->bank_id = $request->bank_id;
        $data->read_start_line = $request->read_start_line;
        $data->read_end_line = $request->read_end_line;
        $data->balance_according_bank = $request->balance_according_bank;
        $data->position_reference_column = $request->position_reference_column;
        $data->position_date_column = $request->position_date_column;
        $data->position_debit_amount_column = $request->position_debit_amount_column;
        $data->position_credit_amount_column = $request->position_credit_amount_column;
        $data->position_description_column = $request->position_description_column;
        $data->position_balance_according_bank = $request->position_balance_according_bank;
        $data->separated_by = $request->separated_by;
        $data->date_format = $request->date_format;
        $data->thousands_separator = $request->thousands_separator;
        $data->decimal_separator = $request->decimal_separator;
        $data->save();
        return response()->json(['message' => 'Registro actualizado correctamente'], 200);
    }

    /**
     * Elimina un registro específico de la base de datos.
     *
     * @author Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $data = FinanceSettingBankReconciliationFiles::find($id);
        $data->delete();
        return response()->json(['record' => $data, 'message' => 'Success'], 200);
    }
}
