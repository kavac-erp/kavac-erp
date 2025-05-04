<?php

namespace Modules\Payroll\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Payroll\Models\Parameter;

/**
 * @class PayrollSettingsTableSeeder
 * @brief Carga los datos de la configuración inicial del módulo de Talento Humano
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSettingsTableSeeder extends Seeder
{
    /**
     * Método que ejecuta el seeder e inserta los datos en la base de datos.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        DB::transaction(function () {
            Parameter::updateOrCreate(
                [
                    'p_key' => 'work_age',
                    'required_by' => 'payroll',
                    'active' => true
                ],
                [
                    'p_key' => 'work_age',
                    'p_value' => '16',
                    'required_by' => 'payroll',
                ]
            );
        });

        $parameters = Parameter::where(
            [
                'required_by' => 'payroll',
                'active'      => true,
            ]
        )->where('p_key', 'like', 'global_parameter_%')->withTrashed()->orderBy('id')->get();

        $parameter_name = false;
        $index = 0;
        if (!is_null($parameters)) {
            foreach ($parameters as $parameter) {
                $param = json_decode($parameter->p_value);

                if ($param->name == 'Numero de lunes del mes') {
                    $parameter_name = true;
                }
                $index = $param->id;
            }
        }

        //Sí el nombre != 'Numero de lunes del mes' se agrega este parametro a la tabla.
        if (!$parameter_name) {
             /* Objeto asociado al modelo Parameter */
            $payrollParameter = [
                'id'             => $index + 1,
                'name'           => 'Numero de lunes del mes',
                'description'    => '<p>variable de tipo reiniciable a cero por periodo de nómina, que representa el número de lunes del mes</p>',
                'parameter_type' => 'resettable_variable',
                'percentage'     => false,
                'value'          => '',
                'formula'        => ''
            ];
            DB::transaction(function () use ($payrollParameter) {
                Parameter::updateOrCreate(
                    [
                        'p_key'       => 'global_parameter_' . $payrollParameter['id'],
                        'required_by' => 'payroll',
                        'active'      => true
                    ],
                    [
                        'p_value'     => json_encode($payrollParameter)
                    ]
                );
            });
        }
    }
}
