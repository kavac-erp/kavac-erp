<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Nwidart\Modules\Facades\Module;
use App\Models\Currency;
use App\Models\CodeSetting;
use App\Models\FiscalYear;
use App\Models\Parameter;
use App\Models\Institution;
use Illuminate\Support\Facades\Validator;

/**
 * @class CloseFiscalYearController
 * @brief Controlador para el cierre de ejercicio
 *
 * Clase que gestiona para el cierre de ejercicio
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
 * @license<a href='http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/'>
 *              LICENCIA DE SOFTWARE CENDITEL
 *          </a>
 */
class CloseFiscalYearController extends Controller
{
    public function __construct()
    {
        /**
         * Establece permisos de acceso para cada método del controlador
         */
        $this->middleware('permission:close_fiscal_year.list', ['only' => ['index', 'vueList']]);
        $this->middleware('permission:close_fiscal_year.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:close_fiscal_year.delete', ['only' => 'destroy']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $currencyId = Currency::query()
            ->where('default', true)
            ->orderBy('id', 'ASC')
            ->first()
            ->id;

        $fiscalYear = FiscalYear::query()
            ->where(['active' => true, 'closed' => false])
            ->orderBy('year', 'desc')
            ->first();

        if (isset($fiscalYear)) {
            $fiscalYear = $fiscalYear->year;
        }

        return view('close-fiscal-year.registers.index', compact('currencyId', 'fiscalYear'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createEntries()
    {
        if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
            $accountingController = app(\Modules\Accounting\Http\Controllers\AccountingEntryController::class);

            $currency = Currency::where('default', true)->orderBy('id', 'ASC')->first();

            $currencies = json_encode(template_choices('App\Models\Currency', ['symbol', '-', 'name'], [], true));

            $institutions = json_encode($accountingController->getInstitutionAvailables('Seleccione...'));

            /**
             * [$AccountingAccounts almacena las cuentas pratrimoniales]
             * @var json
             */
            $AccountingAccounts = $accountingController->getGroupAccountingAccount();

            /**
             * [$categories contendra las categorias]
             * @var array
             */
            $categories = [];
            array_push($categories, [
                'id'      => '',
                'text'    => 'Seleccione...',
                'acronym' => ''
            ]);

            foreach (\Modules\Accounting\Models\AccountingEntryCategory::all() as $category) {
                array_push($categories, [
                    'id'      => $category->id,
                    'text'    => $category->name,
                    'acronym' => $category->acronym,
                ]);
            }

            /**
             * se convierte array a JSON
             */
            $categories = json_encode($categories);
            $currency   = json_encode($currency);

            return view('close-fiscal-year.entries.create', compact(
                'AccountingAccounts',
                'categories',
                'currency',
                'currencies',
                'institutions'
            ));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $ifExistAccounting = Module::has('Accounting') && Module::isEnabled('Accounting');
        $ifExistAsset = Module::has('Asset') && Module::isEnabled('Asset');
        $ifExistWarehouse = Module::has('Warehouse') && Module::isEnabled('Warehouse');

        $this->validate(
            $request,
            [
                'account_analysis' => $ifExistAccounting ? 'accepted' : 'nullable',
                'adjustment_entries' => $ifExistAccounting ? 'accepted' : 'nullable',
                'depreciation' => $ifExistAsset ? 'accepted' : 'nullable',
                'inventory_closing' => $ifExistWarehouse ? 'accepted' : 'nullable',
            ],
            [
                'account_analysis.accepted' => 'El campo análisis de cuenta es obligatorio',
                'adjustment_entries.accepted' => 'El campo asientos de ajustes es obligatorio',
                'depreciation.accepted' => 'El campo depreciación es obligatorio',
                'inventory_closing.accepted' => 'El campo cierre de inventario es obligatorio',
            ]
        );

        if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
            $institution = $this->getInstitutionAvailable();

            $codeSetting = CodeSetting::where('table', 'entries')->where('module', 'base')->first();

            if (!isset($codeSetting)) {
                $request->session()->flash('message', [
                    'type' => 'other', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'growl-danger',
                    'text' => 'Debe configurar previamente el formato para el código a generar',
                ]);
                return response()->json(['result' => false, 'redirect' => route('close-fiscal-year.settings.index')], 200);
            }

            $currentFiscalYear = FiscalYear::where(['active' => true, 'closed' => false, 'institution_id' => $institution->id])
                                           ->orderBy('year', 'desc')->first();

            if (isset($currentFiscalYear) && isset($currentFiscalYear->entries)) {
                $errors = [
                    'fiscal_year_unique' => [
                        0 => 'Ya se ha realizado el cierre para el año fiscal en curso, debe aprobarlo antes de hacer un nuevo cierre.'
                    ]
                ];

                return response()->json(['message' => 'The given data was invalid.','errors' => $errors], 422);
            }

            if ($this->getEntryAccounts('resource') == false || $this->getEntryAccounts('egress') == false) {
                $request->session()->flash('message', [
                    'type' => 'other', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'growl-danger',
                    'text' => 'Debe configurar previamente la cuenta asociada al resultado del ejercicio',
                ]);
                return response()->json(['result' => false, 'redirect' => route('close-fiscal-year.settings.index')], 200);
            }

            $entriesId = [];

            $accountEntryResource = $this->storeEntries($this->getEntryAccounts('resource'), 'resource', $currentFiscalYear->year);
            array_push($entriesId, $accountEntryResource->id);

            $accountEntryEgress = $this->storeEntries($this->getEntryAccounts('egress'), 'egress', $currentFiscalYear->year);
            array_push($entriesId, $accountEntryEgress->id);

            $currentFiscalYear->entries = $entriesId;
            $currentFiscalYear->save();

            return response()->json(['result' => true, 'message' => 'Success']);
        } else {
            $institution = $this->getInstitutionAvailable();
            $currentFiscalYear = FiscalYear::where(['active' => true, 'closed' => false, 'institution_id' => $institution->id])
                                           ->orderBy('year', 'desc')->first();
            $currentFiscalYear->active = false;
            $currentFiscalYear->closed = true;
            $currentFiscalYear->save();

            return response()->json(['result' => true, 'message' => 'Success']);
        }
    }

    public function generateCodeAvailable()
    {
        $codeSetting = CodeSetting::where('table', 'entries')->where('module', 'base')->first();

        $currentFiscalYear = FiscalYear::select('year')
                                    ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();

        $code  = generate_registration_code(
            $codeSetting->format_prefix,
            strlen($codeSetting->format_digits),
            (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
            substr($currentFiscalYear->year, 2, 2) : date('y')) : (isset($currentFiscalYear) ?
            $currentFiscalYear->year : date('Y')),
            \Modules\Accounting\Models\AccountingEntry::class,
            $codeSetting->field
        );

        return $code;
    }

    public function getInstitutionAvailable()
    {
        if (isset(auth()->user()->profile) && isset(auth()->user()->profile->institution_id)) {
            $institution = Institution::where(['id' => auth()->user()->profile->institution_id])->first();
        } else {
            $institution = Institution::where(['active' => true, 'default' => true])->first();
        }

        return $institution;
    }

    public function getEntryAccounts($type)
    {
        $institution = $this->getInstitutionAvailable();
        $accounts = \Modules\Accounting\Models\AccountingEntryAccount::with(['account', 'entries'])->whereHas('account', function ($query) use ($type) {
            $query->where($type, true);
        })->whereHas('entries', function ($q) use ($institution) {
            $q->where('approved', true)->where('institution_id', $institution->id);
        })->get()->groupBy('accounting_account_id');

        $parameter = Parameter::where('p_key', 'close_fiscal_year_account')->first();

        if (!isset($parameter)) {
            return false;
        }

        $accountingAccount = \Modules\Accounting\Models\AccountingAccount::where('id', $parameter->p_value)->first();

        $newAccounts = [];
        $entryAccounts = [];
        $total = 0;
        $totDebit = 0;
        $totAssets = 0;

        foreach ($accounts as $account) {
            foreach ($account as $acc) {
                $newAccounts['assets'] = $acc['assets'];
                $newAccounts['debit'] = $acc['debit'];
                $newAccounts['accounting_account_id'] = $acc['accounting_account_id'];

                $totAssets += $acc['assets'];
                $totDebit += $acc['debit'];

                if (!array_key_exists($acc['accounting_account_id'], $entryAccounts)) {
                    $entryAccounts[$acc['accounting_account_id']] = $newAccounts;
                } else {
                    $entryAccounts[$acc['accounting_account_id']]['assets'] += $acc['assets'];
                    $entryAccounts[$acc['accounting_account_id']]['debit'] += $acc['debit'];
                }
            }
        }

        $total = $totDebit - $totAssets;
        $total = $total < 0 ? $total * -1 : $total;

        $newAccounts['assets'] = $type == 'egress' ? $total : 0;
        $newAccounts['debit'] = $type == 'resource' ? $total : 0;
        $newAccounts['accounting_account_id'] = $accountingAccount->id;

        $entryAccounts[$accountingAccount->id] = $newAccounts;

        return $entryAccounts;
    }

    public function storeEntries($entryAccounts, $type, $fiscalYear)
    {
        $parameter = Parameter::where('p_key', 'close_fiscal_year_account')->first();
        $accountingAccount = \Modules\Accounting\Models\AccountingAccount::where('id', $parameter->p_value)->first();
        $code = $this->generateCodeAvailable();
        $institution = $this->getInstitutionAvailable();
        $account = $entryAccounts[$accountingAccount->id];
        $totDebit = $account['debit'];
        $totAssets = $account['assets'];
        $entryDate = $fiscalYear . '-12-31';

        $accountingCategory = \Modules\Accounting\Models\AccountingEntryCategory::where('acronym', 'EDR')->first();

        $currency = Currency::where('default', true)->orderBy('id', 'ASC')->first();

        $accountEntry = \Modules\Accounting\Models\AccountingEntry::create([
            'from_date'                      => $entryDate,
            'reference'                      => $code,
            'concept'                        => 'Cierre de ejercicio',
            'observations'                   => null,
            'accounting_entry_category_id'   => $accountingCategory->id,
            'institution_id'                 => $institution->id,
            'currency_id'                    => $currency->id,
            'tot_debit'                      => $type == 'resource' ? $totDebit : $totAssets,
            'tot_assets'                     => $type == 'resource' ? $totDebit : $totAssets,
            'approved'                       => false
        ]);

        foreach ($entryAccounts as $account) {
            if ($account['accounting_account_id'] != $accountingAccount->id) {
                $realAmount = $account['debit'] - $account['assets'];
                $realAmount = $realAmount < 0 ? $realAmount * -1 : $realAmount;

                if ($type == 'resource') {
                    /**
                     * crea el nuevo registro de cuenta
                     */
                    \Modules\Accounting\Models\AccountingEntryAccount::create([
                        'accounting_entry_id' => $accountEntry->id,
                        'accounting_account_id' => $account['accounting_account_id'],
                        'debit' => $realAmount,
                        'assets' => 0,
                    ]);
                } else {
                    /**
                     * crea el nuevo registro de cuenta
                     */
                    \Modules\Accounting\Models\AccountingEntryAccount::create([
                        'accounting_entry_id' => $accountEntry->id,
                        'accounting_account_id' => $account['accounting_account_id'],
                        'debit' => 0,
                        'assets' => $realAmount,
                    ]);
                }
            } else {
                /**
                 * crea el nuevo registro de cuenta
                 */
                \Modules\Accounting\Models\AccountingEntryAccount::create([
                    'accounting_entry_id' => $accountEntry->id,
                    'accounting_account_id' => $account['accounting_account_id'],
                    'debit' => $account['assets'],
                    'assets' => $account['debit'],
                ]);
            }
        }

        return $accountEntry;
    }

    /**
     * Obtiene los registros a mostrar en listados de componente Vue
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
     * @return \Illuminate\Http\JsonResponse Devuelve un JSON con la información de los cierres de año fiscal
     */
    public function vueList()
    {
        $records = [];
        $institution = $this->getInstitutionAvailable();
        $closeFiscalYear = FiscalYear::where('institution_id', $institution->id)->whereNotNull('entries')->get();
        $parameter = Parameter::where('p_key', 'close_fiscal_year_account')->first();

        if (!isset($parameter)) {
            return response()->json([
                'records' => [],
            ], 200);
        }

        $accountingAccount = \Modules\Accounting\Models\AccountingAccount::where('id', $parameter->p_value)->first();

        foreach ($closeFiscalYear as $close) {
            $entries = \Modules\Accounting\Models\AccountingEntry::whereIn('id', $close->entries)->first();
            if ($entries->approved == false) {
                $close->resource_entries = $close->resourceEntries();
                $close->egress_entries = $close->egressEntries();
                array_push($records, $close);
            }
        }

        return response()->json([
            'records' => $records,
            'close_account' => $accountingAccount
        ], 200);
    }

    /**
     * Aprueba el cierre de año fiscal
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
     * @return \Illuminate\Http\JsonResponse Devuelve un JSON con la información de los cierres de año fiscal
     */
    public function approve($id)
    {
        if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
            $closeFiscalYear = FiscalYear::find($id);
            $institution = $this->getInstitutionAvailable();

            foreach ($closeFiscalYear->entries as $entry) {
                $entry = \Modules\Accounting\Models\AccountingEntry::find($entry);
                $entry->approved = true;
                $entry->save();
            }

            $closeFiscalYear->active = 'false';
            $closeFiscalYear->closed = 'true';
            $closeFiscalYear->save();

            $institution->fiscalYears()->updateOrCreate(
                ['year' => (int)$closeFiscalYear->year + 1],
                [
                    'active' => true,
                    'observations' => 'Ejercicio económico de ' . $institution->acronym,
                    'closed' => false,
                ]
            );

            return response()->json(['message' => 'Success'], 200);
        } else {
            return response()->json(['result' => false, 'message' => [
                'type' => 'custom', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'danger',
                'text' => 'Debe tener instalado el módulo de contabilidad para acceder a esta funcionalidad',
            ]], 403);
        }
    }

    /**
     * Elimina un cierre que no fue aprobado
     *
     * @method  destroy
     *
     * @author  Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
     *
     * @param  id  $id    id del registro a eliminar
     *
     * @return JsonResponse     JSON con información del resultado de la eliminación
     */
    public function destroy($id)
    {
        if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
            $closeFiscalYear = FiscalYear::find($id);
            $entry = \Modules\Accounting\Models\AccountingEntry::find($closeFiscalYear->entries[0]);

            if ($entry->approved == true) {
                return response()->json(['result' => false, 'message' => [
                    'type' => 'custom', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'danger',
                    'text' => 'No puede eliminar un cierre ya aprobado previamente',
                ]], 403);
            }

            $entries = \Modules\Accounting\Models\AccountingEntry::whereIn('id', $closeFiscalYear->entries);
            $entries->delete();
            $closeFiscalYear->entries = null;
            $closeFiscalYear->save();

            return response()->json(['message' => 'Success'], 200);
        } else {
            return response()->json(['result' => false, 'message' => [
                'type' => 'custom', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'danger',
                'text' => 'Debe tener instalado el módulo de contabilidad para acceder a esta funcionalidad',
            ]], 403);
        }
    }

    /**
     * Obtiene los registros a mostrar en listados de componente Vue filtrados por los campos
     * de institución y año fiscal
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
     * @return \Illuminate\Http\JsonResponse Devuelve un JSON con la información de los cierres de año fiscal
     */
    public function searchCloseFiscalYear(Request $request)
    {
        $this->validate(
            $request,
            [
                'fiscal_year' => 'required',
                'institution_id' => 'required',
            ],
            [
                'fiscal_year.required' => 'El campo año fiscal es obligatorio',
                'institution_id.required' => 'El campo organización es obligatorio',
            ]
        );

        $records = [];
        $closeFiscalYear = FiscalYear::where('year', $request->fiscal_year)->orWhere('institution_id', $request->institution_id)->get();

        foreach ($closeFiscalYear as $close) {
            if (isset($close->entries)) {
                $entries = \Modules\Accounting\Models\AccountingEntry::whereIn('id', $close->entries)->first();
                if ($entries->approved == true) {
                    $close->resource_entries = $close->resourceEntries();
                    $close->egress_entries = $close->egressEntries();
                    array_push($records, $close);
                }
            }
        }

        $parameter = Parameter::where('p_key', 'close_fiscal_year_account')->first();

        if (!isset($parameter)) {
            return response()->json([
                'records' => [],
            ], 200);
        }

        $accountingAccount = \Modules\Accounting\Models\AccountingAccount::where('id', $parameter->p_value)->first();

        return response()->json([
            'records' => $records,
            'close_account' => $accountingAccount
        ], 200);
    }
}
