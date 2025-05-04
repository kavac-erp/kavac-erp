<?php

namespace Modules\Asset\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Models\Country;
use App\Models\Estate;
use App\Models\RequiredDocument;
use App\Models\Phone;
use App\Models\Contact;
use App\Repositories\UploadDocRepository;
use App\Rules\Rif as RifRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Modules\Asset\Models\AssetDocumentRequiredDocument;
use Modules\Asset\Models\AssetSupplierBranch;
use Modules\Asset\Models\AssetSupplierObject;
use Modules\Asset\Models\AssetSupplierSpecialty;
use Modules\Asset\Models\AssetSupplierType;
use Modules\Asset\Models\AssetSupplier;
use Modules\Asset\Models\City;
use Illuminate\Validation\Rule;
use Nwidart\Modules\Facades\Module;

/**
 * @class      AssetSupplierController
 * @brief      Controlador de Proveedores de bienes
 *
 * Clase que gestiona los Proveedores de bienes
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetSupplierController extends Controller
{
    use ValidatesRequests;

    /**
     * Establece los países
     *
     * @var Country $countries
     */
    protected $countries;

    /**
     * Establece los estados
     *
     * @var Estate $estates
     */
    protected $estates;

    /**
     * Establece las ciudades
     * @var City $cities
     */
    protected $cities;

    /**
     * Establece los proveedores
     *
     * @var AssetSupplier $supplier
     */
    protected $supplier;

    /**
     * Establece los tipos de proveedor
     *
     * @var AssetSupplierType $supplier_types
     */
    protected $supplier_types;

    /**
     * Establece las ramas de los proveedores
     *
     * @var AssetSupplierBranch $supplier_branches
     */
    protected $supplier_branches;

    /**
     * Establece las especialidades de los proveedores
     *
     * @var AssetSupplierSpecialty $supplier_specialties
     */
    protected $supplier_specialties;

    /**
     * Establece los objetos de los proveedores
     *
     * @var AssetSupplierObject $supplier_objects
     */
    protected $supplier_objects;

    /**
     * Establece los documentos requeridos
     *
     * @var RequiredDocument $requiredDocuments
     */
    protected $requiredDocuments;

    /**
     * Establece las cuentas Contables
     *
     * @var array $accounting_accounts
     */
    protected $accounting_accounts;

    /**
     * Método constructor de la clase
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('permission:purchase.supplier.list', ['only' => 'index', 'vueList']);
        // $this->middleware('permission:purchase.supplier.create', ['only' => ['create', 'store']]);
        // $this->middleware('permission:purchase.supplier.edit', ['only' => ['edit', 'update']]);
        // $this->middleware('permission:purchase.supplier.delete', ['only' => 'destroy']);

        $this->countries = template_choices(Country::class);
        $this->estates = template_choices(Estate::class);
        $this->cities = template_choices(City::class);
        $this->supplier = template_choices(AssetSupplier::class);

        $this->supplier_types = template_choices(AssetSupplierType::class);
        $this->supplier_branches = template_choices(AssetSupplierBranch::class);
        $this->supplier_specialties = template_choices(AssetSupplierSpecialty::class);
        if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
            $this->accounting_accounts = template_choices(
                \Modules\Accounting\Models\AccountingAccount::class,
                ['code', '-', 'denomination' ],
                ['active' => 't'],
                false
            );
        }

        $supplier_objects = ['Bienes' => [], 'Obras' => [], 'Servicios' => []];
        $assets = $works = $services = [];

        foreach (AssetSupplierObject::all() as $so) {
            $type = ($so->type === 'B') ? 'Bienes' : (($so->type === 'O') ? 'Obras' : 'Servicios');
            $supplier_objects[$type][$so->id] = $so->name;
        }

        $this->supplier_objects = $supplier_objects;
        $this->requiredDocuments = RequiredDocument::where(['model' => 'supplier', 'module' => 'purchase'])->get();
    }

    /**
     * Muestra la lista de Proveedores de bienes
     *
     * @return Renderable
     */
    public function index()
    {
        return view('asset::suppliers.list');
    }

    /**
     * Muestra el formulario para crear un nuevo Proveedor de bienes
     *
     * @return Renderable
     */
    public function create()
    {
        $header = [
            'route' => 'asset.suppliers.store',
            'method' => 'POST',
            'role' => 'form',
            'enctype' => 'multipart/form-data'
        ];

        if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
            $accountings = \Modules\Accounting\Models\AccountingAccount::where(['active' => true])
            ->orderBy('group')
            ->orderBy('subgroup')
            ->orderBy('item')
            ->orderBy('generic')
            ->orderBy('specific')
            ->orderBy('subspecific')
            ->orderBy('institutional')
            ->toBase()->get();
            $options = ['' => 'Seleccione...'];
            /* Arreglo con el listado de opciones de cuentas patrimoniales a seleccionar */
            foreach ($accountings as $rec) {
                $options[$rec->id] = "{$rec->group}.{$rec->subgroup}.{$rec->item}.{$rec->generic}.{$rec->specific}.{$rec->subspecific}.{$rec->institutional} - {$rec->denomination}";
            }
        } else {
            $options = ['' => 'Seleccione...'];
        }
        return view('asset::suppliers.create-edit-form', [
            'countries' => $this->countries, 'estates' => $this->estates, 'cities' => $this->cities,
            'supplier_types' => $this->supplier_types, 'supplier_objects' => $this->supplier_objects,
            'supplier_branches' => $this->supplier_branches, 'supplier_specialties' => $this->supplier_specialties,
            'header' => $header, 'requiredDocuments' => $this->requiredDocuments,
            'accounting_accounts' => $options
        ]);
    }

    /**
     * Almacena un nuevo Proveedor de bienes
     *
     * @param  Request $request Datos de la petición
     * @param  UploadDocRepository $upDoc Repositorio para la gestión de documentos
     *
     * @return Renderable
     */
    public function store(Request $request, UploadDocRepository $upDoc)
    {
        /*
         * Validación de que el rif que viene por $request sea único en la base
         * de datos tomando en cuenta mayúsculas y minúsculas.
         */
        $rifSupplier = AssetSupplier::whereRaw("LOWER(rif) = ?", strtolower($request->rif))->first();

        /*
         * Validación de que el rif que viene por $request y el tipo de persona
         * coincidan.
         */
        $primeraLetraRif = '';
        if ($request->rif) {
            $primeraLetraRif = substr($request->rif, 0, 1);
        }
        if ($request->person_type == 'N' && $primeraLetraRif == 'V' || $primeraLetraRif == 'v') {
            $validateTypePersonRif = true;
        } elseif ($request->person_type == 'J' && $primeraLetraRif == 'J' || $primeraLetraRif == 'j') {
            $validateTypePersonRif = true;
        } elseif ($request->person_type == 'G' && $primeraLetraRif == 'G' || $primeraLetraRif == 'g') {
            $validateTypePersonRif = true;
        } elseif ($request->person_type == 'E' && $primeraLetraRif == 'E' || $primeraLetraRif == 'e') {
            $validateTypePersonRif = true;
        } else {
            $validateTypePersonRif = false;
        }

        $rules = [
            'person_type'                    => ['required'],
            'company_type'                   => ['required'],
            'rif'                            => [
                'required',
                'size:10',
                new RifRule(),
                'unique:asset_suppliers,rif,' . $request->rif,
                Rule::callback(function ($attribute, $value, $fail) use ($validateTypePersonRif) {
                    if (!$validateTypePersonRif) {
                        $fail('El tipo de persona y el rif introducido no coinciden.');
                    }
                }),
            ],
            'name'                           => ['required'],
            'asset_supplier_type_id'      => ['required'],
            'asset_supplier_object_id'    => ['required'],
            'asset_supplier_branch_id'    => ['required'],
            'asset_supplier_specialty_id' => ['required'],
            'country_id'                     => ['required'],
            'estate_id'                      => ['required'],
            'city_id'                        => ['required'],
            'direction'                      => ['required'],
            'rnc_certificate_number'         => ['required_with:rnc_status'],
            'contact_names'                  => ['array'],
            'contact_emails'                 => ['array'],
            'phone_type'                     => ['array'],
            'phone_area_code'                => ['array'],
            'phone_number'                   => ['array'],
            'phone_extension'                => ['sometimes', 'array'],
        ];

        $messages = [
            'person_type.required'                    => 'El campo tipo de persona es obligatorio.',
            'company_type.required'                   => 'El campo tipo de empresa es obligatorio.',
            'rif.required'                            => 'El campo rif es obligatorio.',
            'rif.unique'                              => 'El campo rif ya ha sido registrado.',
            'name.required'                           => 'El campo nombre es obligatorio.',
            'asset_supplier_type_id.required'      => 'El campo denominación comercial es obligatorio.',
            'asset_supplier_object_id.required'    => 'El campo objeto principal es obligatorio.',
            'asset_supplier_branch_id.required'    => 'El campo rama es obligatorio.',
            'asset_supplier_specialty_id.required' => 'El campo especialidad es obligatorio.',
            'country_id.required'                     => 'El campo país es obligatorio.',
            'estate_id.required'                      => 'El campo estado es obligatorio.',
            'city_id.required'                        => 'El campo ciudad es obligatorio.',
            'direction.required'                      => 'El campo dirección fiscal es obligatorio.',
            'rnc_certificate_number.required_with'    => 'El campo número de certificado es obligatorio cuando situación actual este presente',
            'empty_contact_info.required'             => 'Los campos de datos de contacto son obligatorios.',
            'empty_phone_info.required'               => 'Los campos de nùmeros telefònicos son obligatorios.',
        ];

        /* Se verifica que no tenga informaciòn en los campos de nùmeros telefònicos */
        if (array_key_exists("phone_type", $request->all())) {
            foreach ($request->phone_type as $key => $value) {
                if (!$value || !$request->phone_area_code[$key] || !$request->phone_number[$key]) {
                    $rules['empty_phone_info'] = ['required'];
                    $request->merge(['empty_phone_info' => null]);
                    break;
                }
            }
        }

        /* Se verifica que no tenga informaciòn en los campos de contacto */
        if (array_key_exists("contact_names", $request->all())) {
            foreach ($request->contact_names as $key => $value) {
                if (!$value || !$request->contact_emails[$key]) {
                    $rules['empty_contact_info'] = ['required'];
                    $request->merge(['empty_contact_info' => null]);
                    break;
                }
            }
        }

        $this->validate($request, $rules, $messages);

        $supplier = AssetSupplier::create([
            'person_type'                    => $request->person_type,
            'company_type'                   => $request->company_type,
            'rif'                            => $request->rif,
            'code'                           => generate_code(AssetSupplier::class, 'code'),
            'name'                           => $request->name,
            'direction'                      => $request->direction,
            'website'                        => $request->website ?? null,
            'active'                         => $request->active ? true : false,
            'asset_supplier_type_id'      => $request->asset_supplier_type_id,
            'accounting_account_id'          => $request->accounting_account_id,
            'country_id'                     => $request->country_id,
            'estate_id'                      => $request->estate_id,
            'city_id'                        => $request->city_id,
            'rnc_status'                     => $request->rnc_status ?? 'NOI',
            'rnc_certificate_number'         => $request->rnc_certificate_number ?? null,
            'social_purpose'                 => $request->social_purpose,
        ]);

        /* sincroniza la relacion en la tabla pivote de asset_object_supplier */
        $supplier->assetSupplierObjects()->sync($request->asset_supplier_object_id);

        /* sincroniza la relacion en la tabla pivote de asset_branch_supplier */
        $supplier->assetSupplierBranch()->sync($request->asset_supplier_branch_id);

        /* sincroniza la relacion en la tabla pivote de asset_specialty_supplier */
        $supplier->assetSupplierSpecialty()->sync($request->asset_supplier_specialty_id);


        /* Registros asociados a contactos */
        if ($request->contact_names && !empty($request->contact_names)) {
            foreach ($request->contact_names as $key => $contact) {
                $supplier->contacts()->save(new Contact([
                    'name' => $request->contact_names[$key],
                    'email' => $request->contact_emails[$key],
                ]));
            }
        }

        /* Asociación de números telefónicos */
        if ($request->phone_type && !empty($request->phone_type)) {
            foreach ($request->phone_type as $key => $phone_type) {
                $supplier->phones()->save(new Phone([
                    'type' => $phone_type,
                    'area_code' => $request->phone_area_code[$key],
                    'number' => $request->phone_number[$key],
                    'extension' => $request->phone_extension[$key] ?? null
                ]));
            }
        }

        /* Registro y asociación de documentos */
        $documentFormat = ['doc', 'docx', 'pdf', 'odt'];
        if ($request->file('docs')) {
            foreach ($request->file('docs') as $key => $file) {
                $extensionFile = $file->getClientOriginalExtension();

                if (in_array($extensionFile, $documentFormat)) {
                    /* Se guarda el archivo y se almacena */
                    $upDoc->uploadDoc(
                        $file,
                        'documents',
                        AssetSupplier::class,
                        $supplier->id
                    );
                    /* Se almacena la relacion entre documentos y documentos requeridos en tabla pivote */
                    if ($upDoc->getDocStored()) {
                        AssetDocumentRequiredDocument::create([
                            'document_id' => $upDoc->getDocStored()->id,
                            'required_document_id' => $request->reqDocs[$key],
                        ]);
                    }
                }
            }
        }
        session()->flash('message', ['type' => 'store']);
        return redirect()->route('asset.suppliers.index');
    }

    /**
     * Obtiene información de un proveedor de bienes
     *
     * @return JsonResponse
     */
    public function show($id)
    {
        return response()->json([
            'records' => AssetSupplier::with(
                'documents.assetDocumentRequiredDocument.requiredDocument'
            )->find($id)
        ], 200);
    }

    /**
     * Obtiene un listado de proveedores de bienes
     *
     * @return array
     */
    public function showall()
    {
        return template_choices(AssetSupplier::class, 'name', '', true);
    }

    /**
     * Muestra el formulario de edición de un proveedor de bienes
     *
     * @return Renderable
     */
    public function edit($id)
    {
        $model = AssetSupplier::with('documents.assetDocumentRequiredDocument')->find($id);
        /*
         * Variable para almacenar los identificadores de documentos existentes
         * para habilitar su descarga
         */
        $docs_to_download = [];

        foreach ($model->documents as $doc) {
            if ($doc->assetDocumentRequiredDocument) {
                $docs_to_download["req_doc_" . $doc->assetDocumentRequiredDocument->required_document_id] = $doc;
            }
        }

        $asset_supplier_objects = [];
        $asset_supplier_branch = [];
        $asset_supplier_specialty = [];

        foreach ($model->assetSupplierObjects as $record) {
            array_push($asset_supplier_objects, $record->id);
        }
        foreach ($model->assetSupplierBranch as $record) {
            array_push($asset_supplier_branch, $record->id);
        }
        foreach ($model->assetSupplierSpecialty as $record) {
            array_push($asset_supplier_specialty, $record->id);
        }

        $header = [
            'route' => ['asset.suppliers.update', $model->id],
            'method' => 'PUT',
            'role' => 'form',
            'enctype' => 'multipart/form-data'
        ];

        return view('asset::suppliers.create-edit-form', [
            'countries' => $this->countries, 'estates' => $this->estates, 'cities' => $this->cities,
            'supplier_types' => $this->supplier_types, 'supplier_objects' => $this->supplier_objects,
            'supplier_branches' => $this->supplier_branches, 'supplier_specialties' => $this->supplier_specialties,
            'model_supplier_branches' => $asset_supplier_branch,
            'model_supplier_specialties' => $asset_supplier_specialty,
            'header' => $header, 'requiredDocuments' => $this->requiredDocuments, 'model' => $model,
            'model_supplier_objects' => $asset_supplier_objects, 'docs_to_download' => $docs_to_download,
            'accounting_accounts' => $this->accounting_accounts
        ]);
    }

    /**
     * Actualiza la información de un proveedor de bienes
     *
     * @param  Request $request Datos de la petición
     * @param  UploadDocRepository $upDoc Repositorio para la gestión de documentos
     * @param  $id ID del proveedor
     *
     * @return Response
     */
    public function update(Request $request, UploadDocRepository $upDoc, $id)
    {
        $rules = [
            'person_type'                    => ['required'],
            'company_type'                   => ['required'],
            'rif'                            => ['required', 'size:10', new RifRule(), 'unique:purchase_suppliers,rif,' . $id],
            'name'                           => ['required'],
            'asset_supplier_type_id'      => ['required'],
            'asset_supplier_object_id'    => ['required'],
            'asset_supplier_branch_id'    => ['required'],
            'asset_supplier_specialty_id' => ['required'],
            'country_id'                     => ['required'],
            'estate_id'                      => ['required'],
            'city_id'                        => ['required'],
            'direction'                      => ['required'],
            'rnc_certificate_number'         => ['required_with:rnc_status'],
            'contact_names'                  => ['array'],
            'contact_emails'                 => ['array'],
            'phone_type'                     => ['array'],
            'phone_area_code'                => ['array'],
            'phone_number'                   => ['array'],
            'phone_extension'                => ['sometimes', 'array'],
        ];

        $messages = [
            'person_type.required'                    => 'El campo tipo de persona es obligatorio.',
            'company_type.required'                   => 'El campo tipo de empresa es obligatorio.',
            'rif.required'                            => 'El campo rif es obligatorio.',
            'name.required'                           => 'El campo nombre es obligatorio.',
            'asset_supplier_type_id.required'      => 'El campo denominación comercial es obligatorio.',
            'asset_supplier_object_id.required'    => 'El campo objeto principal es obligatorio.',
            'asset_supplier_branch_id.required'    => 'El campo rama es obligatorio.',
            'asset_supplier_specialty_id.required' => 'El campo especialidad es obligatorio.',
            'country_id.required'                     => 'El campo país es obligatorio.',
            'estate_id.required'                      => 'El campo estado es obligatorio.',
            'city_id.required'                        => 'El campo ciudad es obligatorio.',
            'direction.required'                      => 'El campo dirección fiscal es obligatorio.',
            'empty_contact_info.required'             => 'Los campos de datos de contacto son obligatorios.',
            'empty_phone_info.required'               => 'Los campos de nùmeros telefònicos son obligatorios.',
        ];

        /* Se verifica que no tenga informaciòn en los campos de nùmeros telefónicos */
        if (array_key_exists("phone_type", $request->all())) {
            foreach ($request->phone_type as $key => $value) {
                if (!$value || !$request->phone_area_code[$key] || !$request->phone_number[$key]) {
                    $rules['empty_phone_info'] = ['required'];
                    $request->merge(['empty_phone_info' => null]);
                    break;
                }
            }
        }

        /* Se verifica que no tenga informaciòn en los campos de contacto */
        if (array_key_exists("contact_names", $request->all())) {
            foreach ($request->contact_names as $key => $value) {
                if (!$value || !$request->contact_emails[$key]) {
                    $rules['empty_contact_info'] = ['required'];
                    $request->merge(['empty_contact_info' => null]);
                    break;
                }
            }
        }
        $this->validate($request, $rules, $messages);
        $supplier = AssetSupplier::find($id);
        $supplier->person_type                    = $request->person_type;
        $supplier->company_type                   = $request->company_type;
        $supplier->rif                            = $request->rif;
        $supplier->name                           = $request->name;
        $supplier->direction                      = $request->direction;
        $supplier->website                        = $request->website ?? null;
        $supplier->active                         = $request->active ? true : false;
        $supplier->asset_supplier_type_id         = $request->asset_supplier_type_id;
        $supplier->accounting_account_id          = $request->accounting_account_id;
        $supplier->country_id                     = $request->country_id;
        $supplier->estate_id                      = $request->estate_id;
        $supplier->city_id                        = $request->city_id;
        $supplier->rnc_status                     = $request->rnc_status ?? 'NOI';
        $supplier->rnc_certificate_number         = $request->rnc_certificate_number ?? null;
        $supplier->social_purpose                 = $request->social_purpose;
        $supplier->save();

        /* sincroniza la relacion en la tabla pivote de asset_object_supplier */
        $supplier->assetSupplierObjects()->sync($request->asset_supplier_object_id);

        /* sincroniza la relacion en la tabla pivote de asset_branch_supplier */
        $supplier->assetSupplierBranch()->sync($request->asset_supplier_branch_id);

        /* sincroniza la relacion en la tabla pivote de asset_specialty_supplier */
        $supplier->assetSupplierSpecialty()->sync($request->asset_supplier_specialty_id);

        /* Se elimina la relacion de proveedor con los contactos anteriores */
        $supp_contacts = $supplier->contacts()->forceDelete();

        /* Registros asociados a contactos */
        if ($request->contact_names && !empty($request->contact_names)) {
            foreach ($request->contact_names as $key => $contact) {
                $supplier->contacts()->save(new Contact([
                    'name' => $request->contact_names[$key],
                    'email' => $request->contact_emails[$key],
                ]));
            }
        }

        /* Se elimina la relacion de proveedor con los telefonos anteriores **/
        $supp_ph = $supplier->phones()->forceDelete();

        /* Asociación de números telefónicos */
        if ($request->phone_type && !empty($request->phone_type)) {
            foreach ($request->phone_type as $key => $phone_type) {
                $supplier->phones()->save(new Phone([
                    'type' => $phone_type,
                    'area_code' => $request->phone_area_code[$key],
                    'number' => $request->phone_number[$key],
                    'extension' => $request->phone_extension[$key] ?? null
                ]));
            }
        }

        /* Se elimina la relacion y los documentos previos **/
        $supp_docs = $supplier->documents()->with('assetDocumentRequiredDocument')->get();
        if (count($supp_docs) > 0) {
            foreach ($supp_docs as $doc) {
                $upDoc->deleteDoc(
                    $doc->file,
                    'documents'
                );
                if (
                    isset($doc->assetDocumentRequiredDocument)
                    && in_array($doc->assetDocumentRequiredDocument->required_document_id, $request->reqDocs)
                ) {
                    $purDocReqDoc = AssetDocumentRequiredDocument::where('document_id', $doc->id)->first();
                    if ($purDocReqDoc) {
                        $purDocReqDoc->delete();
                    }
                }
                $doc->delete();
            }
        }

        /* Registro y asociación de documentos */
        $documentFormat = ['doc', 'docx', 'pdf', 'odt'];
        if ($request->file('docs')) {
            foreach ($request->file('docs') as $key => $file) {
                $extensionFile = $file->getClientOriginalExtension();

                if (in_array($extensionFile, $documentFormat)) {
                    /* Se guarda el archivo y se almacena */
                    $upDoc->uploadDoc(
                        $file,
                        'documents',
                        AssetSupplier::class,
                        $supplier->id
                    );
                    /* Se almacena la relacion entre documentos y documentos requeridos en tabla pivote */
                    if ($upDoc->getDocStored()) {
                        AssetDocumentRequiredDocument::create([
                            'document_id' => $upDoc->getDocStored()->id,
                            'required_document_id' => $request->reqDocs[$key],
                        ]);
                    }
                }
            }
        }
        session()->flash('message', ['type' => 'store']);
        return redirect()->route('asset.suppliers.index');
    }

    /**
     * Elimina el proveedor de bienes
     *
     * @param UploadDocRepository $upDoc Repositorio para la gestión de documentos
     *
     * @return JsonResponse
     */
    public function destroy(UploadDocRepository $upDoc, $id)
    {
        /* Objeto con la información asociada al modelo AssetSupplier */
        $supplier = AssetSupplier::with('assetOrder')->find($id);

        if ($supplier && count($supplier->assetOrder) > 0) {
            return response()->json([
                'error'   => true,
                'message' => 'El registro no se puede eliminar, debido a que esta siendo usado por ordenes de compra.'
            ], 200);
        }
        if ($supplier) {
            /* Se elimina la relacion de proveedor con los contactos anteriores */
            $supp_contacts = $supplier->contacts()->get();
            if (count($supp_contacts) > 0) {
                foreach ($supp_contacts as $value) {
                    $value->delete();
                }
            }

            /* Se elimina la relacion de proveedor con los telefonos anteriores */
            $supp_ph = $supplier->phones()->get();
            if (count($supp_ph) > 0) {
                foreach ($supp_ph as $value) {
                    $value->delete();
                }
            }

            /* Se elimina la relacion y los documentos previos */
            $supp_docs = $supplier->documents()->get();
            if (count($supp_docs) > 0) {
                foreach ($supp_docs as $doc) {
                    $upDoc->deleteDoc($doc->file, 'documents');
                    $purDocReqDoc = AssetDocumentRequiredDocument::where('document_id', $doc->id)->first();
                    if ($purDocReqDoc) {
                        $purDocReqDoc->delete();
                    }
                    $doc->delete();
                }
            }
            $supplier->delete();
        }
        return response()->json([
            'records' => AssetSupplier::orderBy('id')->get(),
            'message' => 'Success'
        ], 200);
    }

    /**
     * Obtiene listado de registros
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueList()
    {
        return response()->json(['records' => AssetSupplier::all()], 200);
    }

    /**
     * Migración de los datos Rama y Especialización a las tablas pivotes
     *
     * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
     *
     * @return void
     */
    public function dataMigratePivote()
    {
        //
    }
}
