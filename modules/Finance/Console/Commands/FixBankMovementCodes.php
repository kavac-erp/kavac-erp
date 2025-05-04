<?php

namespace Modules\Finance\Console\Commands;

use App\Models\FiscalYear;
use App\Models\CodeSetting;
use App\Models\Institution;
use App\Models\DocumentStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Nwidart\Modules\Facades\Module;
use Modules\Budget\Models\BudgetCompromise;
use Modules\Accounting\Models\AccountingEntry;
use Symfony\Component\Console\Input\InputOption;
use Modules\Accounting\Models\AccountingEntryable;
use Modules\Finance\Models\FinanceBankingMovement;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @class FixBankMovementCodes
 * @brief Reestructurar la secuencia de los códigos de movimientos bancarios.
 *
 * Corrije la secuencia de los códigos de movimientos bancarios
 *
 * @author Francisco J. P. Ruiz <fpenya@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FixBankMovementCodes extends Command
{
    /**
     * El nombre del comando.
     *
     * @var string $signature
     */
    protected $signature = 'module:finance-fix-bank-movement-codes';

    /**
     * La descripción del comando.
     *
     * @var string $description
     */
    protected $description = 'Reestructurar la secuencia de los códigos de movimientos bancarios.';

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
            shell_exec('php artisan cache:clear');
            $this->info('Se reestructurará la secuencia de los códigos de movimientos bancarios registrados.');

            $codeSetting = CodeSetting::where('table', 'finance_movements_code')->firstOrFail();

            $institution = Institution::query()->where('default', true)->firstOrFail();

            $currentFiscalYear = FiscalYear::query()
            ->where([
                'active' => true,
                'closed' => false,
                'institution_id' => $institution->id
            ])->orderBy('year', 'desc')->firstOrFail();

            $YEAR = (strlen($codeSetting->format_year) == 2)
            ? substr($currentFiscalYear->year, 2, 2)
            : $currentFiscalYear->year;

            $isAccounting = false;
            if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
                $isAccounting = true;
            }

            $isBudget = false;
            if (Module::has('Budget') && Module::isEnabled('Budget')) {
                $isBudget = true;
            }

            /* Reemplazar la secuencia de los códigos de movimientos con su ID */
            DB::beginTransaction();
            try {
                $financeBankMovements = FinanceBankingMovement::query()
                ->whereYear('payment_date', '=', $currentFiscalYear->year)
                ->orderBy('id', 'asc')
                ->withTrashed()
                ->get();

                $financeBankMovements->each(function ($financeBankMovement) {
                    $financeBankMovement['code'] = $financeBankMovement->id;
                    $financeBankMovement->save();
                });
                DB::commit();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
                DB::rollback();
                $this->error($e->getMessage());
            }

            $document_estatus_id = DocumentStatus::where('action', 'AN')->value('id');
            /* Se consultan de nuevo los movimientos bancarios actualizados */
            $financeBankMovements = FinanceBankingMovement::query()
            ->whereYear('payment_date', '=', $currentFiscalYear->year)
            ->withTrashed()
            ->orderBy('payment_date', 'asc')->orderBy('id', 'asc');

            $count = $financeBankMovements->count();
            $i = 0;
            /* Se reestructura la secuencia de los códigos de movimientos bancarios para ese año fiscal */
            foreach ($financeBankMovements->get() as $financeBankMovement) {
                $codeMovement = $this->customGenerateRegistrationCodeForBankMovements(
                    $codeSetting->format_prefix,
                    strlen($codeSetting->format_digits),
                    $YEAR,
                    $codeSetting->model,
                    $codeSetting->field,
                    'payment_date'
                );

                DB::beginTransaction();
                try {
                    $financeBankMovement['code'] = $codeMovement;

                    $i++;

                    $this->info("Se reestructuró el código de Movimiento: {$codeMovement} ({$i} de {$count})");

                    if ($isAccounting && !$financeBankMovement->is_payment_executed) {
                        /* Se consultan los asientos contables relacionados al movimiento bancario */
                        $accounting_ids = AccountingEntryable::query()
                            ->where('accounting_entryable_type', 'Modules\Finance\Models\FinanceBankingMovement')
                            ->where('accounting_entryable_id', $financeBankMovement->id)
                            ->pluck('accounting_entry_id');

                        $accountancies = AccountingEntry::query()->whereIn('id', $accounting_ids)->get();

                        /* Se actualizan las referencias de los asientos contables */
                        $accountancies->each(function ($accounting) use ($codeMovement) {
                            $accounting->reference = $codeMovement;
                            $accounting->save();
                        });
                    }

                    if ($isBudget) {
                        $budgetCompromise = BudgetCompromise::query()
                            ->where('compromiseable_type', 'Modules\Finance\Models\FinanceBankingMovement')
                            ->where('compromiseable_id', $financeBankMovement->id)
                            ->first();

                        if ($budgetCompromise) {
                            if (!$budgetCompromise->budgetCompromiseDetails()->count() && $financeBankMovement->document_status_id != $document_estatus_id) {
                                $budgetCompromise->budgetCompromiseDetails()->withTrashed()?->forceDelete();
                                $budgetCompromise->budgetStages()->withTrashed()?->forceDelete();
                                $sd = $budgetCompromise->forceDelete();
                                $financeBankMovement['transaction_type'] = $sd && $financeBankMovement['transaction_type'] == 'Nota de débito'
                                    ? 'Nota de crédito'
                                    : $financeBankMovement['transaction_type'];
                                dump("estus de la eliminación del compromiso {$budgetCompromise->id}, {$budgetCompromise->code}: ", $sd);
                            } else {
                                $budgetCompromise->document_number = $codeMovement;
                                $budgetCompromise->save();
                            }
                        } elseif ($financeBankMovement['transaction_type'] == 'Nota de débito' && !$financeBankMovement->is_payment_executed) {
                            $financeBankMovement['transaction_type'] = 'Nota de crédito';
                        }
                    }
                    $financeBankMovement->save();
                    DB::commit();
                    // Limpia el cache
                    shell_exec('php artisan cache:clear');
                } catch (\Exception $e) {
                    Log::error($e->getMessage());
                    DB::rollback();
                    $this->error($e->getMessage());
                }
            }

            $this->info('Se reestructuraron los códigos de movimientos bancarios. Cantidad: ' . $count);
        } catch (\Throwable $th) {
            Log::error('Error en reestructuración de códigos de movimientos bancarios: ' . $th->getMessage());
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

    /**
     * Genera códigos a implementar en los movimientos bancarios
     *
     * @author Francisco J. P. Ruíz <fpenya@cenditel.gob.ve>
     *
     * @param  string           $prefix      Prefijo que identifica el código
     * @param  integer          $code_length Longitud máxima permitida para el código a generar
     * @param  integer|string   $year        Sufijo que identifica el año del cual se va a generar el código
     * @param  string           $model       Namespace y nombre del modelo en donde se aplicará el nuevo código
     * @param  string           $field       Nombre del campo del código a generar
     * @param  string           $order_field Nombre del campo por el cual se ordenará la secuencia de los códigos
     *
     * @return string|array                  Retorna una cadena con el nuevo código
     */
    private function customGenerateRegistrationCodeForBankMovements($prefix, $code_length, $year, $model, $field, $order_field)
    {
        $newCode = 1;

        $targetModel = $model::select('id', $field, $order_field)->where($field, 'like', "{$prefix}-%-{$year}")
                            ->withTrashed()->orderBy($order_field, 'desc')->orderBy('id', 'desc')->first();

        $newCode += ($targetModel) ? (int)explode('-', $targetModel->$field)[1] : 0;

        if (strlen((string)$newCode) > $code_length) {
            return ["error" => "El nuevo código excede la longitud permitida"];
        }

        return "{$prefix}-{$newCode}-{$year}";
    }
}
