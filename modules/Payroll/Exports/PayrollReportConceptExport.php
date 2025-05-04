<?php

namespace Modules\Payroll\Exports;

use App\Models\Source;
use App\Models\User;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Modules\Payroll\Models\PayrollConcept;

/**
 * @class PayrollReportConceptExport
 * @brief Clase que exporta el listado de conceptos
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollReportConceptExport implements WithHeadings, ShouldAutoSize, WithMapping, FromQuery
{
    use Exportable;

    /**
     * Método constructor de la clase.
     *
     * @param array $requestArray Arreglo con los datos de la consulta
     * @param User $user Datos del usuario
     * @param string $created_at Fecha de creación
     *
     * @return void
     */
    public function __construct(protected $requestArray, protected ?object $user = null, protected string $created_at = '')
    {
        //
    }

    /**
     * Colección de datos a exportar
     *
     * @return PayrollConcept|\Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $conceptIds = null;
        $conceptTypeIds = null;
        $conceptPaymentTypeIds = null;
        $all = false;

        if (isset($this->requestArray["payroll_concepts"])) {
            $conceptIds = array_column($this->requestArray["payroll_concepts"], "id");
            if (in_array('todos', $conceptIds)) {
                $all = true;
            }
        }
        if (isset($this->requestArray["payroll_concept_types"])) {
            $conceptTypeIds = array_column($this->requestArray["payroll_concept_types"], "id");
            if (in_array('todos', $conceptTypeIds)) {
                $all = true;
            }
        }
        if (isset($this->requestArray["payroll_payment_types"])) {
            $conceptPaymentTypeIds = array_column($this->requestArray["payroll_payment_types"], "id");
            if (in_array('todos', $conceptPaymentTypeIds)) {
                $all = true;
            }
        }

        if ($all != false) {
            $records = PayrollConcept::query()->orderBy('name', 'ASC');
        } else {
            $records = PayrollConcept::when($conceptIds, function ($query) use ($conceptIds) {
                $query->whereIn('id', $conceptIds);
            })
            ->when($conceptTypeIds, function ($query) use ($conceptTypeIds) {
                $query->whereIn('payroll_concept_type_id', $conceptTypeIds);
            })
            ->when($conceptPaymentTypeIds, function ($query) use ($conceptPaymentTypeIds) {
                $query->whereHas('payrollPaymentTypes', function ($query) use ($conceptPaymentTypeIds) {
                    $query->whereIn('payroll_payment_type_id', $conceptPaymentTypeIds);
                });
            })
            ->orderBy('name', 'ASC');
        }

        return $records;
    }

    /**
     * Cantidad de trozos a exportar por ID
     *
     * @return integer
     */
    public function chunkById()
    {
        return 25;
    }

    /**
     * Encabezados de la hoja
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Concepto',
            'Cuenta contable',
            'Cuenta presupuestaria',
            'Formula',
            'Tipo de concepto',
            'Tipo de nómina',
            'Beneficiario',
            'Cuenta contable del beneficiario',
            'Genera orden de pago'
        ];
    }

    /**
     * Mapeo de los datos de la hoja a exportar
     *
     * @param object|array $row datos de la fila
     *
     * @return array
     */
    public function map($row): array
    {
        $payrollPaymentTypeNames = '';

        $conceptTypeName = $row->payrollConceptType->name;

        $totalNames = count($row->payrollPaymentTypes);
        foreach ($row->payrollPaymentTypes as $key => $payrollPaymentType) {
            $payrollPaymentTypeName = $payrollPaymentType->name;
            $payrollPaymentTypeNames .= $payrollPaymentTypeName;

            if ($key < $totalNames - 1) {
                $payrollPaymentTypeNames .= ', ';
            } else {
                $payrollPaymentTypeNames .= '.';
            }
        }

        $source = Source::with('receiver.associateable')->where('sourceable_id', $row->id)
            ->where('sourceable_type', PayrollConcept::class)->first();
        $row->receiver = null;

        if ($source) {
            $text = $source->receiver->description . (!empty($source->receiver->associateable?->code)
                ? (' - ' . $source->receiver->associateable->code)
                : '');
            $row->receiver = [
                'id' => $source->receiver->id,
                'text' => $text,
                'accounting_account' => $source->receiver->associateable?->code ?? '',
                'denomination' => $source->receiver->associateable->denomination ?? ''
            ];
        }


        if ($row->receiver !== null) {
            $receiver = $row->receiver['text'];
            $receiverAccount = $row->receiver['accounting_account'] . '-' . $row->receiver['denomination'];
        }

        if ($row->accountingAccount !== null) {
            $accountingAccount = $row->accountingAccount->code . ' - ' . $row->accountingAccount->denomination;
        }

        if ($row->budgetAccount !== null) {
            $budgetAccount = $row->budgetAccount->code . ' - ' . $row->budgetAccount->denomination;
        }

        return [
            $row->name,
            $accountingAccount ?? 'No definido',
            $budgetAccount ?? 'No definido',
            $row->translate_formula ?? '',
            $conceptTypeName ?? '',
            $payrollPaymentTypeNames ?? '',
            $receiver ?? 'No definido',
            $receiverAccount ?? 'No definido',
            $row->pay_order ? 'Si' : 'No'
        ];
    }
}
