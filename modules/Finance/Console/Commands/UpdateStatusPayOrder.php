<?php

namespace Modules\Finance\Console\Commands;

use App\Models\DocumentStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Budget\Models\BudgetCompromise;
use Modules\Budget\Models\BudgetStage;
use Modules\Finance\Models\FinancePayOrder;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @class updateStatusPayOrder
 * @brief Actualiza el estatus de las ordenes de pago
 *
 * @author Francisco J. P. Ruiz <fpenya@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateStatusPayOrder extends Command
{
    /**
     * El nombre del comando.
     *
     * @var string $signature
     */
    protected $signature = 'module:finance-update-status-an-pay-order
                        {compromise_id : id del compromiso}';

    /**
     * La descripción del comando.
     *
     * @var string $description
     */
    protected $description = 'Comando para actualizar el estado de las ordenes de pago a anulado';

    /**
     * Crea una nueva instancia del comando.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Ejecuta la consola de comandos.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $count = 0;
            $compromise_id = $this->argument('compromise_id');

            DB::transaction(function () use ($count, $compromise_id) {
                $documentStatusAN = DocumentStatus::where('action', 'AN')->first();
                $payOrders = FinancePayOrder::query()
                ->where(
                    'document_status_id',
                    '!=',
                    $documentStatusAN->id
                )->where(
                    'document_sourceable_id',
                    $compromise_id
                )
                ->get();

                foreach ($payOrders as $payOrder) {
                    $payOrder['document_status_id'] = $documentStatusAN->id;
                    $payOrder->save();
                    BudgetStage::query()
                    ->where([
                        'budget_compromise_id'  => $compromise_id,
                        'stageable_type'        => FinancePayOrder::class,
                        'stageable_id'          => $payOrder->id
                        ])
                    ->where('type', 'PAG')
                    ->OrWhere('type', 'CAU')
                    ->Orwhere('type', 'COM')
                    ->delete();

                    $count++;
                }
            });

            $this->info($count . ' pay orders aupdate successfully');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $this->error($e->getMessage());
        }
    }
}
