<?php

namespace App\Http\Controllers;

use App\Models\Parameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

/**
 * @class ParameterController
 * @brief Gestiona información para la configuración de parámetros del sistema
 *
 * Controlador para gestionar configuración de parámetros
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ParameterController extends Controller
{
    /**
     * Define la configuración de la clase
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     */
    public function __construct()
    {
        // Establece permisos de acceso del controlador
        $this->middleware('permission:system.param.setting');
        $this->middleware('permission:payroll.parameters.create', ['only' => 'store']);
    }

    /**
     * Registra un nuevo parámetro general del sistema
     *
     * @author     Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <roldandvg@gmail.com>
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     * @author     William Páez <wpaez@cenditel.gob.ve> | <paez.william8@gmail.com>
     *
     * @param     Request    $request    Objeto con información de la petición
     *
     * @return    RedirectResponse     Redirecciona al usuario a la URL previa
     */
    public function store(Request $request)
    {
        // Gestiona el formulario de Configuración de la Edad Laboral Permitida
        if ($request->p_key == 'work_age') {
            $work_age = Parameter::where([
                'required_by' => 'payroll', 'p_key' => $request->p_key
            ])->first();

            if ($work_age) {
                $this->validate(
                    $request,
                    [
                        'p_value' => ['required', 'integer', 'min:16']
                    ],
                    [
                        'p_value.min' => 'La edad laboral mínima permitida es 16 años.'
                    ],
                    [
                        'p_value' => 'edad laboral permitida'
                    ]
                );
                Parameter::updateOrCreate(
                    [
                        'p_key' => $request->p_key,
                        'required_by' => 'payroll',
                        'active' => true
                    ],
                    [
                        'p_value' => $request->p_value
                    ]
                );
            }
        } else { // Gestiona el formulario de Configuración de parametros para reporte de nómina
            // parámetros del formularios
            $parameters = ['number_decimals', 'round', 'zero_concept'];

            foreach ($parameters as $parameter) {
                if ($parameter == 'number_decimals') {
                    Parameter::updateOrCreate(
                        [
                            'p_key' => $parameter,
                            'required_by' => 'payroll',
                            'active' => true
                        ],
                        [
                            'p_value' => $request->$parameter
                        ]
                    );
                } else {
                    Parameter::updateOrCreate(
                        [
                        'p_key' => $parameter,
                        'required_by' => 'payroll',
                        'active' => true
                        ],
                        [
                        'p_value' => (!is_null($request->$parameter)) ? 'true' : 'false'
                        ]
                    );
                }
            }
        }
        $request->session()->flash('message', ['type' => 'store']);
        return redirect()->back();
    }

    /**
     * Obtiene la lista de parametros activo asociado al modelo payroll
     *
     * @author     Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    JsonResponse    Listado de los registros a mostrar
     */
    public function getParameters()
    {
        return response()->json(['records' => Parameter::where(['active' => true, 'required_by' => 'payroll'])->orderBy('id')->get()], 200);
    }
}
