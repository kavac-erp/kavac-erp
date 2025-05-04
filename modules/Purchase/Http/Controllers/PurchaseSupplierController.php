<?php

namespace Modules\Purchase\Http\Controllers;

use App\Models\Phone;
use App\Models\Estate;
use App\Models\Contact;
use App\Models\Country;
use Illuminate\Http\Request;
use App\Rules\Rif as RifRule;
use App\Models\RequiredDocument;
use Modules\Purchase\Models\City;
use Illuminate\Routing\Controller;
use Nwidart\Modules\Facades\Module;
use App\Repositories\UploadDocRepository;
use Modules\Purchase\Models\PurchaseSupplier;
use Modules\Purchase\Models\PurchaseSupplierType;
use Modules\Purchase\Models\PurchaseSupplierBranch;
use Modules\Purchase\Models\PurchaseSupplierObject;
use Modules\Purchase\Models\PurchaseSupplierSpecialty;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Purchase\Models\PurchaseDocumentRequiredDocument;

/**
 * @class PurchaseSupplierController
 * @brief Gestiona los procesos para los registros de proveedores
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseSupplierController extends Controller
{
    use ValidatesRequests;

    /**
     * Listado de Paises
     *
     * @var array $countries
     */
    protected $countries;

    /**
     * Listado de Estados
     *
     * @var array $estates
     */
    protected $estates;

    /**
     * Listado de Ciudades
     *
     * @var array $cities
     */
    protected $cities;

    /**
     * Listado de Proveedores
     *
     * @var array $supplier
     */
    protected $supplier;

    /**
     * Listado de tipos de proveedores
     *
     * @var array $supplier_types
     */
    protected $supplier_types;

    /**
     * Listado de ramas de proveedores
     *
     * @var array $supplier_branches
     */
    protected $supplier_branches;

    /**
     * Listado de especialidades de proveedores
     *
     * @var array $supplier_specialties
     */
    protected $supplier_specialties;

    /**
     * Listado de objetos de proveedores
     *
     * @var array $supplier_objects
     */
    protected $supplier_objects;

    /**
     * Listado de requerimientos de documentos
     *
     * @var array $requiredDocuments
     */
    protected $requiredDocuments;

    /**
     * Listado de cuentas contables
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
        $this->middleware('permission:purchase.supplier.list', ['only' => 'index', 'vueList']);
        $this->middleware('permission:purchase.supplier.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:purchase.supplier.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:purchase.supplier.delete', ['only' => 'destroy']);

        $this->countries = template_choices(Country::class);
        $this->estates = template_choices(Estate::class);
        $this->cities = template_choices(City::class);
        $this->supplier = template_choices(PurchaseSupplier::class);

        $this->supplier_types = template_choices(PurchaseSupplierType::class);
        $this->supplier_branches = template_choices(PurchaseSupplierBranch::class);
        $this->supplier_specialties = template_choices(PurchaseSupplierSpecialty::class);
        $this->accounting_accounts = (
            Module::has('Accounting') && Module::isEnabled('Accounting')
        ) ? template_choices(
            \Modules\Accounting\Models\AccountingAccount::class,
            ['code', '-', 'denomination' ],
            ['active' => 't'],
            false
        ) : ['' => 'Seleccione...'];

        $supplier_objects = ['Bienes' => [], 'Obras' => [], 'Servicios' => []];
        $assets = $works = $services = [];

        foreach (PurchaseSupplierObject::all() as $so) {
            $type = ($so->type === 'B') ? 'Bienes' : (($so->type === 'O') ? 'Obras' : 'Servicios');
            $supplier_objects[$type][$so->id] = $so->name;
        }

        $this->supplier_objects = $supplier_objects;
        $this->requiredDocuments = RequiredDocument::where(['model' => 'supplier', 'module' => 'purchase'])->get();
    }

    /**
     * Muestra el listado de proveedores
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('purchase::suppliers.list');
    }

    /**
     * Muestra el formulario para registrar un nuevo proveedor
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $header = [
            'route' => 'purchase.suppliers.store',
            'method' => 'POST',
            'role' => 'form',
            'enctype' => 'multipart/form-data'
        ];

        /* Arreglo con el listado de opciones de cuentas patrimoniales a seleccionar */
        $options = ['' => 'Seleccione...'];

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

            foreach ($accountings as $rec) {
                $options[$rec->id] = "{$rec->group}.{$rec->subgroup}.{$rec->item}.{$rec->generic}.{$rec->specific}.{$rec->subspecific}.{$rec->institutional} - {$rec->denomination}";
            }
        }
        return view('purchase::suppliers.create-edit-form', [
            'countries' => $this->countries,
            'estates' => $this->estates,
            'cities' => $this->cities,
            'supplier_types' => $this->supplier_types,
            'supplier_objects' => $this->supplier_objects,
            'supplier_branches' => $this->supplier_branches,
            'supplier_specialties' => $this->supplier_specialties,
            'header' => $header,
            'requiredDocuments' => $this->requiredDocuments,
            'accounting_accounts' => $options
        ]);
    }

    /**
     * Almacena un nuevo proveedor
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, UploadDocRepository $upDoc)
    {
        /*
         | Validación de que el rif que viene por $request sea único en la base
         | de datos tomando en cuenta mayúsculas y minúsculas.
         */
        $rifSupplier = PurchaseSupplier::whereRaw("LOWER(rif) = ?", strtolower($request->rif))->first();

        /*
         | Validación de que el rif que viene por $request y el tipo de persona
         | coincidan.
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
                $rifSupplier ? function ($attribute, $value, $fail) {
                    $fail('El campo rif ya ha sido registrado.');
                } : [],
                !$validateTypePersonRif ? function ($attribute, $value, $fail) {
                    $fail('El tipo de persona y el rif introducido no coinciden.');
                } : [],
            ],
            'name'                           => ['required'],
            'file_number'                    => ['required', 'unique:purchase_suppliers,file_number'],
            'purchase_supplier_type_id'      => ['required'],
            'purchase_supplier_object_id'    => ['required'],
            'purchase_supplier_branch_id'    => ['required'],
            'purchase_supplier_specialty_id' => ['required'],
            'accounting_account_id'          => ['required'],
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
            'file_number.required'                    => 'El campo número de expediente es obligatorio.',
            'file_number.unique'                      => 'El número de expediente ya ha sido registrado.',
            'purchase_supplier_type_id.required'      => 'El campo denominación comercial es obligatorio.',
            'purchase_supplier_object_id.required'    => 'El campo objeto principal es obligatorio.',
            'purchase_supplier_branch_id.required'    => 'El campo rama es obligatorio.',
            'purchase_supplier_specialty_id.required' => 'El campo especialidad es obligatorio.',
            'accounting_account_id.required'          => 'El campo cuentas contables es obligatorio.',
            'country_id.required'                     => 'El campo país es obligatorio.',
            'estate_id.required'                      => 'El campo estado es obligatorio.',
            'city_id.required'                        => 'El campo ciudad es obligatorio.',
            'direction.required'                      => 'El campo dirección fiscal es obligatorio.',
            'rnc_certificate_number.required_with'    => 'El campo número de certificado es obligatorio cuando situación actual este presente',
            'empty_contact_info.required'             => 'Los campos de datos de contacto son obligatorios.',
            'empty_phone_info.required'               => 'Los campos de nùmeros telefònicos son obligatorios.',
        ];

        /* Se verifica que no tenga información en los campos de nùmeros telefónicos */
        if (array_key_exists("phone_type", $request->all())) {
            foreach ($request->phone_type as $key => $value) {
                if (!$value || !$request->phone_area_code[$key] || !$request->phone_number[$key]) {
                    $rules['empty_phone_info'] = ['required'];
                    $request->merge(['empty_phone_info' => null]);
                    break;
                }
            }
        }

        /* Se verifica que no tenga información en los campos de contacto */
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

        $supplier = PurchaseSupplier::create([
            'person_type'                    => $request->person_type,
            'company_type'                   => $request->company_type,
            'rif'                            => $request->rif,
            'code'                           => generate_code(PurchaseSupplier::class, 'code'),
            'name'                           => $request->name,
            'file_number'                    => $request->file_number,
            'direction'                      => $request->direction,
            'website'                        => $request->website ?? null,
            'active'                         => $request->active ? true : false,
            'purchase_supplier_type_id'      => $request->purchase_supplier_type_id,
            'accounting_account_id'          => $request->accounting_account_id,
            'country_id'                     => $request->country_id,
            'estate_id'                      => $request->estate_id,
            'city_id'                        => $request->city_id,
            'rnc_status'                     => $request->rnc_status ?? 'NOI',
            'rnc_certificate_number'         => $request->rnc_certificate_number ?? null,
            'social_purpose'                 => $request->social_purpose,
        ]);

        /* sincroniza la relacion en la tabla pivote de purchase_object_supplier */
        $supplier->purchaseSupplierObjects()->sync($request->purchase_supplier_object_id);

        /* sincroniza la relacion en la tabla pivote de purchase_branch_supplier */
        $supplier->purchaseSupplierBranch()->sync($request->purchase_supplier_branch_id);

        /* sincroniza la relacion en la tabla pivote de purchase_specialty_supplier */
        $supplier->purchaseSupplierSpecialty()->sync($request->purchase_supplier_specialty_id);


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
                        PurchaseSupplier::class,
                        $supplier->id
                    );
                    /* Se almacena la relacion entre documentos y documentos requeridos en tabla pivote */
                    if ($upDoc->getDocStored()) {
                        PurchaseDocumentRequiredDocument::create([
                            'document_id' => $upDoc->getDocStored()->id,
                            'required_document_id' => $request->reqDocs[$key],
                        ]);
                    }
                }
            }
        }
        session()->flash('message', ['type' => 'store']);
        return redirect()->route('purchase.suppliers.index');
    }

    /**
     * Muestra información de un proveedor
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        return response()->json([
            'records' => PurchaseSupplier::with(
                'documents.purchaseDocumentRequiredDocument.requiredDocument'
            )->find($id)
        ], 200);
    }

    /**
     * Obtiene un listado de proveedores
     *
     * @return array
     */
    public function showall()
    {
        return template_choices(PurchaseSupplier::class, 'name', '', true);
    }

    /**
     * Muestra el formulario para editar un proveedor
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $model = PurchaseSupplier::with('documents.purchaseDocumentRequiredDocument')->find($id);
        /* Variable para almacenar los identificadores de documentos existentes
        para habilitar su descarga */
        $docs_to_download = [];

        foreach ($model->documents as $doc) {
            if ($doc->purchaseDocumentRequiredDocument) {
                $docs_to_download["req_doc_" . $doc->purchaseDocumentRequiredDocument->required_document_id] = $doc;
            }
        }

        $purchase_supplier_objects = [];
        $purchase_supplier_branch = [];
        $purchase_supplier_specialty = [];

        foreach ($model->purchaseSupplierObjects as $record) {
            array_push($purchase_supplier_objects, $record->id);
        }
        foreach ($model->purchaseSupplierBranch as $record) {
            array_push($purchase_supplier_branch, $record->id);
        }
        foreach ($model->purchaseSupplierSpecialty as $record) {
            array_push($purchase_supplier_specialty, $record->id);
        }

        $header = [
            'route' => ['purchase.suppliers.update', $model->id],
            'method' => 'PUT',
            'role' => 'form',
            'enctype' => 'multipart/form-data'
        ];

        return view('purchase::suppliers.create-edit-form', [
            'countries' => $this->countries,
            'estates' => $this->estates,
            'cities' => $this->cities,
            'supplier_types' => $this->supplier_types,
            'supplier_objects' => $this->supplier_objects,
            'supplier_branches' => $this->supplier_branches,
            'supplier_specialties' => $this->supplier_specialties,
            'model_supplier_branches' => $purchase_supplier_branch,
            'model_supplier_specialties' => $purchase_supplier_specialty,
            'header' => $header,
            'requiredDocuments' => $this->requiredDocuments,
            'model' => $model,
            'model_supplier_objects' => $purchase_supplier_objects,
            'docs_to_download' => $docs_to_download,
            'accounting_accounts' => $this->accounting_accounts
        ]);
    }

    /**
     * Actualiza la información de un proveedor
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, UploadDocRepository $upDoc, $id)
    {
        $rules = [
            'person_type'                    => ['required'],
            'company_type'                   => ['required'],
            'rif'                            => ['required', 'size:10', new RifRule(), 'unique:purchase_suppliers,rif,' . $id],
            'name'                           => ['required'],
            'file_number'                    => ['required', 'unique:purchase_suppliers,file_number,' . $id],
            'purchase_supplier_type_id'      => ['required'],
            'purchase_supplier_object_id'    => ['required'],
            'purchase_supplier_branch_id'    => ['required'],
            'purchase_supplier_specialty_id' => ['required'],
            'accounting_account_id'          => ['required'],
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
            'file_number.required'                    => 'El campo número de expediente es obligatorio.',
            'file_number.unique'                      => 'El número de expediente ya se encuentra registrado.',
            'purchase_supplier_type_id.required'      => 'El campo denominación comercial es obligatorio.',
            'purchase_supplier_object_id.required'    => 'El campo objeto principal es obligatorio.',
            'purchase_supplier_branch_id.required'    => 'El campo rama es obligatorio.',
            'purchase_supplier_specialty_id.required' => 'El campo especialidad es obligatorio.',
            'accounting_account_id.required'          => 'El campo cuentas contables es obligatorio.',
            'country_id.required'                     => 'El campo país es obligatorio.',
            'estate_id.required'                      => 'El campo estado es obligatorio.',
            'city_id.required'                        => 'El campo ciudad es obligatorio.',
            'direction.required'                      => 'El campo dirección fiscal es obligatorio.',
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
        $supplier = PurchaseSupplier::find($id);
        $supplier->person_type                    = $request->person_type;
        $supplier->company_type                   = $request->company_type;
        $supplier->rif                            = $request->rif;
        $supplier->code                           = $request->code ?? $supplier->code;
        $supplier->name                           = $request->name;
        $supplier->file_number                    = $request->file_number;
        $supplier->direction                      = $request->direction;
        $supplier->website                        = $request->website ?? null;
        $supplier->active                         = $request->active ? true : false;
        $supplier->purchase_supplier_type_id      = $request->purchase_supplier_type_id;
        $supplier->accounting_account_id          = $request->accounting_account_id;
        $supplier->country_id                     = $request->country_id;
        $supplier->estate_id                      = $request->estate_id;
        $supplier->city_id                        = $request->city_id;
        $supplier->rnc_status                     = $request->rnc_status ?? 'NOI';
        $supplier->rnc_certificate_number         = $request->rnc_certificate_number ?? null;
        $supplier->social_purpose                 = $request->social_purpose;
        $supplier->save();

        /* sincroniza la relacion en la tabla pivote de purchase_object_supplier */
        $supplier->purchaseSupplierObjects()->sync($request->purchase_supplier_object_id);

        /* sincroniza la relacion en la tabla pivote de purchase_branch_supplier */
        $supplier->purchaseSupplierBranch()->sync($request->purchase_supplier_branch_id);

        /* sincroniza la relacion en la tabla pivote de purchase_specialty_supplier */
        $supplier->purchaseSupplierSpecialty()->sync($request->purchase_supplier_specialty_id);

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

        /* Se elimina la relacion de proveedor con los telefonos anteriores */
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

        /* Se elimina la relacion y los documentos previos */
        $supp_docs = $supplier->documents()->with('purchaseDocumentRequiredDocument')->get();
        if (count($supp_docs) > 0) {
            foreach ($supp_docs as $doc) {
                $upDoc->deleteDoc(
                    $doc->file,
                    'documents'
                );
                if (
                    isset($doc->purchaseDocumentRequiredDocument)
                    && in_array($doc->purchaseDocumentRequiredDocument->required_document_id, $request->reqDocs)
                ) {
                    $purDocReqDoc = PurchaseDocumentRequiredDocument::where('document_id', $doc->id)->first();
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
                        PurchaseSupplier::class,
                        $supplier->id
                    );
                    /* Se almacena la relacion entre documentos y documentos requeridos en tabla pivote */
                    if ($upDoc->getDocStored()) {
                        PurchaseDocumentRequiredDocument::create([
                            'document_id' => $upDoc->getDocStored()->id,
                            'required_document_id' => $request->reqDocs[$key],
                        ]);
                    }
                }
            }
        }
        session()->flash('message', ['type' => 'store']);
        return redirect()->route('purchase.suppliers.index');
    }

    /**
     * Elimina un proveedor
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(UploadDocRepository $upDoc, $id)
    {
        /* Objeto con la información asociada al modelo PurchaseSupplier */
        $supplier = PurchaseSupplier::with('purchaseOrder')->find($id);

        if ($supplier && count($supplier->purchaseOrder) > 0) {
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
                    $purDocReqDoc = PurchaseDocumentRequiredDocument::where('document_id', $doc->id)->first();
                    if ($purDocReqDoc) {
                        $purDocReqDoc->delete();
                    }
                    $doc->delete();
                }
            }
            $supplier->delete();
        }
        return response()->json([
            'records' => PurchaseSupplier::orderBy('id')->get(),
            'message' => 'Success'
        ], 200);
    }

    /**
     * Obtiene listado de registros de proveedores
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueList()
    {
        $suppliers = PurchaseSupplier::orderBy('file_number')->orderBy('name')->get();
        return response()->json(['records' => $suppliers], 200);
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
