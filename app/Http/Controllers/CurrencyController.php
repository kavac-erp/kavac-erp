<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;
use App\Rules\UniqueCurrency;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToArray;

/**
 * @class CurrencyController
 * @brief Gestiona información de Monedas
 *
 * Controlador para gestionar Monedas
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CurrencyController extends Controller
{
    /**
     * Define la configuración de la clase
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:currency.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:currency.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:currency.delete', ['only' => 'destroy']);
        $this->middleware('permission:currency.list', ['only' => 'index']);
    }

    /**
     * Muesta todos los registros de las monedas.
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    JsonResponse    JSON con los datos de respuesta a la petición
     */
    public function index()
    {
        return response()->json(['records' => Currency::with('country')->get()], 200);
    }

    /**
     * Registra una nueva moneda
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Request    $request    Objeto con datos de la petición
     *
     * @return    JsonResponse     JSON con datos de respuesta a la petición
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'max:40', new UniqueCurrency()],
            'plural_name' => ['required', 'max:40'],
            'symbol' => ['required', 'max:4'],
            'country_id' => ['required'],
            'decimal_places' => ['required', 'numeric', 'min:2', 'max:10']
        ], [
            'name.required' => 'El campo nombre es obligatorio.',
            'name.max' => 'El campo nombre no debe ser mayor a 40 caracteres.',
            'plural_name.required' => 'El campo nombre en plural es obligatorio.',
            'plural_name.max' => 'El campo nombre en plural no debe ser mayor a 40 caracteres.',
            'symbol.required' => 'El campo simbolo es obligatorio.',
            'symbol.max' => 'El campo simbolo no debe ser mayor a 4 caracteres.',
            'country_id.required' => 'El campo país es obligatorio.',
            'decimal_places.required' => 'El campo decimales es obligatorio.',
            'decimal_places.max' => 'El campo decimales no debe ser mayor a 10 caracteres.',
            'decimal_places.min' => 'El campo decimales no debe ser menor a 2 caracteres.',
        ]);

        if ($request->default || !empty($request->default)) {
            // Si se ha indicado la moneda por defecto, se deshabilita esta condición en las ya registradas
            foreach (Currency::all() as $curr) {
                $curr->default = false;
                $curr->save();
            }
        }

        // Objeto con información de la moneda registrada
        $currency = Currency::create([
            'name' => $request->name,
            'plural_name' => $request->plural_name ?? $request->name,
            'symbol' => $request->symbol,
            'default' => ($request->default || !empty($request->default)),
            'country_id' => $request->country_id,
            'decimal_places' => $request->decimal_places
        ]);

        return response()->json(['record' => $currency, 'message' => 'Success'], 200);
    }

    /**
     * Actualiza un registro de moneda
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Request     $request     Objeto con datos de la petición
     * @param     Currency    $currency    Objeto con información de la moneda a modificar
     *
     * @return    JsonResponse      JSON con información de respuesta a la petición
     */
    public function update(Request $request, Currency $currency)
    {
        $this->validate($request, [
            'name' => ['required', 'max:40'],
            'plural_name' => ['required', 'max:40'],
            'symbol' => ['required', 'max:4'],
            'country_id' => ['required'],
            'decimal_places' => ['required', 'numeric', 'min:2', 'max:10']
        ], [
            'name.required' => 'El campo nombre es obligatorio.',
            'name.max' => 'El campo nombre no debe ser mayor a 40 caracteres.',
            'plural_name.required' => 'El campo nombre en plural es obligatorio.',
            'plural_name.max' => 'El campo nombre en plural no debe ser mayor a 40 caracteres.',
            'symbol.required' => 'El campo simbolo es obligatorio.',
            'symbol.max' => 'El campo simbolo no debe ser mayor a 4 caracteres.',
            'country_id.required' => 'El campo país es obligatorio.',
            'decimal_places.required' => 'El campo decimales es obligatorio.',
            'decimal_places.max' => 'El campo decimales no debe ser mayor a 10 caracteres.',
            'decimal_places.min' => 'El campo decimales no debe ser menor a 2 caracteres.',
        ]);

        $currency->name = $request->name;
        $currency->plural_name = $request->plural_name ?? $request->name;
        $currency->symbol = $request->symbol;
        $currency->country_id = $request->country_id;
        $currency->decimal_places = $request->decimal_places;
        if ($request->default) {
            // Si se ha indicado la moneda por defecto, se deshabilita esta condición en las ya registradas
            foreach (Currency::where('id', '!=', $request->id)->get() as $curr) {
                $curr->default = false;
                $curr->save();
            }

            $currency->default = $request->default;
        }
        $currency->save();

        return response()->json(['message' => __('Registro actualizado correctamente')], 200);
    }

    /**
     * Elimina una moneda
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Currency    $currency    Objeto con información de la moneda a eliminar
     *
     * @return    JsonResponse      JSON con información de respuesta a la petición
     */
    public function destroy(Currency $currency)
    {
        try {
            $currency->delete();
            return response()->json(['record' => $currency, 'message' => 'Success'], 200);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => true, 'message' => __($e->getMessage())], 200);
        }
    }

    /**
     * Obtiene las monedas registradas
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     integer           $id    Identificador de la moneda a buscar, este parámetro es opcional
     *
     * @return    JsonResponse             JSON con los datos de las monedas
     */
    public function getCurrencies($id = null)
    {
        $records = [];
        $currency = Currency::where('default', 't')->first();

        array_push($records, [
            'id' => $currency->id,
            'text' => $currency->symbol . ' - ' . $currency->name,
            'plural_name' => $currency->plural_name,
            'default' => true
        ]);

        $currencies = Currency::where('default', 'f')->get();

        foreach ($currencies as $curr) {
            array_push($records, [
                'id' => $curr->id,
                'text' => $curr->symbol . ' - ' . $curr->name,
                'plural_name' => $curr->plural_name,
                'default' => false
            ]);
        }

        return response()->json($records, 200);
    }

    /**
     * Obtiene la moneda registrada por defecto
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param     integer           $id    Identificador de la moneda a buscar, este parámetro es opcional
     *
     * @return    JsonResponse             JSON con los datos de las monedas
     */
    public function getDefaultCurrencies($id = null)
    {
        return response()->json(
            template_choices(
                'App\Models\Currency',
                ['symbol', '-', 'name'],
                ['default' => 't'],
                true
            )
        );
    }

    /**
     * Obtiene información de una moneda
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     integer             $id    Identificador de la moneda de la cual se va a obtener información
     *
     * @return    JsonResponse               JSON con datos de respuesta a la petición
     */
    public function getCurrencyInfo($id)
    {
        return response()->json(['result' => true, 'currency' => Currency::find($id)], 200);
    }
}
