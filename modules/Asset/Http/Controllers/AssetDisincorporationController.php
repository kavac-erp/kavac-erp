<?php

namespace Modules\Asset\Http\Controllers;

use App\Models\Image;
use App\Models\Profile;
use App\Models\Document;
use App\Models\FiscalYear;
use App\Models\CodeSetting;
use App\Models\Institution;
use Illuminate\Http\Request;
use Modules\Asset\Models\Asset;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Repositories\ReportRepository;
use Illuminate\Support\Facades\Storage;
use Modules\Payroll\Models\PayrollStaff;
use App\Repositories\UploadDocRepository;
use App\Repositories\UploadImageRepository;
use Illuminate\Contracts\Support\Renderable;
use Modules\Asset\Models\AssetDisincorporation;
use Modules\Asset\Models\AssetDisincorporationAsset;
use Modules\Asset\Http\Resources\AssetAsignationResource;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Models\DocumentStatus;

/**
 * @class     AssetDisincorporationController
 * @brief     Controlador de las desincorporaciones de bienes institucionales
 *
 * Clase que gestiona las desincorporaciones de bienes institucionales
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetDisincorporationController extends Controller
{
    use ValidatesRequests;

    /**
     * Reglas de validación
     *
     * @var array $validateRules
     */
    protected $validateRules;

    /**
     * Mensajes de validación
     *
     * @var array $messages
     */
    protected $messages;

    /**
     * Define la configuración de la clase
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    void
     */
    public function __construct()
    {
        $this->middleware('permission:asset.desincorporation.download', ['only' => 'managePdf']);
        $this->middleware('permission:asset.disincorporation.index', ['only' => 'index']);
        $this->middleware('permission:asset.disincorporation.create', ['only' => 'create']);
        // Establece permisos de acceso para cada método del controlador
        $this->validateRules = [
            'date' => ['required'],
            'asset_disincorporation_motive_id' => ['required'],
            'observation' => ['required'],
            'files.*' => ['required', 'max:5000', 'mimes:jpeg,jpg,png,pdf,docx,doc,odt'],
            'authorized_by_id' => ['required'],
            'formed_by_id' => ['required'],
            'produced_by_id' => ['required'],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'date.required' => 'El campo fecha de desincorporación es obligatorio.',
            'asset_disincorporation_motive_id.required' => 'El campo motivo de la desincorporación es obligatorio.',
            'observation.required' => 'El campo observaciones generales es obligatorio.',
            'files.*.required' => 'El campo adjuntar archivos es obligatorio.',
            'files.*.max' => 'El campo adjuntar archivos no debe contener más de 5000 caracteres.',
            'files.*.mimes' => 'El campo adjuntar archivos no permite ese formato.',
            'authorized_by_id.required' => 'El campo autorizado por es obligatorio.',
            'formed_by_id.required' => 'El campo conformado por es obligatorio.',
            'produced_by_id.required' => 'El campo elaborado por es obligatorio.',

        ];
    }

    /**
     * Muestra un listado de las Ddsincorporaciones de bienes institucionales
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    Renderable
     */
    public function index()
    {
        return view('asset::disincorporations.list');
    }

    /**
     * Muestra el formulario para registrar una nueva desincorporación de bienes institucionales
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    Renderable
     */
    public function create()
    {
        return view('asset::disincorporations.create');
    }

    /**
     * Valida y registra una nueva desincorporación de bienes institucionales
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     \Illuminate\Http\Request         $request    Datos de la petición
     * @param     \App\Repositories\UploadImageRepository $upImage    Repositorio para la gestión de imagenes
     *
     * @return    \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar)
     */
    public function store(Request $request, UploadImageRepository $upImage, UploadDocRepository $upDoc)
    {
        $validateRules = $this->validateRules;
        $this->validate($request, $validateRules, $this->messages);

        $codeSetting = CodeSetting::where('table', 'asset_disincorporations')->first();
        if (is_null($codeSetting)) {
            $request->session()->flash('message', [
                'type' => 'other', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'growl-danger',
                'text' => 'Debe configurar previamente el formato para el código a generar',
            ]);
            return response()->json(['result' => false, 'redirect' => route('asset.setting.index')], 200);
        }

        $currentFiscalYear = FiscalYear::select('year')
            ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();

        $code = generate_registration_code(
            $codeSetting->format_prefix,
            strlen($codeSetting->format_digits),
            (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
                substr($currentFiscalYear->year, 2, 2) : date('y')) : (isset($currentFiscalYear) ?
                $currentFiscalYear->year : date('Y')),
            AssetDisincorporation::class,
            $codeSetting->field
        );

        $user_id = auth()->user()->id;
        $institution = Institution::where(['active' => true, 'default' => true])->first();
        $institution_id = isset($institution->id) ? $institution->id : null;

        $documentStatus = DocumentStatus::where('action', 'PR')->first();

        /* Objeto asociado al modelo AssetDisincorporation */
        $disincorporation = AssetDisincorporation::create([
            'code' => $code,
            'date' => $request->date,
            'asset_disincorporation_motive_id' => $request->asset_disincorporation_motive_id,
            'observation' => $request->observation,
            'user_id' => Auth::id(),
            'institution_id' => $institution_id,
            'authorized_by_id' => $request->authorized_by_id,
            'formed_by_id' => $request->formed_by_id,
            'produced_by_id' => $request->produced_by_id,
            'document_status_id' => $documentStatus->id,
        ]);

        $assets = explode(",", $request->assets);
        foreach ($assets as $asset_id) {
            $asset = Asset::find($asset_id);
            $asset->asset_status_id = 11;
            $asset->save();
            $asset_disincorporation = AssetDisincorporationAsset::create([
                'asset_id' => $asset->id,
                'asset_disincorporation_id' => $disincorporation->id,
            ]);
        }

        /* Se guardan los docmentos, según sea el tipo (imágenes y/o documentos)*/
        $documentFormat = ['doc', 'docx', 'pdf', 'odt'];
        $imageFormat = ['jpeg', 'jpg', 'png'];

        if ($request->has('files')) {
            foreach ($request->file('files') as $file) {
                $extensionFile = $file->getClientOriginalExtension();

                if (in_array($extensionFile, $documentFormat)) {
                    $upDoc->uploadDoc(
                        $file,
                        'documents',
                        AssetDisincorporation::class,
                        $disincorporation->id
                    );
                } elseif (in_array($extensionFile, $imageFormat)) {
                    $upImage->uploadImage(
                        $file,
                        'pictures',
                        AssetDisincorporation::class,
                        $disincorporation->id
                    );
                }
            }
        }
        $request->session()->flash('message', ['type' => 'store']);
        return response()->json(['result' => true, 'redirect' => route('asset.disincorporation.index')], 200);
    }

    /**
     * Muestra el formulario para desincorporar un bien institucional
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     integer                          $id    Identificador único del bien a desincorporar
     *
     * @return    Renderable    Objeto con los registros a mostrar
     */
    public function assetDisassign($id)
    {
        $asset = Asset::find($id);
        return view('asset::disincorporations.create', compact('asset'));
    }

    /**
     * Muestra el formulario para actualizar la información de las desincorporaciones de bienes institucionales
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     integer                          $id    Identificador único de la desincorporación a editar
     *
     * @return    Renderable    Objeto con los datos a mostrar
     */
    public function edit($id)
    {
        $disincorporation = AssetDisincorporation::find($id);
        return view('asset::disincorporations.create', compact('disincorporation'));
    }

    /**
     * Muestra el documento registrado
     *
     * @param mixed $filename Archivo a mostrar
     *
     * @return mixed|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function showDocuments($filename)
    {
        if (Storage::disk('pictures')->exists($filename)) {
            $file = storage_path() . '/pictures/' . $filename;
        } elseif (Storage::disk('documents')->exists($filename)) {
            $file = storage_path() . '/documents/' . $filename;
        }

        return response()->download($file, $filename, [], 'inline');
    }

    /**
     * Gestiona la petición de un documento de desincorporación
     *
     * @param integer $id Identificador de la desincorporación
     * @param boolean|null $all Determina si se obtiene toda la información
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getDisincorporationRequestDocuments($id, $all = null)
    {
        $AssetDisincorporation = AssetDisincorporation::where(['id' => $id])
            ->with('documents', 'images')->first();

        $docs = $AssetDisincorporation->documents ?? null;
        $images = $AssetDisincorporation->images ?? null;
        $records = [];
        if (isset($docs)) {
            if (isset($images)) {
                $records = $docs->merge($images);
            } else {
                $records = $docs;
            }
        } elseif (isset($images)) {
            if (isset($docs)) {
                $records = $images->merge($docs);
            } else {
                $records = $images;
            }
        }
        return response()->json(['records' => $records], 200);
    }
    /**
     * Actualiza la información de las desincorporaciones de bienes institucionales
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     \Illuminate\Http\Request         $request    Datos de la petición
     * @param     integer                          $id         Identificador único de la desincorporación
     *
     * @return    \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar
     */
    public function update(Request $request, $id)
    {

        $upImage = new UploadImageRepository();
        $upDoc = new UploadDocRepository();
        $disincorporation = AssetDisincorporation::where(['id' => $id])
            ->with('documents', 'images')->first();

        $this->validate($request, [
            'date' => ['required'],
            'asset_disincorporation_motive_id' => ['required'],
            'observation' => ['required'],
            'authorized_by_id' => ['required'],
            'formed_by_id' => ['required'],
            'produced_by_id' => ['required'],

        ], $this->messages);

        $disincorporation->date = $request->date;
        $disincorporation->asset_disincorporation_motive_id = $request->asset_disincorporation_motive_id;
        $disincorporation->observation = $request->observation;
        $disincorporation->authorized_by_id = $request->authorized_by_id;
        $disincorporation->formed_by_id = $request->formed_by_id;
        $disincorporation->produced_by_id = $request->produced_by_id;
        $disincorporation->save();

        /* Se eliminan los demas elementos de la solicitud */
        $assets_disincorporation = AssetDisincorporationAsset::where('asset_disincorporation_id', $disincorporation->id)
            ->get();

        foreach ($assets_disincorporation as $asset_disincorporation) {
            $asset = Asset::find($asset_disincorporation->asset_id);
            $asset->asset_status_id = 10;
            $asset->save();

            $asset_disincorporation->delete();
        }

        /* Se agregan los nuevos elementos a la solicitud */
        foreach ($request->assets as $asset_id) {
            $asset = Asset::find($asset_id);
            $asset->asset_status_id = 11;
            $asset->save();
            $asset_disincorporation = AssetDisincorporationAsset::Create([
                'asset_id' => $asset->id,
                'asset_disincorporation_id' => $disincorporation->id,
            ]);
        }

        /* Se guardan los docmentos, según sea el tipo (imágenes y/o documentos)*/
        $documentFormat = ['doc', 'docx', 'pdf', 'odt'];
        $imageFormat = ['jpeg', 'jpg', 'png'];

        if ($request->has('files')) {
            if (count($disincorporation->documents) > 0) {
                foreach ($disincorporation->documents as $key) {
                    Storage::disk('documents')->delete($key->file);
                }
                Document::where(['documentable_type' => 'Modules\Asset\Models\AssetDisincorporation', 'documentable_id' => $disincorporation->id])->delete();
            }
            if (count($disincorporation->images) > 0) {
                foreach ($disincorporation->images as $key) {
                    Storage::disk('pictures')->delete($key->file);
                }
                Image::where(['imageable_type' => 'Modules\Asset\Models\AssetDisincorporation', 'imageable_id' => $disincorporation->id])->delete();
            }

            foreach ($request->file('files') as $file) {
                $extensionFile = $file->getClientOriginalExtension();

                if (in_array($extensionFile, $documentFormat)) {
                    $upDoc->uploadDoc(
                        $file,
                        'documents',
                        AssetDisincorporation::class,
                        $disincorporation->id
                    );
                } elseif (in_array($extensionFile, $imageFormat)) {
                    $upImage->uploadImage(
                        $file,
                        'pictures',
                        AssetDisincorporation::class,
                        $disincorporation->id
                    );
                }
            }
        }

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('asset.disincorporation.index')], 200);
    }

    /**
     * Elimina una desincorporación de bienes institucionales
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     \Modules\Asset\Models\AssetDisincorporation    $disincorporation    Datos de la desincorporación
     *                                                                                de un bien
     * @return    \Illuminate\Http\JsonResponse                  Objeto con los registros a mostrar
     */
    public function destroy(AssetDisincorporation $disincorporation)
    {
        $assets_disincorporation_assets = AssetDisincorporationAsset::where('asset_disincorporation_id', $disincorporation->id)->get();

        foreach ($assets_disincorporation_assets as $assets_disincorporation) {
            $asset = Asset::find($assets_disincorporation->asset_id);
            $asset->asset_status_id = 10;
            $asset->save();

            $assets_disincorporation->delete();
        }
        $disincorporation->delete();
        return response()->json(['message' => 'destroy'], 200);
    }

    /**
     * Vizualiza la información de la desincorporación de un bien institucional
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     * @param     integer                          $id    Identificador único de la desincorporación
     * @return    \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar
     */
    public function vueInfo($id)
    {
        $disincorporation = AssetDisincorporation::where('id', $id)
            ->with([
                'assetDisincorporationMotive','documentStatus', 'assetDisincorporationAssets' =>
                function ($query) {
                    $query->with(['asset' =>
                        function ($query) {
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
                        }]);
                },
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
            ])->first();

        return response()->json(['records' => $disincorporation], 200);
    }

    /**
     * Carga la información de la desincorporación de un bien
     *
     * @param string $ids
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function loadInfo($ids)
    {
        $ids = explode(',', $ids);
        $disincorporation = Asset::query()
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
                'assetDisincorporationAsset.assetDisincorporation.institution' => function ($query) {
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
            ->whereHas('assetDisincorporationAsset', function ($query) use ($ids) {
                $query->whereIn('asset_disincorporation_id', $ids);
            });
            $disincorporation = AssetAsignationResource::collection($disincorporation->get());

        return response()->json(['records' => $disincorporation], 200);
    }

    /**
     * Otiene un listado de las desincorporaciones registradas
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar
     */
    public function vueList(Request $request)
    {
        $user_profile = Profile::where('user_id', auth()->user()->id)->first();
        $institution_id = isset($user_profile->institution_id)
        ? $user_profile->institution_id
        : null;

        if (auth()->user()->isAdmin()) {
            $assetDisincorporations = AssetDisincorporation::query()
            ->search($request->query('query'))
            ->with(
                'assetDisincorporationMotive',
                'documentStatus'
            )
            ->orderBy('id');
        } else {
            $assetDisincorporations = AssetDisincorporation::query()
            ->search($request->query('query'))
            ->where('institution_id', $institution_id)
            ->with('assetDisincorporationMotive', 'documentStatus')
            ->orderBy('id');
        }
        $assetDisincorporations = $assetDisincorporations->paginate((int) request()->limit);
        return response()->json(
            [
                'data' => $assetDisincorporations->items(),
                'count' => $assetDisincorporations->total()
            ],
            200
        );
    }

    /**
     * Otiene un listado de los motivos de las desincorporaciones registradas
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    array    Array con los registros a mostrar
     */
    public function getAssetDisincorporationMotives()
    {
        return template_choices('Modules\Asset\Models\AssetDisincorporationMotive', 'name', '', true);
    }

    /**
     * Método que genera el archivo del acta en formato pdf
     *
     * @author    Francisco J. P. Ruiz <javierrupe19@gmail.com>
     *
     * @param     \Illuminate\Http\Request         $request    Datos de la petición
     * @param     integer                          $id         Identificador único de la asignación
     *
     * @return    \Illuminate\Http\JsonResponse|void    Objeto con los registros a mostrar
     */
    public function managePdf($id)
    {
        $disincorporation = AssetDisincorporation::where('id', $id)
            ->with([
                'assetDisincorporationMotive', 'assetDisincorporationAssets' =>
                function ($query) {
                    $query->with(['asset' =>
                        function ($query) {
                            $query->with(
                                'institution',
                                'assetType',
                                'assetCategory',
                                'assetSubcategory',
                                'assetSpecificCategory',
                                'assetAcquisitionType',
                                'assetCondition',
                                'assetStatus',
                                'assetUseFunction',
                                'assetAsignationAsset.assetAsignation',
                                'assetDepreciationAsset',
                                'assetBook',
                                'currency'
                            );
                        }]);
                },
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
            ])->first()->toArray();

        $data = [];

        $data['action'] = 'Desincorporación';
        $data['institution'] = ($disincorporation['institution_id']) ? $disincorporation['institution']['name'] : 'N/A';
        $data['estate'] = ($disincorporation['institution_id']) ? $disincorporation['institution']['municipality']['estate']['name'] : 'N/A';
        $data['municipality'] = ($disincorporation['institution_id']) ? $disincorporation['institution']['municipality']['name'] : 'N/A';
        $data['address'] = ($disincorporation['institution_id']) ? strip_tags($disincorporation['institution']['legal_address']) : 'N/A';
        $data['fiscal_year'] = ($disincorporation['institution_id']) ? $disincorporation['institution']['fiscal_years'][0]['year'] : 'N/A';

        $date = date_create($disincorporation['date']);

        $data['disincorporation_date'] = ($date) ? date_format($date, "d/m/Y") : 'N/A';

        $data['code'] = $disincorporation['code'];
        $data['observation'] = ($disincorporation['observation']) ? strip_tags($disincorporation['observation']) : 'N/A';
        $data['assets'] = $disincorporation['asset_disincorporation_assets'];
        $data['disincorporation_motive'] = ($disincorporation['asset_disincorporation_motive_id']) ? $disincorporation['asset_disincorporation_motive']['name'] : 'N/A';

        $authorized_by = PayrollStaff::where('id', $disincorporation['authorized_by_id'])->first()->toArray();
        $formed_by = PayrollStaff::where('id', $disincorporation['formed_by_id'])->first()->toArray();
        $produced_by = PayrollStaff::where('id', $disincorporation['produced_by_id'])->first()->toArray();

        $data['authorized_by'] = $authorized_by['first_name'] . ' ' . $authorized_by['last_name'] . ' - ' . $authorized_by['payroll_employment']['payrollPosition']['name'];
        $data['formed_by'] = $formed_by['first_name'] . ' ' . $formed_by['last_name'] . ' - ' . $formed_by['payroll_employment']['payrollPosition']['name'];
        $data['produced_by'] = $produced_by['first_name'] . ' ' . $produced_by['last_name'] . ' - ' . $produced_by['payroll_employment']['payrollPosition']['name'];

        $user_profile = $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();
        $is_admin = $user_profile == null || $user_profile['institution_id'] == null ? true : false;
        /*base para generar el pdf */
        $pdf = new ReportRepository();

        /* Definicion de las caracteristicas generales de la página pdf */
        $institution = null;

        /* Definicion de las caracteristicas generales de la página pdf */
        if ($is_admin) {
            $institution = Institution::find($disincorporation['institution_id']);
        } else {
            $institution = Institution::find($user_profile['institution_id']);
        }

        /* Definición del Nombre y ruta del acata en pdf */
        $filename = 'reporte-de-desincorporacion-de-bienes-' . $data['code'] . '.pdf';

        $pdf->setConfig([
            'institution' => $institution, 'filename' => $filename,
            'orientation' => 'L',
            'urlVerify' => url('/asset/disincorporations/disincorporations-record-pdf/' . $id),
        ]);
        $pdf->setHeader(
            $title = 'Bienes Desincorporados',
            $subTitle = 'Código: ' . $data['code'],
            $hasQR = false,
            $hasBarCode = false,
            $logoAlign = 'L',
            $titleAlign = 'C',
            $subTitleAlign = 'C'
        );
        $pdf->setFooter(true, $institution->name . ' - ' . strip_tags($institution->legal_address));
        $pdf->setBody('asset::pdf.asset_disincorporation', true, [
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
     * @return    \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar
     */
    public function download($code)
    {
        return response()->download(storage_path('reports/' . 'reporte-de-desincorporacion-de-bienes-' . $code . '.pdf'));
    }

    /**
     * Método que permite cambiar el estatus de la desincorporación (document_status)
     *
     * @author    manuel Zambrano <mazbrano@cenditel.gob.ve>
     *
     * @param      Request $request     Identificador único de la asignación
     * @param      integer $id          id del registro a actualizar
     *
     * @return    integer|void valor de retorno que indica si se actualizó el registro
     */
    public function changeDocumentStatus(Request $request, $id)
    {
        $action = $request->action;
        $documentStatusId = $request->document_status_id;
        $newDocumentStatusId = DocumentStatus::where('action', $action)->first()->id;
        $disincorporation = AssetDisincorporation::find($id);
        if ($documentStatusId != $newDocumentStatusId) {
            if ($action != 'RE') {
                $disincorporation->document_status_id = $newDocumentStatusId;
                $disincorporation->save();
                return 1;
            } else {
                $assets = Asset::whereHas('assetDisincorporationAsset', function ($query) use ($id) {
                    $query->where('asset_disincorporation_id', $id);
                })->update(['asset_status_id' => '10']);
                $disincorporation->document_status_id = $newDocumentStatusId;
                $disincorporation->save();
            }
        } else {
            return 0;
        }
    }
}
