<?php

namespace Modules\Payroll\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Payroll\Models\Payroll;
use Modules\Payroll\Models\PayrollConcept;

/**
 * @class LoadBasicPayrollStaffData
 * @brief Carga datos básicos de los empleados
 *
 * @author Francisco J. P. Ruíz <fpenya@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class LoadBasicPayrollStaffData extends Command
{
    /**
     * El nombre del comando.
     *
     * @var string $signature
     */
    protected $signature = 'module:load_basic_payroll_staff_data';

    /**
     * La descripción del comando.
     *
     * @var string $description
     */
    protected $description = 'load basic payroll staff data from the given payroll register.';

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
            $this->info("Inicio de la carga de los datos básicos del personal de nómina desde el registro de nómina proporcionado...\n");
            DB::transaction(function () {

                $payrolls = Payroll::query()
                ->whereNull('salary_tabulators')
                ->orWhereNull('concept_types')
                ->with('payrollStaffPayrolls')
                ->get();

                $index = 0;
                foreach ($payrolls as $payroll) {
                    $this->info("payroll with id - code : " . $payroll->id . " - " . $payroll->code . "\n");
                    $period_end = $payroll->payrollPaymentPeriod->end_date;
                    $concept_types = $payroll->payrollStaffPayrolls()->firstOrFail()->concept_type;

                    $conceptaAndTabulators = $this->getConceptsAndTabulators($concept_types);
                    $conceptTypes = $conceptaAndTabulators['concept_type'];
                    $salaryTabulators = $conceptaAndTabulators['salary_tabulators'];

                    $payroll->payrollStaffPayrolls->each(function ($payrollStaffPayroll) use ($period_end) {
                        $payrollStaff = $payrollStaffPayroll->payrollStaff()->first();
                        $basic_payroll_staff_data = loadBasicPayrollStaffData($payrollStaff, $period_end);
                        $payrollStaffPayroll['basic_payroll_staff_data'] = $basic_payroll_staff_data;
                        $payrollStaffPayroll->save();
                    });
                    $payroll['concept_types'] = $conceptTypes;
                    $payroll['salary_tabulators'] = $salaryTabulators;
                    $payroll->save();

                    $index++;
                }

                $this->info("Datos básicos cargados del personal de nómina desde el registro de nómina proporcionado: " . $index . "\n\n");
            });
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            $this->error($th->getMessage());
        }
    }

    /**
     * Obtiene los conceptos y tabuladores de nómina
     *
     * @param array $concept_type Tipos de conceptos
     *
     * @return array
     */
    private function getConceptsAndTabulators($concept_type)
    {
        $conceptTypes = [];
        $allConcepts = [];
        $salaryTabulators = [];
        foreach ($concept_type as $category => $concepts) {
            foreach ($concepts as $concept) {
                try {
                    $payrollConcept = PayrollConcept::query()->where('id', $concept['id'])->firstOrFail();
                    $concept['formula'] = $payrollConcept->formula;
                    $allConcepts[] = $concept;
                    $concept['formula'] = $payrollConcept->translate_formula ?? '';
                    $conceptTypes[$category][] =  $concept;
                } catch (\Throwable $th) {
                    $this->error($th->getMessage());
                    continue;
                }
            }
        }
        $salaryTabulators = getPayrollSalaryTabulators($allConcepts);

        return [
                'salary_tabulators' => $salaryTabulators,
                'concept_type' => $conceptTypes
            ];
    }
}
