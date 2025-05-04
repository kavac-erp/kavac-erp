<?php

namespace Modules\CitizenService\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\CitizenService\Models\CitizenServiceRequest;
use App\Models\CodeSetting;
use App\Rules\CodeSetting as CodeSettingRule;

/**
 * @class CitizenServiceSettingController
 * @brief Controlador de la configuración de loficina de atención al ciudadano
 *
 * Clase que gestiona el controlador de la configuración de la OAC
 *
 * @author Ing. Yenifer Ramirez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceSettingController extends Controller
{
    use ValidatesRequests;

    /**
     * Método constructor de la clase
     *
     * @return void
     */
    public function __construct()
    {
         // Establece permisos de acceso para cada método del controlador
         $this->middleware('permission:citizenservice.setting.index', ['only' => 'index']);
    }

    /**
     * Obtiene los códigos configurados para la OAC
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $codeSettings = CodeSetting::where('module', 'citizenservice')->get();
        $sCode = $codeSettings->where('table', 'citizen_service_requests')->first();
        return view('citizenservice::settings', compact('codeSettings', 'sCode'));
    }

    /**
     * Muestra el formulario para registrar configuraciones de la OAC
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('citizenservice::create');
    }

    /**
     * Registra una nueva configuración de la OAC
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'request_code' => [new CodeSettingRule()]
        ]);

        $requestCode = $request->request_code;
        if (!is_null($requestCode)) {
            $model = CitizenServiceRequest::class;
            list($prefix, $digits, $sufix) = CodeSetting::divideCode($requestCode);

            $codeSetting = CodeSetting::where([
                'module' => 'citizenservice',
                'table'  => 'citizen_service_requests',
                'field'  => 'code'
            ])->first();

            if (!isset($codeSetting)) {
                $codeSetting = CodeSetting::create([
                    'module'        => 'citizenservice',
                    'table'         => 'citizen_service_requests',
                    'field'         => 'code',
                    'format_prefix' => $prefix,
                    'format_digits' => $digits,
                    'format_year'   => $sufix,
                    'model'         => $model
                ]);
            }
            $request->session()->flash('message', ['type' => 'store']);
        }

        return redirect()->back();
    }

    /**
     * Muestra información de una configuración
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('citizenservice::show');
    }

    /**
     * Muestra el formulario para editar la configuración de la OAC
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('citizenservice::edit');
    }

    /**
     * Actualiza los datos de una configuración
     *
     * @param  Request $request Datos de la petición
     *
     * @return void
     */
    public function update(Request $request)
    {
        //
    }

    /**
     * Elimina una configuración
     *
     * @return void
     */
    public function destroy()
    {
        //
    }
}
