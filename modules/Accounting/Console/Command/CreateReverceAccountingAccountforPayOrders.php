<?php

namespace Modules\Accounting\Console\Command;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Nwidart\Modules\Facades\Module;
use Modules\Accounting\Models\AccountingEntry;
use Modules\Accounting\Models\AccountingEntryable;
use Modules\Accounting\Models\AccountingEntryAccount;

/**
 * @class CreateReverceAccountingAccountforPayOrders
 * @brief Crea el reverso de los asientos contables de las ordenes de pago pertenecientes al compromiso.
 *
 * Gestiona los asientos contables de las ordenes de pago
 *
 * @author Ing. Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateReverceAccountingAccountforPayOrders extends Command
{
    /**
     * El nombre del comando.
     *
     * @var string $signature
     */
    protected $signature = 'module:accounting-create-reverse-accounting-account-for-pay-orders
                            {compromise_id : El ID del compromiso.}
                            {document_number : El código de la referencia del compromiso.}
                            {canceled_at : La fecha de anulación (YYYY-MM-DD).}';

    /**
     * Descripción del comando.
     *
     * @var string $description
     */
    protected $description = 'Crea el reverso de los asientos contables de las ordenes de pago pertenecientes al compromiso.';

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
            $document_number = $this->argument('document_number');
            $canceled_at = $this->argument('canceled_at');
            $isBudget = Module::has('Budget') && Module::isEnabled('Budget');
            $isAccounting = Module::has('Accounting') && Module::isEnabled('Accounting');
            $isFinance = Module::has('Finance') && Module::isEnabled('Finance');

            DB::transaction(function () use ($count, $compromise_id, $document_number, $canceled_at, $isFinance, $isBudget, $isAccounting) {
                if ($isBudget) {
                    $budgetCompromise = (
                        Module::has('Budget') && Module::isEnabled('Budget')
                    ) ? \Modules\Budget\Models\BudgetCompromise::query()
                    ->where('id', $compromise_id)
                    ->where('document_number', $document_number)
                    ->first() : null;

                    if ($budgetCompromise && $isFinance) {
                        $financePayOrders = (
                            Module::has('Finance') && Module::isEnabled('Finance')
                        ) ? \Modules\Finance\Models\FinancePayOrder::query()
                        ->where(
                            'document_sourceable_id',
                            $budgetCompromise->id
                        )->get() : [];

                        foreach ($financePayOrders as $financePayOrder) {
                            $count++;

                            if ($isAccounting) {
                                /* Reverso de Asiento contable de la orden de pago */
                                $accountEntry = AccountingEntry::where('reference', $financePayOrder->code);

                                if ($accountEntry && $accountEntry->count() == 1) {
                                    $accountEntry = $accountEntry->first();
                                    $accountEntryNew = AccountingEntry::create([
                                        'from_date' => $canceled_at,
                                        // Código de la orden de pago como referencia
                                        'reference' => $financePayOrder->code,
                                        'concept' => 'Anulación: ' . $accountEntry->concept ,
                                        'observations' => $financePayOrder->observations,
                                        'accounting_entry_category_id' => $accountEntry->accounting_entry_category_id,
                                        'institution_id' => $accountEntry->institution_id,
                                        'currency_id' => $accountEntry->currency_id,
                                        'tot_debit' => $accountEntry->tot_assets,
                                        'tot_assets' => $accountEntry->tot_debit,
                                        'approved' => false,
                                    ]);

                                    $accountingItems = AccountingEntryAccount::query()
                                    ->where(
                                        'accounting_entry_id',
                                        $accountEntry->id,
                                    )->get();
                                    foreach ($accountingItems as $account) {
                                        /*
                                         * Se crea la relación de cuenta a ese asiento
                                         */
                                        AccountingEntryAccount::create([
                                            'accounting_entry_id' => $accountEntryNew->id,
                                            'accounting_account_id' => $account['accounting_account_id'],
                                            'debit' => $account['assets'],
                                            'assets' => $account['debit'],
                                        ]);
                                    }

                                    if (Module::has('Finance') && Module::isEnabled('Finance')) {
                                        /* Crea la relación entre el asiento contable y el registro de orden de pago */
                                        AccountingEntryable::create([
                                            'accounting_entry_id' => $accountEntryNew->id,
                                            'accounting_entryable_type' => \Modules\Finance\Models\FinancePayOrder::class,
                                            'accounting_entryable_id' => $financePayOrder->id,
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
            });

            $this->info('reverse accounting accounts created successfully');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $this->error($e->getMessage());
        }
    }
}
