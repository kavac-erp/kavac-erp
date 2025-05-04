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
use Modules\Finance\Models\FinanceAccountType;
use Modules\Finance\Models\FinanceBank;
use Modules\Payroll\Models\PayrollFinancial;
use Modules\Payroll\Models\PayrollStaff;
use Maatwebsite\Excel\Validators\Failure;

/**
 * @class FinancialStaffImport
 * @brief Importa un archivo de datos financieros del personal
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FinancialStaffImport implements
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
        DB::transaction(function () use ($row) {
            PayrollFinancial::updateOrCreate([
                'payroll_staff_id' => $row["id"],
            ], [
                'finance_bank_id' => $row["banco"],
                'finance_account_type_id' => $row["tipo_de_cuenta"],
                'payroll_account_number' => $row["numero_de_cuenta"],
            ]);
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
        if (isset($data["cedula_de_identidad"]) && !is_null($data["cedula_de_identidad"]) && $data["cedula_de_identidad"] != "" && $data["cedula_de_identidad"] != "null" && $data["cedula_de_identidad"] != null) {
            $usuario = PayrollStaff::where(['id_number' => $data["cedula_de_identidad"],])->first();
            if ($usuario) {
                $data["id"] = $usuario->id;
            }
        }

        if (isset($data['tipo_de_cuenta']) && $data['tipo_de_cuenta'] && !is_null($data['tipo_de_cuenta']) && trim($data['tipo_de_cuenta']) != '') {
            $modelID = false;
            $data['tipo_de_cuenta_value'] = $data['tipo_de_cuenta'];
            if (is_int($data['tipo_de_cuenta']) && $data['tipo_de_cuenta'] > 0) {
                $modelID = FinanceAccountType::where(['id' => $data['tipo_de_cuenta'],])->first();
            }
            if (!$modelID && trim($data['tipo_de_cuenta']) != '') {
                $modelID = FinanceAccountType::where(['name' => $data['tipo_de_cuenta'],])->first();
            }
            if ($modelID) {
                $data['tipo_de_cuenta'] = $modelID->id;
                $data['tipo_de_cuenta_value'] = false;
            }
        }

        if (isset($data['banco']) && $data['banco'] && !is_null($data['banco']) && trim($data['banco']) != '') {
            $modelID = false;
            $data['banco_value'] = $data['banco'];
            if (is_int($data['banco']) && $data['banco'] > 0) {
                $modelID = FinanceBank::where(['id' => $data['banco'],])->first();
            }
            if (!$modelID && trim($data['banco']) != '') {
                $modelID = FinanceBank::where(['name' => $data['banco'],])->first();
            }
            if ($modelID) {
                $data['banco'] = $modelID->id;
                $data['banco_value'] = false;
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
                'sheetName' => 'Datos Financieros'
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
            'cedula_de_identidad' => ['required'],
            'tipo_de_cuenta' => ['required'],
            'banco' => ['required'],
            'numero_de_cuenta' => ['required', 'numeric', 'digits_between:20,20'],
            'id' => ['required'],
            'banco_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(
                        'El nombre del Banco ingresado (' . strip_tags($value) .
                        ') no coincide con la lista disponible en la base de datos del sistema'
                    );
                }
            },
            'tipo_de_cuenta_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(
                        'El nombre del tipo de cuenta ingresado (' . strip_tags($value) .
                        ') no coincide con la lista disponible en la base de datos del sistema'
                    );
                }
            },
        ];
    }

    /**
     * Mensajes personalizados de validación
     *
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            '*id.required' => 'Revise si se encuentran registrados los datos Personales de este trabajador en el sistema o si la cedula es la correcta',
            '*id.unique' => 'El trabajador ya tiene registrado datos financieros.',
            '*tipo_de_cuenta.required' => 'El campo tipo de cuenta es obligatorio',
            '*banco.required' => 'El campo banco es obligatorio.',
            '*numero_de_cuenta.required' => 'El campo número de cuenta es obligatorio.',
            '*numero_de_cuenta.numeric' => 'El campo número de cuenta debe se númerico',
            '*numero_de_cuenta.digits_between' => 'El campo número de cuenta debe tener 20 dígitos',
        ];
    }
}
