<?php

namespace Modules\Warehouse\Imports;

use Modules\Warehouse\Models\WarehouseProduct;
use App\Models\MeasurementUnit;
use Illuminate\Support\Facades\Log;
use App\Models\HistoryTax;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * @class WarehouseProductImport
 * @brief Gestiona la importación de los datos de los productos
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WarehouseProductImport extends \App\Imports\DataImport implements
    ToModel,
    WithValidation,
    SkipsOnFailure
{
    /**
     * Función para importar los datos
     *
     * @param array $row Arreglo de columnas a importar
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $measurementUnit = MeasurementUnit::where("name", $row['nombre_de_la_unidad_de_medida'])->first();

        $taxName = explode(' ', $row['porcentaje_del_impuesto_aplicado_al_insumo']);

        $hTax = HistoryTax::with('tax')->whereHas('tax', function ($query) use ($taxName) {
            $query->where('name', $taxName[0]);
        })->first();

        /* Datos de los productos a importar */
        $data = [
            'name'                => $row['nombre_del_insumo'],
            'description'         => $row['descripcion_del_insumo'],
            'measurement_unit_id' => $measurementUnit ? $measurementUnit->id : null,
            'tax_id' => $hTax ? $hTax->id : null
        ];
        return WarehouseProduct::updateOrCreate(
            ['name' => $row['nombre_del_insumo']],
            $data
        );
    }

    /**
     * Reglas de validación para la importación
     *
     * @return array
     */
    public function rules(): array
    {
        return [
        'nombre_del_insumo' => [
            'required',
        ],
        'nombre_de_la_unidad_de_medida' => [
            'required',
        ],
        'descripcion_del_insumo' => [
            'required',
        ],
        ];
    }

    /**
     * Callback de error de validación.
     *
     * @param \Maatwebsite\Excel\Validators\Failure[] $failures
     *
     * @return void
     */
    public function onFailure(Failure ...$failures)
    {
        // Handle the failures how you'd like.
    }
}
