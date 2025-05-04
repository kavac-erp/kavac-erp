<?php

namespace App\Http\Controllers;

use App\Models\ExchangeRate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * @class ExchangeRateController
 * @brief Gestiona información de tipos de cambio de monedas
 *
 * Controlador para gestionar tipos de cambio de monedas
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ExchangeRateController extends Controller
{
    /**
     * Reglas de validación
     *
     * @var array $rules
     */
    private $rules;

    /**
     * Mensajes de validación
     *
     * @var array $messages
     */
    private $messages;

    /**
     * Define la configuración de la clase
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:exchange.rate.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:exchange.rate.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:exchange.rate.delete', ['only' => 'destroy']);
        $this->middleware('permission:exchange.rate.list', ['only' => 'index']);
        $this->rules = [
            'start_at' => ['required', 'date', 'before_or_equal:' . date('Y-m-d')],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at', 'before_or_equal:' . date('Y-m-d')],
            'amount' => ['required', 'numeric', 'gt:0'],
            'from_currency_id' => ['required', 'exists:currencies,id'],
            'to_currency_id' => ['required', 'exists:currencies,id']
        ];
        $this->messages = [
            'start_at.required' => __('La fecha de inicio es requerida'),
            'start_at.date' => __('La fecha de inicio es inválida'),
            'start_at.before_or_equal' => __(
                'La fecha de inicio no puede ser mayor a la fecha actual ' .
                'y debe ser menor o igual a la fecha final si es indicada'
            ),
            'end_at.date' => __('La fecha final es inválida'),
            'end_at.after_or_equal' => __('La fecha final no puede ser menor a la fecha de inicio'),
            'end_at.before_or_equal' => __('La fecha final no puede ser mayor a la fecha actual'),
            'from_currency_id.required' => __('La moneda desde la cual realizar la conversión es requerida'),
            'to_currency_id.required' => __('La moneda a la cual realizar la conversión es requerida'),
            'amount.required' => __('El monto de conversión es requerido'),
            'amount.numeric' => __('El monto de conversión debe ser numérico'),
            'amount.gt' => __('El monto de conversión debe ser mayor a 0'),
            'from_currency_id.exists' => __('La moneda desde la cual realizar la conversión no existe'),
            'to_currency_id.exists' => __('La moneda a la cual realizar la conversión no existe')
        ];
    }

    /**
     * Listado de tipos de cambio de monedas
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    JsonResponse      JSON con datos de los tipos de cambio de monedas registrados
     */
    public function index()
    {
        return response()->json(['records' => ExchangeRate::with('fromCurrency', 'toCurrency')->get()], 200);
    }

    /**
     * Registra un nuevo tipo de cambio de moneda
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Request    $request    Objeto con información de la petición
     *
     * @return    JsonResponse      JSON con datos de respuesta a la petición
     */
    public function store(Request $request)
    {
        $this->checkSameExchange($request);
        $this->validate($request, $this->rules, $this->messages);

        $this->checkAlreadyExchange($request);

        try {
            // Objeto con información del tipo de cambio creado
            if ($request->active) {
                ExchangeRate::where([
                    'from_currency_id' => $request->from_currency_id,
                    'to_currency_id' => $request->to_currency_id
                ])->update([
                    'active' => false
                ]);
            }
            if (
                $exchangeRate = ExchangeRate::withTrashed()->where([
                'from_currency_id' => $request->from_currency_id,
                'to_currency_id' => $request->to_currency_id,
                'start_at' => $request->start_at,
                'end_at' => $request->end_at
                ])->first()
            ) {
                $exchangeRate->update($request->all());
                $exchangeRate->restore();
            } else {
                $exchangeRate = ExchangeRate::create($request->all());
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            // Genera un mensaje al usuario debido a que se generó un error de base de datos de violación de llave única
            $this->validate($request, [
                'start_at' => [
                    function ($attribute, $value, $fail) {
                        $fail(__('El tipo de cambio ya está registrado en el mismo período indicado'));
                    }
                ]
            ]);
        }

        return response()->json(['record' => $exchangeRate, 'message' => 'Success'], 200);
    }

    /**
     * Actualiza los datos de un tipo de cambio de moneda
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Request         $request         Objeto con información de la petición
     * @param     ExchangeRate    $exchangeRate    Objeto con información del tipo de cambio a actualizar
     *
     * @return    JsonResponse      JSON con datos de respuesta a la petición
     */
    public function update(Request $request, ExchangeRate $exchangeRate)
    {
        $this->validate($request, $this->rules, $this->messages);

        $exchangeRate->update($request->all());
        return response()->json(['record' => $exchangeRate, 'message' => 'Update'], 200);
    }

    /**
     * Elimina un tipo de cambio de moneda
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     ExchangeRate    $exchangeRate    Objeto con información del tipo de cambio a eliminar
     *
     * @return    JsonResponse      JSON con datos del tipo de cambio eliminado
     */
    public function destroy(ExchangeRate $exchangeRate)
    {
        $exchangeRate->delete();
        return response()->json(['record' => $exchangeRate, 'message' => 'Success'], 200);
    }

    /**
     * Verifica si existe un tipo de cambio en el mismo período
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg at gmail.com>
     *
     * @param  Request         $request         Objeto con información de la petición
     *
     * @return void
     */
    private function checkSameExchange($request)
    {
        $sameExchange = ExchangeRate::where([
            'from_currency_id' => $request->from_currency_id,
            'to_currency_id' => $request->to_currency_id,
            'start_at' => $request->start_at,
            'end_at' => $request->end_at,
        ])->first();

        if ($sameExchange) {
            $this->validate($request, [
                'start_at' => [
                    function ($attribute, $value, $fail) {
                        $fail(__('Ya existe un tipo de cambio registrado en el mismo período indicado'));
                    }
                ]
            ]);
        }
    }

    /**
     * Determina si el tipo de cambio con la moneda de origen y destino ya existe,
     * en caso de que exista desactiva la registrada y le coloca fecha final
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg at gmail.com>
     *
     * @param  Request         $request         Objeto con información de la petición
     *
     * @return void
     */
    private function checkAlreadyExchange($request)
    {
        $alreadyExchange = ExchangeRate::where([
            'from_currency_id' => $request->from_currency_id,
            'to_currency_id' => $request->to_currency_id
        ])->orderBy('id', 'desc')->first();
        if ($alreadyExchange) {
            $update = ['active' => false];
            if ($alreadyExchange->end_at) {
                $this->validate($request, [
                    'start_at' => ['after_or_equal:' . $alreadyExchange->end_at]
                ], [
                    'start_at.after_or_equal' => __(
                        'La fecha de inicio debe ser mayor o igual a la fecha final ' .
                        'del último tipo de cambio registrado con estas monedas'
                    )
                ]);
            } else {
                $this->validate($request, [
                    'start_at' => ['after_or_equal:' . $alreadyExchange->start_at]
                ], [
                    'start_at.after_or_equal' => __(
                        'La fecha de inicio debe ser mayor o igual a la fecha de inicio ' .
                        'del último tipo de cambio registrado con estas monedas'
                    )
                ]);
            }

            if ($request->active) {
                $currentExchanges = ExchangeRate::where([
                    'from_currency_id' => $request->from_currency_id,
                    'to_currency_id' => $request->to_currency_id
                ])->get();
                foreach ($currentExchanges as $exchange) {
                    if (!$exchange->end_at) {
                        $update['end_at'] = $request->start_at;
                    }
                    try {
                        $exchange->update($update);
                    } catch (\Exception $e) {
                        Log::error($e->getMessage());
                    }
                }
            }
        }
    }
}
