<?php

namespace Modules\Asset\Http\Controllers;

use App\Models\Profile;
use App\Models\FiscalYear;
use App\Models\CodeSetting;
use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Asset\Models\Asset;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Repositories\ReportRepository;
use Modules\Payroll\Models\PayrollStaff;
use Modules\Asset\Models\AssetAsignation;
use Illuminate\Contracts\Support\Renderable;
use Modules\Asset\Models\AssetAsignationAsset;
use Modules\Asset\Models\AssetAsignationDelivery;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Asset\Http\Resources\AssetAsignationResource;

/**
 * @class      AssetAsignationController
 * @brief      Controlador de asignaciones de bienes institucionales
 *
 * Clase que gestiona las asignaciones de bienes institucionales
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetAsignationController extends Controller
{
    use ValidatesRequests;

    /**
     * Arreglo con las reglas de validación sobre los datos de un formulario
     *
     * @var array $validateRules
     */
    protected $validateRules;

    /**
     * Arreglo con los mensajes para las reglas de validación
     *
     * @var array $messages
     */
    protected $messages;

    /**
     * Define la configuración de la clase
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     * @author    Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @return    void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:asset.deliver', ['only' => 'deliver']);
        $this->middleware('permission:asset.download', ['only' => 'managePdf']);
        $this->middleware('permission:asset.asignations.view', ['only' => 'index']);
        $this->middleware('permission:asset.asignations.create', ['only' => 'create']);

        $this->validateRules = [
            'institution_id' => ['required'],
            'location_place' => ['nullable'],
            'authorized_by_id' => ['required'],
            'formed_by_id' => ['required'],
            'delivered_by_id' => ['required'],
            'building_id' => ['required'],
            'floor_id' => ['required'],
            'section_id' => ['required'],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'institution_id.required' => 'El campo institución es obligatorio.',
            'location_place.required' => 'El campo lugar de ubicación es obligatorio.',
            'authorized_by_id.required' => 'El campo autorizado por es obligatorio.',
            'formed_by_id.required' => 'El campo conformado por es obligatorio.',
            'delivered_by_id.required' => 'El campo entregado por es obligatorio.',
            'building_id.required' => 'El campo edificación es obligatorio.',
            'floor_id.required' => 'El campo nivel es obligatorio.',
            'section_id.required' => 'El campo sección es obligatorio.',
        ];
    }

    /**
     * Muestra el listado de las asignaciones de bienes institucionales
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    Renderable
     */
    public function index()
    {
        return view('asset::asignations.list');
    }

    /**
     * Muestra el formulario para registrar una nueva asignación de bienes institucionales
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    Renderable
     */
    public function create()
    {
        $is_admin = false;
        if (isset(auth()->user()->profile) && isset(auth()->user()->profile->institution_id)) {
            $institution_id = Institution::where(['id' => auth()->user()->profile->institution_id])
                ->first()->value('id');
        } else {
            $institution_id = Institution::where(['active' => true, 'default' => true])
                ->first()->value('id');
            $is_admin = true;
        }
        $asignationList = url('asset/registers') . '/vue-list/asignations';
        return view('asset::asignations.create', compact('is_admin', 'institution_id', 'asignationList'));
    }

    /**
     * Muestra el formulario para registrar una nueva asignación de un bien nstitucional
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     integer      $id    Identificador único del bien a asignar
     *
     * @return    Renderable
     */
    public function assetAssign($id)
    {
        $is_admin = false;
        $asset = Asset::find($id);
        if (isset(auth()->user()->profile) && isset(auth()->user()->profile->institution_id)) {
            $institution_id = Institution::where(['id' => auth()->user()->profile->institution_id])
                ->first()->value('id');
        } else {
            $institution_id = Institution::where(['active' => true, 'default' => true])
                ->first()->value('id');
            $is_admin = true;
        }
        return view('asset::asignations.create', compact('asset', 'institution_id', 'is_admin'));
    }

    /**
     * Valida y registra una nueva asignación de bienes institucionales
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     Request         $request    Datos de la petición
     *
     * @return    JsonResponse    Objeto con los registros a mostrar
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules, $this->messages);

        $codeSetting = CodeSetting::where('table', 'asset_asignations')->first();
        if (is_null($codeSetting)) {
            $request->session()->flash('message', [
                'type' => 'other', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'growl-danger',
                'text' => 'Debe configurar previamente el formato para el código a generar',
            ]);
            return response()->json(['result' => false, 'redirect' => route('asset.setting.index')], 200);
        }

        $found_asignation = AssetAsignation::where('building_id', $request->input('building_id'))
            ->where('floor_id', $request->input('floor_id'))
            ->where('section_id', $request->input('section_id'))
            // ->where('location_place', $request->input('location_place'))
            ->where('payroll_staff_id', $request->input('payroll_staff_id'))
            ->first();

        if ($found_asignation) {
            $this->validate($request, [
                'section_id' => [
                    'unique:asset_asignations,section_id,NULL,id,building_id,' .
                    $request->building_id . ',floor_id,' .
                    $request->floor_id . ',payroll_staff_id,' .
                    $request->payroll_staff_id,
                ],
            ], [
                'section_id.unique' => 'Esta ubicación ya está asignada.',
            ]);
        }

        $currentFiscalYear = FiscalYear::select('year')
            ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();

        $code = generate_registration_code(
            $codeSetting->format_prefix,
            strlen($codeSetting->format_digits),
            (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
                substr($currentFiscalYear->year, 2, 2) : date('y')) : (isset($currentFiscalYear) ?
                $currentFiscalYear->year : date('Y')),
            AssetAsignation::class,
            $codeSetting->field
        );

        /* Objeto asociado al modelo AssetAsignation */
        $asignation = AssetAsignation::create([
            'code' => $code,
            'department_id' => $request->input('department_id'),
            'institution_id' => $request->input('institution_id'),
            'payroll_staff_id' => $request->input('payroll_staff_id'),
            'location_place' => $request->input('location_place'),
            'state' => 'Asignado',
            'ids_assets' => null,
            'user_id' => Auth::id(),
            'authorized_by_id' => $request->input('authorized_by_id'),
            'formed_by_id' => $request->input('formed_by_id'),
            'delivered_by_id' => $request->input('delivered_by_id'),
            'building_id' => $request->input('building_id'),
            'floor_id' => $request->input('floor_id'),
            'section_id' => $request->input('section_id'),
        ]);

        foreach ($request->assets as $product) {
            $asset = Asset::find($product);
            $asset->asset_status_id = 1;
            $asset->save();

            AssetAsignationAsset::create([
                'asset_id' => $asset->id,
                'asset_asignation_id' => $asignation->id,
            ]);
        }
        $request->session()->flash('message', ['type' => 'store']);
        return response()->json(['result' => true, 'redirect' => route('asset.asignation.index')], 200);
    }

    /**
     * Muestra el formulario para actualizar la información de las asignaciones de bienes institucionales
     *
     * @author     Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param      AssetAsignation    $asignation    Datos de la asignación de un bien
     *
     * @return     Renderable
     */
    public function edit($id)
    {
        $is_admin = false;
        $asignation = AssetAsignation::find($id);
        $institution_id = $asignation->institution_id;
        return view('asset::asignations.create', compact('is_admin', 'asignation', 'institution_id'));
    }

    /**
     * Actualiza la información de las asignaciones de bienes institucionales
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     Request      $request    Datos de la petición
     * @param     integer      $id         Identificador único de la asignación
     *
     * @return    JsonResponse JSON con los registros a mostrar
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, $this->validateRules, $this->messages);

        $asignation = AssetAsignation::where('id', $id)->with('assetAsignationAssets')->first();
        $asignation->payroll_staff_id = $request->payroll_staff_id;
        $asignation->location_place = $request->location_place;
        $asignation->ids_assets = null;
        $asignation->authorized_by_id = $request->authorized_by_id;
        $asignation->formed_by_id = $request->formed_by_id;
        $asignation->delivered_by_id = $request->delivered_by_id;
        $asignation->building_id = $request->building_id;
        $asignation->floor_id = $request->floor_id;
        $asignation->section_id = $request->section_id;
        $asignation->save();

        /* Se eliminan los demas elementos de la solicitud */
        $assets_asignation = AssetAsignationAsset::where('asset_asignation_id', $asignation->id)->get();

        foreach ($assets_asignation as $asset_asignation) {
            $asset = Asset::find($asset_asignation->asset_id);
            $asset->asset_status_id = 10;
            $asset->save();

            $asset_asignation->delete();
        }
        /* Se agregan los nuevos elementos a la solicitud */
        foreach ($request->assets as $asset_id) {
            $asset = Asset::find($asset_id);
            $asset->asset_status_id = 1;
            $asset->save();

            AssetAsignationAsset::updateOrCreate([
                'asset_id' => $asset->id,
                'asset_asignation_id' => $asignation->id,
            ]);
        }

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('asset.asignation.index')], 200);
    }

    /**
     * Elimina una asignación de un bien institucional
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     AssetAsignation    $asignation    Datos de la asignación de un bien
     *
     * @return    JsonResponse       Objeto con los registros a mostrar
     */
    public function destroy(AssetAsignation $asignation)
    {
        $assets_asignation_assets = AssetAsignationAsset::where('asset_asignation_id', $asignation->id)->get();
        $assets_asignation_delivery = AssetAsignationDelivery::where('asset_asignation_id', $asignation->id);
        foreach ($assets_asignation_assets as $assets_asignation) {
            $asset = Asset::find($assets_asignation->asset_id);
            $asset->asset_status_id = 10;
            $asset->save();

            $assets_asignation->delete();
        }

        $assets_asignation_delivery->delete();
        $asignation->delete();
        return response()->json(['message' => 'destroy', 'redirect' => route('asset.asignation.index')], 200);
    }

    /**
     * Obtiene la información de la asignación de un bien institucional
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     integer    $id    Identificador de la asignación de bienes
     *
     * @return    JsonResponse       Objeto con los registros a mostrar
     */
    public function vueInfo($id)
    {
        $ids = explode(',', $id);
        if (count($ids) > 1) {
            $asignation = AssetAsignation::whereIn('id', $ids)
                ->with([
                    'payrollStaff', 'institution' => function ($query) {
                        $query->with([
                            'fiscalYears' => function ($query) {
                                $query->where(['active' => true])->first();
                            },
                            'municipality' => function ($query) {
                                $query->with('estate');
                            },
                        ]);
                    }, 'assetAsignationAssets' =>
                    function ($query) {
                        $query->with(
                            ['asset' => function ($query) {
                                $query->with(
                                    'institution',
                                    'assetType',
                                    'assetCategory',
                                    'assetSubcategory',
                                    'assetSpecificCategory',
                                    'assetAcquisitionType',
                                    'assetCondition',
                                    'assetStatus',
                                    'assetUseFunction'
                                );
                            }]
                        );
                    },
                ])->get();
        } else {
            $asignation = AssetAsignation::where('id', $id)
                ->with([
                    'payrollStaff',
                    'building',
                    'floor',
                    'section',
                    'institution' => function ($query) {
                        $query->with([
                            'fiscalYears' => function ($query) {
                                $query->where(['active' => true])->first();
                            },
                            'municipality' => function ($query) {
                                $query->with('estate');
                            },
                        ]);
                    }, 'assetAsignationAssets' =>
                    function ($query) {
                        $query->with(
                            ['asset' => function ($query) {
                                $query->with(
                                    'institution',
                                    'assetType',
                                    'assetCategory',
                                    'assetSubcategory',
                                    'assetSpecificCategory',
                                    'assetAcquisitionType',
                                    'assetCondition',
                                    'assetStatus',
                                    'assetUseFunction'
                                );
                            }]
                        );
                    },
                ])->first();
        }

        return response()->json(['records' => $asignation], 200);
    }

    /**
     * Carga información de la asignación de bienes
     *
     * @param string $ids Identificadores de los bienes a cargar
     *
     * @return JsonResponse
     */
    public function loadInfo($ids)
    {
        $ids = explode(',', $ids);
        $asignation = Asset::query()
            ->with([
                'institution',
                'assetType',
                'assetCategory',
                'assetSubcategory',
                'assetSpecificCategory',
                'assetAcquisitionType',
                'assetCondition',
                'assetStatus',
                'assetUseFunction',
                'assetAsignationAsset.assetAsignation.payrollStaff',
                'assetAsignationAsset.assetAsignation.institution' => function ($query) {
                    $query->with([
                        'fiscalYears' => function ($query) {
                            $query->where(['active' => true])->first();
                        },
                        'municipality' => function ($query) {
                            $query->with('estate');
                        },
                    ]);
                }
            ])
            ->whereHas('assetAsignationAsset', function ($query) use ($ids) {
                $query->whereIn('asset_asignation_id', $ids);
            });
        $asignation = AssetAsignationResource::collection($asignation->get());

        return response()->json(['records' => $asignation], 200);
    }

    /**
     * Actualiza el estado de una solicitud de entrega
     *
     * @author    Francisco J. P. Ruiz <javierrupe19@gmail.com>
     *
     * @param     Request         $request    Datos de la petición
     * @param     integer         $id         Identificador único de la asignación
     *
     * @return    JsonResponse    Objeto con los registros a mostrar
     */
    public function deliver(Request $request, $id)
    {
        $asset_asignation = AssetAsignation::find($id);
        $asset_asignation->state = 'Procesando entrega';

        if (isset($asset_asignation->ids_assets)) {
            $asset_asignation->ids_assets = json_decode($asset_asignation->ids_assets);
            $asset_asignation->ids_assets->assigned = $request->equipments['assigned'];
            $asset_asignation->ids_assets->possible_deliveries = $request->equipments['possible_deliveries'];

            $asset_asignation->ids_assets = json_encode($asset_asignation->ids_assets);
        } else {
            $asset_asignation->ids_assets = json_encode($request->equipments);
        }
        $asset_asignation->save();

        AssetAsignationDelivery::create([
            'state' => 'Pendiente',
            'asset_asignation_id' => $asset_asignation->id,
            'user_id' => Auth::id(),
        ]);

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('asset.asignation.index')], 200);
    }
    /**
     * Otiene un listado de las asignaciones registradas
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param   Request $request Datos de la petición
     *
     * @return  JsonResponse    Objeto con los registros a mostrar
     */
    public function vueList(Request $request)
    {
        $user_profile = Profile::where('user_id', auth()->user()->id)->first();
        $institution_id = isset($user_profile->institution_id)
            ? $user_profile->institution_id
            : null;

        if (auth()->user()->isAdmin()) {
            $assetAsignations = AssetAsignation::query()
                ->search($request->query('query'))
                ->with([
                    'payrollStaff',
                    'assetAsignationAssets',
                    'section',
                    'institution' => function ($query) {
                        $query->with([
                            'fiscalYears' => function ($query) {
                                $query->where(['active' => true])->first();
                            },
                            'municipality' => function ($query) {
                                $query->with('estate');
                            },
                        ]);
                    },
                ])
                ->orderBy('id');

            $assetAsignations = $assetAsignations->paginate((int) request()->limit);
            $assetAsignationItems = array_map(function ($record) {
                $assetAsignationIds = [];
                foreach ($record->assetAsignationAssets as $asset) {
                    array_push($assetAsignationIds, $asset->id);
                }
                return [
                    'id' => $record->id,
                    'code' => $record->code,
                    'created_at' => $record->created_at,
                    'delivered_by_id' => $record->delivered_by_id,
                    'formed_by_id' => $record->formed_by_id,
                    'ids_assets' => $record->ids_assets,
                    'asset_assignation_ids' => $assetAsignationIds,
                    'institution' => $record->institution,
                    'institution_id' => $record->institution_id,
                    'location_place' => $record->section,
                    'payroll_staff' => $record->payrollStaff,
                    'payroll_staff_id' => $record->payroll_staff_id,
                    'state' => $record->state,
                    'user_id' => $record->user_id,
                ];
            }, $assetAsignations->items());

            return response()->json(
                [
                    'data' => $assetAsignationItems,
                    'count' => $assetAsignations->total()
                ],
                200
            );
        } else {
            $assetAsignations = AssetAsignation::query()
                ->search($request->query('query'))
                ->where('institution_id', $institution_id)
                ->with(['payrollStaff','section', 'assetAsignationAssets', 'institution' => function ($query) {
                    $query->with([
                        'fiscalYears', 'municipality' => function ($query) {
                            $query->with('estate');
                        },
                    ]);
                }])->orderBy('id');

            $assetAsignations = $assetAsignations->paginate((int) request()->limit);
            $assetAsignationItems = array_map(function ($record) {
                $assetAsignationIds = [];
                foreach ($record->assetAsignationAssets as $asset) {
                    array_push($assetAsignationIds, $asset->id);
                }
                return [
                    'id' => $record->id,
                    'code' => $record->code,
                    'created_at' => $record->created_at,
                    'delivered_by_id' => $record->delivered_by_id,
                    'formed_by_id' => $record->formed_by_id,
                    'ids_assets' => $record->ids_assets,
                    'asset_assignation_ids' => $assetAsignationIds,
                    'institution' => $record->institution,
                    'institution_id' => $record->institution_id,
                    'location_place' => $record->section,
                    'payroll_staff' => $record->payrollStaff,
                    'payroll_staff_id' => $record->payroll_staff_id,
                    'state' => $record->state,
                    'user_id' => $record->user_id,
                ];
            }, $assetAsignations->items());

            return response()->json(
                [
                    'data' => $assetAsignations->items(),
                    'count' => $assetAsignations->total()
                ],
                200
            );
        }
    }

    /**
     * Método que genera el archivo del acta en formato pdf
     *
     * @author    Francisco J. P. Ruiz <javierrupe19@gmail.com>
     *
     * @param     integer         $id         Identificador único de la asignación
     *
     * @return    void
     */
    public function managePdf($id)
    {
        $asset_asignation = AssetAsignation::where('id', $id)
            ->with([
                'payrollStaff', 'institution' => function ($query) {
                    $query->with([
                        'fiscalYears' => function ($query) {
                            $query->where(['active' => true])->first();
                        },
                        'municipality' => function ($query) {
                            $query->with('estate');
                        },
                    ]);
                }, 'assetAsignationAssets' =>
                function ($query) {
                    $query->with(
                        ['asset' => function ($query) {
                            $query->with(
                                'institution',
                                'assetType',
                                'assetCategory',
                                'assetSubcategory',
                                'assetSpecificCategory',
                                'assetAcquisitionType',
                                'assetCondition',
                                'assetStatus',
                                'assetUseFunction'
                            );
                        }]
                    );
                },
            ])->first()->toArray();

        $data = [];
        $data['action'] = 'Asignación';
        $data['institution'] = (
            $asset_asignation['institution_id']
        ) ? $asset_asignation['institution']['name'] : 'N/A';
        $data['estate'] = (
            $asset_asignation['institution_id']
        ) ? $asset_asignation['institution']['municipality']['estate']['name'] : 'N/A';
        $data['municipality'] = (
            $asset_asignation['institution_id']
        ) ? $asset_asignation['institution']['municipality']['name'] : 'N/A';
        $data['address'] = (
            $asset_asignation['institution_id']
        ) ? strip_tags($asset_asignation['institution']['legal_address']) : 'N/A';
        $data['fiscal_year'] = (
            $asset_asignation['institution_id']
        ) ? $asset_asignation['institution']['fiscal_years'][0]['year'] : 'N/A';

        $date = date_create($asset_asignation['created_at']);

        $data['created_at'] = ($date) ? date_format($date, "d/m/Y") : 'N/A';
        $data['last_name'] = (
            $asset_asignation['payroll_staff']
        ) ? $asset_asignation['payroll_staff']['last_name'] : 'N/A';
        $data['first_name'] = (
            $asset_asignation['payroll_staff']
        ) ? $asset_asignation['payroll_staff']['first_name'] : 'N/A';
        $data['id_number'] = (
            $asset_asignation['payroll_staff_id']
        ) ? $asset_asignation['payroll_staff']['id_number'] : 'N/A';
        $data['department'] = (
            $asset_asignation['payroll_staff_id']
        ) ? $asset_asignation['payroll_staff']['payroll_employment']['department']['name'] : 'N/A';
        $data['payroll_position'] = (
            $asset_asignation['payroll_staff_id']
        ) ? $asset_asignation['payroll_staff']['payroll_employment']['payrollPosition']['name'] : 'N/A';
        $data['location_place'] = (
            $asset_asignation['location_place']
        ) ? $asset_asignation['location_place'] : 'N/A';
        $data['code'] = $asset_asignation['code'];
        $data['assets'] = $asset_asignation['asset_asignation_assets'];

        $authorized_by = PayrollStaff::where('id', $asset_asignation['authorized_by_id'])->first()->toArray();
        $formed_by = PayrollStaff::where('id', $asset_asignation['formed_by_id'])->first()->toArray();
        $delivered_by = PayrollStaff::where('id', $asset_asignation['delivered_by_id'])->first()->toArray();

        $data['authorized_by'] = $authorized_by['first_name'] . ' ' .
                                 $authorized_by['last_name'] . ' - ' .
                                 $authorized_by['payroll_employment']['payrollPosition']['name'];
        $data['formed_by'] = $formed_by['first_name'] . ' ' .
                             $formed_by['last_name'] . ' - ' .
                             $formed_by['payroll_employment']['payrollPosition']['name'];
        $data['delivered_by'] = $delivered_by['first_name'] . ' ' .
                                $delivered_by['last_name'] . ' - ' .
                                $delivered_by['payroll_employment']['payrollPosition']['name'];

        $user_profile = $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();
        $is_admin = $user_profile == null || $user_profile['institution_id'] == null ? true : false;

        /* base para generar el pdf */
        $pdf = new ReportRepository();

        /* Definicion de las caracteristicas generales de la página pdf */
        $institution = null;

        /* Definicion de las caracteristicas generales de la página pdf */
        if ($is_admin) {
            $institution = Institution::find($asset_asignation['institution_id']);
        } else {
            $institution = Institution::find($user_profile['institution_id']);
        }

        /* Definición del Nombre y ruta del acata en pdf */
        $filename = 'acta-de-asignacion-de-bienes-' . $data['code'] . '.pdf';

        $pdf->setConfig([
            'institution' => $institution, 'filename' => $filename,
            'urlVerify' => url('/asset/asignations/asignations-record-pdf/' . $id),
        ]);
        $pdf->setHeader(
            'Bienes Asignados',
            'Código: ' . $data['code'],
            false,
            false,
            'L',
            'C',
            'C'
        );
        $pdf->setFooter(true, $institution->name . ' - ' . strip_tags($institution->legal_address));
        $pdf->setBody('asset::pdf.asset_acta', true, [
            'pdf' => $pdf,
            'request' => $data,
        ]);
    }

    /**
     * Método que permite descargar el archivo del acta en pdf
     *
     * @author    Francisco J. P. Ruiz <javierrupe19@gmail.com>
     *
     * @param     string  $code   Código único del registro
     *
     * @return JsonResponse
     */
    public function download($code)
    {
        return response()->download(storage_path('reports/' . 'acta-de-asignacion-de-bienes-' . $code . '.pdf'));
    }
}
