<?php

namespace Modules\Purchase\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Nwidart\Modules\Facades\Module;
use Modules\Purchase\Models\PurchaseBaseBudget;
use Modules\Purchase\Models\PurchaseBudgetaryAvailability;

/**
 * @class UpdateStatusBudgetaryAvailability
 * @brief Comando para actualizar los estatus de las disponibilidades presupuestarias en los presupuestos base y las nóminas.
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateStatusBudgetaryAvailability extends Command
{
    /**
     * El nombre del comando.
     *
     * @var string $signature
     */
    protected $signature = 'module:update-status-budgetary-availability';

    /**
     * La descripción del comando.
     *
     * @var string $description
     */
    protected $description = 'Actualizar los estatus de las disponibilidades presupuestarias en los presupuestos base y las nóminas.';

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
     * @return integer|void
     */
    public function handle()
    {
        $this->info("Inicio de actualización de los estados de disponibilidad presupuestaria a aprobados...\n");

        try {
            $PurchaseBaseBudgets = PurchaseBaseBudget::query()
            ->where('status', 'QUOTED')
            ->orWhere('status', 'PARTIALLY_QUOTED')
            ->with('purchaseRequirement')
            ->get();

            $Payrolls = (
                Module::has('Payroll') && Module::isEnabled('Payroll')
            ) ? \Modules\Payroll\Models\Payroll::query()
                ->whereHas('payrollPaymentPeriod', function ($query) {
                    $query->where('availability_status', 'available')
                        ->where('payment_status', 'approved')
                        ->orWhere('payment_status', 'generated');
                })->get()
              : [];
            $count = 0;
            DB::transaction(function () use ($PurchaseBaseBudgets, $Payrolls, &$count) {
                foreach ($PurchaseBaseBudgets as $PurchaseBaseBudget) {
                    try {
                        $BudgetaryAvailabilities = PurchaseBudgetaryAvailability::query()
                        ->where('purchase_base_budgets_id', $PurchaseBaseBudget->id)
                        ->get();
                        foreach ($BudgetaryAvailabilities as $BudgetaryAvailability) {
                            $BudgetaryAvailability['availability'] = 2; //Aprobado
                            $BudgetaryAvailability->save();
                        }
                        $this->info('Presupuesto base actualizado: ' . $PurchaseBaseBudget->purchaseRequirement->code);
                        $count++;
                    } catch (\Throwable $th) {
                        Log::error($th->getMessage());
                        $this->info($th->getMessage() . ' - ' . $PurchaseBaseBudget->purchaseRequirement->code);
                        continue;
                    }
                }

                foreach ($Payrolls as $Payroll) {
                    try {
                        $payrollPaymentPeriod = $Payroll->payrollPaymentPeriod;
                        if ($payrollPaymentPeriod) {
                            if (
                                $payrollPaymentPeriod->availability_status == 'available'
                                && ($payrollPaymentPeriod->payment_status == 'approved'
                                    || $payrollPaymentPeriod->payment_status == 'generated')
                            ) {
                                //cambiar el estatus a aprobado
                                $Payroll->payrollPaymentPeriod->availability_status = 'AP';
                                $Payroll->payrollPaymentPeriod->save();
                                $this->info('Updated payroll: ' . $Payroll->code);
                                $count++;
                            }
                        }
                    } catch (\Throwable $th) {
                        Log::error($th->getMessage());
                        $this->info($th->getMessage() . ' - ' . $Payroll->code);
                        continue;
                    }
                }
            });

            $this->info("\nEl estatus de la disponibilidad presupuestaria {$count} a sido actualizada a aprobada");
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            $this->info($th->getMessage());
        }
    }
}
