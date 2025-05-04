<?php

namespace Modules\Accounting\Http\Controllers;

use DateTime;
use App\Models\FiscalYear;
use App\Models\CodeSetting;
use Illuminate\Http\Request;
use App\Models\DocumentStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Rules\DateBeforeFiscalYear;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Date;
use Modules\Accounting\Models\Profile;
use Modules\Accounting\Models\Currency;
use Modules\Accounting\Models\Institution;
use Illuminate\Contracts\Support\Renderable;
use App\Exceptions\ClosedFiscalYearException;
use Modules\Accounting\Models\AccountingEntry;
use Modules\Accounting\Models\AccountingAccount;
use App\Models\Institution as DefaultInstitution;
use Modules\Accounting\Jobs\AccountingManageEntries;
use Modules\Accounting\Models\AccountingEntryAccount;
use Modules\Accounting\Models\AccountingEntryCategory;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * @class AccountingEntryController
 * @brief Controlador para la gestion los asientos contables
 *
 * Clase que gestiona los asientos contables
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AccountingEntryController extends Controller
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
        $this->middleware('permission:accounting.entries.list', ['only' => ['index', 'unapproved']]);
        $this->middleware('permission:accounting.entries.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:accounting.entries.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:accounting.entries.delete', ['only' => 'destroy']);
        // $this->middleware('permission:accounting.entries.approve', ['only' => 'approve']);
        $this->middleware('permission:accounting.entries.reverse', ['only' => 'reverse']);
    }
    /**
     * muestra la vista donde se mostraran los asientos contables aprobados
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return Renderable
     */
    public function index()
    {
        /* contendra la moneda manejada por defecto */
        $currency = Currency::where('default', true)->first();

        $institutions = json_encode($this->getInstitutionAvailables('Todas'));

        /* almacena el registro de asiento contable mas antiguo */
        $entries = AccountingEntry::orderBy('from_date', 'ASC')->first();

        /* almacena el registro de asiento contable no aprobados */
        $entriesNotApproved = $this->unapproved();

        /* determinara el año mas antiguo para el filtrado */
        $yearOld = '';

        if ($entries && $entries->from_date !== null) {
            $yearOld = explode('-', $entries->from_date)[0];
        }

        /* si no existe asientos contables la fecha mas antigua es la actual*/
        if ($yearOld == '') {
            $yearOld = date('Y');
        }

        /* contendra las categorias */
        $categories = [];
        array_push($categories, [
            'id' => 0,
            'text' => 'Todas',
            'acronym' => '',
        ]);

        foreach (AccountingEntryCategory::all() as $category) {
            array_push($categories, [
                'id' => $category->id,
                'text' => $category->name,
                'acronym' => $category->acronym,
            ]);
        }

        if (isset(auth()->user()->profile) && isset(auth()->user()->profile->institution_id)) {
            $institution = DefaultInstitution::where(['id' => auth()->user()->profile->institution_id])->first();
        } else {
            $institution = DefaultInstitution::where(['active' => true, 'default' => true])->first();
        }

        /* se convierte array a JSON */
        $categories = json_encode($categories);

        return view('accounting::entries.index', compact(
            'categories',
            'yearOld',
            'institutions',
            'entriesNotApproved',
            'institution'
        ));
    }

    /**
     * Muestra un formulario para la creación de asientos contables
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return Renderable
     */
    public function create()
    {
        /* almacena la información del tipo de moneda por defecto */
        $currency = Currency::where('default', true)->orderBy('id', 'ASC')->first();

        $institutions = json_encode($this->getInstitutionAvailables('Seleccione...'));

        /* almacena las cuentas pratrimoniales */
        $AccountingAccounts = $this->getGroupAccountingAccount();

        /* contendra las categorias */
        $categories = [];
        array_push($categories, [
            'id' => '',
            'text' => 'Seleccione...',
            'acronym' => '',
        ]);
        foreach (AccountingEntryCategory::all() as $category) {
            array_push($categories, [
                'id' => $category->id,
                'text' => $category->name,
                'acronym' => $category->acronym,
            ]);
        }

        /* se convierte array a JSON */
        $categories = json_encode($categories);
        $currency = json_encode($currency);

        if (isset(auth()->user()->profile) && isset(auth()->user()->profile->institution_id)) {
            $institution = DefaultInstitution::where(['id' => auth()->user()->profile->institution_id])->first();
        } else {
            $institution = DefaultInstitution::where(['active' => true, 'default' => true])->first();
        }

        return view('accounting::entries.form', compact(
            'AccountingAccounts',
            'categories',
            'currency',
            'institutions',
            'institution'
        ));
    }

    /**
     * Crea una nuevo asiento contable y crea los registros de las cuentas asociadas
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  Request $request Datos de la petición realizada
     *
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $date = new DateTime($request->date);
        $formatedDate = $date->format('Y');

        if (isset(auth()->user()->profile) && isset(auth()->user()->profile->institution_id)) {
            $institution = Institution::query()
                ->where(['id' => auth()->user()->profile->institution_id])
                ->first();
        } else {
            $institution = Institution::query()
                ->where(['active' => true, 'default' => true])
                ->first();
        }

        $currentFiscalYear = FiscalYear::query()
            ->where(['active' => true, 'closed' => false, 'institution_id' => $institution->id])
            ->orderBy('year', 'desc')
            ->first();

        if (isset($currentFiscalYear->entries)) {
            return throw new ClosedFiscalYearException(
                __('No puede registrar, actualizar o eliminar ' .
                    'registros debido a que se está realizando el cierre de año fiscal')
            );
        }

        $closedFiscalYear = FiscalYear::query()
            ->where(['active' => false, 'closed' => true, 'institution_id' => $institution->id])
            ->orderBy('year', 'desc')
            ->first();

        if (isset($closedFiscalYear) && $formatedDate == $closedFiscalYear->year) {
            return throw new ClosedFiscalYearException(
                __('No puede registrar, actualizar o eliminar registros de un año fiscal cerrado')
            );
        }

        $this->validate($request, [
            'date' => ['required', 'date', new DateBeforeFiscalYear('fecha')],
            'concept' => 'required|string',
            'observations' => 'nullable',
            'category' => 'required|integer',
            'institution_id' => 'nullable',
            'currency_id' => 'required|integer',
            'tot' => 'required|confirmed',
        ], [
            'date.required' => 'El campo fecha es obligatorio.',
            'date.date' => 'El campo fecha no tiene el formato adecuado.',
            'concept.required' => 'El campo concepto o descripción es obligatorio.',
            'category.required' => 'El campo categoria es obligatorio.',
            'category.integer' => 'El campo categoria no esta en el formato de entero.',
            'institution_id.required' => 'El campo institución es obligatorio.',
            'institution_id.integer' => 'El campo institución no esta en el formato de entero.',
            'currency_id.required' => 'El campo moneda es obligatorio.',
            'currency_id.integer' => 'El campo moneda no esta en el formato de entero.',
            'tot.confirmed' => 'El asiento no esta balanceado, Por favor verifique.',
        ]);

        $allRequest = $request->all();

        if ($request->close_fiscal_year == true) {
            $codeSetting = CodeSetting::where('table', 'entries')->where('module', 'base')->first();

            if (!isset($codeSetting)) {
                return response()->json(['result' => false, 'message' => [
                    'type' => 'custom', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'danger',
                    'text' => 'Debe configurar previamente el formato para el código a generar',
                ]], 401);
            }

            $currentFiscalYear = FiscalYear::select('year')
                ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();

            $code = generate_registration_code(
                $codeSetting->format_prefix,
                strlen($codeSetting->format_digits),
                (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
                    substr($currentFiscalYear->year, 2, 2) : date('y')) : (isset($currentFiscalYear) ?
                    $currentFiscalYear->year : date('Y')),
                AccountingEntry::class,
                $codeSetting->field
            );

            $allRequest['reference'] = $code;
        }

        AccountingManageEntries::dispatch(
            $allRequest,
            ($request->institution_id) ?
            $request->institution_id :
            $institution->id,
        );

        return response()->json(['message' => 'Success', 'reference' => ''], 200);
    }

    /**
     * Listado de registros
     *
     * @return JsonResponse
     */
    public function show($id)
    {
        return response()->json(['records' => AccountingEntry::with(
            'accountingEntryCategory',
            'accountingAccounts.account',
            'institution',
        )->find($id)], 200);
    }

    /**
     * Muestra el formulario para la edición de asientos contables
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  integer $id Identificador del asiento contable a modificar
     *
     * @return Renderable
     */
    public function edit($id)
    {
        /* asiento contable a editar */
        $entry = AccountingEntry::with('accountingAccounts.account')->find($id);

        // Validar acceso para el registro
        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();

        if (!auth()->user()->isAdmin()) {
            if ($entry && $entry->queryAccess($user_profile['institution']['id'])) {
                return view('errors.403');
            }
        }

        $institutions = json_encode($this->getInstitutionAvailables('Seleccione...'));

        /* cuentas pratrimoniales */
        $AccountingAccounts = $this->getGroupAccountingAccount();

        /* se guarda en variables la información necesaria para la edición del asiento contable */

        $date = $entry->from_date;
        $reference = $entry->reference;
        $concept = $entry->concept;
        $observations = $entry->observations;
        $category = $entry->accounting_entry_category_id;
        $institution = $entry->institution_id;
        $currency = $entry->currency_id;

        /* lista de categorias */
        $categories = [];
        array_push($categories, [
            'id' => '',
            'text' => 'Seleccione...',
            'acronym' => '',
        ]);
        foreach (AccountingEntryCategory::all() as $cat) {
            array_push($categories, [
                'id' => $cat->id,
                'text' => $cat->name,
                'acronym' => $cat->acronym,
            ]);
        }

        /* se convierte array a JSON */
        $categories = json_encode($categories);

        $data_edit = [
            'date' => $date,
            'category' => $category,
            'reference' => $reference,
            'concept' => $concept,
            'observations' => $observations,
            'institution' => $institution,
            'currency' => $currency,
        ];
        $data_edit = json_encode($data_edit);

        return view('accounting::entries.form', compact(
            'AccountingAccounts',
            'entry',
            'categories',
            'data_edit',
            'institutions'
        ));
    }

    /**
     * Actualiza los datos del asiento contable
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  Request $request Datos de la petición realizada
     * @param  integer $id      Identificador del asiento contable a modificar
     *
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $date = new DateTime($request->date);
        $formatedDate = $date->format('Y');

        if (isset(auth()->user()->profile) && isset(auth()->user()->profile->institution_id)) {
            $institution = Institution::query()
                ->where(['id' => auth()->user()->profile->institution_id])
                ->first();
        } else {
            $institution = Institution::query()
                ->where(['active' => true, 'default' => true])
                ->first();
        }

        $currentFiscalYear = FiscalYear::query()
            ->where(['active' => true, 'closed' => false, 'institution_id' => $institution->id])
            ->orderBy('year', 'desc')
            ->first();

        if (isset($currentFiscalYear->entries)) {
            return throw new ClosedFiscalYearException(
                __('No puede registrar, actualizar o eliminar ' .
                    'registros debido a que se está realizando el cierre de año fiscal')
            );
        }

        $closedFiscalYear = FiscalYear::query()
            ->where(['active' => false, 'closed' => true, 'institution_id' => $institution->id])
            ->orderBy('year', 'desc')
            ->first();

        if (isset($closedFiscalYear) && $formatedDate == $closedFiscalYear->year) {
            return throw new ClosedFiscalYearException(
                __('No puede registrar, actualizar o eliminar registros de un año fiscal cerrado')
            );
        }

        $this->validate($request, [
            'date' => ['required', 'date', new DateBeforeFiscalYear('fecha')],
            'reference' => 'required|string|unique:accounting_entries,reference,' . $id,
            'concept' => 'required|string',
            'observations' => 'nullable',
            'category' => 'required|integer',
            'institution_id' => 'required|integer',
            'currency_id' => 'required|integer',
            'tot' => 'required|confirmed',
        ], [
            'date.required' => 'El campo fecha es obligatorio.',
            'date.date' => 'El campo fecha no tiene el formato adecuado.',
            'reference.required' => 'El campo referencia es obligatorio.',
            'reference.unique' => 'El campo referencia debe ser único.',
            'concept.required' => 'El campo concepto o descripción es obligatorio.',
            'category.required' => 'El campo categoria es obligatorio.',
            'category.integer' => 'El campo categoria no esta en el formato de entero.',
            'institution_id.required' => 'El campo institución es obligatorio.',
            'institution_id.integer' => 'El campo intitución no esta en el formato de entero.',
            'currency_id.required' => 'El campo moneda es obligatorio.',
            'currency_id.integer' => 'El campo moneda no esta en el formato de entero.',
            'tot.confirmed' => 'El asiento no esta balanceado, Por favor verifique.',
        ]);

        // Validar acceso para el registro
        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();

        /* informaciónd el asiento contable */
        $entry = AccountingEntry::find($id);

        if (!auth()->user()->isAdmin()) {
            if ($entry && $entry->queryAccess($user_profile['institution']['id'])) {
                return response()->json(['message' => 'No tiene acceso para modificar el registro',
                    'redirect' => route('errors.403')], 403);
            }
        }

        /* se actualiza la información del registro del asiento contable */
        AccountingManageEntries::dispatch($request->all(), $request->institution_id);

        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina un asiento contable
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  integer $id Identificador del asiento contable a eliminar
     *
     * @return JsonResponse
     */
    public function destroy($id)
    {
        // Validar acceso para el registro
        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();

        /* información del asiento contable */
        $entry = AccountingEntry::find($id);

        if (!auth()->user()->isAdmin()) {
            if ($entry && $entry->queryAccess($user_profile['institution']['id'])) {
                return response()->json(['error' => true, 'message' => 'No tiene acceso para eliminar el registro.', 403]);
            }
        }

        /* El registro de asiento contable a eliminar */
        AccountingEntryAccount::where('accounting_entry_id', $id)->delete();

        AccountingEntry::where('reversed_id', $id)->delete();

        $entry->delete();

        return response()->json(['message' => 'Success', 200]);
    }

    /**
     * Filtro de registros
     *
     * @author Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve>
     *
     * @param Request $request Datos de la petición
     * @param  integer $perPage Número de elementos por página
     * @param integer $page Página de la consulta
     *
     * @return JsonResponse
     */
    public function filterRecords(Request $request, $perPage = 10, $page = 1)
    {
        $user = auth()->user();
        $user_profile = Profile::with('institution')->where('user_id', $user->id)->first();
        $institution_id = null;

        if ($user->isAdmin() && $request->institution) {
            $institution_id = $request->institution;
        } elseif ($user_profile && $user_profile->institution && $user_profile->institution->id == $request->institution) {
            $institution_id = $request->institution;
        }

        $documentStatusEL = default_document_status_el();

        $query = AccountingEntry::query()
            ->withCount("pivotEntryable")
            ->where('document_status_id', '!=', $documentStatusEL->id)
            ->whereHas('documentStatus', function ($query) use ($documentStatusEL) {
                $query->where('id', '!=', $documentStatusEL->id);
            });

        $search = $request->search ?: $request->reference;

        if ($request->typeSearch == 'reference' || $request->typeSearch == 'origin') {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhere('from_date', 'like', "%{$search}%")
                    ->orWhere('concept', 'like', "%{$search}%");
            });

            if ($institution_id) {
                $query->where('institution_id', $institution_id);
            }

            if ($request->typeSearch == 'origin' && $request->category) {
                $query->where('accounting_entry_category_id', $request->category);
            }
        }

        if ($request->filterDate == 'generic') {
            if ($request->year) {
                $query->whereYear('from_date', $request->year);
            }
            if ($request->month) {
                $query->whereMonth('from_date', $request->month);
            }
        } else {
            $query->whereBetween('from_date', [$request->init, $request->end]);
        }

        $closeFiscalYear = FiscalYear::query()->get();
        $entriesId = $closeFiscalYear->flatMap(function ($fYear) {
            return $fYear->entries ? collect($fYear->entries)->pluck('id') : collect();
        })->filter()->toArray();

        $query->whereNotIn('id', $entriesId);

        $total = $query->count();
        $records = $query->orderBy('approved', 'ASC')
            ->orderBy('id', 'ASC')
            ->orderBy('from_date', 'ASC')
            ->orderBy('reference', 'ASC')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        foreach ($records as $record) {
            $record->concept = strip_tags($record->concept);
        }

        $lastPage = max((int) ceil($total / $perPage), 1);

        return response()->json([
            'records' => $records,
            'total' => $total,
            'lastPage' => $lastPage,
        ], 200);
    }

    /**
     * Obtiene los registros de las cuentas patrimoniales
     *
     * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return string|JsonResponse JSON con la información de las cuentas formateada
     */
    public function getAccountingAccount()
    {
        /**
         * [$records listado de registros]
         * @var array
         */
        $records = [];
        array_push($records, [
            'id' => '',
            'text' => 'Seleccione...',
        ]);
        /**
         * ciclo para almecenar y formatear en array las cuentas patrimoniales
         */
        foreach (
            AccountingAccount::orderBy('group', 'ASC')
            ->orderBy('subgroup', 'ASC')
            ->orderBy('item', 'ASC')
            ->orderBy('generic', 'ASC')
            ->orderBy('specific', 'ASC')
            ->orderBy('subspecific', 'ASC')
            ->get() as $account
        ) {
            if ($account->active) {
                array_push($records, [
                    'id' => $account->id,
                    'text' => "{$account->getCodeAttribute()} - {$account->denomination}",
                ]);
            }
        };
        /**
         * se convierte array a JSON
         */
        return json_encode($records);
    }

    /**
     * Obtiene las cuentas contables agrupadas
     *
     * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param boolean $first Primera cuenta de la consulta
     * @param integer|null $parent_id Identificador de la cuenta de nivel superior
     *
     * @return string|JsonResponse JSON con la información de las cuentas formateada
     */
    public function getGroupAccountingAccount($first = true, $parent_id = null)
    {
        /* Colección con información de las cuentas contables regsitradas en el cátalogo de cuentas patrimoniales  */
        $accountings = AccountingAccount::where('active', true)
            ->orderBy('group')
            ->orderBy('subgroup')
            ->orderBy('item')
            ->orderBy('generic')
            ->orderBy('specific')
            ->orderBy('subspecific')
            ->orderBy('institutional')
            ->toBase()->get();
        /* Arreglo con el listado de opciones de cuentas patrimoniales a seleccionar */
        $records = $accountings->map(function ($a) {
            return [
                'id' => $a->id,
                'text' => "{$a->group}.{$a->subgroup}.{$a->item}.{$a->generic}.{$a->specific}.{$a->subspecific}.{$a->institutional} - {$a->denomination}",
                'disabled' => ($a->original) ?: false,
            ];
        })->toArray();
        array_unshift($records, [
            'id' => '',
            'text' => 'Seleccione...',
        ]);

        return json_encode($records);
    }

    /**
     * Vista con listado de asientos contable no aprobados
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Collection|AccountingEntry|array
     */
    public function unapproved()
    {
        /**
         * [$entries listado de los asientos contables no aprobados]
         * @var array
         */
        $entries = [];

        /**
         * [$user_profile informacion del perfil del usuario logueado]
         * @var Profile
         */
        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();

        if (auth()->user()->isAdmin()) {
            $entries = AccountingEntry::with('accountingAccounts.account')
            ->where([
                'approved' => false,
                'document_status_id' => default_document_status()->id
            ])->orderBy('from_date', 'ASC')->get();
        } elseif ($user_profile['institution']['id']) {
            $entries = AccountingEntry::with('accountingAccounts.account')
            ->where([
                'approved' => false,
                'document_status_id' => default_document_status()->id
            ])->where('institution_id', $user_profile['institution']['id'])
                ->orderBy('from_date', 'ASC')->get();
        }

        return $entries;
    }

    /**
     * Aprueba el asiento contable
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  integer $id identificador del asiento contable
     *
     * @return JsonResponse
     */
    public function approve($id)
    {
        // Validar acceso para el registro
        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();

        /* contendra el asiento al que se le cambiará el estado */
        $entry = AccountingEntry::find($id);

        if (!auth()->user()->isAdmin()) {
            if ($entry && $entry->queryAccess($user_profile['institution']['id'])) {
                return response()->json(['error' => true, 'message' => 'No tiene acceso para modificar el registro.', 403]);
            }
        }

        try {
            DB::transaction(function () use ($entry) {
                // Aprobar el asiento
                $documentStatusAP = DocumentStatus::where('action', 'AP')->first();
                $entry->approved = true;
                $entry->document_status_id = $documentStatusAP->id;
                $entry->save();
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $message = str_replace("\n", "", $e->getMessage());
            if (strpos($message, 'ERROR') !== false && strpos($message, 'DETAIL') !== false) {
                $pattern = '/ERROR:(.*?)DETAIL/';
                preg_match($pattern, $message, $matches);
                $errorMessage = trim($matches[1]);
            } else {
                $errorMessage = $message;
            }
            return response()->json(
                ['message' => [
                    'type' => 'custom',
                    'title' => 'Alerta',
                    'icon' => 'screen-error',
                    'class' => 'growl-danger',
                    'text' => 'No se pudo completar la operación. ' . ucfirst($errorMessage),
                ]],
                500
            );
        }
        return response()->json(['result' => false, 'redirect' => route('accounting.entries.index')], 200);
    }

    /**
     * Obtiene un listado con las instituciones registradas en el sistemas
     * Caso Admin muestra todas
     * Caso User muestra solo a la que pertenece
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return array
     */
    public function getInstitutionAvailables($text)
    {
        $institutions = [];
        $profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();

        if ($profile) {
            if (auth()->user()->hasRole('admin')) {
                $institutions = template_choices('App\Models\Institution', 'name', [], true);
                $institutions[0]['text'] = $text;
            } else {
                array_push($institutions, [
                    'id' => $profile->institution->id,
                    'text' => $profile->institution->name,
                ]);
            }
        } elseif (!$profile && auth()->user()->hasRole('admin')) {
            $institutions = template_choices('App\Models\Institution', 'name', [], true);
            $institutions[0]['text'] = $text;
        }
        return $institutions;
    }

    /**
     * Genera asiento contable apartir de datos de registros relacionados a cuentas patrimoniales
     * ejemplo de datos que recibe en request
     *  objectsList => [
     *      {
     *          'module'                : 'Budget',     Nombre del modulo hacia el cual se relacionara el registro
     *          'model'                 : Modules\\Accounting\\Models\\BudgetAccount',  Clase a la que se hara la relacion
     *          'accountable_id'        : id, identificador del registro a relacionar
     *          'accounting_account_id' : id, identificador de la cuenta patrimonial
     *      }
     *  ]
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve | exodiadaniel@gmail.com>
     *
     * @param  Request $request
     *
     * @return JsonResponse
     */
    public function converterToEntry(Request $request)
    {
        $accounting_accounts = json_decode($this->getAccountingAccount());
        $accounts = [];

        foreach ($request->objectsList as $data) {
            foreach ($accounting_accounts as $account) {
                if ($account->id == $data['account']) {
                    if (!array_key_exists($data['account'], $accounts)) {
                        $account->amount = $data['amount'];
                        $account->is_retention = $data['is_retention'];

                        if (isset($data['debit']) && $data['debit'] == true) {
                            $account->debit = $data['amount'];
                        } else {
                            $account->assets = $data['amount'];
                        }

                        $accounts[$data['account']] = $account;
                    } else {
                        $account->amount += $data['amount'];
                        $account->is_retention = $data['is_retention'];

                        if (isset($data['debit']) && $data['debit'] == true) {
                            $account->debit += $data['amount'];
                        } else {
                            $account->assets += $data['amount'];
                        }

                        $accounts[$data['account']] = $account;
                    }
                }
            }
        }

        $retAmount = 0;
        $assetsLength = 0;

        foreach ($accounts as $acc) {
            if ($acc->is_retention == true) {
                $retAmount += $acc->amount;
            } elseif ($acc->is_retention == false && isset($acc->assets)) {
                $assetsLength++;
            }
        }

        if ($retAmount > 0) {
            $retAmount = $retAmount / $assetsLength;
        }

        foreach ($accounts as $acc) {
            if ($acc->is_retention == false && isset($acc->assets)) {
                $acc->assets = $acc->assets - $retAmount;
                $acc->amount = $acc->amount - $retAmount;
            }
        }

        return response()->json([
            'recordsAccounting' => $accounts,
            'accountingAccounts' => json_decode($this->getGroupAccountingAccount()),
            'currency' => Currency::where('default', true)->first(),
        ], 200);
    }

    /**
     * Genera reverso de asiento contable
     *
     * @author Juan Rosas <juan.rosasr01@gmail.com>
     *
     * @param Request $request Datos de la petición
     *
     * @return JsonResponse
     */
    public function reverse(Request $request)
    {

        $this->validate($request, [
            'entryId' => 'required',
            'reversed_at' => ['required', 'date', new DateBeforeFiscalYear('fecha de reverso')],
        ], [
            'reversed_at.required' => 'El campo fecha del reverso es requerido',
            'reversed_at.date' => 'El campo fecha del reverso debe ser de tipo fecha',
            'entryId.required' => 'El campo asiento contable es requerido',
        ]);

        try {
            $entry = AccountingEntry::findOrFail($request->entryId);

            // Se verifica que la fecha de reverso no sea menor a las fecha del resgistro
            if ($request->reversed_at < $entry->from_date) {
                $errors[0] = ["La fecha de reverso no puede ser menor a la fecha de creación ("
                . date_format(date_create($entry->from_date), 'd/m/Y') . ")"];
                return response()->json(['result' => true, 'errors' => $errors], 422);
            }

            DB::transaction(function () use ($request, $entry) {

                $accounts = AccountingEntryAccount::where('accounting_entry_id', $request->entryId)->get();

                $reverseEntry = AccountingEntry::create([
                    'from_date' => $request->reversed_at,
                    'concept' => 'Reverso ' . $entry->reference . ': ' . $entry->concept,
                    'observations' => 'Reverso ' . $entry->reference . ': ' . $entry->observations,
                    'reference' => $this->generateCodeAvailable(),
                    'tot_debit' => $entry->tot_assets,
                    'tot_assets' => $entry->tot_debit,
                    'accounting_entry_category_id' => $entry->accounting_entry_category_id,
                    'currency_id' => $entry->currency_id,
                    'approved' => true,
                    'institution_id' => $entry->institution_id,
                    'document_status_id' => $entry->document_status_id,
                    'reversed' => false,
                    'reversed_id' => $entry->id,
                ]);

                foreach ($accounts as $account) {
                    AccountingEntryAccount::create([
                        'accounting_entry_id' => $reverseEntry->id,
                        'accounting_account_id' => $account->accounting_account_id,
                        'debit' => $account->assets,
                        'assets' => $account->debit
                    ]);
                }

                $entry->reversed = true;
                $entry->reversed_at = $request->reversed_at;

                $entry->save();
            });

            return response()->json(['result' => false, 'redirect' => route('accounting.entries.index')], 200);
        } catch (\Exception $e) {
            $message = str_replace("\n", "", $e->getMessage());
            if (strpos($message, 'ERROR') !== false && strpos($message, 'DETAIL') !== false) {
                $pattern = '/ERROR:(.*?)DETAIL/';
                preg_match($pattern, $message, $matches);
                $errorMessage = trim($matches[1]);
            } else {
                $errorMessage = $message;
            }

            return response()->json(
                ['message' =>
                [
                    'type' => 'custom',
                    'title' => 'Alerta',
                    'icon' => 'screen-error',
                    'class' => 'growl-danger',
                    'text' => 'No se pudo completar la operación. ' . ucfirst($errorMessage)
                ]],
                500
            );
        }
    }

    /**
     * Genera el código disponible
     *
     * @author Juan Rosas <juan.rosasr01@gmail.com>
     *
     * @return string
     */
    public function generateCodeAvailable(): string
    {
        $codeSetting = CodeSetting::where('table', 'accounting_entries')
            ->first();

        if (!$codeSetting) {
            $codeSetting = CodeSetting::where('table', 'accounting_entries')
                ->first();
        }

        $currentFiscalYear = FiscalYear::select('year')
            ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();

        if ($codeSetting) {
            $code = generate_registration_code(
                $codeSetting->format_prefix,
                strlen($codeSetting->format_digits),
                (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
                    substr($currentFiscalYear->year, 2, 2) : date('y')) : (isset($currentFiscalYear) ?
                    $currentFiscalYear->year : date('Y')),
                AccountingEntry::class,
                $codeSetting->field
            );
        } else {
            $code = 'error al generar código de referencia';
        }

        return $code;
    }
}
