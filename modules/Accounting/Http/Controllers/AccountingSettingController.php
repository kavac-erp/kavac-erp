<?php

namespace Modules\Accounting\Http\Controllers;

use App\Models\Profile;
use App\Models\Receiver;
use App\Models\Parameter;
use App\Models\CodeSetting;
use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Support\Renderable;
use App\Rules\CodeSetting as CodeSettingRule;
use Modules\Accounting\Models\AccountingEntry;
use Modules\Accounting\Models\AccountingAccount;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * @class AccountingSettingController
 * @brief Controlador de las configuracion de codigo del modulo
 *
 * Clase que gestiona las configuracion de codigo del modulo
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AccountingSettingController extends Controller
{
    use ValidatesRequests;

    /**
     * Define la configuración de la clase
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:accounting.setting.index', ['only' => 'index']);
    }

    /**
     * Muestra la vista la configuración del modulo
     *
     * @return Renderable
     */
    public function index()
    {
        $institution  = get_institution();
        $codeSettings = CodeSetting::where('module', 'accounting')->get();
        $refCode      = $codeSettings->where('table', 'accounting_entries')
                                ->first();
        $accounting_accounts = [];
        $accounting_accounts[''] = "Seleccione...";
        /* se realiza la busqueda de manera ordenada en base al código */
        foreach (
            AccountingAccount::with('parent')->orderBy('group', 'ASC')
                                    ->orderBy('subgroup', 'ASC')
                                    ->orderBy('item', 'ASC')
                                    ->orderBy('generic', 'ASC')
                                    ->orderBy('specific', 'ASC')
                                    ->orderBy('subspecific', 'ASC')
                                    ->orderBy('institutional', 'ASC')
                                    ->get() as $record
        ) {
            $accounting_accounts[$record->id] = "{$record->getCodeAttribute()} - {$record->denomination}";
        }
        $parameter = Parameter::where([
            'active' => true, 'required_by' => 'accounting', 'p_key' => 'institution_account'
        ])->first();
        return view('accounting::settings.index', compact('refCode', 'parameter', 'accounting_accounts'));
    }

    /**
     * Código a registrar
     *
     * @return Response
     */
    public function codeStore(Request $request)
    {
        /* Reglas de validación para la configuración de códigos */
        $this->validate($request, [
            'entries_reference' => [new CodeSettingRule()]
        ]);

        $institution = get_institution();

        /* información de los campos de códigos configurados */
        $codes = $request->input();

        /* Define el estatus verdadero para indicar que no se ha registrado información */
        $saved = false;

        foreach ($codes as $key => $value) {
            /* Define el modelo al cual hace referencia el código */
            $model = '';

            if ($key !== '_token' && !is_null($value)) {
                list($table, $field) = explode("_", $key);
                list($prefix, $digits, $sufix) = CodeSetting::divideCode($value);

                /* define el modelo asociado a asientos contables */
                $model = AccountingEntry::class;

                $codeSetting = CodeSetting::where([
                    'module' => 'accounting',
                    'table'  => 'accounting_' . $table,
                    'field'  => $field,
                ])->first();

                if (!isset($codeSetting)) {
                    $codeSetting = CodeSetting::create([
                        'module'        => 'accounting',
                        'table'         => 'accounting_' . $table,
                        'field'         => $field,
                        'format_prefix' => strtoupper($prefix),
                        'format_digits' => $digits,
                        'format_year'   => strtoupper($sufix),
                        'model'         => $model,
                    ]);
                }

                /* Define el estatus verdadero para indicar que se ha registrado información */
                $saved = true;
            }
        }

        if ($saved) {
            $request->session()->flash('message', ['type' => 'store']);
        }

        return redirect()->back();
    }

    /**
     * Genera código de referencia
     *
     * @param Request $request Datos de la petición
     *
     * @return JsonResponse
     */
    public function generateReferenceCode(Request $request)
    {
        $institution = get_institution();
        $codeSetting = CodeSetting::where('table', 'accounting_entries')
        ->first();
        if (is_null($codeSetting)) {
            $code = AccountingEntry::count();
            $request->session()->flash('message', [
                'type'  => 'other',
                'title' => 'Alerta',
                'icon'  => 'screen-error',
                'class' => 'growl-danger',
                'text'  => 'Se debe configurar previamente el formato para el código de referencia del asiento.
                De lo contrario el sistema les asignara números de forma progresiva'
            ]);
        } else {
            $code  = generate_registration_code(
                $codeSetting->format_prefix,
                strlen($codeSetting->format_digits),
                (strlen($codeSetting->format_year) == 2) ? date('y') : date('Y'),
                AccountingEntry::class,
                $codeSetting->field
            );
        }

        return response()->json(['code' => $code], 200);
    }

    /**
     * Actualiza parámetros de la institución
     *
     * @param Request $request Datos de la petición
     *
     * @return Response
     */
    public function updateInstitutionParameters(Request $request)
    {
        //Gestiona el formulario de Configuración de la Edad Laboral Permitida
        if ($request->p_key == 'institution_account') {
            $this->validate(
                $request,
                [
                    'p_value' => ['required', 'integer']
                ],
                [
                    'p_value.required' => 'Es obligatorio seleccionar una cuenta contable para la institución.'
                ],
                [
                    'p_value' => 'cuenta contable de la institución'
                ]
            );
            $is_admin = auth()->user()->level == 1 ? true : false;
            if ($is_admin) {
                $institution = Institution::where('default', true)->first();
            } else {
                $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();

                $institution = $user_profile['institution'];
            }

            Receiver::updateOrCreate(
                [
                    'receiverable_type' => Institution::class,
                    'receiverable_id' => $institution->id,
                    'associateable_type' => AccountingAccount::class,
                    'associateable_id' => $request->p_value
                ],
                [
                    'group' => 'Institución',
                    'description' => $institution->name
                ]
            );

            Parameter::updateOrCreate(
                [
                    'p_key' => $request->p_key,
                    'required_by' => 'accounting',
                    'active' => true
                ],
                [
                    'p_value' => $request->p_value
                ]
            );
        }
        $request->session()->flash('message', ['type' => 'store']);
        return redirect()->back();
    }
}
