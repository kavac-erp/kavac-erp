<?php

namespace Modules\Asset\Http\Controllers;

use Carbon\Carbon;
use App\Models\Currency;
use App\Models\FiscalYear;
use App\Models\CodeSetting;
use App\Models\Institution;
use App\Models\ExchangeRate;
use Illuminate\Http\Request;
use App\Models\DocumentStatus;
use Modules\Asset\Models\Asset;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Asset\Models\AssetBook;
use Nwidart\Modules\Facades\Module;
use App\Repositories\ReportRepository;
use Modules\Asset\Models\AssetDepreciation;
use Illuminate\Contracts\Support\Renderable;
use Modules\Asset\Models\AssetDepreciationAsset;
use Modules\Asset\Models\AssetDepreciationMethod;
use Modules\Asset\Http\Resources\AssetDepreciationResource;

/**
 * @class  AssetDepreciationController
 * @brief  Controlador para la gestión de depreciación de bienes
 *
 * Controlador para la depreciación de bienes
 *
 * @author Fabián Palmera <fpalmera@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetDepreciationController extends Controller
{
    /**
     * Define la configuración de la clase
     *
     * @author Fabián Palmera <fpalmera@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        /* Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:asset.depreciation.list', ['only' => ['index', 'vueInfo', 'vueList']]);
        $this->middleware('permission:asset.depreciation.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:asset.depreciation.report', ['only' => 'managePdf']);
    }

    /**
     * Muestra el listado de depreciación de bienes
     *
     * @author Fabián Palmera <fpalmera@cenditel.gob.ve>
     *
     * @return Renderable
     */
    public function index()
    {
        return view('asset::depreciations.list');
    }

    /**
     * Muestra el formulario de creación de depreciación de bienes
     *
     * @author Fabián Palmera <fpalmera@cenditel.gob.ve>
     *
     * @return Renderable
     */
    public function create()
    {
        return view('asset::depreciations.create');
    }

    /**
     * Registra la depreciación de bienes
     *
     * @author Fabián Palmera <fpalmera@cenditel.gob.ve>
     *
     * @param Request $request Datos de la petición
     *
     * @return Renderable
     */
    public function store(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $findAssets = Asset::query()
                    ->with(['assetSpecificCategory', 'assetDepreciationAssets', 'assetAdjustmentAssets.assetBook'])
                    ->has('assetSpecificCategory')
                    ->get();

                $assets = [];
                foreach ($findAssets as $asset) {
                    if (array_key_exists('depresciation_years', $asset->asset_details)) {
                        $depreciatedYears = $asset->asset_details['depresciation_years'];
                        $acquisitionDate = $asset->acquisition_date;
                        $acquisitionDate = new Carbon($acquisitionDate);
                        $currentDate = Carbon::now();
                        if ($acquisitionDate->addYears($depreciatedYears) >= $currentDate) {
                            array_push($assets, $asset);
                        }
                    }
                }

                $assetMethod = AssetDepreciationMethod::query()
                    ->where('depreciation_type_id', 1)
                    ->first();

                $currentFiscalYear = FiscalYear::query()
                    ->select('year')
                    ->where(['active' => true, 'closed' => false])
                    ->orderBy('year', 'desc')
                    ->first();

                $defaultCurrency = Currency::query()
                    ->where('default', true)
                    ->first();

                $depreciationDate = $currentFiscalYear->year . '-12-31';
                $formatedDepreciationDate = date('d/m/Y', strtotime($depreciationDate));

                $arr = [];
                $totalDepreciationAmount = 0.00;

                foreach ($assets as $key => $asset) {
                    $exchangeRate = ExchangeRate::query()
                        ->where('active', true)
                        ->where('start_at', '<=', $depreciationDate)
                        ->where(function ($query) use ($depreciationDate) {
                            $query
                                ->where('end_at', '>=', $depreciationDate)
                                ->orWhereNull('end_at');
                        })
                        ->where('from_currency_id', $defaultCurrency->id)
                        ->where('to_currency_id', $asset->currency_id)
                        ->orderBy('id', 'desc')
                        ->first();

                    if (!$exchangeRate && $asset->currency_id != $defaultCurrency->id) {
                        throw new \Exception(
                            'No se puede realizar la depreciación, por favor revise los tipos de' .
                                'cambios activos para la fecha de depreciación ' . $formatedDepreciationDate .
                                ':asset.depreciation.index',
                            1
                        );
                    }

                    $details = $asset->asset_details;

                    $cond_a = (!empty($details['acquisition_value']) && $details['acquisition_value'] != 0);
                    $cond_b = (!empty($details['depresciation_years']) && $details['depresciation_years'] != 0);
                    $cond_c = $asset->acquisition_date != null;

                    if ($cond_a && $cond_b && $cond_c) {
                        if ($assetMethod) {
                            $depreciationFormula = $assetMethod->depreciation_type_id->getTranslateFormula();
                            $months = 12;
                            $days = 30;
                            $totalDays = 360;
                            $notDepreciated = false;
                            $depreciatedYears = $asset->assetDepreciationAssets->last()->depreciated_years ?? 0;

                            // Toma los valores del bien
                            $fisrtAcquisitionValue = $details['acquisition_value'];
                            $residualValue = $details['residual_value'];
                            $depresciationYears = $details['depresciation_years'];

                            if (count($asset->assetAdjustmentAssets) > 0) {
                                $fisrtAcquisitionValue = $asset->assetAdjustmentAssets->last()->assetBook->amount;
                                $residualValue = $asset->assetAdjustmentAssets->last()->residual_value;
                                $depresciationYears = $asset->assetAdjustmentAssets->last()->depresciation_years;
                            }

                            if (
                                count($asset->assetDepreciationAssets) > 0 &&
                                ($depreciatedYears + 1) > $depresciationYears &&
                                $asset->assetDepreciationAssets->last()->days_remaining == 0
                            ) {
                                // Indica que el bien ya no se puede depreciar
                                $notDepreciated = true;
                            }

                            if ($notDepreciated ===  false) {
                                $depreciationFormula = str_replace(
                                    'acquisition_value',
                                    currency_format($fisrtAcquisitionValue, 2, true),
                                    $depreciationFormula
                                );

                                $depreciationFormula = str_replace(
                                    'residual_value',
                                    currency_format($residualValue ?? 0, 2, true),
                                    $depreciationFormula
                                );

                                $depreciationFormula = str_replace(
                                    'depresciation_years',
                                    currency_format($depresciationYears, 2, true),
                                    $depreciationFormula
                                );

                                // Se calcula el resultado de la formula de depreciación
                                $string = 'select(' . $depreciationFormula . ')';
                                $calc = DB::select(DB::raw($string));
                                $col = '?column?';
                                $value = $calc[0]->$col ?? $calc[0]->case;

                                if ($exchangeRate && $asset->currency_id != $defaultCurrency->id) {
                                    $value = $value * $exchangeRate->amount;
                                }

                                // Se indica la fecha del periodo a tomar según el caso correspondiente
                                $useRemainingDays = false;

                                if (count($asset->assetDepreciationAssets) == 0) {
                                    // Toma la fecha de adquisición del bien y la divide en año, mes y día
                                    $date = explode('-', $asset->acquisition_date);
                                } elseif (
                                    count($asset->assetDepreciationAssets) > 0 &&
                                    ($depreciatedYears + 1) <= $depresciationYears
                                ) {
                                    // Toma la fecha de adquisición del bien y la divide en año, mes y día
                                    $date = explode('-', '2023-01-01');
                                } elseif (
                                    count($asset->assetDepreciationAssets) > 0 &&
                                    ($depreciatedYears + 1) > $depresciationYears &&
                                    $asset->assetDepreciationAssets->last()->days_remaining > 0
                                ) {
                                    // Usa los días restantes para depreciar
                                    $useRemainingDays = true;
                                }

                                // Calcula el valor diario de adquisición del bien
                                $dailyAcquisitionValue = ($value / 12) / 30;

                                if ($useRemainingDays == false) {
                                    // Calcula el valor de los meses pendientes para depreciar
                                    $pendingMonths = $months - $date[1];

                                    // Calcula el valor de los días pendientes para depreciar
                                    $pendingDays = ($days - $date[2]) + 1;

                                    // Calcula el valor de los meses pendientes para depreciar en días
                                    $pendingMonthsToDays = $pendingMonths * $days;

                                    // Calcula el valor del total de días pendientes para depreciar
                                    $totalDepreciationDays = $pendingDays + $pendingMonthsToDays;

                                    // Calcula el valor de los días que quedarón pendientes para el final de la depreciación
                                    $pendingDaysAfterDepreciation = $asset->assetDepreciationAssets->last() &&
                                        $asset->assetDepreciationAssets->last()->days_remaining > 0 ?
                                        $asset->assetDepreciationAssets->last()->days_remaining :
                                        $totalDays - $totalDepreciationDays;

                                    $value = $dailyAcquisitionValue * $totalDepreciationDays;
                                } else {
                                    $value = $dailyAcquisitionValue *
                                    $asset
                                        ->assetDepreciationAssets
                                        ->last()
                                        ->days_remaining;
                                    $pendingDaysAfterDepreciation = 0;
                                }

                                $depreciatedYears = $depreciatedYears + 1;
                                $totalDepreciationAmount = $totalDepreciationAmount + $value;

                                $arr[] = [
                                    'asset_id' => $asset->id,
                                    'name' => $asset['assetSpecificCategory']['name'],
                                    'amount' => $value,
                                    'depreciated_years' => $depreciatedYears,
                                    'remaining_days' => $pendingDaysAfterDepreciation
                                ];
                            }
                        } else {
                            throw new \Exception(
                                'Debe configurar previamente el método de depreciación a utilizar:asset.setting.index',
                                1
                            );
                        }
                    }
                }

                $documentStatus = DocumentStatus::where('action', 'PR')->first();

                $codeSetting = CodeSetting::where('table', 'asset_depreciations')->first();
                if (is_null($codeSetting)) {
                    throw new \Exception(
                        'Debe configurar previamente el formato para el código a generar:asset.setting.index',
                        1
                    );
                }

                $documentStatusCancel = DocumentStatus::where('action', 'AN')->first();
                $oldAssetDepreciations = AssetDepreciation::query()
                    ->where('document_status_id', '!=', $documentStatusCancel->id)
                    ->where('year', $currentFiscalYear->year)
                    ->get();

                if (count($oldAssetDepreciations) > 0) {
                    throw new \Exception(
                        'Solo se puede realizar una depreciación por año fiscal:asset.depreciation.index',
                        1
                    );
                }

                $code = generate_registration_code(
                    $codeSetting->format_prefix,
                    strlen($codeSetting->format_digits),
                    (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
                        substr($currentFiscalYear->year, 2, 2) : date('y')) : (isset($currentFiscalYear) ?
                        $currentFiscalYear->year : date('Y')),
                    AssetDepreciation::class,
                    $codeSetting->field
                );

                if (isset(auth()->user()->profile) && isset(auth()->user()->profile->institution_id)) {
                    $institution = Institution::where(['id' => auth()->user()->profile->institution_id])->first();
                } else {
                    $institution = Institution::where(['active' => true, 'default' => true])->first();
                }

                $assetDepreciation = AssetDepreciation::create([
                    'code' => $code,
                    'year' => $currentFiscalYear->year,
                    'amount' => $totalDepreciationAmount,
                    'document_status_id' => $documentStatus->id,
                    'institution_id' => $institution->id
                ]);

                foreach ($arr as $item) {
                    $lastAssetBook = AssetBook::query()
                        ->where('asset_id', $item['asset_id'])
                        ->toBase()
                        ->get()
                        ->last();

                    $amount = ($lastAssetBook?->amount - $item['amount']) < 0 ?
                        ($lastAssetBook?->amount - $item['amount']) * -1 :
                        ($lastAssetBook?->amount - $item['amount']);

                    $assetBook = AssetBook::create([
                        'asset_id' => $item['asset_id'],
                        'amount' => $amount,
                    ]);

                    AssetDepreciationAsset::create([
                        'asset_depreciation_id' => $assetDepreciation->id,
                        'asset_id' => $item['asset_id'],
                        'asset_book_id' => $assetBook->id,
                        'amount' => $item['amount'],
                        'depreciated_years' => $item['depreciated_years'],
                        'days_remaining' => $item['remaining_days']
                    ]);
                }
            });

            $request->session()->flash('message', ['type' => 'store']);
            return response()->json(['redirect' => route('asset.depreciation.index')], 200);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            $message = explode(':', $th->getMessage());
            $request->session()->flash('message', [
                'type' => 'other', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'growl-danger',
                'text' => $message[0],
            ]);

            return response()->json(['result' => false, 'redirect' => route($message[1])], 200);
        }
    }

    /**
     * Muestra los detalles de la depreciación de un bien
     *
     * @author Fabián Palmera <fpalmera@cenditel.gob.ve>
     *
     * @param integer $id Identificador del registro
     *
     * @return Renderable
     */
    public function show($id)
    {
        return view('asset::show');
    }

    /**
     * Muestra el formulario para editar la depreciación de un bien
     *
     * @author Fabián Palmera <fpalmera@cenditel.gob.ve>
     *
     * @param integer $id Identificador del registro
     *
     * @return Renderable
     */
    public function edit($id)
    {
        return view('asset::edit');
    }

    /**
     * Actualiza los datos de depreciación de un bien
     *
     * @author Fabián Palmera <fpalmera@cenditel.gob.ve>
     *
     * @param Request $request Datos de la petición
     * @param integer $id      Identificador del registro
     *
     * @return void
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Elimina la depreciación de un bien
     *
     * @author Fabián Palmera <fpalmera@cenditel.gob.ve>
     *
     * @param AssetDepreciation $depreciation Identificador del registro
     *
     * @return void
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Vizualiza la información de la desincorporación de un bien institucional
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param integer $id Identificador único de la desincorporación     *
     *
     * @return JsonResponse    Objeto con los registros a mostrar
     */
    public function vueInfo(Request $request, $id)
    {
        $assetDepreciation = AssetDepreciation::query()
            ->with('documentStatus')
            ->find($id);

        $assetDepreciationAssets = AssetDepreciationAsset::query()
            ->withTrashed()
            ->with('asset.assetSpecificCategory')
            ->where('asset_depreciation_id', $assetDepreciation->id)
            ->search($request->query('query'))
            ->paginate($request->limit ?? 10);

        return response()->json(
            [
                'record' => $assetDepreciation,
                'data' => $assetDepreciationAssets->items(),
                'count' => $assetDepreciationAssets->total(),
            ],
            200
        );
    }

    /**
     * Obtiene un listado de los bienes registradas
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param string  $operation    Tipo de operación realizada
     * @param integer $operation_id Identificador único de la operación
     *
     * @return JsonResponse    Objeto con los registros a mostrar
     */
    public function vueList()
    {
        $documentStatus = DocumentStatus::query()
            ->where('action', 'AP')
            ->first();

        $records = AssetDepreciation::query()
            ->with('documentStatus')
            ->get()
            ->map(function ($record) use ($documentStatus) {
                $record['has_approved_entry'] = false;

                if ($record->document_status_id == $documentStatus->id) {
                    $accountingEntry = \Modules\Accounting\Models\AccountingEntry::query()
                        ->where('reference', $record->code)
                        ->first();

                    if ($accountingEntry && $accountingEntry->approved == true) {
                        $record['has_approved_entry'] = true;
                    }
                }

                return $record;
            });

        return response()->json(
            ['records' => $records],
            200
        );
    }

    /**
     * Gestiona la generación de un informe en formato PDF basado en el tipo de informe
     * y código opcional proporcionados.
     *
     * @author Manuel Zambrano <mazambrano@cenditel.gob.ve>
     *
     * @param string      $type_report    El tipo de informe a generar.
     * @param string      $institution_id El identificador de la institución.
     * @param string|null $code           Código opcional para filtrar el informe.
     *
     * @return integer
     */

    public function managePdf(string $type_report, string $institution_id, ?string $code = null)
    {
        $title = 'Reporte de Depreciacion de Bienes';
        $hasQR = false;
        $hasBarCode = false;
        $logoAlign = 'L';
        $titleAlign = 'C';
        $subTitleAlign = 'L';
        $filename = ($code ? "reporte-{$type_report}-{$code}.pdf" : "reporte-{$type_report}.pdf");
        $subTitle = ($code ? 'Reporte Específico' : 'Reporte General');
        $assetsQuery = Asset::with([
            'institution',
            'assetSpecificCategory',
            'currency',
            'assetBook',
            'assetDepreciationAsset' => function ($query) {
                $query->with('assetDepreciation');
            }
        ])->where('institution_id', $institution_id)
        ->has('assetDepreciationAsset');

        if ($code) {
            $assetsQuery->where('asset_institutional_code', $code);
        }

        $assets = $assetsQuery->get();
        $institution = Institution::find($assets->first()->institution_id);
        $report = new ReportRepository();
        $report->setConfig([
            'institution' => $institution,
            'filename' => $filename,
        ]);
        $report->setHeader(
            $title,
            $subTitle,
            $hasQR,
            $hasBarCode,
            $logoAlign,
            $titleAlign,
            $subTitleAlign
        );
        $htmlTemplate = (
            $type_report === 'table'
            ? 'asset::pdf.asset_report_depreciation_table'
            : 'asset::pdf.asset_report_depreciation_accumulated'
        );

        $report->setFooter();
        $request = new Request();
        $reportData = AssetDepreciationResource::collection($assets)->toArray($request);
        $report->setBody($htmlTemplate, true, [
            'pdf' => $report,
            'request' => $reportData
        ]);
        return 0;
    }

    /**
     * Registra los asientos contables de la depreciación en caso que exista contabilidad
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param integer $id Identificador único de la operación     *
     *
     * @return JsonResponse    Objeto con los registros a mostrar
     */
    public function approve($id)
    {
        $exist_accounting = Module::has('Accounting') && Module::isEnabled('Accounting');

        if ($exist_accounting) {
            DB::transaction(function () use ($id) {
                $assetDepreciation = AssetDepreciation::query()
                    ->with('assetDepreciationAssets.asset.assetSubcategory')
                    ->find($id);

                $assets = [];

                $codeSetting = CodeSetting::where('table', 'accounting_entries')
                    ->first();

                $currentFiscalYear = FiscalYear::select('year')
                    ->where(['active' => true, 'closed' => false])
                    ->orderBy('year', 'desc')
                    ->first();

                $code  = generate_registration_code(
                    $codeSetting->format_prefix,
                    strlen($codeSetting->format_digits),
                    (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
                    substr($currentFiscalYear->year, 2, 2) : date('y')) : (isset($currentFiscalYear) ?
                    $currentFiscalYear->year : date('Y')),
                    \Modules\Accounting\Models\AccountingEntry::class,
                    $codeSetting->field
                );

                if (isset(auth()->user()->profile) && isset(auth()->user()->profile->institution_id)) {
                    $institution = Institution::where(['id' => auth()->user()->profile->institution_id])->first();
                } else {
                    $institution = Institution::where(['active' => true, 'default' => true])->first();
                }

                $currency = Currency::where('default', true)->orderBy('id', 'ASC')->first();

                $entryCategory = \Modules\Accounting\Models\AccountingEntryCategory::updateOrCreate(
                    [
                        'acronym' => 'DPR',
                    ],
                    [
                        'name' => 'Depreciación anual',
                        'institution_id' => $institution->id
                    ]
                );

                $accountEntry = \Modules\Accounting\Models\AccountingEntry::create([
                    'from_date'                      => $currentFiscalYear->year . '-12-31',
                    'reference'                      => $assetDepreciation->code ?? $code,
                    'concept'                        => 'Depreciación de bienes',
                    'observations'                   => null,
                    'accounting_entry_category_id'   => $entryCategory->id,
                    'institution_id'                 => $institution->id,
                    'currency_id'                    => $currency->id,
                    'tot_debit'                      => $assetDepreciation->amount,
                    'tot_assets'                     => $assetDepreciation->amount,
                    'approved'                       => false
                ]);

                \Modules\Accounting\Models\AccountingEntryable::create([
                    'accounting_entry_id' => $accountEntry->id,
                    'accounting_entryable_type' => AssetDepreciation::class,
                    'accounting_entryable_id' => $id,
                ]);

                foreach ($assetDepreciation->assetDepreciationAssets as $asset) {
                    $subcategoryId = $asset->asset->assetSubcategory->id;

                    if (!isset($assets[$subcategoryId])) {
                        $assets[$subcategoryId] = [];
                    }

                    $assets[$subcategoryId][] = $asset;
                }

                foreach ($assets as $groupedAssets) {
                    $amountDebit = 0;
                    $amountAssets = 0;
                    $debitAccount = '';
                    $assetAccount = '';

                    foreach ($groupedAssets as $asset) {
                        $debitAccount = $asset->asset->assetSubcategory->accounting_account_debit;
                        $assetAccount = $asset->asset->assetSubcategory->accounting_account_asset;
                        $amountDebit += $asset->amount;
                        $amountAssets += $asset->amount;
                    }

                    \Modules\Accounting\Models\AccountingEntryAccount::create([
                        'accounting_entry_id' => $accountEntry->id,
                        'accounting_account_id' => $debitAccount,
                        'debit' => $amountDebit,
                        'assets' => 0,
                    ]);

                    \Modules\Accounting\Models\AccountingEntryAccount::create([
                        'accounting_entry_id' => $accountEntry->id,
                        'accounting_account_id' => $assetAccount,
                        'debit' => 0,
                        'assets' => $amountAssets,
                    ]);
                }

                $documentStatus = DocumentStatus::where('action', 'AP')->first();
                $assetDepreciation->document_status_id = $documentStatus->id;
                $assetDepreciation->save();

                return response()->json(['message' => 'Success'], 200);
            });
        }

        return response()->json(['result' => false, 'message' => [
            'type' => 'custom', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'danger',
            'text' => 'Debe tener instalado el módulo de contabilidad para acceder a esta funcionalidad',
        ]], 403);
    }

    /**
     * Anula la depreciación y todos sus registros asociados
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param integer $id Identificador único de la operación
     *
     * @return JsonResponse    Objeto con los registros a mostrar
     */
    public function cancel($id)
    {
        $exist_accounting = Module::has('Accounting') && Module::isEnabled('Accounting');

        if ($exist_accounting) {
            $documentStatus = DocumentStatus::query()
                ->where('action', 'AP')
                ->first();

            $documentStatusCancel = DocumentStatus::query()
                ->where('action', 'AN')
                ->first();

            // Se busca el registro de la depreciación
            $assetDepreciation = AssetDepreciation::query()
                ->with('assetDepreciationAssets.assetBook')
                ->find($id);

            // Se elimina el asiento contable de la depreciación en caso de que esté aprobada
            if ($assetDepreciation->document_status_id == $documentStatus->id) {
                $accountingEntry = \Modules\Accounting\Models\AccountingEntry::query()
                    ->where('reference', $assetDepreciation->code)
                    ->first();

                if ($accountingEntry) {
                    \Modules\Accounting\Models\AccountingEntryAccount::query()
                        ->where('accounting_entry_id', $accountingEntry->id)
                        ->delete();

                    \Modules\Accounting\Models\AccountingEntryable::query()
                        ->where('accounting_entry_id', $accountingEntry->id)
                        ->delete();

                    $accountingEntry->delete();
                }
            }

            // Se eliminan todos los registros asociados a esa depreciación
            if ($assetDepreciation) {
                foreach ($assetDepreciation->assetDepreciationAssets as $depreciationAsset) {
                    AssetBook::query()
                        ->where('id', $depreciationAsset->asset_book_id)
                        ->delete();

                    $depreciationAsset->delete();
                }

                $assetDepreciation->document_status_id = $documentStatusCancel->id;
                $assetDepreciation->save();
            }

            return response()->json(['message' => 'Success'], 200);
        }

        return response()->json(['result' => false, 'message' => [
            'type' => 'custom', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'danger',
            'text' => 'Debe tener instalado el módulo de contabilidad para acceder a esta funcionalidad',
        ]], 403);
    }
}
