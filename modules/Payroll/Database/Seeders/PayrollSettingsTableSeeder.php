<?php

namespace Modules\Payroll\Database\Seeders;

use Doctrine\DBAL\Schema\Index;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Payroll\Models\Parameter;

class PayrollSettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
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
             /**
             * Objeto asociado al modelo Parameter
             * @var Object $parameter
             */
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
