<?php

namespace Modules\Finance\Console\Commands;

use Exception;
use App\Models\DocumentStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Nwidart\Modules\Facades\Module;
use Modules\Budget\Models\BudgetCompromise;
use Modules\Budget\Models\BudgetAccountOpen;
use Modules\Accounting\Models\AccountingEntry;
use Symfony\Component\Console\Input\InputOption;
use Modules\Budget\Models\BudgetCompromiseDetail;
use Modules\Accounting\Models\AccountingEntryable;
use Modules\Finance\Models\FinanceBankingMovement;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @class DeleleBankMovement
 * @brief Elimina un movimiento bancario dado su ID
 *
 * Elimina un movimiento bancario dado su ID
 *
 * @author Francisco J. P. Ruiz <fpenya@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class DeleteBankMovement extends Command
{
    /**
     * El nombre del comando.
     *
     * @var string $signature
     */
    protected $signature = 'module:finance-delete-bank-movement
                            {bank_movement_id : ID del movimiento bancario a eliminar}';

    /**
     * La descripción del comando.
     *
     * @var string $description
     */
    protected $description = 'Elimina un movimiento bancario dado su ID';

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
            // Limpia el cache
            shell_exec('php artisan cache:clear');

            $bank_movement_id = $this->argument('bank_movement_id');
            $this->info('Eliminando movimiento bancario ' . $bank_movement_id);

            // Elimina el movimiento
            try {
                $bankMovement = FinanceBankingMovement::query()
                    ->findOrFail($bank_movement_id);

                $document_estatus_an_id = DocumentStatus::where('action', 'AN')->value('id');

                if ($bankMovement->is_payment_executed) {
                    new Exception('No se puede eliminar este movimiento bancario, ya que proviene de una emisión de pago');
                }

                if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
                    /* Se consultan los asientos contables relacionados al movimiento bancario */
                    $accounting_ids = AccountingEntryable::query()
                        ->where('accounting_entryable_type', 'Modules\Finance\Models\FinanceBankingMovement')
                        ->where('accounting_entryable_id', $bankMovement->id)
                        ->pluck('accounting_entry_id');

                    $accountancies = AccountingEntry::query()->whereIn('id', $accounting_ids)->get();

                    /* Se actualizan las referencias de los asientos contables */
                    $accountancies->each(function ($accounting) {
                        $accounting->accountingAccounts()->forceDelete();
                        $accounting->pivotEntryable()->forceDelete();
                        $sda = $accounting->forceDelete();

                        dump("Estus de la eliminación del asiento contable {$accounting->id}, con referencia {$accounting->reference}: ", $sda);
                    });
                }

                if (Module::has('Budget') && Module::isEnabled('Budget')) {
                    $budgetCompromise = BudgetCompromise::where('compromiseable_type', 'Modules\Finance\Models\FinanceBankingMovement')
                        ->where('compromiseable_id', $bankMovement->id)->firstOrFail();

                    if ($bankMovement->document_status_id != $document_estatus_an_id) {
                        $compromiseDetails = BudgetCompromiseDetail::where('budget_compromise_id', $budgetCompromise->id)->get();
                        $compromisedYear = explode("-", $budgetCompromise->compromised_at)[0];

                        if ($compromisedYear) {
                            foreach ($compromiseDetails as $budgetCompromiseDetail) {
                                $formulation = $budgetCompromiseDetail
                                    ->budgetSubSpecificFormulation()
                                    ->where('year', $compromisedYear)->firstOrFail();

                                $taxAmount = isset($budgetCompromiseDetail['tax_id'])
                                ? $budgetCompromiseDetail['amount'] : 0;

                                $total = $taxAmount != 0
                                ? $taxAmount : $budgetCompromiseDetail['amount'];

                                $budgetAccountOpen = BudgetAccountOpen::with('budgetAccount')
                                ->where(
                                    'budget_sub_specific_formulation_id',
                                    $formulation->id
                                )->where(
                                    'budget_account_id',
                                    $budgetCompromiseDetail['budget_account_id']
                                )
                                ->whereHas('budgetAccount', function ($query) {
                                    $query->where('specific', '!=', '00');
                                })->first();
                                if (isset($budgetAccountOpen)) {
                                    $budgetAccountOpen->update([
                                        'total_year_amount_m'
                                            => $budgetAccountOpen->total_year_amount_m + $total,
                                    ]);
                                }

                                $budgetCompromiseDetail->forceDelete();
                            }
                        }

                        $budgetCompromise->budgetCompromiseDetails()->withTrashed()?->forceDelete();
                        $budgetCompromise->budgetStages()->withTrashed()?->forceDelete();
                    } else {
                        $budgetCompromise->budgetCompromiseDetails()->withTrashed()?->forceDelete();
                        $budgetCompromise->budgetStages()->withTrashed()?->forceDelete();
                    }
                    $sdc = $budgetCompromise->forceDelete();
                    dump("Estus de la eliminación del compromiso {$budgetCompromise->id}, {$budgetCompromise->code}: ", $sdc);
                }
                $sdbm = $bankMovement->ForceDelete();

                dump("Estus de la eliminación del movimiento {$bankMovement->id}, {$bankMovement->code}: ", $sdbm);
                $this->info("Movimiento bancario {$bank_movement_id}, {$bankMovement->code} eliminado correctamente");
                // Limpia el cache
                shell_exec('php artisan cache:clear');
            } catch (Exception $e) {
                Log::error($e->getMessage());
                $this->error($e->getMessage());
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            $this->error($th->getMessage());
        }
    }

    /**
     * Obtiene los argumentos del comando.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['example', InputArgument::REQUIRED, 'An example argument.'],
        ];
    }

    /**
     * Obtiene las opciones del comando.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
        ];
    }
}
