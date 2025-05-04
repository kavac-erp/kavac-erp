<?php

namespace Modules\Sale\Http\Controllers;

use Modules\Sale\Models\SaleSettingFrecuency;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * @class FrecuencyController
 * @brief Gestiona información de Periodos de tiempo
 *
 * Controlador para gestionar Periodos de tiempo
 *
 * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FrecuencyController extends Controller
{
    use ValidatesRequests;

    /**
     * Define la configuración de la clase
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:sale.setting.frecuency', ['only' => 'index']);
    }

    /**
     * Muesta todos los registros de Periodos de tiempo
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse con los registros de Periodos de tiempo
     */
    public function index()
    {
        return response()->json(['records' => SaleSettingFrecuency::all()], 200);
    }

    /**
     * Valida y registra un nuevo Periodo de tiempo
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @param  \Illuminate\Http\Request  $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse con mensaje de exito
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'max:100'],
            'days' => ['required'],
        ]);

        if (!restore_record(SaleSettingFrecuency::class, ['name' => $request->name])) {
            $this->validate($request, [
                'name' => ['unique:frecuencies,name']
            ]);
        }

        /* Objeto con información del Periodo de tiempo registrado */
        $frecuency = SaleSettingFrecuency::updateOrCreate([
            'name' => $request->name,
            'days' => $request->days
        ]);

        return response()->json(['record' => $frecuency, 'message' => 'Success'], 200);
    }

    /**
     * Actualiza la información del Periodo de tiempo
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @param  \Illuminate\Http\Request  $request Datos de la petición
     * @param  \Modules\Sale\Models\SaleSettingFrecuency  $frecuency Datos del período de tiempo
     *
     * @return \Illuminate\Http\JsonResponse con mensaje de exito
     */
    public function update(Request $request, SaleSettingFrecuency $frecuency)
    {
        $this->validate($request, [
            'name' => ['required', 'max:100', 'unique:frecuencies,name,' . $frecuency->id],
            'days' => ['required']
        ]);

        $frecuency->name = $request->name;
        $frecuency->days = $request->days;
        $frecuency->save();

        return response()->json(['message' => __('Registro actualizado correctamente')], 200);
    }

    /**
     * Elimina el Periodo de tiempo
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @param  \Modules\Sale\Models\SaleSettingFrecuency  $frecuency Datos del Periodo de tiempo
     *
     * @return \Illuminate\Http\JsonResponse con mensaje de exito
     */
    public function destroy(SaleSettingFrecuency $frecuency)
    {
        $frecuency->delete();
        return response()->json(['record' => $frecuency, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene las frecuencias
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFrecuencies()
    {
        return response()->json(template_choices('Modules\Sale\Models\SaleSettingFrecuency', 'name', 'days', false));
    }
}
