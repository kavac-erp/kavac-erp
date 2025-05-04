<?php

namespace App\Http\Controllers\Admin;

use App\Models\City;
use App\Models\User;
use App\Models\Estate;
use App\Models\Parish;
use App\Models\Country;
use App\Models\Parameter;
use App\Roles\Models\Role;
use App\Models\Institution;
use App\Models\Municipality;
use Illuminate\Http\Request;
use App\Models\InstitutionType;
use App\Models\InstitutionSector;
use Illuminate\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use App\Repositories\ParameterRepository;
use App\Notifications\System as AppNotification;

/**
 * @class SettingController
 * @brief Gestiona información de configuración general
 *
 * Controlador para gestionar configuraciones
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SettingController extends Controller
{
    /**
     * Listado de todos los registros de los parámetros de configuración de la aplicación
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return View     Vista para mostrar el listado de configuraciones
     */
    public function index()
    {
        // Objeto con información de los parámetros activos pertenecientes a la aplicación base
        $parameters = Parameter::where([
            'active' => true, 'required_by' => 'core', 'p_value' => 'true',
        ])->get();

        // Parámetro asociado a la gestión de soporte técnico
        $paramSupport = $parameters->filter(function ($param) {
            return $param->p_key === 'support';
        })->first();
        // Parámetro asociado a la gestión de chat
        $paramChat = $parameters->filter(function ($param) {
            return $param->p_key === 'chat';
        })->first();
        // Parámetro asociado a la gestión de notificaciones
        $paramNotify = $parameters->filter(function ($param) {
            return $param->p_key === 'notify';
        })->first();
        // Parámetro asociado a la gestión de banner en reportes
        $paramReportBanner = $parameters->filter(function ($param) {
            return $param->p_key === 'report_banner';
        })->first();
        // Parámetro asociado a la gestión de multiples organismos
        $paramMultiInstitution = $parameters->filter(function ($param) {
            return $param->p_key === 'multi_institution';
        })->first();
        // Parámetro asociado a la gestión de firma electrónica
        $paramDigitalSign = $parameters->filter(function ($param) {
            return $param->p_key === 'digital_sign';
        })->first();
        // Parámetro asociado a la gestión de mantenimiento de la aplicación
        $paramOnline = $parameters->filter(function ($param) {
            return $param->p_key === 'online';
        })->first();

        // Arreglo con atributos del formulario para la configuración de parámetros
        $header_parameters = [
            'route' => 'settings.store', 'method' => 'POST', 'role' => 'form', 'class' => 'form',
        ];
        // Objeto con información del organismo. Por defecto el valor es nulo
        $model_institution = null;
        if (is_null($paramMultiInstitution)) {
            $model_institution = Institution::where([
                'active' => true,
                'default' => true,
            ])->first();
        }
        // Arreglo con atributos del formulario para la configuración de organismos
        $header_institution = [
            'route' => 'institutions.store', 'method' => 'POST', 'role' => 'form', 'class' => 'form',
            'enctype' => 'multipart/form-data',
        ];

        // Arreglo con información de los organismos de adscripción
        $organism_adscripts = (!is_null($model_institution)) ? template_choices(
            Institution::class,
            'name',
            [],
            false,
            $model_institution->id
        ) : ['' => 'Seleccione...'];

        // Objeto con información de los organismos registrados
        $institutions = Institution::all();
        $canDesactivate = ($institutions->count()) > 1 ? true : false;
        // Objeto con información de los Países registrados
        $countries = template_choices(Country::class);
        // Objeto con información de los Estados registrados
        $estates = template_choices(
            ($model_institution) ? Estate::class : collect()
        );
        // Objeto con información de los Municipios registrados
        $municipalities = template_choices(
            ($model_institution) ? Municipality::class : collect()
        );
        // Objeto con información de las Parroquias registradas
        $parishes = template_choices(
            ($model_institution) ? Parish::class : collect()
        );
        // Objeto con información de las Ciudades registradas
        $cities = template_choices(
            ($model_institution) ? City::class : collect()
        );
        // Objeto con información de los sectores de organismos
        $sectors = template_choices(InstitutionSector::class);
        // Objeto con información de los tipos de organismos
        $types = template_choices(InstitutionType::class);
        // Arreglo con los nombres de las redes sociales más comúnes
        $social_networks = [
            'facebook', 'twitter', 'linkedin', 'instagram', 'youtube', 'telegram',
        ];

        return view('admin.settings', compact(
            'header_parameters',
            'paramSupport',
            'paramChat',
            'paramNotify',
            'paramReportBanner',
            'paramMultiInstitution',
            'paramDigitalSign',
            'paramOnline',
            'model_institution',
            'header_institution',
            'institutions',
            'countries',
            'estates',
            'municipalities',
            'parishes',
            'cities',
            'sectors',
            'types',
            'organism_adscripts',
            'social_networks',
            'canDesactivate'
        ));
    }

    /**
     * Muestra el formulacio con información del documento a actualizar
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Institution $id    Objeto con información del documento a actualizar
     */
    public function edit(Institution $id)
    {

        // Objeto con información de los parámetros activos pertenecientes a la aplicación base
        $parameters = Parameter::where([
            'active' => true, 'required_by' => 'core', 'p_value' => 'true',
        ])->get();

        // Parámetro asociado a la gestión de soporte técnico
        $paramSupport = $parameters->filter(function ($param) {
            return $param->p_key === 'support';
        })->first();
        // Parámetro asociado a la gestión de chat
        $paramChat = $parameters->filter(function ($param) {
            return $param->p_key === 'chat';
        })->first();
        // Parámetro asociado a la gestión de notificaciones
        $paramNotify = $parameters->filter(function ($param) {
            return $param->p_key === 'notify';
        })->first();
        // Parámetro asociado a la gestión de banner en reportes
        $paramReportBanner = $parameters->filter(function ($param) {
            return $param->p_key === 'report_banner';
        })->first();
        // Parámetro asociado a la gestión de multiples organismos
        $paramMultiInstitution = $parameters->filter(function ($param) {
            return $param->p_key === 'multi_institution';
        })->first();
        // Parámetro asociado a la gestión de firma electrónica
        $paramDigitalSign = $parameters->filter(function ($param) {
            return $param->p_key === 'digital_sign';
        })->first();
        // Parámetro asociado a la gestión de mantenimiento de la aplicación
        $paramOnline = $parameters->filter(function ($param) {
            return $param->p_key === 'online';
        })->first();

        // Arreglo con atributos del formulario para la configuración de parámetros
        $header_parameters = [
            'route' => 'settings.store', 'method' => 'POST', 'role' => 'form', 'class' => 'form',
        ];
        // Objeto con información del organismo. Por defecto el valor es nulo
        $model_institution = $id;

        // Arreglo con atributos del formulario para la configuración de organismos
        $header_institution = [
            'route' => 'institutions.store', 'method' => 'POST', 'role' => 'form', 'class' => 'form',
            'enctype' => 'multipart/form-data',
        ];

        // Arreglo con información de los organismos de adscripción
        $organism_adscripts = (!is_null($model_institution)) ? template_choices(
            Institution::class,
            'name',
            [],
            false,
            $model_institution->id
        ) : ['' => 'Seleccione...'];

        // Objeto con información de los organismos registrados
        $institutions = Institution::all();
        $canDesactivate = ($institutions->count()) > 1 ? true : false;
        // Objeto con información de los Países registrados
        $countries = template_choices(Country::class);
        // Objeto con información de los Estados registrados
        $estates = template_choices(Estate::class);
        // Objeto con información de los Municipios registrados
        $municipalities = template_choices(Municipality::class);
        // Objeto con información de las Parroquias registradas
        $parishes = template_choices(Parish::class);
        // Objeto con información de las Ciudades registradas
        $cities = template_choices(City::class);
        // Objeto con información de los sectores de organismos
        $sectors = template_choices(InstitutionSector::class);
        // Objeto con información de los tipos de organismos
        $types = template_choices(InstitutionType::class);
        // Arreglo con los nombres de las redes sociales más comúnes
        $social_networks = [
            'facebook', 'twitter', 'linkedin', 'instagram', 'youtube', 'telegram',
        ];

        return view('admin.settings', compact(
            'header_parameters',
            'paramSupport',
            'paramChat',
            'paramNotify',
            'paramReportBanner',
            'paramMultiInstitution',
            'paramDigitalSign',
            'paramOnline',
            'model_institution',
            'header_institution',
            'institutions',
            'countries',
            'estates',
            'municipalities',
            'parishes',
            'cities',
            'sectors',
            'types',
            'organism_adscripts',
            'social_networks',
            'canDesactivate'
        ));
    }

    /**
     * Registra los parámetros de configuración de la aplicación
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  Request  $request    Objeto con información de la petición
     *
     * @return RedirectResponse     Redirecciona al usuario a la página del listado de configuraciones
     */
    public function store(Request $request, ParameterRepository $parameterRepository)
    {
        // Arreglo con los parámetros a registrar
        $parameters = ['support', 'chat', 'notify', 'report_banner', 'multi_institution', 'digital_sign', 'online'];
        // Arreglo con los tipos de mensaje a mostrar
        $msgType = ['type' => 'store'];

        foreach ($parameters as $parameter) {
            $parameterRepository->updateOrCreate(
                ['p_key' => $parameter, 'required_by' => 'core'],
                ['p_value' => (!is_null($request->$parameter)) ? 'true' : 'false']
            );

            if ($parameter === "online") {
                if (is_null($request->$parameter)) {
                    Artisan::call('up');
                    $msgType = [
                        'type' => 'other',
                        'text' => __(
                            'El sistema esta actualmente en línea, ' .
                            'todos los usuarios pueden acceder a la aplicación'
                        ),
                    ];
                    // Título del mensaje
                    $title = config('app.name') . ' - ' . __('En línea');
                    // Descripión del mensaje
                    $description = __(
                        'Se ha reestablecido el acceso a la aplicación. ' .
                        'Puede acceder en cualquier momento'
                    );
                    // Objeto con información de los usuarios del sistema
                    $users = User::all();
                    foreach ($users as $user) {
                        $user->notify(new AppNotification($title, '', $description, true));
                    }
                } else {
                    // Hash generado para acceder a la aplicación después de colocarla en mantenimiento
                    $secretHash = generate_hash(36, false, true);
                    Artisan::call('down', ['--secret' => $secretHash]);
                    $msgType = [
                        'type' => 'other',
                        'text' => __(
                            'El sistema esta actualmente en mantenimiento, ' .
                            'solo puede acceder a la aplicación desde el enlace proporcionado'
                        ),
                    ];
                    // Objeto con información del rol administrador
                    $roleAdmin = Role::where('slug', 'admin')->first();
                    if ($roleAdmin && !$roleAdmin->users->isEmpty()) {
                        // Título del mensaje
                        $title = config('app.name') . ' - ' . __('Configuración en modo mantenimiento');
                        // Descripción del mensaje
                        $description = __(
                            'Se ha configurado la aplicación en modo mantenimiento. ' .
                            'Para poder acceder bajo esta modalidad es necesario que acceda a la siguiente URL: '
                        ) . config('app.url') . '/' . $secretHash;
                        foreach ($roleAdmin->users as $user) {
                            $user->notify(new AppNotification($title, '', $description, true));
                        }
                    }
                }
            }
        }

        $request->session()->flash('message', $msgType);
        return redirect()->route('settings.index');
    }
}
