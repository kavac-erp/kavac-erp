<?php

namespace Modules\Payroll\Models;

use App\Models\Parameter as BaseParameter;
use Modules\Payroll\Repositories\PayrollAssociatedParametersRepository;

/**
 * @class Parameter
 * @brief Datos de configuración de parámetros de la aplicación
 *
 * Gestiona la configuración de parámetros de la aplicación
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @property string $translate_formula Traduce el nombre del paraméetro asocido a una fórmula
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Parameter extends BaseParameter
{
    /**
     * Lista de atributos personalizados para mostrar en consultas
     *
     * @var array $appends
     */
    protected $appends = ['translate_formula'];

    /**
     * Método traduce el nombre del paramétro asocido a una fórmula
     *
     * @author Francisco J. P. Ruiz <javierrupe19@gmail.com>
     *
     * @return string
     */
    public function getTranslateFormulaAttribute()
    {
        $p_value = json_decode($this->p_value);
        $formula = '';

        if ($p_value) {
            $formula = str_replace('if', 'Si', $p_value->formula ?? '');
            $types = Parameter::where(
                [
                    'required_by' => 'payroll',
                    'active' => true,
                ]
            )->where('p_key', 'like', 'global_parameter_%')->get();
            foreach ($types as $type) {
                $jsonValue = json_decode($type->p_value);
                $formula = str_replace(
                    'parameter(' . $jsonValue->id . ')',
                    $jsonValue->name,
                    $formula
                );
            }

            $parameters = new PayrollAssociatedParametersRepository();
            $typesParameters = [
                'associatedVacation',
                'associatedWorkerFile',
                'associatedBenefit'
            ];
            foreach ($typesParameters as $typeParameter) {
                $types = $parameters->loadData($typeParameter);
                foreach ($types as $type) {
                    if (empty($type['children'])) {
                        $formula = str_replace(
                            $type['id'],
                            $type['name'],
                            $formula
                        );
                    } else {
                        foreach ($type['children'] as $children) {
                            $formula = str_replace(
                                $children['id'],
                                $children['name'],
                                $formula
                            );
                        }
                    }
                }
            }
        }

        return $formula;
    }
}
