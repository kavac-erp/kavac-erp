<?php

namespace Modules\Purchase\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Purchase\Models\PurchaseBaseBudget;
use Modules\Purchase\Models\PurchasePivotModelsToRequirementItem;
use Modules\Purchase\Models\PurchaseRequirement;

/**
 * @class PurchaseManageBaseBudget
 * @brief Ejecuta los trabajos para la gestión del presupuesto base en compras
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseManageBaseBudget implements ShouldQueue
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
     * Ejecuta el trabajo
     *
     * @return void
     */
    public function handle()
    {
        $data = $this->data;
        if ($data['action'] == 'create') {
            $baseBudget = PurchaseBaseBudget::create([
                'currency_id' => $data['currency_id'],
                'tax_id' => $data['tax_id'],
                'status' => 'WAIT_QUOTATION',
                'subtotal' => $data['subtotal'],
            ]);
            foreach ($data['list'] as $requirement) {
                $rq = PurchaseRequirement::find($requirement['id']);
                //ya no se cambiará el estado del requerimiento en esta parte del proceso
                //$rq->requirement_status = 'PROCESSED';
                $rq->purchase_base_budget_id = $baseBudget['id'];
                $rq->save();

                foreach ($requirement['purchase_requirement_items'] as $item) {
                    PurchasePivotModelsToRequirementItem::create([
                        'purchase_requirement_item_id' => $item['id'],
                        'relatable_type' => PurchaseBaseBudget::class,
                        'relatable_id' => $baseBudget['id'],
                        'unit_price' => $item['unit_price'],
                    ]);
                }
            }
        } elseif ($data['action'] == 'update') {
            $baseBudget = PurchaseBaseBudget::find($data['id_edit']);
            $baseBudget->currency_id = $data['currency_id'];
            $baseBudget->subtotal = $data['total'];
            $baseBudget->status = 'WAIT_QUOTATION';
            $baseBudget->prepared_by_id = $data['prepared_by_id'];
            $baseBudget->reviewed_by_id = $data['reviewed_by_id'];
            $baseBudget->verified_by_id = $data['verified_by_id'];
            $baseBudget->first_signature_id = $data['first_signature_id'];
            $baseBudget->second_signature_id = $data['second_signature_id'];
            $baseBudget->save();

            foreach ($data['list_to_delete'] as $requirement) {
                // trae requerimiento
                $rq = PurchaseRequirement::find($requirement['id']);

                if ($rq) {
                    $rq->requirement_status = 'WAIT';
                    $rq->purchase_base_budget_id = null;
                    $rq->save();
                }
                foreach ($requirement['purchase_requirement_items'] as $item) {
                    $r = PurchasePivotModelsToRequirementItem::where('purchase_requirement_item_id', $item['id'])
                        ->fisrt();
                    if ($r) {
                        $r->delete();
                    }
                }
            }

            foreach ($data['list'] as $requirement) {
                $rq = PurchaseRequirement::find($requirement['id']);
                $rq->purchase_base_budget_id = $baseBudget['id'];
                $rq->prepared_by_id = $data['prepared_by_id'];
                $rq->reviewed_by_id = $data['reviewed_by_id'];
                $rq->verified_by_id = $data['verified_by_id'];
                $rq->first_signature_id = $data['first_signature_id'];
                $rq->second_signature_id = $data['second_signature_id'];

                $rq->save();

                foreach ($requirement['purchase_requirement_items'] as $item) {
                    PurchasePivotModelsToRequirementItem::updateOrcreate([
                        'purchase_requirement_item_id' => $item['id'],
                        'relatable_type' => PurchaseBaseBudget::class,
                        'relatable_id' => $baseBudget['id'],
                    ], [
                        'unit_price' => $item['unit_price'],
                    ]);
                }
            }
        }
    }
}
