<?php

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use App\Models\Parameter;
use App\Models\Institution;
use Illuminate\Http\Request;
use App\Rules\Rif as RifRule;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

/**
 * @class InstitutionController
 * @brief Gestiona información de Organizaciones
 *
 * Controlador para gestionar Organizaciones
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class InstitutionController extends Controller
{
    /**
     * Lista de elementos a mostrar
     *
     * @var array $data
     */
    protected $data = [];

    /**
     * Método constructor de la clase
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     */
    public function __construct()
    {
        $this->data[0] = [
            'id' => '',
            'text' => __('Seleccione...')
        ];
    }

    /**
     * Listado de todos los registros de organismos
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return JsonResponse|void     JSON con el listado de organismos
     */
    public function index(Request $request)
    {
        if (!$request->ajax()) {
            return abort(403);
        }
        return response()->json(['records' => Institution::all()], 200);
    }

    /**
     * Registra un nuevo organismo
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  Request  $request    Objeto con información de la petición
     *
     * @return RedirectResponse     Redirecciona al usuario a la página de listado de organismos
     */
    public function store(Request $request)
    {
        $validations = [
            'rif' => [
                'required',
                'regex:/^[E, G, J, P, V, 0-9 ]+$/',
                'size:10',
                new RifRule(),
                ($request->institution_id !== null)
                ? Rule::unique('institutions', 'rif')->ignore($request->institution_id)
                : Rule::unique('institutions', 'rif')
            ],
            'acronym' => ['required', 'max:100'],
            'name' => ['required', 'max:100'],
            'business_name' => ['required', 'max:100'],
            'start_operations_date' => ['required', 'date'],
            'legal_address' => ['required'],
            'postal_code' => ['required', 'max:10'],
            'institution_sector_id' => ['required'],
            'institution_type_id' => ['required'],
            'municipality_id' => ['required'],
            'city_id' => ['required'],
            'logo_id' => ['required']
        ];

        $errorMessages = [
            'rif.required' => __('El R.I.F. es obligatorio.'),
            'rif.regex' => __(
                'El formato del R.I.F es inválido. Debe estar formado por 10 caracteres, ' .
                'el primer carácter debe ser una letra: J,V,E,G o P (en mayúscula); ' .
                'los otros nueve caracteres deben ser números. Ej. V000000000.'
            ),
            'rif.size' => __('El R.I.F. debe contener 10 caracteres.'),
            'rif.unique' => __('El R.I.F. ya está registrado'),
            'acronym.required' => __('El acrónimo es obligatorio.'),
            'acronym.max' => __('El acrónimo debe contener un máximo de 100 caracteres.'),
            'name.required' => __('El nombre es obligatorio.'),
            'name.max' => __('El nombre debe contener un máximo de 100 caracteres.'),
            'business_name.required' => __('La razón social es obligatoria.'),
            'business_name.max' => __('La razón social debe contener un máximo de 100 caracteres.'),
            'start_operations_date.required' => __('La fecha de inicio de operaciones es obligatoria.'),
            'start_operations_date.date' => __('La fecha de inicio de operaciones no tiene un formato válido.'),
            'legal_address.required' => __('La dirección fiscal es obligatoria.'),
            'postal_code.required' => __('El código postal es obligatorio.'),
            'postal_code.max' => __('El código postal debe contener un máximo de 10 caracteres.'),
            'institution_sector_id.required' => __('El sector del organismo es obligatorio.'),
            'institution_type_id.required' => __('El tipo de organismo es obligatorio.'),
            'municipality_id.required' => __('Seleccione un municipio.'),
            'city_id.required' => __('Seleccione una ciudad.'),
            'logo_id.required' => __('El logo es obligatorio.'),
        ];

        $bannerParameter = Parameter::where('p_key', "report_banner")->first();

        if ($bannerParameter && $bannerParameter->p_value == 'true') {
            $validations['banner_id'] = ['required'];
            $errorMessages['banner_id.required'] = __('El banner o cintillo es obligatorio.');
        }

        $this->validate($request, $validations, $errorMessages);

        /*
         * TODO: Validación para múltiples organizaciones para cuando se establece en verdadero en la configuración de
         * la aplicación
         */

        // Identificador del logo del organismo a registrar
        $logo = (!empty($request->logo_id)) ? $request->logo_id : null;
        // Identificador del banner del organismo a registrar
        $banner = (!empty($request->banner_id)) ? $request->banner_id : null;

        // Objeto con información de la configuración de la aplicación
        $setting = Setting::where('active', true)->first();
        $Parameter = Parameter::where('p_key', "multi_institution")->first();

        // Arreglo con los datos del organismo a registrar
        $data = [
            'onapre_code' => ($request->onapre_code) ? $request->onapre_code : null,
            'rif' => $request->rif,
            'acronym' => $request->acronym,
            'name' => $request->name,
            'business_name' => $request->business_name,
            'start_operations_date' => $request->start_operations_date,
            'legal_address' => $request->legal_address,
            'postal_code' => $request->postal_code,
            'institution_sector_id' => $request->institution_sector_id,
            'institution_type_id' => $request->institution_type_id,
            'municipality_id' => $request->municipality_id,
            'city_id' => $request->city_id,
            'default' => ($request->default !== null),
            'active' => (($request->active === null && Institution::where('active', true)->get()->count() <= 1) || $request->active),
            'legal_base' => ($request->legal_base) ? $request->legal_base : null,
            'legal_form' => ($request->legal_form) ? $request->legal_form : null,
            'main_activity' => ($request->main_activity) ? $request->main_activity : null,
            'mission' => ($request->mission) ? $request->mission : null,
            'vision' => ($request->vision) ? $request->vision : null,
            'web' => ($request->web) ? $request->web : null,
            'composition_assets' => ($request->composition_assets) ? $request->composition_assets : null,
            'retention_agent' => ($request->retention_agent !== null),
            'logo_id' => $logo,
            'banner_id' => $banner,
        ];
        $multi_institution = false;

        if (is_null($Parameter)) {
            $multi_institution = false;
        } else {
            $multi_institution = $Parameter->p_value;
        }

        if (is_null($setting->multi_institution) || !$setting->multi_institution  and !$multi_institution) {
            /*
             * Crea o actualiza información de una organización si la aplicación esta configurada
             * para el uso de un solo organismo
             */
            $data['default'] = true;
            Institution::updateOrCreate(['rif' => $request->rif], $data);
        } else {
            if ($request->default !== null) {
                      $Institutions = Institution::first();

                if ($Institutions) {
                    Institution::where('default', true)
                      ->update(['default' => false]);
                }
            }

            if (!empty($request->institution_id)) {
                // Si existe el identificador de la organización, se actualizan sus datos

                // Objeto con información de la organización
                $inst = Institution::find($request->institution_id);
                $inst->fill($data);
                $inst->save();
            } else {
                // Si no existe un identificador de organización, se crea una nueva
                Institution::create($data);
            }
        }

        session()->flash('message', ['type' => 'store']);
        return redirect()->route('settings.index');
    }

    /**
     * Obtiene un listado de organismos con id y nombre
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return JsonResponse     JSON con información de los organismos registrados
     */
    public function getInstitutions()
    {
        foreach (Institution::all() as $institution) {
            $this->data[] = [
                'id' => $institution->id,
                'text' => $institution->name
            ];
        }

        return response()->json($this->data);
    }

    /**
     * Obtiene los datos de un organismo
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  Institution $institution Objeto con información asociada a un organismo
     *
     * @return JsonResponse     JSON con información del organismo
     */
    public function getDetails(Institution $institution)
    {
        // Objeto con información del organismo del cual se requieren detalles
        $inst = Institution::where('id', $institution->id)->with(['municipality' => function ($q) {
            return $q->with(['estate' => function ($qq) {
                return $qq->with('country');
            }]);
        }, 'banner', 'logo'])->first();

        return response()->json(['result' => true, 'institution' => $inst], 200);
    }
}
