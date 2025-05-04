<?php

namespace Modules\Asset\Http\Controllers;

use App\Models\Profile;
use App\Models\Institution;
use Illuminate\Http\Request;
use Modules\Asset\Models\Asset;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Asset\Models\AssetBook;
use Modules\Asset\Models\AssetType;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Asset\Exports\AssetExport;
use Modules\Asset\Models\AssetRequest;
use Illuminate\Support\Facades\Storage;
use Modules\Asset\Models\AssetCategory;
use Modules\Asset\Rules\AcquisitionYear;
use Modules\Asset\Models\AssetAsignation;
use Modules\Asset\Rules\ContractStartDate;
use Illuminate\Contracts\Support\Renderable;
use Modules\Asset\Models\AssetAsignationAsset;
use Modules\Asset\Http\Resources\AssetResource;
use Modules\Asset\Models\AssetDisincorporation;
use Modules\Asset\Imports\AssetImportMultiSheet;
use Modules\Asset\Models\AssetDisincorporationAsset;
use Modules\Asset\Http\Resources\AssetReportResource;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Asset\Http\Resources\AssetAsignationResource;
use Modules\Asset\Repositories\AssetParametersRepository;

/**
 * @class      AssetController
 * @brief      Controlador de bienes institucionales
 *
 * Clase que gestiona los bienes institucionales
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetController extends Controller
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
     * Arreglo con los atributos para las reglas de validación
     *
     * @var array $attributes
     */
    protected $attributes;

    /**
     * Define la configuración de la clase
     *
     * @author    Yennifer Ramirez <yramirez@cenditel.gob.ve>
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->validateRules = [
            '*.asset_type_id' => ['required'],
            '*.asset_category_id' => ['required'],
            '*.asset_subcategory_id' => ['required'],
            '*.asset_specific_category_id' => ['required'],
            '*.asset_acquisition_type_id' => ['required'],
            '*.acquisition_date' => ['required', 'before:today', new AcquisitionYear(Date("Y"))],
            '*.asset_details.*.asset_status_id' => ['required'],
            '*.asset_details.*.asset_condition_id' => ['required'],
            '*.institution_id' => ['required'],
            '*.document_num' => ['required', 'regex:/^([0-9]{1,10}|[nN]\/[pP])$/', 'max:10'],
            '*.asset_details.*.code' => ['required', 'unique:assets,asset_institutional_code'],
            '*.asset_details.*.contract_start_date' => [
                'sometimes',
                function ($attribute, $value, $fail) {
                    preg_match_all('/\d+/', $attribute, $matches);
                    $assetIndex = $matches[0][0];
                    $detailsIndex = $matches[0][1];
                    $end_date = request()->input("{$assetIndex}.asset_details.{$detailsIndex}.contract_end_date");
                    $acquisition_date = request()->input("{$assetIndex}.acquisition_date");

                    $rule_end_date = new ContractStartDate('contract_end_date', $end_date);
                    if (!$rule_end_date->passes($attribute, $value)) {
                        $fail($rule_end_date->message());
                    }

                    $rule_acquisition_date = new ContractStartDate('acquisition_date', $acquisition_date);
                    if (!$rule_acquisition_date->passes($attribute, $value)) {
                        $fail($rule_acquisition_date->message());
                    }
                }
            ],
            '*.asset_details.*.contract_end_date' => ['sometimes', 'after_or_equal:*.asset_details.*.contract_start_date'],
            '*.asset_details.*.registration_date' => ['required_if:*.asset_type_id,2', 'after:*.acquisition_date'],
        ];

        /** Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            '*.institution_id.required' => 'El campo Organización es obligatorio.',
            '*.asset_type_id.required' => 'El campo Tipo de bien es obligatorio.',
            '*.asset_category_id.required' => 'El campo Categoria general es obligatorio.',
            '*.asset_subcategory_id.required' => 'El campo Subcategoria es obligatorio.',
            '*.asset_specific_category_id.required' => 'El campo Categoria especifica es obligatorio.',
            '*.asset_acquisition_type_id.required' => 'El campo Forma de adquisición es obligatorio.',
            '*.acquisition_date.required' => 'El campo Fecha de adquisición es obligatorio.',
            '*.acquisition_date.before' => 'La fecha de adquisición no puede ser posterior a la fecha actual.',
            '*.asset_details.*.asset_status_id.required' => 'El campo Estatus de uso es obligatorio.',
            '*.asset_details.*.serial.required' => 'El campo Serial es obligatorio.',
            '*.asset_details.*.serial.unique' => 'El campo Serial ya existe',
            '*.asset_details.*.brand.required' => 'El campo Marca es obligatorio.',
            '*.asset_details.*.model.required' => 'El campo Modelo es obligatorio.',
            'value.regex' => 'El formato de valor es inválido.',
            'asset_use_function_id.required' => 'El campo función de uso es obligatorio.',
            'parish_id.required' => 'El campo país es obligatorio.',
            'address.required' => 'El campo dirección es obligatorio.',
            '*.asset_details.*.asset_condition_id.required' => 'El campo Condición física es obligatorio.',
            '*.asset_details.*.code.required' => 'El campo Código interno es obligatorio.',
            '*.asset_details.*.code.unique' => 'El campo Código interno ya existe',
            '*.document_num.required' => 'El campo No. de documento es obligatorio.',
            '*.asset_details.*.color_id.required' => 'El campo Color es obligatorio.',
            '*.asset_details.*.acquisition_value.required' => 'El campo Valor de adquisición es obligatorio.',
            '*.asset_details.*.residual_value.required' => 'El campo Valor residual es obligatorio.',
            '*.asset_details.*.depresciation_years.required' => 'El campo Años de depreciación es obligatorio.',
            '*.asset_details.*.contract_end_date.after_or_equal' => 'La fecha de fin de contrato no puede ser anterior a la fecha de inicio de contrato.',
            '*.asset_details.*.registration_date.required' => 'El campo Fecha de registro del inmueble es obligatorio.',
            '*.asset_details.*.registration_date.after' => 'La fecha de registro del inmueble no puede ser menor o igual a la fecha de adquisición.',
        ];

        $this->attributes = [
            'value' => 'valor',
            'parish_id' => 'país',
            'asset_use_function_id' => 'función de uso',
            'model' => 'modelo',
        ];
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:asset.request.register', ['only' => 'index']);
        $this->middleware('permission:asset.create', ['only' => 'create']);
        $this->middleware('permission:asset.edit', ['only' => 'edit']);
        $this->middleware('permission:asset.delete', ['only' => 'delete']);
    }

    /**
     * Muestra un listado de los bienes institucionales
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    Renderable
     */
    public function index()
    {
        return view('asset::registers.list');
    }

    /**
     * Muestra el formulario para registrar un nuevo bien institucional
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    Renderable
     */
    public function create()
    {
        $parameters['asset_type_id'] = false;
        $parameters['asset_category_id'] = false;
        $parameters['asset_subcategory_id'] = false;
        $parameters['asset_specific_category_id'] = true;

        if (isset(auth()->user()->profile) && isset(auth()->user()->profile->institution_id)) {
            $institution = Institution::where(['id' => auth()->user()->profile->institution_id])->first();
        } else {
            $institution = Institution::where(['active' => true, 'default' => true])->first();
        }

        return view('asset::registers.create-group', compact('parameters', 'institution'));
    }

    /**
     * Valida y registra un nuevo bien institucional
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     * @author    Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @param     Request         $request    Datos de la petición
     *
     * @return    JsonResponse    Objeto con los registros a mostrar
     */
    public function store(Request $request)
    {
        if (count($request->input()) == 0) {
            $errors = [
                'error' => [
                    0 => 'Debe registrar al menos un bien a la solicitud.'
                ]
            ];

            return response()->json(['message' => 'The given data was invalid.', 'errors' => $errors], 422);
        }

        $this->validate($request, $this->validateRules, $this->messages, $this->attributes);

        $is_admin = auth()->user()->isAdmin();

        if ($is_admin) {
            $institution = Institution::where('default', true)->first();
        } else {
            $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();
            $institution = $user_profile['institution'] ?? null;
        }

        $records = $request->input();
        foreach ($records as $record) {
            foreach ($record['asset_details'] as $details) {
                isset($details['residual_value'])
                ? $details['residual_value'] = $this->inverseFormatNumber($details['residual_value'])
                : null;
                $details['acquisition_value'] = $this->inverseFormatNumber($details['acquisition_value']);

                $asset = Asset::create([
                    'asset_type_id' => $record['asset_type_id'],
                    'asset_category_id' => $record['asset_category_id'],
                    'asset_subcategory_id' => $record['asset_subcategory_id'],
                    'asset_specific_category_id' => $record['asset_specific_category_id'],
                    'asset_condition_id' => $details['asset_condition_id'],
                    'asset_acquisition_type_id' => $record['asset_acquisition_type_id'],
                    'acquisition_date' => $record['acquisition_date'],
                    'asset_status_id' => $details['asset_status_id'],
                    'asset_institution_storages_id' => $details['asset_institution_storages_id'] ?? null,
                    'acquisition_value' => $details['acquisition_value'],
                    'description' => $details['description'],
                    'institution_id' => $record['institution_id'] ?? $institution->id,
                    'department_id' => $details['department_id'],
                    'purchase_supplier_id' => $record['purchase_supplier_id'],
                    'asset_institutional_code' => $details['code'],
                    'code_sigecof' => $record['code_sigecof'],
                    'currency_id' => $record['currency_id'],
                    'document_num' => $record['document_num'],
                    'asset_details' => $details,
                    'headquarter_id' => $details['headquarter_id'],
                ]);

                AssetBook::create([
                    'asset_id' => $asset->id,
                    'amount' => $details['acquisition_value'],
                ]);
            };
        }

        $request->session()->flash('message', ['type' => 'store']);
        return response()->json(['result' => true, 'redirect' => route('asset.register.index')], 200);
    }

    /**
     * Muestra el formulario para actualizar la información de los bienes institucionales
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     * @author    Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @param     integer    $id    Identificador del Bien
     *
     * @return    Renderable
     */
    public function edit($id)
    {
        $asset = Asset::find($id);
        $parameters['asset_type_id'] = false;
        $parameters['asset_category_id'] = false;
        $parameters['asset_subcategory_id'] = false;
        $parameters['asset_specific_category_id'] = true;

        return view('asset::registers.create-group', compact('asset', 'parameters'));
    }

    /**
     * Actualiza la información de los bienes institucionales
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     Request         $request    Datos de la petición
     * @param     integer         $id         Identificador único del bien
     *
     * @return    JsonResponse    Objeto con los registros a mostrar
     */
    public function update(Request $request, $id)
    {
        $asset = Asset::find($id);

        $validateRules = $this->validateRules;
        if ($request->value) {
            $validateRules = array_merge(
                $validateRules,
                [
                    'value' => ['regex:/^\d+(\.\d+)?$/u'],
                ]
            );
        }
        if ($request->asset_type_id == 1) {
            $validateRules = $this->validateRules;
            $validateRules = array_merge(
                $validateRules,
                [
                    'serial' => ['required', 'unique:assets,serial' . $asset->id, 'max:50'],
                    'marca' => ['required', 'max:50'],
                    'model' => ['required', 'max:50'],
                    'asset_institutional_code' => [
                        'required', 'unique:assets,asset_institutional_code,' . $asset->id
                    ],

                ]
            );
        } elseif ($request->type_id == 2) {
            $validateRules = $this->validateRules;
            $validateRules = array_merge(
                $validateRules,
                [
                    'asset_use_function_id' => ['required'],
                    'parish_id' => ['required'],
                    'address' => ['required'],
                    'asset_institutional_code' => [
                        'required', 'unique:assets,asset_institutional_code,' . $asset->id
                    ],
                ]
            );
        }

        $records = $request->input();
        foreach ($records as $record) {
            foreach ($record['asset_details'] as $details) {
                isset($details['residual_value'])
                ? $details['residual_value'] = $this->inverseFormatNumber($details['residual_value'])
                : null;
                $details['acquisition_value'] = $this->inverseFormatNumber($details['acquisition_value']);

                $asset->update([
                    'asset_type_id' => $record['asset_type_id'],
                    'asset_category_id' => $record['asset_category_id'],
                    'asset_subcategory_id' => $record['asset_subcategory_id'],
                    'asset_specific_category_id' => $record['asset_specific_category_id'],
                    'asset_condition_id' => $details['asset_condition_id'],
                    'asset_acquisition_type_id' => $record['asset_acquisition_type_id'],
                    'acquisition_date' => $record['acquisition_date'],
                    'asset_status_id' => $details['asset_status_id'],
                    'asset_institution_storages_id' => $details['asset_institution_storages_id'],
                    'acquisition_value' => $details['acquisition_value'],
                    'description' => $details['description'],
                    'institution_id' => $record['institution_id'],
                    'department_id' => $details['department_id'],
                    'purchase_supplier_id' => $record['purchase_supplier_id'],
                    'asset_institutional_code' => $details['code'],
                    'code_sigecof' => $record['code_sigecof'],
                    'currency_id' => $record['currency_id'],
                    'document_num' => $record['document_num'],
                    'asset_details' => $details,
                    'headquarter_id' => $details['headquarter_id'],
                ]);
                if ($details['acquisition_value'] != $asset->asset_details['acquisition_value']) {
                    AssetBook::create([
                        'asset_id' => $asset->id,
                        'amount' => $details['acquisition_value'],
                    ]);
                }
            };
        }

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('asset.register.index')], 200);
    }

    /**
     * Elimina un bien institucional
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     Asset      $asset    Datos del Bien
     *
     * @return    JsonResponse         Objeto con los registros a mostrar
     */
    public function destroy(Asset $asset)
    {
        $asset->delete();
        return response()->json(['message' => 'destroy'], 200);
    }

    /**
     * Obtiene la información del bien institucional registrado
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     Asset      $asset     Datos del bien institucional
     *
     * @return    JsonResponse          Objeto con los registros a mostrar
     */
    public function vueInfo($id)
    {
        $asset = Asset::where('id', $id)->with(
            [
                'assetType',
                'assetCategory',
                'assetSubcategory',
                'assetSpecificCategory',
                'assetAcquisitionType',
                'assetCondition',
                'assetStatus',
                'assetUseFunction',
                'institution',
                'assetAdjustmentAssets',
                'assetBooks',
                'parish' => function ($query) {
                    $query->with([
                        'municipality' => function ($query) {
                            $query->with([
                                'estate' => function ($query) {
                                    $query->with('country')->get();
                                }
                            ])->get();
                        }
                    ])->get();
                },
                'assetDisincorporationAsset' => function ($query) {
                    $query->with([
                        'assetDisincorporation' => function ($query) {
                            $query->with('assetDisincorporationMotive')->get();
                        }
                    ])->get();
                },
            ]
        )->first();

        return response()->json(['records' => $asset], 200);
    }

    /**
     * Otiene un listado de los bienes registradas
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     \Illuminate\Http\Request  $request         Datos de la petición
     * @param     string|null               $operation       Tipo de operación realizada
     * @param     integer|null              $operation_id    Identificador único de la operación
     *
     * @return    JsonResponse    Objeto con los registros a mostrar
     */
    public function vueList(Request $request, $operation = null, $operation_id = null)
    {
        $user_profile = Profile::where('user_id', auth()->user()->id)->first();
        $institution_id = isset($request->institution_id)
            ? $request->institution_id
            : (isset($user_profile->institution_id)
                ? $user_profile->institution_id
                : null);
        if ($operation == null) {
            if (auth()->user()->isAdmin()) {
                $assets = Asset::query()
                    ->searchRegisters($request->query('query'))
                    ->with([
                        'institution',
                        'assetCondition',
                        'assetStatus',
                        'AssetSpecificCategory',
                        'assetAdjustmentAssets',
                        'assetSupplier',
                        'assetAsignationAsset' => function ($query) {
                            $query->with('assetAsignation');
                        },
                        'assetDisincorporationAsset' => function ($query) {
                            $query->with([
                                'assetDisincorporation' => function ($query) {
                                    $query->with('assetDisincorporationMotive');
                                },
                            ]);
                        },
                        'assetRequestAsset' => function ($query) {
                            $query->with('assetRequest');
                        },
                    ])
                    ->orderBy('id');
            } else {
                $assets = Asset::query()
                    ->where('institution_id', $institution_id)
                    ->with([
                        'institution',
                        'assetCondition',
                        'assetStatus',
                        'assetSupplier',
                        'assetAsignationAsset' => function ($query) {
                            $query->with('assetAsignation');
                        },
                        'assetDisincorporationAsset' => function ($query) {
                            $query->with([
                                'assetDisincorporation' => function ($query) {
                                    $query->with('assetDisincorporationMotive');
                                },
                            ]);
                        },
                        'assetRequestAsset' => function ($query) {
                            $query->with('assetRequest');
                        },
                    ])
                    ->orderBy('id');
            }
        } elseif ($operation_id == null) {
            if ($operation == 'asignations' || $operation == 'requests') {
                if (auth()->user()->isAdmin()) {
                    $assets = Asset::query()
                        ->searchAsignation($request->query('query') ?? '')
                        ->codeClasification(
                            $request->asset_type,
                            $request->asset_category,
                            $request->asset_subcategory,
                            $request->asset_specific_category,
                            $request->is_dis ?? false,
                            []
                        )
                        ->where('asset_condition_id', 1)
                        ->where('asset_status_id', 10)
                        ->with([
                            'institution',
                            'assetCondition',
                            'assetSpecificCategory',
                            'assetStatus',
                            'assetAsignationAsset' => function ($query) {
                                $query->with('assetAsignation');
                            },
                            'assetDisincorporationAsset',
                            'assetRequestAsset' => function ($query) {
                                $query->with('assetRequest');
                            },
                        ])
                        ->orderBy('id');
                } else {
                    $assets = Asset::query()
                        ->searchAsignation($request->query('query') ?? '')
                        ->codeClasification(
                            $request->asset_type,
                            $request->asset_category,
                            $request->asset_subcategory,
                            $request->asset_specific_category,
                            $request->is_dis ?? false,
                            []
                        )
                        ->where('institution_id', $institution_id)
                        ->where('asset_condition_id', 1)
                        ->where('asset_status_id', 10)
                        ->with([
                            'institution',
                            'assetCondition',
                            'assetStatus',
                            'assetAsignationAsset' => function ($query) {
                                $query->with('assetAsignation');
                            },
                            'assetDisincorporationAsset',
                            'assetRequestAsset' => function ($query) {
                                $query->with('assetRequest');
                            },
                        ])
                        ->orderBy('id');
                }

                $assets = $assets->paginate((int) request()->limit);

                return response()->json(
                    [
                        'data' => !is_null($assets)
                            ? AssetAsignationResource::collection($assets->items())
                            : null,
                        'count' => $assets->total()
                    ],
                    200
                );
            } elseif ($operation == 'disincorporations') {
                if (auth()->user()->isAdmin()) {
                    $assets = Asset::query()
                        ->searchAsignation($request->query('query') ?? '')
                        ->codeClasification(
                            $request->asset_type,
                            $request->asset_category,
                            $request->asset_subcategory,
                            $request->asset_specific_category,
                            true,
                            []
                        )
                        ->with([
                            'institution',
                            'assetCondition',
                            'assetSpecificCategory',
                            'assetAsignationAsset.assetAsignation.payrollStaff',
                            'department',
                            'assetStatus',
                            'assetDisincorporationAsset',
                            'assetRequestAsset' => function ($query) {
                                $query->with('assetRequest');
                            },
                        ])->where('asset_status_id', '!=', 1)
                        ->where('asset_status_id', '!=', 3)
                        ->where('asset_status_id', '!=', 6)
                        ->where('asset_status_id', '!=', 11)
                        ->orderBy('id');
                } else {
                    $assets = Asset::with([
                        'institution',
                        'assetCondition',
                        'assetSpecificCategory',
                        'assetAsignationAsset.assetAsignation.payrollStaff',
                        'department',
                        'assetStatus',
                        'assetDisincorporationAsset',
                        'assetRequestAsset' => function ($query) {
                            $query->with('assetRequest');
                        },
                    ])->where('institution_id', $institution_id)
                        ->where('asset_status_id', '!=', 1)
                        ->where('asset_status_id', '!=', 3)
                        ->where('asset_status_id', '!=', 6)
                        ->where('asset_status_id', '!=', 11)
                        ->orderBy('id');
                }
            }
            $assets = $assets->paginate((int) request()->limit);
            return response()->json(
                [
                    'data' => !is_null($assets)
                        ? AssetAsignationResource::collection($assets->items())
                        : null,
                    'count' => $assets->total()
                ],
                200
            );
        } else {
            if ($operation == 'asignations') {
                $selected = AssetAsignation::find($operation_id)
                    ->assetAsignationAssets()
                    ->toBase()
                    ->get()
                    ->pluck('asset_id')
                    ->toArray();

                if (auth()->user()->isAdmin()) {
                    $assets = Asset::query()
                        ->with('institution', 'assetCondition', 'assetStatus')
                        ->orderBy('id')
                        ->searchAsignation($request->query('query') ?? '')
                        ->codeClasification(
                            $request->asset_type,
                            $request->asset_category,
                            $request->asset_subcategory,
                            $request->asset_specific_category,
                            $request->is_dis ?? false,
                            []
                        )
                        ->where(function ($query) {
                            $query->where('asset_status_id', 10)
                                ->where('asset_condition_id', 1);
                        })
                        ->orWhere(function ($query) use ($selected) {
                            $query->whereIn('id', $selected);
                        });
                } else {
                    $assets = Asset::query()
                        ->with('institution', 'assetCondition', 'assetStatus')
                        ->orderBy('id')
                        ->searchAsignation($request->query('query') ?? '')
                        ->codeClasification(
                            $request->asset_type,
                            $request->asset_category,
                            $request->asset_subcategory,
                            $request->asset_specific_category,
                            $request->is_dis ?? false,
                            []
                        )
                        ->where('institution_id', $institution_id)
                        ->where(function ($query) {
                            $query->where('asset_status_id', 10)
                                ->where('asset_condition_id', 1);
                        })
                        ->orWhere(function ($query) use ($selected) {
                            $query->whereIn('id', $selected);
                        });
                }
            } elseif ($operation == 'disincorporations') {
                $selected = AssetDisincorporation::find($operation_id)
                    ->assetDisincorporationAssets()
                    ->toBase()
                    ->get()
                    ->pluck('asset_id')
                    ->toArray();

                if (auth()->user()->isAdmin()) {
                    $assets = Asset::query()
                        ->with(
                            'institution',
                            'assetCondition',
                            'assetSpecificCategory',
                            'department',
                            'assetStatus',
                            'assetDisincorporationAsset'
                        )
                        ->orderBy('id')
                        ->searchAsignation($request->query('query') ?? '')
                        ->codeClasification(
                            $request->asset_type,
                            $request->asset_category,
                            $request->asset_subcategory,
                            $request->asset_specific_category,
                            true,
                            []
                        )
                        ->where(function ($query) {
                            $query->orWhere('asset_status_id', '!=', 1)
                                ->orWhere('asset_status_id', '!=', 3)
                                ->where('asset_status_id', '!=', 6)
                                ->where('asset_status_id', '!=', 11);
                        })
                        ->orWhere(function ($query) use ($selected) {
                            $query->whereIn('id', $selected);
                        });
                } else {
                    $assets = Asset::query()
                        ->with(
                            'institution',
                            'assetCondition',
                            'assetSpecificCategory',
                            'department',
                            'assetStatus',
                            'assetDisincorporationAsset'
                        )
                        ->orderBy('id')
                        ->searchAsignation($request->query('query') ?? '')
                        ->codeClasification(
                            $request->asset_type,
                            $request->asset_category,
                            $request->asset_subcategory,
                            $request->asset_specific_category,
                            true,
                            []
                        )
                        ->where('institution_id', $institution_id)
                        ->where(function ($query) {
                            $query->orWhere('asset_status_id', '!=', 1)
                                ->orWhere('asset_status_id', '!=', 3)
                                ->where('asset_status_id', '!=', 6)
                                ->where('asset_status_id', '!=', 11);
                        })
                        ->orWhere(function ($query) use ($selected) {
                            $query->whereIn('id', $selected);
                        });
                }
            } elseif ($operation == 'requests') {
                $selected = [];
                $assetRequestAssets = AssetRequest::find($operation_id)->assetRequestAssets()->get();
                foreach ($assetRequestAssets as $assetRequestAsset) {
                    array_push($selected, $assetRequestAsset->asset_id);
                }
                if (auth()->user()->isAdmin()) {
                    $assets = Asset::with(
                        'institution',
                        'assetCondition',
                        'assetStatus',
                        'assetSpecificCategory'
                    )->orderBy('id')
                        ->whereIn('id', $selected)
                        ->orWhere('asset_status_id', 10)
                        ->where('asset_condition_id', 1);
                } else {
                    $assets = Asset::with(
                        'institution',
                        'assetCondition',
                        'assetStatus',
                        'assetSpecificCategory'
                    )->orderBy('id')
                        ->whereIn('id', $selected)
                        ->orWhere('asset_status_id', 10)
                        ->where('asset_condition_id', 1)
                        ->where('institution_id', $institution_id);
                }
            }
        }
        $assets = $assets->paginate((int) request()->limit);

        return response()->json(
            [
                'data' => !is_null($assets)
                    ? AssetResource::collection($assets->items())
                    : null,
                'count' => $assets->total()
            ],
            200
        );
    }

    /**
     * Filtra por su código de clasificación los bienes registradas
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     Request         $request    Datos de la petición
     * @param     string|null     $operation  Operación a realizar
     *
     * @return    JsonResponse    Objeto con los registros a mostrar
     */
    public function searchClasification(Request $request, $operation = null)
    {
        $ids = [];
        if ($request->disincorporation) {
            $is_dis = $request->disincorporation;

            $assetAsignations = AssetAsignationAsset::get();
            $assetDisincorporations = AssetDisincorporationAsset::get();

            foreach ($assetAsignations as $asignation) {
                $ids[] = $asignation->asset_id;
            }

            foreach ($assetDisincorporations as $disincorporation) {
                $ids[] = $disincorporation->asset_id;
            }
        } else {
            $is_dis = false;
        }
        if ($request->institution) {
            $institution = $request->institution;
        } else {
            $is_admin = auth()->user()->isAdmin();

            if ($is_admin) {
                $institution = Institution::where('default', true)->first()->id;
            } else {
                $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();
                $institution = $user_profile['institution'] ?? null;
                $institution = $institution->id;
            }
        }
        $assets = Asset::CodeClasification(
            $request->asset_type,
            $request->asset_category,
            $request->asset_subcategory,
            $request->asset_specific_category,
            $is_dis,
            $ids
        )->with([
            'institution',
            'assetCondition',
            'assetType',
            'assetCategory',
            'assetSubcategory',
            'assetSpecificCategory',
            'assetAsignationAsset.assetAsignation.payrollStaff',
            'department',
            'assetStatus',
            'assetDisincorporationAsset' => function ($query) {
                $query->with([
                    'assetDisincorporation' => function ($query) {
                        $query->with('assetDisincorporationMotive');
                    }
                ]);
            }
        ])->where('assets.institution_id', $institution);

        if ($request->asset_status > 0) {
            $assets = $assets->where('asset_status_id', $request->asset_status);
        }

        if ($operation == 'assignation') {
            $assets = AssetAsignationResource::collection($assets->get());
            return response()->json(['records' => $assets], 200);
        } elseif ($operation == 'disincorporation') {
            $assets = AssetAsignationResource::collection($assets->get());
            return response()->json(['records' => $assets], 200);
        }

        //se realiza el filtrado de los registros por el texto introducido en el buscador de la tabla
        if ($request->search != "" and $request->search != null and $request->search != " ") {
            $assets = $assets->Where('asset_institutional_code', 'like', '%' . $request->search . '%')
                ->orWhere('code_sigecof', 'like', '%' . $request->search . '%')
                ->orWhereHas('assetStatus', function ($query) use ($request) {
                    $query->whereRaw('LOWER(name) LIKE ?', [strtolower("%$request->search%")]);
                })->orWhereHas('department', function ($query) use ($request) {
                    $query->whereRaw('LOWER(name) LIKE ?', [strtolower("%$request->search%")]);
                })->orWhereHas('assetSpecificCategory', function ($query) use ($request) {
                    $query->whereRaw('LOWER(name) LIKE ?', [strtolower("%$request->search%")]);
                })->orWhereHas('assetAsignationAsset', function ($query) use ($request) {
                    $query->whereHas('assetAsignation', function ($query) use ($request) {
                        $query->whereRaw('LOWER(location_place) LIKE ?', [strtolower("%$request->search%")]);
                    });
                });
        }

        if ($operation == 'clasification') {
            if ($request->start_date || $request->end_date) {
                if ($request->start_date != '' && !is_null($request->start_date)) {
                    if ($request->end_date != '' && !is_null($request->end_date)) {
                        $assets = $assets->whereBetween("created_at", [$request->start_date, $request->end_date]);
                    } else {
                        $assets = $assets->whereBetween("created_at", [$request->start_date, now()]);
                    }
                }
                if ($request->asset_status > 0) {
                    $assets = $assets->where('asset_status_id', $request->asset_status);
                }
            } elseif ($request->year || $request->mes_id) {
                if ($request->mes_id != '' && !is_null($request->mes_id)) {
                    if ($request->year != '' && !is_null($request->year)) {
                        $assets = $assets->whereMonth('created_at', $request->mes_id)
                            ->whereYear('created_at', $request->year);
                    } else {
                        $assets = $assets->whereMonth('created_at', $request->mes_id);
                    }
                }
                if ($request->year != '' && !is_null($request->year) && $request->mes_id == '') {
                    $assets = $assets->whereYear('created_at', $request->year);
                }
            }


            switch ($request->type_asset) {
                case 'furniture_active':
                    $assets->whereHas('assetType', function ($query) {
                        $query->where('name', 'Mueble');
                    });
                    break;

                case 'property_active':
                    $assets->whereHas('assetType', function ($query) {
                        $query->where('name', 'Inmueble');
                    });
                    break;

                case 'vehicle_active':
                    $assets->whereHas('assetType', function ($query) {
                        $query->where('name', 'Mueble');
                    })->whereHas('assetCategory', function ($query) {
                        $query->where('name', 'like', '%transporte%');
                    });
                    break;

                case 'livestock_active':
                    $assets->whereHas('assetType', function ($query) {
                        $query->where('name', 'Mueble');
                    })->whereHas('assetCategory', function ($query) {
                        $query->where('name', 'like', '%semoviente%');
                    });
                    break;
            }

            if ($request->code != '') {
                $assets->where('asset_institutional_code', $request->code)->first();
                if ($assets->count() == 0) {
                    $errors = [
                        'error' => [
                            0 => 'El código interno no existe o no coincide con el Tipo de bien previamente seleccionado.'
                        ]
                    ];

                    return response()->json([
                        'message' => 'The given data was invalid.', 'errors' => $errors
                    ], 422);
                }
            }

            if ($request->orderBy) {
                $order = $request->orderBy;
                $ascending = ($request->ascending) ? 'asc' : 'desc';
                $assets = match ($order) {
                    'code_sigecof' => $assets->orderBy('code_sigecof', $ascending),

                    'asset_specific_category.name' => $assets
                        ->join(
                            'asset_specific_categories',
                            'asset_specific_categories.id',
                            '=',
                            'assets.asset_specific_category_id'
                        )
                        ->orderBy('asset_specific_categories.name', $ascending)
                        ->select('assets.*'),

                    'asset_status.name' => $assets
                        ->join(
                            'asset_status',
                            'asset_status.id',
                            '=',
                            'assets.asset_status_id'
                        )
                        ->orderBy('asset_status.name', $ascending)
                        ->select('assets.*'),

                    'department.name' => $assets
                        ->join(
                            'departments',
                            'departments.id',
                            '=',
                            'assets.department_id'
                        )
                        ->orderBy('departments.name', $ascending)
                        ->select('assets.*'),

                    default => $assets
                };
            }

            $assets = $assets->paginate((int)$request->limit);
            return response()->json(
                [
                    'data' => $assets->items(),
                    'count' => $assets->total()
                ],
                200
            );
        }

        $assets = $assets->paginate((int)$request->limit);

        return response()->json(
            [
                'data' => $assets->items(),
                'count' => $assets->total()
            ],
            200
        );
    }

    /**
     * Filtra por su fecha de registro los bienes registradas
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param     Request         $request    Datos de la petición
     *
     * @return    JsonResponse    Objeto con los registros a mostrar
     */
    public function searchGeneral(Request $request)
    {
        $assets = Asset::where('assets.institution_id', $request->institution)
            ->with([
                'institution',
                'assetCondition',
                'assetSpecificCategory',
                'assetAsignationAsset.assetAsignation.payrollStaff',
                'department',
                'assetStatus',
                'assetDisincorporationAsset' => function ($query) {
                    $query->with([
                        'assetDisincorporation' => function ($query) {
                            $query->with('assetDisincorporationMotive');
                        }
                    ]);
                }
            ]);

        if ($request->start_date || $request->end_date) {
            if ($request->start_date != '' && !is_null($request->start_date)) {
                if ($request->end_date != '' && !is_null($request->end_date)) {
                    $assets = $assets->whereBetween("created_at", [$request->start_date, $request->end_date]);
                } else {
                    $assets = $assets->whereBetween("created_at", [$request->start_date, now()]);
                }
            }
            if ($request->asset_status > 0) {
                $assets = $assets->where('asset_status_id', $request->asset_status);
            }
        } elseif ($request->year || $request->mes_id) {
            if ($request->mes_id != '' && !is_null($request->mes_id)) {
                if ($request->year != '' && !is_null($request->year)) {
                    $assets = $assets->whereMonth('created_at', $request->mes_id)
                        ->whereYear('created_at', $request->year);
                } else {
                    $assets = $assets->whereMonth('created_at', $request->mes_id);
                }
            }

            if ($request->year != '' && !is_null($request->year) && $request->mes_id == '') {
                $assets = $assets->whereYear('created_at', $request->year);
            }

            if ($request->asset_status > 0) {
                $assets = $assets->where('asset_status_id', $request->asset_status);
            }
        } else {
            if ($request->asset_status > 0) {
                $assets = Asset::with([
                    'institution',
                    'assetCondition',
                    'assetSpecificCategory',
                    'assetAsignationAsset.assetAsignation.payrollStaff',
                    'department',
                    'assetStatus',
                    'assetDisincorporationAsset' => function ($query) {
                        $query->with([
                            'assetDisincorporation' => function ($query) {
                                $query->with('assetDisincorporationMotive');
                            }
                        ]);
                    }
                ])
                    ->where('asset_status_id', $request->asset_status)
                    ->where('assets.institution_id', $request->institution);
            } else {
                $assets = Asset::with([
                    'institution',
                    'assetCondition',
                    'assetStatus',
                    'assetSpecificCategory',
                    'assetAsignationAsset.assetAsignation.payrollStaff',
                    'department',
                    'assetDisincorporationAsset' => function ($query) {
                        $query->with([
                            'assetDisincorporation' => function ($query) {
                                $query->with('assetDisincorporationMotive');
                            }
                        ]);
                    }
                ])
                    ->where('assets.institution_id', $request->institution);
            }
        }
        //se realiza el filtrado de los registros por el texto introducido en el buscador de la tabla
        if ($request->search != "" and $request->search != null and $request->search != " ") {
            $assets = $assets->Where('asset_institutional_code', 'like', '%' . $request->search . '%')
                ->orWhere('code_sigecof', 'like', '%' . $request->search . '%')
                ->orWhereHas('assetStatus', function ($query) use ($request) {
                    $query->whereRaw('LOWER(name) LIKE ?', [strtolower("%$request->search%")]);
                })->orWhereHas('department', function ($query) use ($request) {
                    $query->whereRaw('LOWER(name) LIKE ?', [strtolower("%$request->search%")]);
                })->orWhereHas('assetSpecificCategory', function ($query) use ($request) {
                    $query->whereRaw('LOWER(name) LIKE ?', [strtolower("%$request->search%")]);
                })->orWhereHas('assetAsignationAsset', function ($query) use ($request) {
                    $query->whereHas('assetAsignation', function ($query) use ($request) {
                        $query->whereRaw('LOWER(location_place) LIKE ?', [strtolower("%$request->search%")]);
                    });
                });
        }
        //Condicion y logica para ordenar por columna de manera ascendente o descendente.
        // en caso de ordenar por una columna con nombre diferente al del modelo
        // se debe agregar al match.
        if ($request->orderBy) {
            $order = $request->orderBy;
            $ascending = ($request->ascending) ? 'asc' : 'desc';
            $assets = match ($order) {
                'code_sigecof' => $assets->orderBy('code_sigecof', $ascending),

                'asset_specific_category.name' => $assets
                    ->join(
                        'asset_specific_categories',
                        'asset_specific_categories.id',
                        '=',
                        'assets.asset_specific_category_id'
                    )
                    ->orderBy('asset_specific_categories.name', $ascending)
                    ->select('assets.*'),

                'asset_status.name' => $assets
                    ->join(
                        'asset_status',
                        'asset_status.id',
                        '=',
                        'assets.asset_status_id'
                    )
                    ->orderBy('asset_status.name', $ascending)
                    ->select('assets.*'),

                'department.name' => $assets
                    ->join(
                        'departments',
                        'departments.id',
                        '=',
                        'assets.department_id'
                    )
                    ->orderBy('departments.name', $ascending)
                    ->select('assets.*'),

                default => $assets
            };
        }
        //paginando la respuesta para la tabla servidor
        $assets = $assets->paginate((int)$request->limit);

        return response()->json(
            [
                'data' => $assets->items(),
                'count' => $assets->total()
            ],
            200
        );
    }

    /**
     * Filtra por su ubicación en la institución los bienes registradas
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     Request         $request    Datos de la petición
     *
     * @return    JsonResponse    Objeto con los registros a mostrar
     */
    public function searchDependence(Request $request)
    {
        /*
         *  Falta filtrar por dependencia solicitante
         *  Validar tambien para múltiples instituciones         *
         */
        return response()->json(['records' => []], 200);
    }

    /**
     * Busca un bien por su código interno
     *
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> \ <rojonatanael99@gmail.com>
     *
     * @param     Request                $request    Datos de la petición
     *
     * @return    AssetReportResource    Objeto con los registros a mostrar
     */
    public function searchCode(Request $request)
    {
        $found_assets = Asset::where('asset_institutional_code', $request->code)->with([
            'assetSubcategory',
            'assetSpecificCategory',
            'assetDepreciationAsset',
        ])->get();
        return new AssetReportResource($found_assets);
    }

    /**
     * Realiza la acción necesaria para exportar los datos del modelo Asset
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    object    Objeto que permite descargar el archivo con la información a ser exportada
     */
    public function export(Request $request)
    {
        return Excel::download(new AssetExport($request->type), 'assets.xlsx');
    }

    /**
     * Realiza la acción necesaria para importar los datos suministrados en un archivo para el modelo Asset
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    JsonResponse    Objeto que permite descargar el archivo con la información a ser exportada
     */
    public function import(Request $request)
    {
        $filePath = $request->file('file')->store('', 'temporary');
        $fileErrorsPath = 'import' . uniqid() . '.errors';
        Storage::disk('temporary')->put($fileErrorsPath, '');
        $import = new AssetImportMultiSheet($request->type, $filePath, 'temporary', auth()->user()->id, $fileErrorsPath);

        $import->import();

        return response()->json(['result' => true], 200);
    }

    /**
     * Obtiene los campos requeridos para la creación de un nuevo bien
     *
     * @param Request $request  Datos de la petición
     *
     * @return JsonResponse
     */
    public function getFields(Request $request)
    {
        $params = new AssetParametersRepository();

        $type = AssetType::find($request->asset_type_id);
        $category = AssetCategory::find($request->asset_category_id);

        if (isset($type)) {
            if (str_contains(strtolower($type->name), 'mueble') && !str_contains(strtolower($type->name), 'inmueble')) {
                if (isset($category)) {
                    if (str_contains(strtolower($category->name), 'transporte')) {
                        $parameters = $params->loadParametersData('vehiculos');
                        $options = [
                            'colors' => $params->loadColorsData(),
                        ];
                    } elseif (str_contains(strtolower($category->name), 'semoviente')) {
                        $parameters = $params->loadParametersData('semovientes');
                        $options = [
                            'types' => $params->loadCattleTypesData(),
                            'purposes' => $params->loadPurposesData(),
                            'gender' => $params->loadGendersData(),
                            'measurement_units' => template_choices(
                                'App\Models\MeasurementUnit',
                                ['acronym', '-', 'name'],
                                '',
                                true
                            )
                        ];
                    } else {
                        $parameters = $params->loadParametersData('muebles');
                        $options = [
                            'colors' => $params->loadColorsData(),
                        ];
                    }
                } else {
                    $parameters = $params->loadParametersData('muebles');
                    $options = [
                        'colors' => $params->loadColorsData(),
                    ];
                }
            } elseif (isset($category)) {
                if (str_contains(strtolower($category->name), 'terreno')) {
                    $parameters = $params->loadParametersData('terrenos');
                    $options = [
                        'measurement_units' => template_choices(
                            'App\Models\MeasurementUnit',
                            ['acronym', '-', 'name'],
                            '',
                            true
                        ),
                        'countries' => template_choices('App\Models\Country', ['name'], '', true),
                        'asset_use_functions' => $params->loadAssetUseFunctionsData(),
                        'occupancy_statuses' => $params->loadOccupancyStatusData(),
                    ];
                }
            }
        }
        return response()->json(['records' => $parameters ?? [], 'options' => $options ?? []], 200);
    }

    /**
     * Inverse the formatting of a number and convert it back to a numeric value.
     *
     * @param string $formattedNumber The number to be inverse formatted.
     *
     * @return float The inverse formatted number as a float value.
     */
    private function inverseFormatNumber($formattedNumber)
    {
        // Remove the points and keep the decimals
        $number = str_replace(['.', ','], ['', '.'], $formattedNumber);

        // Convert the string to a number with decimals
        $num = floatval($number);

        return is_numeric($num) ? $num : 0.00;
    }
}
