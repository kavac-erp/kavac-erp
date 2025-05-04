<?php

namespace Modules\Purchase\Jobs;

use App\Models\CodeSetting;
use App\Models\FiscalYear;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Purchase\Models\PurchaseBaseBudget;
use Modules\Purchase\Models\PurchasePivotModelsToRequirementItem;
use Modules\Purchase\Models\PurchaseRequirement;
use Modules\Purchase\Models\PurchaseRequirementItem;
use Modules\Purchase\Models\PurchaseProduct;

/**
 * @class PurchaseManageRequirements
 * @brief Ejecuta los trabajos para la gestión de los requerimientos de compras
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseManageRequirements implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Objeto que contiene la información asociada a la solicitud
     *
     * @var object $asset
     */
    protected $data;

    /**
     * Variable que contiene el tiempo de espera para la ejecución del trabajo,
     * si no se quiere limite de tiempo, se define en 0
     *
     * @var integer $timeout
     */
    public $timeout = 0;

    /**
     * Crea una nueva instancia del trabajo
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = $this->data;
        if ($data['action'] == 'update') {
            $requirement = PurchaseRequirement::find($data['id_edit']);
            $requirement->description = $data['description'];
            $requirement->date = $data['date'];
            $requirement->contracting_department_id = $data['contracting_department_id'];
            $requirement->user_department_id = $data['user_department_id'];
            $requirement->prepared_by_id = $data['prepared_by_id'];
            $requirement->reviewed_by_id = $data['reviewed_by_id'];
            $requirement->verified_by_id = $data['verified_by_id'];
            $requirement->first_signature_id = $data['first_signature_id'];
            $requirement->second_signature_id = $data['second_signature_id'];
            $requirement->requirement_type = $data['requirement_type'];
            $requirement->save();

            $baseBudget = PurchaseBaseBudget::find($requirement->purchase_base_budget_id);
            if ($baseBudget) {
                $baseBudget->date = $data['date'];
                $baseBudget->save();
            }

            foreach ($data['toDelete'] as $toDeleteId) {
                PurchasePivotModelsToRequirementItem::where('purchase_requirement_item_id', $toDeleteId)->delete();
                PurchaseRequirementItem::find($toDeleteId)->delete();
            }

            foreach ($data['products'] as $prod) {
                if (!empty($prod['purchase_product'])) {
                    $p = PurchaseRequirementItem::find($prod['id']);
                    if ($p) {
                        $p['description'] = $prod['description'];
                        $p['technical_specifications'] = $prod['technical_specifications'];
                        $p['quantity'] = $prod['quantity'];
                        $p['purchase_product_id'] = $prod['purchase_product']['id'];
                        $p['history_tax_id'] = $prod['history_tax_id'];
                        $p['measurement_unit_id'] = $prod['measurement_unit_id'];
                        $p['purchase_requirement_id'] = $requirement->id;
                        $p['quantity'] = $prod['quantity'];
                        $p->save();
                    }
                } else {
                    if (array_key_exists('product_requirement_id', $prod)) {
                        PurchasePivotModelsToRequirementItem::
                            where('purchase_requirement_item_id', $prod['product_requirement_id'])
                            ->where('relatable_id', $data['id_edit'])->delete();
                        PurchaseRequirementItem::find($prod['product_requirement_id'])->delete();
                    }
                    $purchaseProd = PurchaseProduct::find($prod['id']);
                    $item = PurchaseRequirementItem::create([
                        'name' => $purchaseProd->code . ' - ' . $purchaseProd->name,
                        'description' => $purchaseProd->description,
                        'technical_specifications' => $prod['technical_specifications'],
                        'quantity' => $prod['quantity'],
                        'purchase_product_id' => $prod['id'],
                        'history_tax_id' => $prod['history_tax_id'],
                        'measurement_unit_id' => $prod['measurement_unit_id'],
                        'purchase_requirement_id' => $requirement->id,
                    ]);
                    PurchasePivotModelsToRequirementItem::create([
                        'relatable_type' => PurchaseBaseBudget::class,
                        'relatable_id' => $baseBudget->id,
                        'purchase_requirement_item_id' => $item->id,
                        'unit_price' => 0,
                    ]);
                }
            }
        } elseif ($data['action'] == 'create') {
            $data['code'] = $this->generateCodeAvailable();
            $baseBudget = PurchaseBaseBudget::create([
                'date' => $data['date'],
                'prepared_by_id' => $data['prepared_by_id'],
                'reviewed_by_id' => $data['reviewed_by_id'],
                'verified_by_id' => $data['verified_by_id'],
                'first_signature_id' => $data['first_signature_id'],
                'second_signature_id' => $data['second_signature_id'],
                'subtotal' => 0,
            ]);
            $data['purchase_base_budget_id'] = $baseBudget->id;

            $requirement = PurchaseRequirement::create($data);

            foreach ($data['products'] as $prod) {
                $prod['purchase_requirement_id'] = $requirement->id;
                $purchaseProd = PurchaseProduct::find($prod['id']);
                $item = PurchaseRequirementItem::create([
                    'name' => $purchaseProd->code . ' - ' . $purchaseProd->name,
                    'description' => $purchaseProd->description,
                    'technical_specifications' => $prod['technical_specifications'],
                    'quantity' => $prod['quantity'],
                    'purchase_product_id' => $prod['id'],
                    'history_tax_id' => $prod['history_tax_id'],
                    'measurement_unit_id' => $prod['measurement_unit_id'],
                    'purchase_requirement_id' => $requirement->id,
                ]);

                PurchasePivotModelsToRequirementItem::create([
                    'relatable_type' => PurchaseBaseBudget::class,
                    'relatable_id' => $baseBudget->id,
                    'purchase_requirement_item_id' => $item->id,
                    'unit_price' => 0,
                ]);
            }
        }
    }

    /**
     * Genera el código disponible para el próximo registro de requerimiento de compra
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return string
     */
    public function generateCodeAvailable()
    {
        $codeSetting = CodeSetting::where('table', 'purchase_requirements')
            ->first();

        if ($codeSetting) {
            $currentFiscalYear = FiscalYear::select('year')
                ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();

            $code = generate_registration_code(
                $codeSetting->format_prefix,
                strlen($codeSetting->format_digits),
                (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
                    substr($currentFiscalYear->year, 2, 2) : date('y')) : (isset($currentFiscalYear) ?
                    $currentFiscalYear->year : date('Y')),
                PurchaseRequirement::class,
                $codeSetting->field
            );
        } else {
            $code = 'error al generar código de referencia';
        }

        return $code;
    }
}
