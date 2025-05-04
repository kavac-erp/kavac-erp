<?php

namespace Modules\Payroll\Imports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Modules\Accounting\Models\AccountingAccount;
use Modules\Payroll\Models\PayrollStaff;
use Modules\Payroll\Models\PayrollStaffAccount;
use Maatwebsite\Excel\Validators\Failure;

/**
 * @class StaffAccountImport
 * @brief Importa un archivo de cuentas contables del personal
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class StaffAccountImport implements
    ToModel,
    WithValidation,
    WithHeadingRow,
    SkipsOnFailure,
    SkipsEmptyRows
{
    use Importable;
    use SkipsErrors;
    use SkipsFailures;

    /**
     * Método constructor de la clase
     *
     * @param string $fileErrosPath Ruta donde se guardan los archivos de errores
     *
     * @return void
     */
    public function __construct(
        protected string $fileErrosPath,
    ) {
        //
    }

    /**
     * Modelo para importar datos
     *
     * @param array $row Arreglo de columnas a importar
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */

    public function model(array $row)
    {
        /* Datos del tipo de bien al cual asociar la información del bien */
        DB::transaction(function () use ($row) {
            // Validar cuenta contable con trabajador
            $hasAccountSavedForStaff = PayrollStaffAccount::query()
                ->where('payroll_staff_id', $row['payroll_staff_id'])
                ->where('accounting_account_id', $row['accounting_account_id'])
                ->exists();

            if (!$hasAccountSavedForStaff) {
                $payrollProfessional = PayrollStaffAccount::create(
                    [
                        'payroll_staff_id' => $row['payroll_staff_id'],
                        'accounting_account_id' => $row['accounting_account_id'],
                    ]
                );
            }
        });
    }

    /**
     * Preparar los datos para ser importados (validaciones)
     *
     * @param array $data Arreglo con los datos
     * @param integer $index Indice de la fila
     *
     * @return array
     */
    public function prepareForValidation($data, $index)
    {
        if (
            isset($data["trabajadores"]) &&
            !is_null($data["trabajadores"]) &&
            $data["trabajadores"] != "" &&
            $data["trabajadores"] != "null" &&
            $data["trabajadores"] != null
        ) {
            $staff = explode('-', $data["trabajadores"]);
            $staffIdNumber = trim($staff[0]);

            $usuario = PayrollStaff::query()
                ->where('id_number', $staffIdNumber)
                ->orWhere('passport', $staffIdNumber)
                ->first();

            if ($usuario) {
                $data['payroll_staff_id'] = $usuario->id;
            }
        }

        if (
            isset($data["cuenta_contable"]) &&
            !is_null($data["cuenta_contable"]) &&
            $data["cuenta_contable"] != "" &&
            $data["cuenta_contable"] != "null" &&
            $data["cuenta_contable"] != null
        ) {
            $account = explode('-', $data["cuenta_contable"]);
            $accountCode = trim($account[0]);
            $formattedAccountCode = explode('.', $accountCode);

            $accountingAccount = AccountingAccount::query()
                ->where('group', $formattedAccountCode[0])
                ->where('subgroup', $formattedAccountCode[1])
                ->where('item', $formattedAccountCode[2])
                ->where('generic', $formattedAccountCode[3])
                ->where('specific', $formattedAccountCode[4])
                ->where('subspecific', $formattedAccountCode[5])
                ->where('institutional', $formattedAccountCode[6])
                ->first();

            if ($accountingAccount) {
                $data['accounting_account_id'] = $accountingAccount->id;
            }
        }

        return $data;
    }

    /**
     * Callback de error de validación
     *
     * @param object $failures Arreglo columnas que fallaron en la validación
     */
    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $validationErrors = [
                'row' => $failure->row(),
                'attribute' => str_replace('_value', '', $failure->attribute()),
                'error' => $failure->errors()[0],
                'sheetName' => 'Datos Contables'
            ];
            $jsonErrors = json_encode($validationErrors);
            \Illuminate\Support\Facades\Storage::disk('temporary')->append($this->fileErrosPath, $jsonErrors);
        }
    }

    /**
     * Reglas de validación
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'trabajadores' => ['required'],
            'cuenta_contable' => ['required'],
        ];
    }
}
